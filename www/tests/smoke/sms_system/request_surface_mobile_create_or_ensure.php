<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_bootstrap_f3cms();

tests_smoke_run(function () {
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

    try {
        $first = \F3CMS\rMobile::createOrEnsureRequest([
            'phone' => '0912345678',
            'insert_user' => 1,
        ]);

        if ((int) ($first['code'] ?? 0) !== 1) {
            throw new \RuntimeException('Expected mobile request surface to create a mobile row from local phone format.');
        }

        $firstRow = $first['data'] ?? [];
        $firstId = (int) ($firstRow['id'] ?? 0);
        if ($firstId <= 0 || ($firstRow['phone_number'] ?? null) !== '+886912345678') {
            throw new \RuntimeException('Expected mobile request surface to normalize local number into +886912345678.');
        }

        $second = \F3CMS\rMobile::createOrEnsureRequest([
            'phone' => '+886912345678',
            'insert_user' => 1,
        ]);

        if ((int) ($second['code'] ?? 0) !== 1) {
            throw new \RuntimeException('Expected mobile request surface to ensure an existing mobile row from E.164 format.');
        }

        $secondRow = $second['data'] ?? [];
        $secondId = (int) ($secondRow['id'] ?? 0);
        if ($secondId !== $firstId) {
            throw new \RuntimeException('Expected mobile request surface to reuse the same mobile row for equivalent normalized numbers.');
        }

        $rows = mh()->select('tbl_mobile', '*', [
            'ORDER' => ['id' => 'ASC'],
        ]);
        if (count($rows) !== 1) {
            throw new \RuntimeException('Expected mobile request surface to leave only one deduplicated mobile row.');
        }

        return [
            'first' => $first,
            'second' => $second,
            'rows' => $rows,
        ];
    } finally {
        mh()->query('DROP TABLE IF EXISTS `tbl_mobile`');
    }
});