<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once __DIR__ . '/_press_seen_table.php';

tests_smoke_run(function () {
    tests_smoke_create_press_seen_table();

    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $originalSqlMode = null;
    $memberId = 0;
    $dutyId = 0;
    $accountId = 0;
    $taskId = 0;
    $pressId = 0;
    $slug = 'seen-target-cross-feed-rollback-' . uniqid();
    $pressSlug = 'seen-target-cross-feed-rollback-press-' . uniqid();
    $failingActionCode = str_repeat('ROLLBACK_ACTION_CODE_', 4);

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'seen-target-cross-feed-rollback-%'")->fetchAll(\PDO::FETCH_COLUMN);
    $staleDutyIds = array_values(array_filter(array_map('intval', is_array($staleDutyIds) ? $staleDutyIds : [])));

    if (!empty($staleDutyIds)) {
        $staleTaskIds = mh()->query('SELECT id FROM tbl_task WHERE duty_id IN (' . implode(',', $staleDutyIds) . ')')->fetchAll(\PDO::FETCH_COLUMN);
        $staleTaskIds = array_values(array_filter(array_map('intval', is_array($staleTaskIds) ? $staleTaskIds : [])));

        if (!empty($staleTaskIds)) {
            mh()->delete('tbl_task_log', [
                'parent_id' => $staleTaskIds,
            ]);
            mh()->delete('tbl_task', [
                'id' => $staleTaskIds,
            ]);
        }

        mh()->delete('tbl_duty', [
            'id' => $staleDutyIds,
        ]);
    }

    mh()->insert('tbl_member', [
        'display_name' => 'Seen Target Cross Feed Rollback Smoke Member',
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $memberId = (int) mh()->id();

    \F3CMS\fMember::_setCurrent([
        'id' => $memberId,
        'email' => '',
        'display_name' => 'Seen Target Cross Feed Rollback Smoke Member',
        'avatar' => '',
    ]);

    mh()->insert('tbl_manaccount', [
        'member_id' => $memberId,
        'balance' => 0,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $accountId = (int) mh()->id();

    mh()->insert('tbl_press', [
        'slug' => $pressSlug,
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $pressId = (int) mh()->id();

    $payload['task_template']['factor']['rules'][0]['row_id'] = $pressId;
    $payload['task_template']['reward']['action_code'] = $failingActionCode;

    mh()->insert('tbl_duty', [
        'slug' => $slug,
        'claim' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $dutyId = (int) mh()->id();

    try {
        $originalSqlMode = (string) mh()->query('SELECT @@SESSION.sql_mode')->fetchColumn();

        if (false === strpos($originalSqlMode, 'STRICT_TRANS_TABLES')) {
            $strictSqlMode = trim($originalSqlMode . ',STRICT_TRANS_TABLES', ',');
            mh()->query("SET SESSION sql_mode = " . mh()->quote($strictSqlMode));
        }

        \F3CMS\kDuty::createTasksForTrigger('Member::Register', $memberId, 1);

        $before = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $taskId = (int) ($before['id'] ?? 0);

        if ($taskId <= 0 || \F3CMS\fTask::ST_NEW !== ($before['status'] ?? null)) {
            throw new \RuntimeException('Expected cross-feed rollback smoke to create a New task before seen completion.');
        }

        $caught = null;

        try {
            \F3CMS\kDuty::completeTasksForSeenTarget($memberId, 'Press', $pressId, 'rollback_smoke', $memberId);
        } catch (\Throwable $e) {
            $caught = $e;
        }

        if (!$caught instanceof \Throwable) {
            throw new \RuntimeException('Expected cross-feed rollback smoke to throw when reward action_code exceeds log column length.');
        }

        if (false === strpos($caught->getMessage(), 'action_code')) {
            throw new \RuntimeException('Expected cross-feed rollback smoke to fail on action_code log write, but got: ' . $caught->getMessage());
        }

        $afterTask = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);

        if (\F3CMS\fTask::ST_NEW !== ($afterTask['status'] ?? null)) {
            throw new \RuntimeException('Expected rollback smoke to restore task status back to New after failure.');
        }

        if (0 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected rollback smoke to restore account balance back to 0 after failure.');
        }

        $pressSeenCount = (int) mh()->count('tbl_press_seen', [
            'member_id' => $memberId,
            'row_id' => $pressId,
        ]);
        $memberSeenCount = (int) mh()->count('tbl_member_seen', [
            'member_id' => $memberId,
            'target' => 'Press',
            'row_id' => $pressId,
        ]);
        $taskDoneLogCount = (int) mh()->count('tbl_task_log', [
            'parent_id' => $taskId,
            'action_code' => $failingActionCode,
            'new_state_code' => \F3CMS\fTask::ST_DONE,
        ]);
        $accountLogCount = (int) mh()->count('tbl_manaccount_log', [
            'parent_id' => $accountId,
            'action_code' => $failingActionCode,
        ]);

        if (0 !== $pressSeenCount || 0 !== $memberSeenCount || 0 !== $taskDoneLogCount || 0 !== $accountLogCount) {
            throw new \RuntimeException('Expected rollback smoke to remove partial press_seen/member_seen/task/account writes after failure.');
        }

        return [
            'member_id' => $memberId,
            'press_id' => $pressId,
            'duty_id' => $dutyId,
            'task_id' => $taskId,
            'caught_class' => get_class($caught),
            'caught_message' => $caught->getMessage(),
            'task' => $afterTask,
            'account' => $account,
            'press_seen_count' => $pressSeenCount,
            'member_seen_count' => $memberSeenCount,
            'task_done_log_count' => $taskDoneLogCount,
            'account_log_count' => $accountLogCount,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

        if (null !== $originalSqlMode) {
            mh()->query("SET SESSION sql_mode = " . mh()->quote($originalSqlMode));
        }

        tests_smoke_drop_press_seen_table();

        if ($memberId > 0) {
            mh()->delete('tbl_member_seen', ['member_id' => $memberId]);
        }
        if ($taskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => $taskId]);
            mh()->delete('tbl_task', ['id' => $taskId]);
        }
        if ($accountId > 0) {
            mh()->delete('tbl_manaccount_log', ['parent_id' => $accountId]);
            mh()->delete('tbl_manaccount', ['id' => $accountId]);
        }
        if ($dutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dutyId]);
        }
        if ($pressId > 0) {
            mh()->delete('tbl_press', ['id' => $pressId]);
            mh()->delete('tbl_press_lang', ['parent_id' => $pressId]);
        }
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');