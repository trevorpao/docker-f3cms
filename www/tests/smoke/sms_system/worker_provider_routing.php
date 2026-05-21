<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

use F3CMS\Contracts\SmsProviderInterface;

tests_bootstrap_f3cms();

class SmsSystemSmokeProvider implements SmsProviderInterface
{
    private string $alias;

    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    public function send(string $phone, string $message, array $options = []): array
    {
        return [
            'status' => 'sent',
            'provider' => $this->alias,
            'message_id' => $this->alias . '_' . substr(md5($phone . $message), 0, 8),
        ];
    }
}

tests_smoke_run(function () {
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
            'display_name' => 'SMS Worker Smoke Member',
            'status' => 'Enabled',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $memberId = (int) mh()->id();

        mh()->insert('tbl_phonebook', [
            'member_id' => $memberId,
            'title' => 'SMS Worker Smoke Phonebook ' . uniqid(),
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
            'Worker smoke SMS content',
            date('Y-m-d H:i:s', time() - 60),
            1
        );
        $campaignId = (int) ($campaign['id'] ?? 0);

        $fakeSender = new \F3CMS\smSender([
            'mitake' => new SmsSystemSmokeProvider('mitake'),
            'sns' => new SmsSystemSmokeProvider('sns'),
        ], 'mitake', '+886', null);
        $fakeSender->enableQueue(false);
        \F3CMS\smSender::setInstance($fakeSender);

        $results = \F3CMS\kCampaign::consumePendingLogs(10, $fakeSender, 1);
        $logs = mh()->select('tbl_campaign_log', '*', [
            'campaign_id' => $campaignId,
            'ORDER' => ['mobile_id' => 'ASC'],
        ]);
        $campaignAfter = mh()->get('tbl_campaign', '*', ['id' => $campaignId]);

        if (2 !== count($results) || 2 !== count($logs)) {
            throw new \RuntimeException('Expected worker slice to process exactly two campaign logs.');
        }

        $providers = [];
        foreach ($logs as $log) {
            if (($log['status'] ?? null) !== \F3CMS\fCampaign::LOG_SENT) {
                throw new \RuntimeException('Expected worker slice to mark all logs as Sent in routing smoke.');
            }
            if (empty($log['sent_ts']) || empty($log['provider_message_id']) || empty($log['attempt_ts'])) {
                throw new \RuntimeException('Expected worker slice to write attempt_ts, sent_ts, and provider_message_id.');
            }
            $providers[(int) $log['mobile_id']] = $log['provider_alias'];
        }

        if (($providers[$mobileIds[0]] ?? null) !== 'mitake' || ($providers[$mobileIds[1]] ?? null) !== 'sns') {
            throw new \RuntimeException('Expected worker slice to preserve +886 -> mitake and non-+886 -> sns routing.');
        }

        if ((int) ($campaignAfter['sent_count'] ?? 0) !== 2 || (int) ($campaignAfter['failed_count'] ?? 0) !== 0 || ($campaignAfter['status'] ?? null) !== \F3CMS\fCampaign::ST_COMPLETED) {
            throw new \RuntimeException('Expected campaign summary to converge to Completed with sent_count=2 and failed_count=0.');
        }

        $mobiles = mh()->select('tbl_mobile', '*', [
            'id' => $mobileIds,
            'ORDER' => ['id' => 'ASC'],
        ]);

        foreach ($mobiles as $mobile) {
            if (empty($mobile['last_sent_ts'])) {
                throw new \RuntimeException('Expected successful worker path to update tbl_mobile.last_sent_ts.');
            }
        }

        return [
            'results' => $results,
            'logs' => $logs,
            'campaign' => $campaignAfter,
            'mobiles' => $mobiles,
        ];
    } finally {
        \F3CMS\smSender::setInstance(null);

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
});