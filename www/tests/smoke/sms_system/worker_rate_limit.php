<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

use F3CMS\Contracts\SmsProviderInterface;

tests_bootstrap_f3cms();

class SmsSystemRateLimitSmokeProvider implements SmsProviderInterface
{
    private static int $callCount = 0;

    public static function reset(): void
    {
        self::$callCount = 0;
    }

    public static function callCount(): int
    {
        return self::$callCount;
    }

    public function send(string $phone, string $message, array $options = []): array
    {
        self::$callCount++;

        return [
            'status' => 'sent',
            'provider' => 'mitake',
            'message_id' => 'should_not_send',
        ];
    }
}

tests_smoke_run(function () {
    $memberId = 0;
    $phonebookId = 0;
    $campaignId = 0;
    $mobileId = 0;
    $seededLastSentTs = date('Y-m-d H:i:s', time() - 60);

    SmsSystemRateLimitSmokeProvider::reset();

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
            'display_name' => 'SMS Rate Limit Smoke Member',
            'status' => 'Enabled',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $memberId = (int) mh()->id();

        mh()->insert('tbl_phonebook', [
            'member_id' => $memberId,
            'title' => 'SMS Rate Limit Phonebook ' . uniqid(),
            'remark' => 'Smoke',
            'status' => 'Enabled',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $phonebookId = (int) mh()->id();

        mh()->insert('tbl_mobile', [
            'phone_number' => '+886912345678',
            'status' => 'Active',
            'last_sent_ts' => $seededLastSentTs,
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);
        $mobileId = (int) mh()->id();

        mh()->insert('tbl_phonebook_mobile', [
            'phonebook_id' => $phonebookId,
            'mobile_id' => $mobileId,
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
        ]);

        $campaign = \F3CMS\fCampaign::createForPhonebook(
            $memberId,
            $phonebookId,
            'Worker rate limit smoke content',
            date('Y-m-d H:i:s', time() - 60),
            1
        );
        $campaignId = (int) ($campaign['id'] ?? 0);

        $fakeSender = new \F3CMS\smSender([
            'mitake' => new SmsSystemRateLimitSmokeProvider(),
        ], 'mitake', '+886', null);
        $fakeSender->enableQueue(false);
        \F3CMS\smSender::setInstance($fakeSender);

        $results = \F3CMS\kCampaign::consumePendingLogs(10, $fakeSender, 1);
        $log = mh()->get('tbl_campaign_log', '*', [
            'campaign_id' => $campaignId,
            'mobile_id' => $mobileId,
        ]);
        $campaignAfter = mh()->get('tbl_campaign', '*', ['id' => $campaignId]);
        $mobileAfter = mh()->get('tbl_mobile', '*', ['id' => $mobileId]);

        if (1 !== count($results)) {
            throw new \RuntimeException('Expected worker rate-limit smoke to process exactly one campaign log.');
        }

        $result = $results[0];
        if (($result['status'] ?? null) !== \F3CMS\fCampaign::LOG_FAILED || ($result['error_message'] ?? null) !== 'Rate Limited (5 mins)') {
            throw new \RuntimeException('Expected rate-limit worker path to return Failed with Rate Limited (5 mins).');
        }

        if (($log['status'] ?? null) !== \F3CMS\fCampaign::LOG_FAILED || ($log['error_message'] ?? null) !== 'Rate Limited (5 mins)') {
            throw new \RuntimeException('Expected campaign log to converge to Failed with Rate Limited (5 mins).');
        }

        if (empty($log['attempt_ts'])) {
            throw new \RuntimeException('Expected rate-limit worker path to still write attempt_ts.');
        }

        if (!empty($log['sent_ts']) || !empty($log['provider_message_id'])) {
            throw new \RuntimeException('Expected rate-limit worker path to keep sent_ts and provider_message_id empty.');
        }

        if (SmsSystemRateLimitSmokeProvider::callCount() !== 0) {
            throw new \RuntimeException('Expected rate-limit worker guardrail to stop before provider dispatch.');
        }

        if ((int) ($campaignAfter['sent_count'] ?? 0) !== 0 || (int) ($campaignAfter['failed_count'] ?? 0) !== 1 || ($campaignAfter['status'] ?? null) !== \F3CMS\fCampaign::ST_FAILED) {
            throw new \RuntimeException('Expected campaign summary to converge to Failed with failed_count=1 for rate limit.');
        }

        if (($mobileAfter['last_sent_ts'] ?? null) !== $seededLastSentTs) {
            throw new \RuntimeException('Expected rate-limit worker path to leave tbl_mobile.last_sent_ts unchanged.');
        }

        return [
            'result' => $result,
            'log' => $log,
            'campaign' => $campaignAfter,
            'mobile' => $mobileAfter,
            'provider_calls' => SmsSystemRateLimitSmokeProvider::callCount(),
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

        if ($mobileId > 0) {
            mh()->delete('tbl_mobile', ['id' => $mobileId]);
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