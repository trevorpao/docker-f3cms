<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $activeDutyId = 0;
    $expiredDutyId = 0;
    $activeTaskId = 0;
    $expiredTaskId = 0;
    $activeSlug = 'pending-active-' . uniqid();
    $expiredSlug = 'pending-expired-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'pending-active-%' OR slug LIKE 'pending-expired-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Pending Tasks Exclude Expired Smoke Member',
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $memberId = (int) mh()->id();

    $activePayload = $payload;
    $activePayload['task_template']['slug'] = 'pending-active';

    mh()->insert('tbl_duty', [
        'slug' => $activeSlug,
        'claim' => json_encode($activePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $activeDutyId = (int) mh()->id();

    $expiredPayload = $payload;
    $expiredPayload['task_template']['slug'] = 'pending-expired';
    $expiredPayload['task_template']['expire_at'] = date('Y-m-d H:i:s', strtotime('-1 day'));

    mh()->insert('tbl_duty', [
        'slug' => $expiredSlug,
        'claim' => json_encode($expiredPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $expiredDutyId = (int) mh()->id();

    try {
        $created = \F3CMS\kDuty::createTasksForTrigger('Member::Register', $memberId, 1);
        if (2 !== count($created)) {
            throw new \RuntimeException('Expected trigger task creation to create both active and expired tasks.');
        }

        $activeTask = \F3CMS\fTask::oneByDutyAndMember($activeDutyId, $memberId);
        $expiredTask = \F3CMS\fTask::oneByDutyAndMember($expiredDutyId, $memberId);
        $activeTaskId = (int) ($activeTask['id'] ?? 0);
        $expiredTaskId = (int) ($expiredTask['id'] ?? 0);

        if ($activeTaskId <= 0 || $expiredTaskId <= 0) {
            throw new \RuntimeException('Expected both active and expired task rows to exist.');
        }

        $allPending = \F3CMS\fTask::byMemberId($memberId, [\F3CMS\fTask::ST_NEW, \F3CMS\fTask::ST_CLAIMED]);
        $visiblePending = \F3CMS\fTask::pendingByMemberId($memberId);

        if (2 !== count($allPending)) {
            throw new \RuntimeException('Expected raw pending query to return both active and expired task rows.');
        }

        if (1 !== count($visiblePending)) {
            throw new \RuntimeException('Expected filtered pending query to exclude expired task rows.');
        }

        if ($activeDutyId !== (int) ($visiblePending[0]['duty_id'] ?? 0)) {
            throw new \RuntimeException('Expected filtered pending query to keep the active duty only.');
        }

        return [
            'member_id' => $memberId,
            'active_duty_id' => $activeDutyId,
            'expired_duty_id' => $expiredDutyId,
            'all_pending_count' => count($allPending),
            'visible_pending_count' => count($visiblePending),
            'visible_pending_duty_id' => (int) ($visiblePending[0]['duty_id'] ?? 0),
        ];
    } finally {
        if ($activeTaskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => $activeTaskId]);
            mh()->delete('tbl_task', ['id' => $activeTaskId]);
        }
        if ($expiredTaskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => $expiredTaskId]);
            mh()->delete('tbl_task', ['id' => $expiredTaskId]);
        }
        if ($activeDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $activeDutyId]);
        }
        if ($expiredDutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $expiredDutyId]);
        }
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');