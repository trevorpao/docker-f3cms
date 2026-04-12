<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $dutyId = 0;
    $slug = 'event-rule-member-register-create-task-' . uniqid();

    mh()->insert('tbl_member', [
        'display_name' => 'Step 2 Smoke Member',
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);

    $memberId = (int) mh()->id();

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
        $first = \F3CMS\kDuty::createTasksForTrigger('Member::Register', $memberId, 1);
        $second = \F3CMS\kDuty::createTasksForTrigger('Member::Register', $memberId, 1);

        if (1 !== count($first)) {
            throw new \RuntimeException('Expected one task to be created for Member::Register trigger.');
        }

        if (1 !== count($second)) {
            throw new \RuntimeException('Expected one matched task on idempotent Member::Register re-trigger.');
        }

        if (empty($first[0]['created'])) {
            throw new \RuntimeException('Expected first Member::Register trigger to create a new task.');
        }

        if (!empty($second[0]['created'])) {
            throw new \RuntimeException('Expected second Member::Register trigger to reuse existing task.');
        }

        $task = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        if ((int) ($task['id'] ?? 0) <= 0) {
            throw new \RuntimeException('Expected task row to be queryable by duty and member.');
        }

        if (\F3CMS\fTask::ST_NEW !== ($task['status'] ?? null)) {
            throw new \RuntimeException('Expected created task status to remain New.');
        }

        $taskCount = (int) mh()->count('tbl_task', [
            'duty_id' => $dutyId,
            'member_id' => $memberId,
        ]);

        if (1 !== $taskCount) {
            throw new \RuntimeException('Expected Member::Register trigger path to stay idempotent at one task row.');
        }

        $taskLogCount = (int) mh()->count('tbl_task_log', [
            'parent_id' => (int) $task['id'],
            'action_code' => \F3CMS\fTask::AC_MEMBER_REGISTER_TRIGGER,
        ]);

        if (1 !== $taskLogCount) {
            throw new \RuntimeException('Expected one task log entry for Member::Register task creation.');
        }

        return [
            'member_id' => $memberId,
            'duty_id' => $dutyId,
            'task_id' => (int) $task['id'],
            'status' => $task['status'],
            'first_trigger' => $first,
            'second_trigger' => $second,
            'task_log_count' => $taskLogCount,
        ];
    } finally {
        if ($dutyId > 0 && $memberId > 0) {
            $task = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
            $taskId = (int) ($task['id'] ?? 0);

            if ($taskId > 0) {
                mh()->delete('tbl_task_log', ['parent_id' => $taskId]);
                mh()->delete('tbl_task', ['id' => $taskId]);
            }
        }

        if ($dutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dutyId]);
        }

        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');