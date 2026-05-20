#!/usr/bin/env php
<?php

require_once __DIR__ . '/../www/f3cms/vendor/autoload.php';
require_once __DIR__ . '/../www/f3cms/libs/Autoload.php';
require_once __DIR__ . '/../www/f3cms/libs/Utils.php';

$f3 = \Base::instance();
require __DIR__ . '/../www/f3cms/config.php';

$options = \F3CMS\kCrontab::parseDailySecurityCheckArguments($argv);
if (!empty($options['help'])) {
    fwrite(STDOUT, \F3CMS\kCrontab::dailySecurityCheckUsage(basename(__FILE__)) . PHP_EOL);
    exit(0);
}

exit(\F3CMS\kCrontab::runDailySecurityCheckCli($options));