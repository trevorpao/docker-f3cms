<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

$context = tests_bootstrap_f3cms();

tests_smoke_run(function () use ($context) {
    $memberId = 0;
    $phonebookId = 0;
    $campaignId = 0;
    $mobileIds = [];

    mh()->query('DROP TABLE IF EXISTS `tbl_campaign_log`');
    mh()->query('DROP TABLE IF EXISTS `tbl_campaign`');
    mh()->query('DROP TABLE IF EXISTS `tbl_phonebook_mobile`');
    mh()->query('DROP TABLE IF EXISTS `tbl_phonebook`');
    mh()->query('DROP TABLE IF EXISTS `tbl_mobile`');

    mh()->query('CREATE TABLE `tbl_mobile` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `phone_number` varchar(32) NOT NULL DEFAULT \'\',
        `status` enum(\'Active\',\'Invalid\',\'Opt-out\') NOT NULL DEFAULT \'Active\',
        `last_sent_ts` datetime DEFAULT NULL,
        `last_ts` datetime DEFAULT NULL,
        `last_user` int(11) NOT NULL DEFAULT 0,
        `insert_ts` datetime DEFAULT NULL,
        `insert_user` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_phone_number` (`phone_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    mh()->query('CREATE TABLE `tbl_phonebook` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `member_id` int(11) NOT NULL DEFAULT 0,
        `title` varchar(191) NOT NULL DEFAULT \'\',
        `remark` varchar(255) NOT NULL DEFAULT \'\',
        `status` enum(\'Enabled\',\'Disabled\') NOT NULL DEFAULT \'Enabled\',
        `last_ts` datetime DEFAULT NULL,
        `last_user` int(11) NOT NULL DEFAULT 0,
        `insert_ts` datetime DEFAULT NULL,
        `insert_user` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    mh()->query('CREATE TABLE `tbl_phonebook_mobile` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `phonebook_id` int(11) NOT NULL DEFAULT 0,
        `mobile_id` int(11) NOT NULL DEFAULT 0,
        `insert_ts` datetime DEFAULT NULL,
        `insert_user` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_phonebook_mobile` (`phonebook_id`,`mobile_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    mh()->query('CREATE TABLE `tbl_campaign` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `member_id` int(11) NOT NULL DEFAULT 0,
        `phonebook_id` int(11) NOT NULL DEFAULT 0,
        `provider_policy` varchar(64) NOT NULL DEFAULT \'TW_TO_MITAKE_ELSE_AWS\',
        `content` text DEFAULT NULL,
        `scheduled_ts` datetime DEFAULT NULL,
        `status` enum(\'Draft\',\'Queued\',\'Processing\',\'Completed\',\'PartiallyFailed\',\'Failed\') NOT NULL DEFAULT \'Draft\',
        `total_targets` int(11) NOT NULL DEFAULT 0,
        `sent_count` int(11) NOT NULL DEFAULT 0,
        `failed_count` int(11) NOT NULL DEFAULT 0,
        `last_ts` datetime DEFAULT NULL,
        `last_user` int(11) NOT NULL DEFAULT 0,
        `insert_ts` datetime DEFAULT NULL,
        `insert_user` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    mh()->query('CREATE TABLE `tbl_campaign_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `campaign_id` int(11) NOT NULL DEFAULT 0,
        `member_id` int(11) NOT NULL DEFAULT 0,
        `phonebook_id` int(11) NOT NULL DEFAULT 0,
        `mobile_id` int(11) NOT NULL DEFAULT 0,
        `provider_alias` varchar(32) NOT NULL DEFAULT \'\',
        `status` enum(\'Pending\',\'Sent\',\'Failed\',\'Skipped\') NOT NULL DEFAULT \'Pending\',
        `error_message` varchar(255) DEFAULT NULL,
        `provider_message_id` varchar(191) DEFAULT NULL,
        `scheduled_ts` datetime DEFAULT NULL,
        `sent_ts` datetime DEFAULT NULL,
        `attempt_ts` datetime DEFAULT NULL,
        `last_ts` datetime DEFAULT NULL,
        `last_user` int(11) NOT NULL DEFAULT 0,
        `insert_ts` datetime DEFAULT NULL,
        `insert_user` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_campaign_mobile` (`campaign_id`,`mobile_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    try {
        mh()->insert('tbl_member', [
            'display_name' => 'SMS Campaign Smoke Member',
            'status' => 'Enabled',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $memberId = (int) mh()->id();

        mh()->insert('tbl_phonebook', [
            'member_id' => $memberId,
            'title' => 'SMS Campaign Smoke Phonebook ' . uniqid(),
            'remark' => 'Smoke',
            'status' => 'Enabled',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $phonebookId = (int) mh()->id();

        foreach (['+886912345678', '+14155550123'] as $phoneNumber) {
            mh()->insert('tbl_mobile', [
                'phone_number' => $phoneNumber,
                'status' => 'Active',
                'insert_ts' => date('Y-m-d H:i:s'),
                'insert_user' => 1,
                'last_ts' => date('Y-m-d H:i:s'),
                'last_user' => 1,
            ]);
            $mobileId = (int) mh()->id();
            $mobileIds[] = $mobileId;

            mh()->insert('tbl_phonebook_mobile', [
                'phonebook_id' => $phonebookId,
                'mobile_id' => $mobileId,
                'insert_ts' => date('Y-m-d H:i:s'),
                'insert_user' => 1,
            ]);
        }

        $campaign = \F3CMS\fCampaign::createForPhonebook(
            $memberId,
            $phonebookId,
            'Smoke SMS campaign content',
            '2026-05-20 12:34:56',
            1
        );

        if (empty($campaign) || !is_array($campaign)) {
            throw new \RuntimeException('Expected campaign creation to return campaign payload.');
        }

        $campaignId = (int) ($campaign['id'] ?? 0);
        if ($campaignId <= 0) {
            throw new \RuntimeException('Expected campaign payload to contain a persisted id.');
        }

        if ((int) ($campaign['total_targets'] ?? 0) !== 2 || \F3CMS\fCampaign::ST_QUEUED !== ($campaign['status'] ?? null)) {
            throw new \RuntimeException('Expected campaign to be queued with exactly two targets.');
        }

        $logs = mh()->select('tbl_campaign_log', '*', [
            'campaign_id' => $campaignId,
            'ORDER' => ['mobile_id' => 'ASC'],
        ]);
        $logs = is_array($logs) ? $logs : [];

        if (2 !== count($logs)) {
            throw new \RuntimeException('Expected campaign expansion to create exactly two campaign logs.');
        }

        $aliases = [];
        foreach ($logs as $log) {
            $aliases[(int) $log['mobile_id']] = $log['provider_alias'] ?? null;

            if (\F3CMS\fCampaign::LOG_PENDING !== ($log['status'] ?? null)) {
                throw new \RuntimeException('Expected all campaign logs to start as Pending.');
            }
        }

        if ('mitake' !== ($aliases[$mobileIds[0]] ?? null) || 'sns' !== ($aliases[$mobileIds[1]] ?? null)) {
            throw new \RuntimeException('Expected +886 mobile to route to mitake and non-+886 mobile to route to sns.');
        }

        return [
            'campaign' => $campaign,
            'campaign_logs' => $logs,
            'mobile_ids' => $mobileIds,
        ];
    } finally {
        if ($campaignId > 0) {
            mh()->delete('tbl_campaign_log', ['campaign_id' => $campaignId]);
            mh()->delete('tbl_campaign', ['id' => $campaignId]);
        }

        if ($phonebookId > 0) {
            mh()->delete('tbl_phonebook_mobile', ['phonebook_id' => $phonebookId]);
            mh()->delete('tbl_phonebook', ['id' => $phonebookId]);
        }

        if (!empty($mobileIds)) {
            mh()->delete('tbl_mobile', ['id' => $mobileIds]);
        }

        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }

        mh()->query('DROP TABLE IF EXISTS `tbl_campaign_log`');
        mh()->query('DROP TABLE IF EXISTS `tbl_campaign`');
        mh()->query('DROP TABLE IF EXISTS `tbl_phonebook_mobile`');
        mh()->query('DROP TABLE IF EXISTS `tbl_phonebook`');
        mh()->query('DROP TABLE IF EXISTS `tbl_mobile`');
    }
}, function () use ($context) {
    return $context;
});