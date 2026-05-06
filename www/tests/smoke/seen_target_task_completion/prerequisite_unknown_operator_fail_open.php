<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once __DIR__ . '/_press_seen_table.php';

tests_smoke_run(function () {
    tests_smoke_create_press_seen_table();

    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $accountId = 0;
    $prerequisiteDutyId = 0;
    $dependentDutyId = 0;
    $prerequisiteTaskId = 0;
    $dependentTaskId = 0;
    $prerequisitePressId = 0;
    $dependentPressId = 0;
    $prerequisiteSlug = 'seen-target-prerequisite-unknown-operator-base-' . uniqid();
    $dependentSlug = 'seen-target-prerequisite-unknown-operator-dependent-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'seen-target-prerequisite-unknown-operator-base-%' OR slug LIKE 'seen-target-prerequisite-unknown-operator-dependent-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Seen Target Unknown Operator Member',
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
        'display_name' => 'Seen Target Unknown Operator Member',
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
        'slug' => 'seen-target-prerequisite-unknown-operator-base-press-' . uniqid(),
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $prerequisitePressId = (int) mh()->id();

    mh()->insert('tbl_press', [
        'slug' => 'seen-target-prerequisite-unknown-operator-dependent-press-' . uniqid(),
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $dependentPressId = (int) mh()->id();

    $prerequisitePayload = $payload;
    $prerequisitePayload['task_template']['slug'] = 'prerequisite-unknown-operator-base';
    $prerequisitePayload['task_template']['factor']['rules'][0]['row_id'] = $prerequisitePressId;

    mh()->insert('tbl_duty', [
        'slug' => $prerequisiteSlug,
        'claim' => json_encode($prerequisitePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $prerequisiteDutyId = (int) mh()->id();

    $dependentPayload = $payload;
    $dependentPayload['task_template']['slug'] = 'prerequisite-unknown-operator-dependent';
    $dependentPayload['task_template']['factor']['rules'][0]['row_id'] = $dependentPressId;
    $dependentPayload['task_template']['prerequisite'] = [
        'operator' => 'XOR',
        'tasks' => [
            [
                'duty_slug' => $prerequisiteSlug,
                'expected_status' => \F3CMS\fTask::ST_DONE,
            ],
        ],
    ];

    mh()->insert('tbl_duty', [
        'slug' => $dependentSlug,
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

        $prerequisiteTaskId = (int) ((\F3CMS\fTask::oneByDutyAndMember($prerequisiteDutyId, $memberId) ?: [])['id'] ?? 0);
        $dependentTaskId = (int) ((\F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId) ?: [])['id'] ?? 0);

        if ($prerequisiteTaskId <= 0 || $dependentTaskId <= 0) {
            throw new \RuntimeException('Expected unknown operator fail-open smoke to create both prerequisite and dependent tasks.');
        }

        $visiblePending = \F3CMS\fTask::pendingByMemberId($memberId);
        $visiblePendingDutyIds = array_map(static function ($task) {
            return (int) ($task['duty_id'] ?? 0);
        }, $visiblePending);

        if (!in_array($dependentDutyId, $visiblePendingDutyIds, true)) {
            throw new \RuntimeException('Expected unknown prerequisite operator to fail-open and keep dependent task visible.');
        }

        $result = \F3CMS\rPress::completeSeenForMember($memberId, [
            'id' => $dependentPressId,
            'source' => 'rPress',
        ]);

        $dependentCompleted = null;
        foreach (($result['completed_tasks'] ?? []) as $completedTask) {
            if ($dependentDutyId === (int) ($completedTask['duty_id'] ?? 0)) {
                $dependentCompleted = $completedTask;
                break;
            }
        }

        $afterPrerequisiteTask = \F3CMS\fTask::oneByDutyAndMember($prerequisiteDutyId, $memberId);
        $afterDependentTask = \F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);

        if (empty($dependentCompleted) || \F3CMS\fTask::ST_DONE !== ($dependentCompleted['status'] ?? null)) {
            throw new \RuntimeException('Expected unknown prerequisite operator to fail-open and allow dependent task completion.');
        }

        if (\F3CMS\fTask::ST_NEW !== ($afterPrerequisiteTask['status'] ?? null) || \F3CMS\fTask::ST_DONE !== ($afterDependentTask['status'] ?? null)) {
            throw new \RuntimeException('Expected unknown prerequisite operator fail-open path to preserve prerequisite task and complete only dependent task.');
        }

        if (100 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected unknown prerequisite operator fail-open path to write one reward for dependent task.');
        }

        return [
            'member_id' => $memberId,
            'prerequisite_duty_id' => $prerequisiteDutyId,
            'dependent_duty_id' => $dependentDutyId,
            'visible_pending_duty_ids' => $visiblePendingDutyIds,
            'result' => $result,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

        tests_smoke_drop_press_seen_table();

        if ($memberId > 0) {
            mh()->delete('tbl_member_seen', ['member_id' => $memberId]);
        }
        if ($prerequisiteTaskId > 0 || $dependentTaskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => [$prerequisiteTaskId, $dependentTaskId]]);
        }
        if ($prerequisiteTaskId > 0) {
            mh()->delete('tbl_task', ['id' => $prerequisiteTaskId]);
        }
        if ($dependentTaskId > 0) {
            mh()->delete('tbl_task', ['id' => $dependentTaskId]);
        }
        if ($accountId > 0) {
            mh()->delete('tbl_manaccount_log', ['parent_id' => $accountId]);
            mh()->delete('tbl_manaccount', ['id' => $accountId]);
        }
        if ($prerequisiteDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $prerequisiteDutyId]);
        }
        if ($dependentDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dependentDutyId]);
        }
        if ($prerequisitePressId > 0) {
            mh()->delete('tbl_press_lang', ['parent_id' => $prerequisitePressId]);
            mh()->delete('tbl_press', ['id' => $prerequisitePressId]);
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