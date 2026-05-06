<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once __DIR__ . '/_press_seen_table.php';

tests_smoke_run(function () {
    tests_smoke_create_press_seen_table();

    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $accountId = 0;
    $unmetDutyId = 0;
    $metDutyId = 0;
    $dependentDutyId = 0;
    $unmetTaskId = 0;
    $metTaskId = 0;
    $dependentTaskId = 0;
    $unmetPressId = 0;
    $metPressId = 0;
    $dependentPressId = 0;
    $unmetDutySlug = 'seen-target-prerequisite-or-duty-unmet-' . uniqid();
    $metDutySlug = 'seen-target-prerequisite-or-duty-met-' . uniqid();
    $dependentDutySlug = 'seen-target-prerequisite-or-duty-dependent-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'seen-target-prerequisite-or-duty-unmet-%' OR slug LIKE 'seen-target-prerequisite-or-duty-met-%' OR slug LIKE 'seen-target-prerequisite-or-duty-dependent-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Seen Target Prerequisite OR Duty Member',
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
        'display_name' => 'Seen Target Prerequisite OR Duty Member',
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

    foreach ([
        'unmet' => 'seen-target-prerequisite-or-duty-unmet-press-' . uniqid(),
        'met' => 'seen-target-prerequisite-or-duty-met-press-' . uniqid(),
        'dependent' => 'seen-target-prerequisite-or-duty-dependent-press-' . uniqid(),
    ] as $key => $pressSlug) {
        mh()->insert('tbl_press', [
            'slug' => $pressSlug,
            'status' => 'Published',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);

        if ('unmet' === $key) {
            $unmetPressId = (int) mh()->id();
        }
        if ('met' === $key) {
            $metPressId = (int) mh()->id();
        }
        if ('dependent' === $key) {
            $dependentPressId = (int) mh()->id();
        }
    }

    $unmetPayload = $payload;
    $unmetPayload['task_template']['slug'] = 'seen-target-prerequisite-or-duty-unmet-task-' . uniqid();
    $unmetPayload['task_template']['factor']['rules'][0]['row_id'] = $unmetPressId;

    mh()->insert('tbl_duty', [
        'slug' => $unmetDutySlug,
        'claim' => json_encode($unmetPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $unmetDutyId = (int) mh()->id();

    $metPayload = $payload;
    $metPayload['task_template']['slug'] = 'seen-target-prerequisite-or-duty-met-task-' . uniqid();
    $metPayload['task_template']['factor']['rules'][0]['row_id'] = $metPressId;

    mh()->insert('tbl_duty', [
        'slug' => $metDutySlug,
        'claim' => json_encode($metPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $metDutyId = (int) mh()->id();

    $dependentPayload = $payload;
    $dependentPayload['task_template']['slug'] = 'seen-target-prerequisite-or-duty-dependent-task-' . uniqid();
    $dependentPayload['task_template']['factor']['rules'][0]['row_id'] = $dependentPressId;
    $dependentPayload['task_template']['prerequisite'] = [
        'operator' => 'OR',
        'tasks' => [
            [
                'duty_slug' => $unmetDutySlug,
                'expected_status' => \F3CMS\fTask::ST_DONE,
            ],
            [
                'duty_slug' => $metDutySlug,
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

        $unmetTaskId = (int) ((\F3CMS\fTask::oneByDutyAndMember($unmetDutyId, $memberId) ?: [])['id'] ?? 0);
        $metTaskId = (int) ((\F3CMS\fTask::oneByDutyAndMember($metDutyId, $memberId) ?: [])['id'] ?? 0);
        $dependentTaskId = (int) ((\F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId) ?: [])['id'] ?? 0);

        if ($unmetTaskId <= 0 || $metTaskId <= 0 || $dependentTaskId <= 0) {
            throw new \RuntimeException('Expected OR duty_slug prerequisite smoke to create unmet, met, and dependent tasks.');
        }

        $beforeVisiblePending = \F3CMS\fTask::pendingByMemberId($memberId);
        $beforeVisibleDutyIds = array_map(static function ($task) {
            return (int) ($task['duty_id'] ?? 0);
        }, $beforeVisiblePending);

        if (in_array($dependentDutyId, $beforeVisibleDutyIds, true)) {
            throw new \RuntimeException('Expected OR duty_slug dependent task to stay hidden before any prerequisite is done.');
        }

        $metResult = \F3CMS\rPress::completeSeenForMember($memberId, [
            'id' => $metPressId,
            'source' => 'rPress',
        ]);

        if (1 !== count($metResult['completed_tasks'] ?? []) || $metDutyId !== (int) (($metResult['completed_tasks'][0]['duty_id'] ?? 0))) {
            throw new \RuntimeException('Expected duty_slug prerequisite target to complete its own prerequisite task first.');
        }

        $afterVisiblePending = \F3CMS\fTask::pendingByMemberId($memberId);
        $afterVisibleDutyIds = array_map(static function ($task) {
            return (int) ($task['duty_id'] ?? 0);
        }, $afterVisiblePending);

        if (!in_array($dependentDutyId, $afterVisibleDutyIds, true)) {
            throw new \RuntimeException('Expected OR duty_slug dependent task to become visible after any satisfiable prerequisite is done.');
        }

        $dependentResult = \F3CMS\rPress::completeSeenForMember($memberId, [
            'id' => $dependentPressId,
            'source' => 'rPress',
        ]);

        $dependentCompleted = null;
        foreach (($dependentResult['completed_tasks'] ?? []) as $completedTask) {
            if ($dependentDutyId === (int) ($completedTask['duty_id'] ?? 0)) {
                $dependentCompleted = $completedTask;
                break;
            }
        }

        if (empty($dependentCompleted) || \F3CMS\fTask::ST_DONE !== ($dependentCompleted['status'] ?? null)) {
            throw new \RuntimeException('Expected OR duty_slug dependent task to complete once duty_slug prerequisite is satisfied.');
        }

        $dependentTask = \F3CMS\fTask::oneByDutyAndMember($dependentDutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);
        $taskDoneLogCount = (int) mh()->count('tbl_task_log', [
            'parent_id' => [$metTaskId, $dependentTaskId],
            'action_code' => 'TASK_DONE_REWARD',
            'new_state_code' => \F3CMS\fTask::ST_DONE,
        ]);
        $accountLogCount = (int) mh()->count('tbl_manaccount_log', [
            'parent_id' => $accountId,
            'action_code' => 'TASK_DONE_REWARD',
            'delta_point' => 100,
        ]);

        if (\F3CMS\fTask::ST_DONE !== ($dependentTask['status'] ?? null)) {
            throw new \RuntimeException('Expected dependent duty_slug task row to persist as Done after OR prerequisite completion path.');
        }

        if (200 !== (int) ($account['balance'] ?? -1) || 2 !== $taskDoneLogCount || 2 !== $accountLogCount) {
            throw new \RuntimeException('Expected OR duty_slug completion path to write both prerequisite and dependent rewards exactly once.');
        }

        return [
            'member_id' => $memberId,
            'unmet_duty_id' => $unmetDutyId,
            'met_duty_id' => $metDutyId,
            'dependent_duty_id' => $dependentDutyId,
            'before_visible_duty_ids' => $beforeVisibleDutyIds,
            'after_visible_duty_ids' => $afterVisibleDutyIds,
            'met_result' => $metResult,
            'dependent_result' => $dependentResult,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

        tests_smoke_drop_press_seen_table();

        if ($memberId > 0) {
            mh()->delete('tbl_member_seen', ['member_id' => $memberId]);
        }
        if ($unmetTaskId > 0 || $metTaskId > 0 || $dependentTaskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => [$unmetTaskId, $metTaskId, $dependentTaskId]]);
        }
        if ($unmetTaskId > 0) {
            mh()->delete('tbl_task', ['id' => $unmetTaskId]);
        }
        if ($metTaskId > 0) {
            mh()->delete('tbl_task', ['id' => $metTaskId]);
        }
        if ($dependentTaskId > 0) {
            mh()->delete('tbl_task', ['id' => $dependentTaskId]);
        }
        if ($accountId > 0) {
            mh()->delete('tbl_manaccount_log', ['parent_id' => $accountId]);
            mh()->delete('tbl_manaccount', ['id' => $accountId]);
        }
        if ($unmetDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $unmetDutyId]);
        }
        if ($metDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $metDutyId]);
        }
        if ($dependentDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dependentDutyId]);
        }
        if ($unmetPressId > 0) {
            mh()->delete('tbl_press_lang', ['parent_id' => $unmetPressId]);
            mh()->delete('tbl_press', ['id' => $unmetPressId]);
        }
        if ($metPressId > 0) {
            mh()->delete('tbl_press_lang', ['parent_id' => $metPressId]);
            mh()->delete('tbl_press', ['id' => $metPressId]);
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