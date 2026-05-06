<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once __DIR__ . '/_press_seen_table.php';

tests_smoke_run(function () {
    tests_smoke_create_press_seen_table();

    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $accountId = 0;
    $dependentDutyId = 0;
    $dependentTaskId = 0;
    $dependentPressId = 0;
    $missingTaskTemplateSlug = 'seen-target-prerequisite-missing-task-template-' . uniqid();
    $dependentDutySlug = 'seen-target-prerequisite-unresolvable-template-dependent-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'seen-target-prerequisite-unresolvable-template-dependent-%'")->fetchAll(\PDO::FETCH_COLUMN);
    $staleDutyIds = array_values(array_filter(array_map('intval', is_array($staleDutyIds) ? $staleDutyIds : [])));

    if (!empty($staleDutyIds)) {
        $staleTaskIds = mh()->query('SELECT id FROM tbl_task WHERE duty_id IN (' . implode(',', $staleDutyIds) . ')')->fetchAll(\PDO::FETCH_COLUMN);
        $staleTaskIds = array_values(array_filter(array_map('intval', is_array($staleTaskIds) ? $staleTaskIds : [])));

        if (!empty($staleTaskIds)) {
            mh()->delete('tbl_task_log', ['parent_id' => $staleTaskIds]);
            mh()->delete('tbl_task', ['id' => $staleTaskIds]);
        }

        mh()->delete('tbl_duty', ['id' => $staleDutyIds]);
    }

    mh()->insert('tbl_member', [
        'display_name' => 'Seen Target Unresolvable Template Member',
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
        'display_name' => 'Seen Target Unresolvable Template Member',
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
        'slug' => 'seen-target-prerequisite-unresolvable-template-dependent-press-' . uniqid(),
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $dependentPressId = (int) mh()->id();

    $dependentPayload = $payload;
    $dependentPayload['task_template']['slug'] = 'prerequisite-unresolvable-template-dependent';
    $dependentPayload['task_template']['factor']['rules'][0]['row_id'] = $dependentPressId;
    $dependentPayload['task_template']['prerequisite'] = [
        'operator' => 'AND',
        'tasks' => [
            [
                'task_template_slug' => $missingTaskTemplateSlug,
                'expected_status' => \F3CMS\fTask::ST_DONE,
            ],
        ],
    ];

    mh()->insert('tbl_duty', [
        'slug' => $dependentDutySlug,
        'claim' => json_encode($dependentPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $dependentDutyId = (int) mh()->id();

    try {
        \F3CMS\kDuty::createTasksForTrigger('Member::Register', $memberId, 1);

        $dependentTask = \F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId);
        $dependentTaskId = (int) ($dependentTask['id'] ?? 0);

        if ($dependentTaskId <= 0) {
            throw new \RuntimeException('Expected unresolvable task_template_slug smoke to create dependent task.');
        }

        $allPending = \F3CMS\fTask::byMemberId($memberId, [\F3CMS\fTask::ST_NEW, \F3CMS\fTask::ST_CLAIMED]);
        $visiblePending = \F3CMS\fTask::pendingByMemberId($memberId);

        if (1 !== count($allPending)) {
            throw new \RuntimeException('Expected raw pending query to keep the dependent task with unresolvable task_template_slug prerequisite.');
        }

        if (!empty($visiblePending)) {
            throw new \RuntimeException('Expected pending query to hide task with unresolvable task_template_slug prerequisite.');
        }

        $result = \F3CMS\rPress::completeSeenForMember($memberId, [
            'id' => $dependentPressId,
            'source' => 'rPress',
        ]);

        $afterDependentTask = \F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);

        if (empty($result['seen']['_created'])) {
            throw new \RuntimeException('Expected unresolvable task_template_slug completion path to preserve seen truth creation.');
        }

        if (!empty($result['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected unresolvable task_template_slug completion path to avoid completed_tasks.');
        }

        $dependentSkip = null;
        foreach (($result['skipped_tasks'] ?? []) as $skippedTask) {
            if ($dependentDutyId === (int) ($skippedTask['duty_id'] ?? 0)) {
                $dependentSkip = $skippedTask;
                break;
            }
        }

        if (empty($dependentSkip) || \F3CMS\fDuty::SK_PREREQUISITE_UNRESOLVABLE !== ($dependentSkip['reason'] ?? null)) {
            throw new \RuntimeException('Expected unresolvable task_template_slug dependent task to be skipped as prerequisite_unresolvable.');
        }

        if (\F3CMS\fTask::ST_NEW !== ($afterDependentTask['status'] ?? null)) {
            throw new \RuntimeException('Expected unresolvable task_template_slug path to preserve New status for dependent task.');
        }

        if (0 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected unresolvable task_template_slug path to avoid reward writes.');
        }

        $dependentPressSeenCount = (int) mh()->count('tbl_press_seen', [
            'member_id' => $memberId,
            'row_id' => $dependentPressId,
        ]);
        $memberSeenCount = (int) mh()->count('tbl_member_seen', [
            'member_id' => $memberId,
            'target' => 'Press',
            'row_id' => $dependentPressId,
        ]);
        $taskLogCount = (int) mh()->count('tbl_task_log', [
            'parent_id' => $dependentTaskId,
            'action_code' => 'TASK_DONE_REWARD',
            'new_state_code' => \F3CMS\fTask::ST_DONE,
        ]);
        $accountLogCount = (int) mh()->count('tbl_manaccount_log', [
            'parent_id' => $accountId,
            'action_code' => 'TASK_DONE_REWARD',
            'delta_point' => 100,
        ]);

        if (1 !== $dependentPressSeenCount || 0 !== $memberSeenCount || 0 !== $taskLogCount || 0 !== $accountLogCount) {
            throw new \RuntimeException('Expected unresolvable task_template_slug path to keep only incoming seen truth and avoid reward writes.');
        }

        return [
            'member_id' => $memberId,
            'dependent_duty_id' => $dependentDutyId,
            'dependent_task_id' => $dependentTaskId,
            'all_pending_count' => count($allPending),
            'visible_pending_count' => count($visiblePending),
            'result' => $result,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

        tests_smoke_drop_press_seen_table();

        if ($memberId > 0) {
            mh()->delete('tbl_member_seen', ['member_id' => $memberId]);
        }
        if ($dependentTaskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => $dependentTaskId]);
            mh()->delete('tbl_task', ['id' => $dependentTaskId]);
        }
        if ($accountId > 0) {
            mh()->delete('tbl_manaccount_log', ['parent_id' => $accountId]);
            mh()->delete('tbl_manaccount', ['id' => $accountId]);
        }
        if ($dependentDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dependentDutyId]);
        }
        if ($dependentPressId > 0) {
            mh()->delete('tbl_press_lang', ['parent_id' => $dependentPressId]);
            mh()->delete('tbl_press', ['id' => $dependentPressId]);
        }
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');