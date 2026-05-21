<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_bootstrap_f3cms();

tests_smoke_run(function () {
    $memberId = 0;
    $phonebookId = 0;
    $campaignId = 0;

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
            'display_name' => 'SMS Owner Surface Member',
            'status' => 'Enabled',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $memberId = (int) mh()->id();

        $phonebook = \F3CMS\fPhonebook::createWithPhones(
            $memberId,
            'Owner Surface Phonebook',
            ['0912345678', '+14155550123', '0912345678'],
            'Smoke',
            1
        );

        if (empty($phonebook) || !is_array($phonebook)) {
            throw new \RuntimeException('Expected owner-side phonebook surface to create a phonebook.');
        }

        $phonebookId = (int) ($phonebook['id'] ?? 0);
        if ($phonebookId <= 0) {
            throw new \RuntimeException('Expected owner-side phonebook surface to return a valid phonebook id.');
        }

        $mobileIds = \F3CMS\fPhonebook::mobileIds($phonebookId);
        if (count($mobileIds) !== 2) {
            throw new \RuntimeException('Expected owner-side phonebook surface to normalize and dedupe phones into two mobile rows.');
        }

        $mobiles = mh()->select('tbl_mobile', '*', [
            'id' => $mobileIds,
            'ORDER' => ['id' => 'ASC'],
        ]);
        $mobileNumbers = array_values(array_map(function ($row) {
            return $row['phone_number'];
        }, $mobiles));

        if (!in_array('+886912345678', $mobileNumbers, true) || !in_array('+14155550123', $mobileNumbers, true)) {
            throw new \RuntimeException('Expected owner-side mobile surface to normalize local and international numbers into E.164 values.');
        }

        $campaign = \F3CMS\fCampaign::createForPhonebook(
            $memberId,
            $phonebookId,
            'Owner surface campaign content',
            date('Y-m-d H:i:s', time() - 60),
            1
        );

        if (empty($campaign) || !is_array($campaign)) {
            throw new \RuntimeException('Expected Campaign owner to expand from phonebook created by owner-side surface.');
        }

        $campaignId = (int) ($campaign['id'] ?? 0);
        if ($campaignId <= 0 || (int) ($campaign['total_targets'] ?? 0) !== 2) {
            throw new \RuntimeException('Expected campaign expansion to create exactly two targets from owner-side phonebook data.');
        }

        $targets = $campaign['_targets'] ?? [];
        $providers = array_column($targets, 'provider_alias');
        sort($providers);
        if ($providers !== ['mitake', 'sns']) {
            throw new \RuntimeException('Expected campaign expansion to preserve +886 -> mitake and non-+886 -> sns routing from owner-side data.');
        }

        $logs = mh()->select('tbl_campaign_log', '*', [
            'campaign_id' => $campaignId,
            'ORDER' => ['mobile_id' => 'ASC'],
        ]);
        if (count($logs) !== 2) {
            throw new \RuntimeException('Expected campaign expansion to create two campaign logs from owner-side phonebook data.');
        }

        return [
            'phonebook' => $phonebook,
            'mobiles' => $mobiles,
            'campaign' => $campaign,
            'logs' => $logs,
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

        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }

        mh()->query('DROP TABLE IF EXISTS `tbl_campaign_log`');
        mh()->query('DROP TABLE IF EXISTS `tbl_campaign`');
        mh()->query('DROP TABLE IF EXISTS `tbl_phonebook_mobile`');
        mh()->query('DROP TABLE IF EXISTS `tbl_phonebook`');
        mh()->query('DROP TABLE IF EXISTS `tbl_mobile`');
    }
});