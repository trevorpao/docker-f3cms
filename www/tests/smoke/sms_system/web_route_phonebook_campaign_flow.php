<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_bootstrap_f3cms();

const SMS_SYSTEM_WEB_ROUTE_INTERNAL_BASE_URL = 'https://web-server/api/';
const SMS_SYSTEM_WEB_ROUTE_HOST = 'loc.f3cms.com';

function sms_system_web_route_post($module, $method, array $payload, array $headers = [])
{
    $requestHeaders = array_merge([
        'Host: ' . SMS_SYSTEM_WEB_ROUTE_HOST,
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json',
    ], $headers);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $requestHeaders) . "\r\n",
            'content' => http_build_query($payload),
            'ignore_errors' => true,
            'timeout' => 20,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $path = $module . '/' . $method;
    $body = file_get_contents(SMS_SYSTEM_WEB_ROUTE_INTERNAL_BASE_URL . $path, false, $context);
    $headers = $http_response_header ?? [];

    if (false === $body) {
        throw new \RuntimeException('Failed to reach web route: /api/' . $path);
    }

    $status = 0;
    if (!empty($headers[0]) && preg_match('/^HTTP\/\S+\s+(\d{3})/', $headers[0], $matches)) {
        $status = (int) $matches[1];
    }

    $decoded = json_decode($body, true);
    if (JSON_ERROR_NONE !== json_last_error() || !is_array($decoded)) {
        throw new \RuntimeException('Expected JSON response from web route /api/' . $path . ', got: ' . substr(trim($body), 0, 400));
    }

    return [
        'path' => '/api/' . $path,
        'status' => $status,
        'headers' => $headers,
        'body' => $decoded,
    ];
}

function sms_system_assert_api_envelope(array $response)
{
    if (($response['status'] ?? 0) !== 200) {
        throw new \RuntimeException('Expected HTTP 200 from ' . ($response['path'] ?? 'unknown route') . '.');
    }

    $body = $response['body'] ?? [];
    if (!is_array($body) || !array_key_exists('code', $body)) {
        throw new \RuntimeException('Expected response envelope with code for ' . ($response['path'] ?? 'unknown route') . '.');
    }

    if (!array_key_exists('csrf', $body) || '' === trim((string) $body['csrf'])) {
        throw new \RuntimeException('Expected response envelope with csrf for ' . ($response['path'] ?? 'unknown route') . '.');
    }
}

function sms_system_assert_api_success(array $response, $message)
{
    sms_system_assert_api_envelope($response);

    if ((int) (($response['body']['code'] ?? 0)) !== 1) {
        $errorMessage = $response['body']['data']['msg'] ?? 'no error message';
        throw new \RuntimeException($message . ' Route: ' . ($response['path'] ?? 'unknown route') . '. API code: ' . (int) ($response['body']['code'] ?? 0) . '. Message: ' . $errorMessage);
    }
}

function sms_system_find_item_by_id(array $items, $id)
{
    $id = (int) $id;

    foreach ($items as $item) {
        if ((int) ($item['id'] ?? 0) === $id) {
            return $item;
        }
    }

    return null;
}

tests_smoke_run(function () {
    $memberId = (int) f3()->get('DEV_MEMBER_ID');
    $devToken = trim((string) f3()->get('DEV_TOKEN'));
    $phonebookId = 0;
    $campaignId = 0;

    if ($memberId <= 0) {
        throw new \RuntimeException('Expected DEV_MEMBER_ID to be configured for HTTP_MOBILE_TOKEN smoke validation.');
    }

    if ('' === $devToken) {
        throw new \RuntimeException('Expected DEV_TOKEN to be configured for HTTP_MOBILE_TOKEN smoke validation.');
    }

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
            'id' => $memberId,
            'display_name' => 'SMS Web Route Flow Member',
            'status' => 'Enabled',
            'insert_ts' => date('Y-m-d H:i:s'),
            'insert_user' => 1,
            'last_ts' => date('Y-m-d H:i:s'),
            'last_user' => 1,
        ]);

        $authHeaders = [
            'Mobile-Token: ' . $devToken,
        ];

        $phonebookResponse = sms_system_web_route_post('phonebook', 'create_with_phones', [
            'title' => 'Web Route Flow Phonebook',
            'phones' => ['0912345678', '+14155550123', '0912345678'],
            'remark' => 'Smoke',
        ], $authHeaders);

        sms_system_assert_api_success($phonebookResponse, 'Expected web route phonebook creation to succeed.');

        $phonebook = $phonebookResponse['body']['data'] ?? [];
        $phonebookId = (int) ($phonebook['id'] ?? 0);
        if ($phonebookId <= 0) {
            throw new \RuntimeException('Expected web route phonebook creation to return a valid phonebook id.');
        }

        $mobileIds = \F3CMS\fPhonebook::mobileIds($phonebookId);
        if (count($mobileIds) !== 2) {
            throw new \RuntimeException('Expected web route phonebook creation to dedupe duplicate phones into two mobile rows.');
        }

        if ((int) ($phonebook['member_id'] ?? 0) !== $memberId) {
            throw new \RuntimeException('Expected phonebook creation to resolve member context from HTTP_MOBILE_TOKEN session.');
        }

        if ((int) ($phonebook['insert_user'] ?? -1) !== 0) {
            throw new \RuntimeException('Expected member-facing phonebook route to keep staff audit insert_user at 0.');
        }

        $campaignResponse = sms_system_web_route_post('campaign', 'create_from_phonebook', [
            'phonebook_id' => $phonebookId,
            'content' => 'Web route request flow campaign content',
            'scheduled_ts' => date('Y-m-d H:i:s', time() - 60),
        ], $authHeaders);

        sms_system_assert_api_success($campaignResponse, 'Expected web route campaign creation to succeed.');

        $campaign = $campaignResponse['body']['data'] ?? [];
        $campaignId = (int) ($campaign['id'] ?? 0);
        if ($campaignId <= 0 || (int) ($campaign['total_targets'] ?? 0) !== 2 || ($campaign['status'] ?? null) !== \F3CMS\fCampaign::ST_QUEUED) {
            throw new \RuntimeException('Expected web route campaign creation to return a queued campaign with two targets.');
        }

        if ((int) ($campaign['member_id'] ?? 0) !== $memberId) {
            throw new \RuntimeException('Expected campaign creation to resolve member context from HTTP_MOBILE_TOKEN session.');
        }

        if ((int) ($campaign['insert_user'] ?? -1) !== 0) {
            throw new \RuntimeException('Expected member-facing campaign route to keep staff audit insert_user at 0.');
        }

        $phonebookMineResponse = sms_system_web_route_post('phonebook', 'mine', [], $authHeaders);
        sms_system_assert_api_success($phonebookMineResponse, 'Expected phonebook mine route to succeed.');

        $phonebookMineItem = sms_system_find_item_by_id($phonebookMineResponse['body']['data']['subset'] ?? [], $phonebookId);
        if (empty($phonebookMineItem) || (int) ($phonebookMineItem['member_id'] ?? 0) !== $memberId) {
            throw new \RuntimeException('Expected phonebook mine route to include the current member phonebook.');
        }

        $campaignMineResponse = sms_system_web_route_post('campaign', 'mine', [], $authHeaders);
        sms_system_assert_api_success($campaignMineResponse, 'Expected campaign mine route to succeed.');

        $campaignMineItem = sms_system_find_item_by_id($campaignMineResponse['body']['data']['subset'] ?? [], $campaignId);
        if (empty($campaignMineItem) || (int) ($campaignMineItem['member_id'] ?? 0) !== $memberId) {
            throw new \RuntimeException('Expected campaign mine route to include the current member campaign.');
        }

        $logs = mh()->select('tbl_campaign_log', '*', [
            'campaign_id' => $campaignId,
            'ORDER' => ['mobile_id' => 'ASC'],
        ]);

        if (count($logs) !== 2) {
            throw new \RuntimeException('Expected web route flow to create two campaign logs.');
        }

        $providers = array_column($logs, 'provider_alias');
        sort($providers);
        if ($providers !== ['mitake', 'sns']) {
            throw new \RuntimeException('Expected web route flow to preserve provider routing aliases.');
        }

        foreach ($logs as $log) {
            if (($log['status'] ?? null) !== \F3CMS\fCampaign::LOG_PENDING) {
                throw new \RuntimeException('Expected web route flow to initialize all logs as Pending.');
            }
        }

        return [
            'auth_header' => 'Mobile-Token',
            'phonebook_response' => $phonebookResponse['body'],
            'phonebook_mine_response' => $phonebookMineResponse['body'],
            'campaign_response' => $campaignResponse['body'],
            'campaign_mine_response' => $campaignMineResponse['body'],
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