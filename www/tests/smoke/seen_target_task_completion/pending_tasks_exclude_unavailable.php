<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $dutyId = 0;
    $taskId = 0;
    $pressId = 0;
    $slug = 'pending-unavailable-' . uniqid();
    $pressSlug = 'pending-unavailable-press-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'pending-unavailable-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Pending Tasks Exclude Unavailable Smoke Member',
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $memberId = (int) mh()->id();

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

        $task = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $taskId = (int) ($task['id'] ?? 0);

        if ($taskId <= 0 || \F3CMS\fTask::ST_NEW !== ($task['status'] ?? null)) {
            throw new \RuntimeException('Expected unavailable pending smoke to create a New task before visibility filtering.');
        }

        mh()->update('tbl_press', [
            'status' => \F3CMS\fPress::ST_OFFLINED,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ], [
            'id' => $pressId,
        ]);

        $allPending = \F3CMS\fTask::byMemberId($memberId, [\F3CMS\fTask::ST_NEW, \F3CMS\fTask::ST_CLAIMED]);
        $visiblePending = \F3CMS\fTask::pendingByMemberId($memberId);

        if (1 !== count($allPending)) {
            throw new \RuntimeException('Expected raw pending query to keep the unavailable task row.');
        }

        if (0 !== count($visiblePending)) {
            throw new \RuntimeException('Expected pending query to exclude target_unavailable task rows.');
        }

        return [
            'member_id' => $memberId,
            'duty_id' => $dutyId,
            'task_id' => $taskId,
            'all_pending_count' => count($allPending),
            'visible_pending_count' => count($visiblePending),
        ];
    } finally {
        if ($taskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => $taskId]);
            mh()->delete('tbl_task', ['id' => $taskId]);
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