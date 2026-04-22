<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $memberId = 0;
    $bindingId = 0;
    $taskId = 0;
    $dutyId = 0;
    $slug = 'event-rule-member-register-oauth-task-' . uniqid();
    $email = 'event-rule-google-register-' . uniqid() . '@example.test';

    mh()->query(
        'CREATE TABLE IF NOT EXISTS `tbl_member_oauth` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `member_id` int(11) NOT NULL DEFAULT 0,
            `provider` varchar(32) NOT NULL DEFAULT \'\',
            `provider_uid` varchar(191) NOT NULL DEFAULT \'\',
            `provider_email` varchar(191) NOT NULL DEFAULT \'\',
            `provider_name` varchar(191) NOT NULL DEFAULT \'\',
            `provider_avatar` varchar(255) NOT NULL DEFAULT \'\',
            `raw_profile` longtext CHARACTER SET utf8mb4 DEFAULT NULL,
            `bind_status` enum(\'Enabled\',\'Disabled\') NOT NULL DEFAULT \'Enabled\',
            `last_login_ts` timestamp NULL DEFAULT NULL,
            `last_ts` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `last_user` int(11) DEFAULT 0,
            `insert_ts` timestamp NULL DEFAULT current_timestamp(),
            `insert_user` int(11) DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_provider_uid` (`provider`,`provider_uid`),
            KEY `idx_member_id` (`member_id`),
            KEY `idx_provider_email` (`provider`,`provider_email`),
            KEY `idx_bind_status` (`bind_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT=\'Member-owned OAuth identity binding\''
    );

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
        $result = \F3CMS\kMember::registerByOauth([
            'provider' => 'Google',
            'uid' => 'google-smoke-' . uniqid(),
            'info' => [
                'email' => $email,
                'name' => 'OAuth Register Smoke',
                'image' => 'https://example.test/avatar.png',
                'email_verified' => true,
            ],
            'email_verified' => true,
        ]);

        $memberId = (int) ($result['member']['id'] ?? 0);
        if ($memberId <= 0) {
            throw new \RuntimeException('Expected registerByOauth to create a member.');
        }

        if (empty($result['is_new'])) {
            throw new \RuntimeException('Expected registerByOauth to report a new member.');
        }

        if (1 !== count($result['created_tasks'] ?? [])) {
            throw new \RuntimeException('Expected registerByOauth to create one duty task via Member::Register trigger.');
        }

        $task = \F3CMS\fTask::oneByDutyAndMember($dutyId, $memberId);
        $taskId = (int) ($task['id'] ?? 0);
        if ($taskId <= 0) {
            throw new \RuntimeException('Expected task row to be queryable after registerByOauth.');
        }

        if (\F3CMS\fTask::ST_NEW !== ($task['status'] ?? null)) {
            throw new \RuntimeException('Expected Member::Register trigger task status to remain New after registration.');
        }

        $binding = \F3CMS\fMember::oneOauthBinding('google', (string) ($result['created_tasks'][0]['task_id'] ?? '')); // force array read before cleanup
        unset($binding);

        $bindingRow = mh()->get('tbl_member_oauth', '*', [
            'member_id' => $memberId,
            'provider' => 'google',
            'provider_email' => $email,
        ]);
        $bindingId = (int) ($bindingRow['id'] ?? 0);
        if ($bindingId <= 0) {
            throw new \RuntimeException('Expected Google OAuth binding row to be created with member registration.');
        }

        $taskLogCount = (int) mh()->count('tbl_task_log', [
            'parent_id' => $taskId,
            'action_code' => \F3CMS\fTask::AC_MEMBER_REGISTER_TRIGGER,
        ]);

        if (1 !== $taskLogCount) {
            throw new \RuntimeException('Expected one task log entry for Member::Register trigger during registerByOauth.');
        }

        return [
            'member_id' => $memberId,
            'binding_id' => $bindingId,
            'duty_id' => $dutyId,
            'task_id' => $taskId,
            'created_tasks' => $result['created_tasks'],
            'task_log_count' => $taskLogCount,
        ];
    } finally {
        \F3CMS\fMember::_clearCurrent();

        if ($taskId > 0) {
            mh()->delete('tbl_task_log', ['parent_id' => $taskId]);
            mh()->delete('tbl_task', ['id' => $taskId]);
        }

        if ($bindingId > 0) {
            mh()->delete('tbl_member_oauth', ['id' => $bindingId]);
        } elseif ($memberId > 0) {
            mh()->delete('tbl_member_oauth', ['member_id' => $memberId]);
        }

        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }

        if ($dutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dutyId]);
        }
    }
}, 'tests_bootstrap_f3cms');