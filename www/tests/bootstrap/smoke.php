<?php

function tests_smoke_emit_json($payload)
{
    if (function_exists('jsonEncode')) {
        echo jsonEncode($payload, true) . PHP_EOL;

        return;
    }

    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (false === $encoded) {
        throw new \RuntimeException('Failed to encode smoke payload as JSON.');
    }

    echo $encoded . PHP_EOL;
}

function tests_smoke_fixture_path($relativePath)
{
    return dirname(__DIR__) . '/fixtures/' . ltrim((string) $relativePath, '/');
}

function tests_smoke_load_json_fixture($relativePath)
{
    $fixturePath = tests_smoke_fixture_path($relativePath);

    if (!is_file($fixturePath)) {
        throw new \RuntimeException('Fixture not found: ' . $fixturePath);
    }

    $decoded = json_decode((string) file_get_contents($fixturePath), true);

    if (JSON_ERROR_NONE !== json_last_error() || !is_array($decoded)) {
        throw new \RuntimeException('Fixture is not valid JSON: ' . $fixturePath);
    }

    return $decoded;
}

function tests_smoke_run(callable $suite, callable $contextFactory = null)
{
    try {
        $context = null;

        if (null !== $contextFactory) {
            $context = call_user_func($contextFactory);
        }

        $result = $suite($context);
        tests_smoke_emit_json($result);
        exit(0);
    } catch (\Throwable $e) {
        fwrite(STDERR, $e->getMessage() . PHP_EOL);
        exit(1);
    }
}