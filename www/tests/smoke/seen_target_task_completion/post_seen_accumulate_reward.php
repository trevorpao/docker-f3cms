<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once __DIR__ . '/_post_seen_table.php';

tests_smoke_run(function () {
    tests_smoke_create_post_seen_table();

    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $dutyId = 0;
    $accountId = 0;
    $taskId = 0;
    $postIds = [];
    $slug = 'seen-target-post-accumulate-' . uniqid();

    $staleDutyIds = mh()->query("SELECT id FROM tbl_duty WHERE slug LIKE 'seen-target-post-accumulate-%'")->fetchAll(\PDO::FETCH_COLUMN);
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
        'display_name' => 'Seen Target Post Accumulate Smoke Member',
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
        'display_name' => 'Seen Target Post Accumulate Smoke Member',
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

    for ($index = 1; $index <= 3; $index++) {
        mh()->insert('tbl_post', [
            'status' => \F3CMS\fPost::ST_ON,
            'slug' => 'seen-target-post-' . $index . '-' . uniqid(),
            'cover' => '',
            'layout' => 'na',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $postId = (int) mh()->id();
        $postIds[] = $postId;

        mh()->insert('tbl_post_lang', [
            'lang' => 'tw',
            'parent_id' => $postId,
            'title' => 'Seen Target Post ' . $index,
            'content' => 'Seen Target Post Content ' . $index,
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
    }

    $payload['task_template']['slug'] = 'post-accumulate-reward';
    $payload['task_template']['title'] = '看完三篇 Post 可得 100 分';
    $payload['task_template']['factor'] = [
        'operator' => 'AND',
        'rules' => [
            [
                'type' => 'MEMBER_SEEN_TARGET',
                'target' => 'Post',
                'row_id' => $postIds[0],
                'threshold' => 80,
            ],
            [
                'type' => 'MEMBER_SEEN_TARGET',
                'target' => 'Post',
                'row_id' => $postIds[1],
                'threshold' => 80,
            ],
            [
                'type' => 'MEMBER_SEEN_TARGET',
                'target' => 'Post',
                'row_id' => $postIds[2],
                'threshold' => 80,
            ],
        ],
    ];

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
            throw new \RuntimeException('Expected post accumulate smoke to create a New task before seen completion.');
        }

        $first = \F3CMS\kDuty::completeTasksForSeenTarget($memberId, 'Post', $postIds[0], 'post_seen_accumulate_smoke', $memberId);
        $second = \F3CMS\kDuty::completeTasksForSeenTarget($memberId, 'Post', $postIds[1], 'post_seen_accumulate_smoke', $memberId);
        $third = \F3CMS\kDuty::completeTasksForSeenTarget($memberId, 'Post', $postIds[2], 'post_seen_accumulate_smoke', $memberId);

        $afterTask = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $account = \F3CMS\fManaccount::oneByMemberId($memberId);

        if (0 !== count($first['completed_tasks'] ?? []) || 0 !== count($second['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected first two post seen completions to stay incomplete until all three truths exist.');
        }

        if (1 !== count($third['completed_tasks'] ?? [])) {
            throw new \RuntimeException('Expected third post seen completion to complete exactly one task.');
        }

        if (1 !== count($first['skipped_tasks'] ?? []) || 1 !== count($second['skipped_tasks'] ?? [])) {
            throw new \RuntimeException('Expected first two post seen completions to report one factor_not_matched skipped task before accumulation completes.');
        }

        if ('factor_not_matched' !== ($first['skipped_tasks'][0]['reason'] ?? null) || 'factor_not_matched' !== ($second['skipped_tasks'][0]['reason'] ?? null)) {
            throw new \RuntimeException('Expected first two post seen completions to skip only because the accumulated factor is not yet matched.');
        }

        if (!empty($third['skipped_tasks'] ?? [])) {
            throw new \RuntimeException('Expected third post seen completion to avoid skipped_tasks once all three truths exist.');
        }

        if (\F3CMS\fTask::ST_DONE !== ($afterTask['status'] ?? null)) {
            throw new \RuntimeException('Expected post accumulate task to become Done after the third seen completion.');
        }

        if (100 !== (int) ($account['balance'] ?? -1)) {
            throw new \RuntimeException('Expected post accumulate reward to grant 100 points after the third seen completion.');
        }

        $postSeenCount = (int) mh()->count('tbl_post_seen', [
            'member_id' => $memberId,
            'row_id' => $postIds,
        ]);
        $memberSeenCount = (int) mh()->count('tbl_member_seen', [
            'member_id' => $memberId,
            'target' => 'Post',
            'row_id' => $postIds,
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

        if (3 !== $postSeenCount || 0 !== $memberSeenCount || 1 !== $taskLogCount || 1 !== $accountLogCount) {
            throw new \RuntimeException('Expected post accumulate path to write three truths into tbl_post_seen and preserve single task/account logs.');
        }

        return [
            'member_id' => $memberId,
            'post_ids' => $postIds,
            'duty_id' => $dutyId,
            'task_id' => $taskId,
            'first' => $first,
            'second' => $second,
            'third' => $third,
            'task' => $afterTask,
            'account' => $account,
            'post_seen_count' => $postSeenCount,
            'member_seen_count' => $memberSeenCount,
            'task_log_count' => $taskLogCount,
            'account_log_count' => $accountLogCount,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

        tests_smoke_drop_post_seen_table();

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
        if (!empty($postIds)) {
            mh()->delete('tbl_post_lang', ['parent_id' => $postIds]);
            mh()->delete('tbl_post', ['id' => $postIds]);
        }
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');