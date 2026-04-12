<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $dutyId = 0;
    $accountId = 0;
    $taskId = 0;
    $slug = 'event-rule-member-seen-done-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'event-rule-member-seen-done-%' OR slug LIKE 'debug-step3-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Step 3 Smoke Member',
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $memberId = (int) mh()->id();

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
            throw new \RuntimeException('Expected Step 2 path to create a New task before seen completion.');
        }

        $first = \F3CMS\kDuty::completeTasksForSeenTarget($memberId, 'Press', 103, 'rPress', 1);
        $second = \F3CMS\kDuty::completeTasksForSeenTarget($memberId, 'Press', 103, 'rPress', 1);

        $afterTask = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);

        if (\F3CMS\fTask::ST_DONE !== ($afterTask['status'] ?? null)) {
            throw new \RuntimeException('Expected task to become Done after member_seen completion.');
        }

        if (100 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected member reward balance to become 100 after task completion.');
        }

        if (1 !== count($first['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected first seen write to complete exactly one task.');
        }

        if (0 !== count($second['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected second seen write to be idempotent for task completion.');
        }

        if (empty($first['seen']['_created']) || !empty($second['seen']['_created'])) {
            throw new \RuntimeException('Expected member_seen truth to be created once and reused on repeat call.');
        }

        $seenCount = (int) mh()->count('tbl_member_seen', [
            'member_id' => $memberId,
            'target' => 'Press',
            'row_id' => 103,
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
            throw new \RuntimeException('Expected seen/task/account logs to remain single-row idempotent after repeat completion.');
        }

        return [
            'member_id' => $memberId,
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
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');