<?php

function tests_bootstrap_f3cms()
{
    static $context = null;

    if (null !== $context) {
        return $context;
    }

    $rootDir = dirname(__DIR__, 3) . '/';

    require_once $rootDir . 'f3cms/vendor/autoload.php';
    require_once $rootDir . 'f3cms/libs/Autoload.php';
    require_once $rootDir . 'f3cms/libs/Utils.php';

    $f3 = \Base::instance();

    require $rootDir . 'f3cms/config.php';

    $context = [
        'root_dir' => $rootDir,
        'f3cms_dir' => $rootDir . 'f3cms/',
        'f3' => $f3,
    ];

    return $context;
}