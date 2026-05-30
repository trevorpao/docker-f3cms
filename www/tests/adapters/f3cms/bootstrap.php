<?php

function tests_bootstrap_require_smoke_database($f3)
{
    $appEnv = trim((string) getenv('APP_ENV'));
    if ('develop' !== $appEnv) {
        throw new \RuntimeException('Smoke bootstrap is only allowed when APP_ENV=develop.');
    }

    $allowSmokeWrite = trim((string) getenv('ALLOW_SMOKE_WRITE'));
    if ('1' !== $allowSmokeWrite) {
        throw new \RuntimeException('Smoke bootstrap requires ALLOW_SMOKE_WRITE=1.');
    }

    $smokeDbName = trim((string) getenv('SMOKE_DB_NAME'));
    if ('' === $smokeDbName) {
        throw new \RuntimeException('Smoke bootstrap requires SMOKE_DB_NAME to be set.');
    }

    if (!preg_match('/^[A-Za-z0-9_]+$/', $smokeDbName)) {
        throw new \RuntimeException('SMOKE_DB_NAME may only contain letters, numbers, and underscores.');
    }

    $primaryDbName = trim((string) $f3->get('db_name'));
    if ('' !== $primaryDbName && $smokeDbName === $primaryDbName) {
        throw new \RuntimeException('SMOKE_DB_NAME must differ from the primary configured db_name.');
    }

    $dbHost = trim((string) $f3->get('db_host'));
    $dbPort = (int) ($f3->exists('db_port') ? $f3->get('db_port') : 3306);
    $dbAccount = trim((string) $f3->get('db_account'));
    $dbPassword = (string) $f3->get('db_password');

    $adminDsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $dbHost, $dbPort);
    $adminPdo = new \PDO($adminDsn, $dbAccount, $dbPassword, array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ));
    $adminPdo->exec('CREATE DATABASE IF NOT EXISTS `' . $smokeDbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $f3->set('db_name', $smokeDbName);
    $f3->set('db', 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $smokeDbName);

    if ($f3->exists('MH')) {
        $f3->clear('MH');
    }

    return array(
        'app_env' => $appEnv,
        'smoke_db_name' => $smokeDbName,
        'primary_db_name' => $primaryDbName,
    );
}

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

    $smokeRuntime = tests_bootstrap_require_smoke_database($f3);

    $context = [
        'root_dir' => $rootDir,
        'f3cms_dir' => $rootDir . 'f3cms/',
        'f3' => $f3,
        'smoke_runtime' => $smokeRuntime,
    ];

    return $context;
}