<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Only in cli mode\n");
    exit(1);
}

require_once __DIR__ . '/bootstrap/smoke.php';
require_once __DIR__ . '/adapters/f3cms/bootstrap.php';
require_once dirname(__DIR__) . '/f3cms/libs/Smoke.php';

function tests_index_require_runtime_guards()
{
    $appEnv = trim((string) getenv('APP_ENV'));

    if ('develop' !== $appEnv) {
        tests_smoke_emit_json(array(
            'code' => 0,
            'status' => 'error',
            'error' => 'smoke_env_blocked',
            'message' => 'Smoke execution is only allowed when APP_ENV=develop.',
            'app_env' => $appEnv,
        ));
        exit(1);
    }

    $allowSmokeWrite = trim((string) getenv('ALLOW_SMOKE_WRITE'));
    if ('1' !== $allowSmokeWrite) {
        tests_smoke_emit_json(array(
            'code' => 0,
            'status' => 'error',
            'error' => 'smoke_write_not_allowed',
            'message' => 'Smoke execution requires ALLOW_SMOKE_WRITE=1.',
            'app_env' => $appEnv,
        ));
        exit(1);
    }
}

tests_index_require_runtime_guards();

function tests_index_emit_error($error, $message, $path, $context = array())
{
    tests_smoke_emit_json(array_merge(array(
        'code' => 0,
        'status' => 'error',
        'error' => $error,
        'message' => $message,
        'path' => $path,
    ), $context));
    exit(1);
}

function tests_index_path_segments($path)
{
    if (!is_string($path) || '' === $path) {
        return null;
    }

    if (!preg_match('/^[a-z][a-z0-9_]*\/[a-z][a-z0-9_]*\/[a-z][a-z0-9_]*$/', $path)) {
        return null;
    }

    $segments = explode('/', $path);

    if (3 !== count($segments)) {
        return null;
    }

    return $segments;
}

function tests_index_normalize_module_name($moduleKey)
{
    $parts = explode('_', strtolower($moduleKey));
    $parts = array_map(function ($part) {
        return ucfirst($part);
    }, $parts);

    return implode('', $parts);
}

function tests_index_emit_success($path, $module, $surface, $contract, $result)
{
    tests_smoke_emit_json(array(
        'code' => 1,
        'status' => 'ok',
        'path' => $path,
        'module' => $module,
        'surface' => $surface,
        'contract' => $contract,
        'result' => $result,
    ));
    exit(0);
}

$path = isset($argv[1]) ? trim((string) $argv[1]) : '';
$segments = tests_index_path_segments($path);

if (null === $segments) {
    tests_index_emit_error('invalid_path', 'Smoke path must match <module>/<surface>/<contract>.', $path);
}

list($moduleKey, $surface, $contract) = $segments;

$module = tests_index_normalize_module_name($moduleKey);
$moduleDir = dirname(__DIR__) . '/f3cms/modules/' . $module;
$smokeFile = $moduleDir . '/smoke.php';

if (!is_dir($moduleDir)) {
    tests_index_emit_error('module_not_found', "Smoke module '{$moduleKey}' not found.", $path, array(
        'module' => $module,
        'surface' => $surface,
        'contract' => $contract,
    ));
}

if (!is_file($smokeFile)) {
    tests_index_emit_error('smoke_file_not_found', "Smoke file not found for module '{$module}'.", $path, array(
        'module' => $module,
        'surface' => $surface,
        'contract' => $contract,
        'smoke_file' => $smokeFile,
    ));
}

require_once $smokeFile;

$smokeClass = '\\F3CMS\\s' . $module;
if (!class_exists($smokeClass)) {
    tests_index_emit_error('invalid_smoke_contract', "Smoke class '{$smokeClass}' not found.", $path, array(
        'module' => $module,
        'surface' => $surface,
        'contract' => $contract,
        'smoke_file' => $smokeFile,
    ));
}

$smoke = new $smokeClass();
if (!is_a($smoke, 'F3CMS\\Smoke')) {
    tests_index_emit_error('invalid_smoke_contract', "Smoke class '{$smokeClass}' must extend F3CMS\\Smoke.", $path, array(
        'module' => $module,
        'surface' => $surface,
        'contract' => $contract,
        'smoke_file' => $smokeFile,
    ));
}

try {
    $result = $smoke->run($surface, $contract, array(
        'path' => $path,
        'module' => $module,
        'surface' => $surface,
        'contract' => $contract,
        'f3cms' => tests_bootstrap_f3cms(),
    ));
} catch (\Throwable $e) {
    if (is_a($e, 'F3CMS\\SmokeContractException') && method_exists($e, 'errorKey')) {
        tests_index_emit_error($e->errorKey(), $e->getMessage(), $path, array(
            'module' => $module,
            'surface' => $surface,
            'contract' => $contract,
            'smoke_file' => $smokeFile,
        ));
    }

    tests_index_emit_error('execution_failed', $e->getMessage(), $path, array(
        'module' => $module,
        'surface' => $surface,
        'contract' => $contract,
        'smoke_file' => $smokeFile,
    ));
}

tests_index_emit_success($path, $module, $surface, $contract, $result);