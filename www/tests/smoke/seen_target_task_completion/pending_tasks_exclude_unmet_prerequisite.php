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
    $prerequisiteSlug = 'seen-target-prerequisite-base-' . uniqid();
    $dependentSlug = 'seen-target-prerequisite-dependent-' . uniqid();
    $prerequisitePressSlug = 'seen-target-prerequisite-press-' . uniqid();
    $dependentPressSlug = 'seen-target-dependent-press-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'seen-target-prerequisite-base-%' OR slug LIKE 'seen-target-prerequisite-dependent-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Seen Target Prerequisite Smoke Member',
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
        'display_name' => 'Seen Target Prerequisite Smoke Member',
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
        'slug' => $prerequisitePressSlug,
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $prerequisitePressId = (int) mh()->id();

    mh()->insert('tbl_press', [
        'slug' => $dependentPressSlug,
        'status' => 'Published',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $dependentPressId = (int) mh()->id();

    $prerequisitePayload = $payload;
    $prerequisitePayload['task_template']['slug'] = 'prerequisite-base';
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
    $dependentPayload['task_template']['slug'] = 'prerequisite-dependent';
    $dependentPayload['task_template']['factor']['rules'][0]['row_id'] = $dependentPressId;
    $dependentPayload['task_template']['prerequisite'] = [
        'operator' => 'AND',
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

        $prerequisiteTask = \F3CMS\fTask::oneByDutyAndMember($prerequisiteDutyId, $memberId);
        $dependentTask = \F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId);
        $prerequisiteTaskId = (int) ($prerequisiteTask['id'] ?? 0);
        $dependentTaskId = (int) ($dependentTask['id'] ?? 0);

        if ($prerequisiteTaskId <= 0 || $dependentTaskId <= 0) {
            throw new \RuntimeException('Expected unmet prerequisite smoke to create both prerequisite and dependent tasks.');
        }

        $allPending = \F3CMS\fTask::byMemberId($memberId, [\F3CMS\fTask::ST_NEW, \F3CMS\fTask::ST_CLAIMED]);
        $visiblePending = \F3CMS\fTask::pendingByMemberId($memberId);

        if (2 !== count($allPending)) {
            throw new \RuntimeException('Expected raw pending query to keep both prerequisite-related tasks.');
        }

        if (1 !== count($visiblePending) || $prerequisiteDutyId !== (int) ($visiblePending[0]['duty_id'] ?? 0)) {
            throw new \RuntimeException('Expected pending query to exclude unmet prerequisite task and keep only prerequisite base task.');
        }

        $result = \F3CMS\rPress::completeSeenForMember($memberId, [
            'id' => $dependentPressId,
            'source' => 'rPress',
        ]);

        $afterPrerequisiteTask = \F3CMS\fTask::oneByDutyAndMember($prerequisiteDutyId, $memberId);
        $afterDependentTask = \F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);

        if (empty($result['seen']['_created'])) {
            throw new \RuntimeException('Expected unmet prerequisite completion path to preserve seen truth creation for the incoming event.');
        }

        if (!empty($result['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected unmet prerequisite completion path to avoid completed_tasks.');
        }

        $dependentSkip = null;
        foreach (($result['skipped_tasks'] ?? []) as $skippedTask) {
            if ($dependentDutyId === (int) ($skippedTask['duty_id'] ?? 0)) {
                $dependentSkip = $skippedTask;
                break;
            }
        }

        if (empty($dependentSkip) || \F3CMS\fDuty::SK_PREREQUISITE_UNMET !== ($dependentSkip['reason'] ?? null)) {
            throw new \RuntimeException('Expected unmet prerequisite dependent task to be skipped as prerequisite_unmet.');
        }

        if (\F3CMS\fTask::ST_NEW !== ($afterPrerequisiteTask['status'] ?? null) || \F3CMS\fTask::ST_NEW !== ($afterDependentTask['status'] ?? null)) {
            throw new \RuntimeException('Expected unmet prerequisite path to preserve New status for both tasks.');
        }

        if (0 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected unmet prerequisite path to avoid reward writes.');
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
            'parent_id' => [$prerequisiteTaskId, $dependentTaskId],
            'action_code' => 'TASK_DONE_REWARD',
            'new_state_code' => \F3CMS\fTask::ST_DONE,
        ]);
        $accountLogCount = (int) mh()->count('tbl_manaccount_log', [
            'parent_id' => $accountId,
            'action_code' => 'TASK_DONE_REWARD',
            'delta_point' => 100,
        ]);

        if (1 !== $dependentPressSeenCount || 0 !== $memberSeenCount || 0 !== $taskLogCount || 0 !== $accountLogCount) {
            throw new \RuntimeException('Expected unmet prerequisite path to keep only incoming seen truth and avoid task/account reward writes.');
        }

        return [
            'member_id' => $memberId,
            'prerequisite_duty_id' => $prerequisiteDutyId,
            'dependent_duty_id' => $dependentDutyId,
            'prerequisite_task_id' => $prerequisiteTaskId,
            'dependent_task_id' => $dependentTaskId,
            'all_pending_count' => count($allPending),
            'visible_pending_count' => count($visiblePending),
            'result' => $result,
            'dependent_press_seen_count' => $dependentPressSeenCount,
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
            mh()->delete('tbl_press', ['id' => $prerequisitePressId]);
            mh()->delete('tbl_press_lang', ['parent_id' => $prerequisitePressId]);
        }
        if ($dependentPressId > 0) {
            mh()->delete('tbl_press', ['id' => $dependentPressId]);
            mh()->delete('tbl_press_lang', ['parent_id' => $dependentPressId]);
        }
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');