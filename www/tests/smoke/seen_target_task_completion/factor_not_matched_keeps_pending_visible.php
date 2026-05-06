<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once __DIR__ . '/_press_seen_table.php';

tests_smoke_run(function () {
    tests_smoke_create_press_seen_table();

    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $dutyId = 0;
    $accountId = 0;
    $taskId = 0;
    $targetPressId = 0;
    $unrelatedPressId = 0;
    $dutySlug = 'seen-target-factor-not-matched-' . uniqid();
    $targetPressSlug = 'seen-target-factor-match-target-' . uniqid();
    $unrelatedPressSlug = 'seen-target-factor-match-other-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'seen-target-factor-not-matched-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Seen Target Factor Not Matched Smoke Member',
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
        'display_name' => 'Seen Target Factor Not Matched Smoke Member',
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
        'slug' => $targetPressSlug,
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $targetPressId = (int) mh()->id();

    mh()->insert('tbl_press', [
        'slug' => $unrelatedPressSlug,
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $unrelatedPressId = (int) mh()->id();

    $payload['task_template']['factor']['rules'][0]['row_id'] = $targetPressId;

    mh()->insert('tbl_duty', [
        'slug' => $dutySlug,
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

        $beforeTask = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $taskId = (int) ($beforeTask['id'] ?? 0);
        $pendingBefore = \F3CMS\fTask::pendingByMemberId($memberId);

        if ($taskId <= 0 || \F3CMS\fTask::ST_NEW !== ($beforeTask['status'] ?? null)) {
            throw new \RuntimeException('Expected factor_not_matched smoke to create a New task before seen completion.');
        }

        if (1 !== count($pendingBefore)) {
            throw new \RuntimeException('Expected pending query to keep the unmatched task visible before unrelated seen.');
        }

        $result = \F3CMS\rPress::completeSeenForMember($memberId, [
            'id' => $unrelatedPressId,
            'source' => 'rPress',
        ]);

        $afterTask = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);
        $pendingAfter = \F3CMS\fTask::pendingByMemberId($memberId);

        if (empty($result['seen']['_created'])) {
            throw new \RuntimeException('Expected unrelated seen to preserve legal seen truth creation before factor evaluation.');
        }

        if (!empty($result['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected unrelated seen to avoid completed_tasks when factor does not match.');
        }

        if (!empty($result['short_circuited_tasks'] ?? [])) {
            throw new \RuntimeException('Expected unrelated seen to avoid short-circuited tasks before any completion.');
        }

        if (1 !== count($result['skipped_tasks'] ?? [])) {
            throw new \RuntimeException('Expected unrelated seen to return exactly one skipped task.');
        }

        if ('factor_not_matched' !== ($result['skipped_tasks'][0]['reason'] ?? null)) {
            throw new \RuntimeException('Expected unrelated seen skip reason to be factor_not_matched.');
        }

        if ((int) ($result['skipped_tasks'][0]['duty_id'] ?? 0) !== $dutyId) {
            throw new \RuntimeException('Expected factor_not_matched skip to belong to the configured duty.');
        }

        if (\F3CMS\fTask::ST_NEW !== ($afterTask['status'] ?? null)) {
            throw new \RuntimeException('Expected factor_not_matched path to preserve New task status.');
        }

        if (1 !== count($pendingAfter)) {
            throw new \RuntimeException('Expected pending query to keep the task visible after unrelated seen.');
        }

        if ((int) ($pendingAfter[0]['id'] ?? 0) !== $taskId) {
            throw new \RuntimeException('Expected pending query to keep the same task visible after unrelated seen.');
        }

        if (0 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected factor_not_matched path to avoid reward writes.');
        }

        $targetPressSeenCount = (int) mh()->count('tbl_press_seen', [
            'member_id' => $memberId,
            'row_id' => $targetPressId,
        ]);
        $unrelatedPressSeenCount = (int) mh()->count('tbl_press_seen', [
            'member_id' => $memberId,
            'row_id' => $unrelatedPressId,
        ]);
        $memberSeenCount = (int) mh()->count('tbl_member_seen', [
            'member_id' => $memberId,
            'target' => 'Press',
            'row_id' => $unrelatedPressId,
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

        if (0 !== $targetPressSeenCount || 1 !== $unrelatedPressSeenCount || 0 !== $memberSeenCount || 0 !== $taskLogCount || 0 !== $accountLogCount) {
            throw new \RuntimeException('Expected factor_not_matched path to write only unrelated press_seen truth and avoid member_seen/task/reward writes.');
        }

        return [
            'member_id' => $memberId,
            'target_press_id' => $targetPressId,
            'unrelated_press_id' => $unrelatedPressId,
            'duty_id' => $dutyId,
            'task_id' => $taskId,
            'pending_before_count' => count($pendingBefore),
            'pending_after_count' => count($pendingAfter),
            'result' => $result,
            'task' => $afterTask,
            'account' => $account,
            'target_press_seen_count' => $targetPressSeenCount,
            'unrelated_press_seen_count' => $unrelatedPressSeenCount,
            'member_seen_count' => $memberSeenCount,
            'task_log_count' => $taskLogCount,
            'account_log_count' => $accountLogCount,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

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
        if ($targetPressId > 0) {
            mh()->delete('tbl_press', ['id' => $targetPressId]);
            mh()->delete('tbl_press_lang', ['parent_id' => $targetPressId]);
        }
        if ($unrelatedPressId > 0) {
            mh()->delete('tbl_press', ['id' => $unrelatedPressId]);
            mh()->delete('tbl_press_lang', ['parent_id' => $unrelatedPressId]);
        }
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');