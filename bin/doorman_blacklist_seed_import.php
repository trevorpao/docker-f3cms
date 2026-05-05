#!/usr/bin/env php
<?php

$scriptName = basename(__FILE__);
$options = parseArguments($argv);

if (!empty($options['help']) || empty($options['input'])) {
    writeUsage($scriptName);
    exit(empty($options['input']) ? 1 : 0);
}

$inputPath = $options['input'];
if (!is_readable($inputPath)) {
    fwrite(STDERR, sprintf("無法讀取字典檔：%s\n", $inputPath));
    exit(1);
}

$defaultMatchType = $options['match-type'] ?? 'exact';
if (!in_array($defaultMatchType, ['exact', 'contains'], true)) {
    fwrite(STDERR, "--match-type 只接受 exact 或 contains\n");
    exit(1);
}

$defaultRemark = $options['remark'] ?? 'Imported dictionary seed';
$status = $options['status'] ?? 'Enabled';
if (!in_array($status, ['Enabled', 'Disabled'], true)) {
    fwrite(STDERR, "--status 只接受 Enabled 或 Disabled\n");
    exit(1);
}

$minLength = isset($options['min-length']) ? max(1, (int) $options['min-length']) : 3;
$maxLength = isset($options['max-length']) ? max($minLength, (int) $options['max-length']) : 191;
$limit = isset($options['limit']) ? max(0, (int) $options['limit']) : 0;

$entries = loadEntries($inputPath, $defaultMatchType, $defaultRemark, $status, $minLength, $maxLength, $limit);
if (empty($entries)) {
    fwrite(STDERR, "沒有可匯出的有效資料。\n");
    exit(1);
}

$sql = renderSql($entries);

if (!empty($options['output'])) {
    $outputPath = $options['output'];
    $bytes = @file_put_contents($outputPath, $sql);
    if ($bytes === false) {
        fwrite(STDERR, sprintf("無法寫入輸出檔：%s\n", $outputPath));
        exit(1);
    }

    printf("已輸出 %d 筆 seed 到 %s\n", count($entries), $outputPath);
    exit(0);
}

echo $sql;

/**
 * 解析 CLI 參數。
 */
function parseArguments(array $argv): array
{
    $result = [];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $result['help'] = true;
            continue;
        }

        if (strncmp($arg, '--', 2) === 0) {
            $parts = explode('=', substr($arg, 2), 2);
            $key = $parts[0];
            $value = $parts[1] ?? true;
            $result[$key] = $value;
            continue;
        }

        if (!isset($result['input'])) {
            $result['input'] = $arg;
        }
    }

    return $result;
}

/**
 * 顯示使用說明。
 */
function writeUsage(string $scriptName): void
{
    $usage = <<<TXT
用法：
  php bin/{$scriptName} /path/to/dictionary.txt [options]

支援的輸入格式：
  1. 每行一個 keyword
  2. TSV：match_type<TAB>keyword<TAB>remark

Options:
  --match-type=exact|contains   預設 match_type，純文字逐行字典會用到，預設 exact
  --remark="Imported seed"      預設 remark，純文字逐行字典會用到
  --status=Enabled|Disabled     預設 status，預設 Enabled
  --min-length=3                最短 keyword 長度，預設 3
  --max-length=191              最長 keyword 長度，預設 191
  --limit=200                   最多匯出幾筆，0 表示不限制
  --output=/path/to/output.sql  寫入檔案；若未提供則輸出到 STDOUT
  --help                        顯示說明

範例：
  php bin/{$scriptName} weak.txt --match-type=exact --remark="HIBP sample" --output=/tmp/blacklist.sql
  php bin/{$scriptName} contains.tsv --output=/tmp/blacklist_contains.sql
TXT;

    fwrite(STDOUT, $usage . PHP_EOL);
}

/**
 * 載入並整理字典檔。
 */
function loadEntries(
    string $path,
    string $defaultMatchType,
    string $defaultRemark,
    string $status,
    int $minLength,
    int $maxLength,
    int $limit
): array {
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return [];
    }

    $entries = [];
    $seen = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $entry = parseLine($line, $defaultMatchType, $defaultRemark, $status);
        if ($entry === null) {
            continue;
        }

        $entry['keyword'] = normalizeKeyword($entry['keyword']);
        if ($entry['keyword'] === '') {
            continue;
        }

        $length = strlen($entry['keyword']);
        if ($length < $minLength || $length > $maxLength) {
            continue;
        }

        $key = $entry['match_type'] . "\t" . $entry['keyword'];
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $entries[] = $entry;

        if ($limit > 0 && count($entries) >= $limit) {
            break;
        }
    }

    return $entries;
}

/**
 * 解析單行資料。
 */
function parseLine(string $line, string $defaultMatchType, string $defaultRemark, string $status): ?array
{
    $parts = preg_split('/\t+/', $line);
    if ($parts === false || $parts === []) {
        return null;
    }

    if (count($parts) >= 2 && in_array($parts[0], ['exact', 'contains'], true)) {
        return [
            'match_type' => $parts[0],
            'keyword'    => $parts[1],
            'status'     => $status,
            'remark'     => trim($parts[2] ?? $defaultRemark),
        ];
    }

    return [
        'match_type' => $defaultMatchType,
        'keyword'    => $parts[0],
        'status'     => $status,
        'remark'     => $defaultRemark,
    ];
}

/**
 * 正規化 keyword。
 */
function normalizeKeyword(string $keyword): string
{
    $keyword = trim(mb_strtolower($keyword));
    $keyword = preg_replace('/\s+/', '', $keyword);

    return $keyword === null ? '' : $keyword;
}

/**
 * 產生 SQL。
 */
function renderSql(array $entries): string
{
    $lines = [];
    $lines[] = "INSERT INTO `tbl_doorman_blacklist` (`match_type`, `keyword`, `status`, `remark`)";
    $lines[] = 'VALUES';

    foreach ($entries as $index => $entry) {
        $lines[] = sprintf(
            "  ('%s', '%s', '%s', '%s')%s",
            sqlEscape($entry['match_type']),
            sqlEscape($entry['keyword']),
            sqlEscape($entry['status']),
            sqlEscape($entry['remark']),
            $index === array_key_last($entries) ? '' : ','
        );
    }

    $lines[] = 'ON DUPLICATE KEY UPDATE';
    $lines[] = '  `status` = VALUES(`status`),';
    $lines[] = '  `remark` = VALUES(`remark`),';
    $lines[] = '  `last_ts` = current_timestamp(),';
    $lines[] = '  `last_user` = VALUES(`last_user`);';

    return implode(PHP_EOL, $lines) . PHP_EOL;
}

/**
 * SQL 字串 escaping。
 */
function sqlEscape(string $value): string
{
    return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
}