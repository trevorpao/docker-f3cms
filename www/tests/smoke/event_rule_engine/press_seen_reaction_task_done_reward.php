<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $dutyId = 0;
    $accountId = 0;
    $taskId = 0;
    $pressId = 0;
    $slug = 'event-rule-press-seen-reaction-' . uniqid();
    $pressSlug = 'event-rule-press-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'event-rule-press-seen-reaction-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Press Seen Reaction Smoke Member',
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
        'display_name' => 'Press Seen Reaction Smoke Member',
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
        \F3CMS\kDuty::createTasksForTrigger('Member::Register', $memberId, 1);

        $before = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $taskId = (int) ($before['id'] ?? 0);

        if ($taskId <= 0 || \F3CMS\fTask::ST_NEW !== ($before['status'] ?? null)) {
            throw new \RuntimeException('Expected Step 2 path to create a New task before rPress seen completion.');
        }

        $first = \F3CMS\rPress::completeSeenForMember($memberId, [
            'id' => $pressId,
            'source' => 'rPress',
        ]);
        $second = \F3CMS\rPress::completeSeenForMember($memberId, [
            'slug' => $pressSlug,
            'source' => 'rPress',
        ]);

        $afterTask = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);

        if (\F3CMS\fTask::ST_DONE !== ($afterTask['status'] ?? null)) {
            throw new \RuntimeException('Expected task to become Done after rPress seen completion.');
        }

        if (100 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected member reward balance to become 100 after rPress seen completion.');
        }

        if (1 !== count($first['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected first rPress seen completion to complete exactly one task.');
        }

        if (0 !== count($second['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected repeat rPress seen completion to stay idempotent.');
        }

        if (empty($first['seen']['_created']) || !empty($second['seen']['_created'])) {
            throw new \RuntimeException('Expected member_seen truth to be created once and reused across rPress requests.');
        }

        $seenCount = (int) mh()->count('tbl_member_seen', [
            'member_id' => $memberId,
            'target' => 'Press',
            'row_id' => $pressId,
        ]);
        $taskLogCount = (int) mh()->count('tbl_task_log', [
            'parent_id' => $taskId,
            'action_code' => 'TASK_DONE_REWARD',
            'new_state_code' => \F3CMS\fTask::ST_DONE,
        ]);
        $accountLogCount = (int) mh()->count('tbl_manaccount_log', [
            'parent_id' => $accountId,
            'action_code' => 'TASK_DONE_REWARD',
            'delta_point' => 100,
        ]);

        if (1 !== $seenCount || 1 !== $taskLogCount || 1 !== $accountLogCount) {
            throw new \RuntimeException('Expected seen/task/account logs to remain single-row idempotent after repeat rPress completion.');
        }

        return [
            'member_id' => $memberId,
            'press_id' => $pressId,
            'duty_id' => $dutyId,
            'task_id' => $taskId,
            'first' => $first,
            'second' => $second,
            'task' => $afterTask,
            'account' => $account,
            'seen_count' => $seenCount,
            'task_log_count' => $taskLogCount,
            'account_log_count' => $accountLogCount,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

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