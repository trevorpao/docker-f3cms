<?php

if (PHP_SAPI != 'cli') {
    die('Only in cli mode');
}

set_time_limit(0);

$rootDir = __DIR__ . '/../';

require_once $rootDir .'f3cms/vendor/autoload.php';
require_once $rootDir .'f3cms/libs/Autoload.php';
require_once $rootDir .'f3cms/libs/Utils.php';

$f3 = \Base::instance();

// config
require $rootDir .'f3cms/config.php';

$f3->set('TEMP', $rootDir .'tmp/');
$f3->set('LOGS', $f3->get('TEMP').'logs/');
$f3->set('UI', $rootDir .'f3cms/theme/');

$logger = new \Log('cronjob.log');
$f3->set('cliLogger', $logger);

$logger->write('Info - 新排程');

$f3->set('opts', \F3CMS\fOption::load('', 'Preload'));

// Define routes
$f3->route('GET /', function ($f3, $args) {
    echo PHP_EOL.'**'. $f3->get('site_title') .' CLI**'.PHP_EOL.PHP_EOL;
});

$f3->route('GET /@freq/@tally', '\F3CMS\rCrontab->do_job');

$f3->run();

