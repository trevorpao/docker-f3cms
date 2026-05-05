#!/usr/bin/env php
<?php

date_default_timezone_set('Asia/Taipei');

$options = parseArguments($argv);
if (!empty($options['help'])) {
    writeUsage(basename(__FILE__));
    exit(0);
}

$projectRoot = isset($options['project-root']) ? rtrim($options['project-root'], '/') : dirname(__DIR__);
$outputDir = isset($options['output-dir']) ? rtrim($options['output-dir'], '/') : '/home/ubuntu/checkresult';
$now = new DateTimeImmutable('now');
$timestamp = $now->format('Ymd_His');
$execution = [
    'dryRun' => !empty($options['dry-run']),
    'only' => parseOnlyOption($options['only'] ?? ''),
];

$paths = [
    'projectRoot' => $projectRoot,
    'envFile' => $projectRoot . '/.env',
    'publicRoot' => $projectRoot . '/www',
    'appRoot' => $projectRoot . '/www/f3cms',
    'uploadImgRoot' => $projectRoot . '/www/f3cms/upload/img',
    'nginxConfRoot' => $projectRoot . '/conf/nginx',
    'phpConfRoot' => $projectRoot . '/conf/php',
    'reportDir' => $outputDir,
    'reportTxt' => $outputDir . '/security_check_' . $timestamp . '.log',
    'reportJson' => $outputDir . '/security_check_' . $timestamp . '.json',
];

ensureDirectory($paths['reportDir']);

$env = loadDotEnv($paths['envFile']);
$settings = buildSettings($env, $options);

$report = [
    'generatedAt' => $now->format(DateTimeInterface::ATOM),
    'projectRoot' => $projectRoot,
    'outputDir' => $outputDir,
    'dryRun' => $execution['dryRun'],
    'selectedChecks' => array_values($execution['only']),
    'checks' => [],
    'summary' => [
        'ok' => 0,
        'warn' => 0,
        'error' => 0,
        'info' => 0,
    ],
];

$pdo = $execution['dryRun'] ? null : connectPdo($settings);

runSelectedCheck($report, $execution, '1', '異常檔名', static function () use ($paths) {
    return checkSuspiciousFilenames($paths);
});
runSelectedCheck($report, $execution, '2', '異常註冊帳號', static function () use ($pdo) {
    return checkSuspiciousAccounts($pdo);
});
runSelectedCheck($report, $execution, '3', '頁面遭插入文字', static function () use ($pdo) {
    return checkTamperedContentMarkers($pdo);
});
runSelectedCheck($report, $execution, '4', '異常微型圖片', static function () use ($paths) {
    return checkTinyRecentImages($paths);
});
runSelectedCheck($report, $execution, '5', '一次性或異常信箱', static function () use ($pdo) {
    return checkDisposableEmails($pdo);
});
runSelectedCheck($report, $execution, '6', '異常程式與持久化', static function () use ($paths) {
    return checkPersistenceAndProcesses($paths);
});
runSelectedCheck($report, $execution, '7', 'XSS 指標', static function () use ($pdo) {
    return checkXssIndicators($pdo);
});
runSelectedCheck($report, $execution, '8', '網站組態檔檢查', static function () use ($paths) {
    return checkConfigDrift($paths);
});
runSelectedCheck($report, $execution, '9', '網頁目錄外洩檢查', static function () use ($paths) {
    return checkDirectoryExposure($paths);
});
runSelectedCheck($report, $execution, '10', '硬碟可用空間', static function () use ($settings, $paths) {
    return checkDiskAvailability($paths['projectRoot'], $settings);
});
runSelectedCheck($report, $execution, '11', '記憶體用量', static function () use ($settings) {
    return checkMemoryUsage($settings);
});
runSelectedCheck($report, $execution, '12', 'CPU 用量', static function () use ($settings) {
    return checkCpuUsage($settings);
});

$textReport = renderTextReport($report);
$jsonReport = json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;

if ($execution['dryRun']) {
    fwrite(STDOUT, $textReport);
    fwrite(STDOUT, "Dry-run mode enabled: report files were not written.\n");
    exit(0);
}

file_put_contents($paths['reportTxt'], $textReport);
file_put_contents($paths['reportJson'], $jsonReport);

fwrite(STDOUT, "Report written:\n");
fwrite(STDOUT, "- " . $paths['reportTxt'] . "\n");
fwrite(STDOUT, "- " . $paths['reportJson'] . "\n");

if ($report['summary']['error'] > 0 || $report['summary']['warn'] > 0) {
    exit(2);
}

exit(0);

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
            $result[$parts[0]] = $parts[1] ?? true;
        }
    }

    return $result;
}

function writeUsage(string $scriptName): void
{
    $usage = <<<TXT
Usage:
  php bin/{$scriptName} [--project-root=/path/to/repo] [--output-dir=/home/ubuntu/checkresult]

Options:
  --project-root   Repository root. Default: parent of /bin
  --output-dir     Directory for report files. Default: /home/ubuntu/checkresult
    --dry-run        Show which checks would run and print the report to STDOUT without writing files
    --only=1,4,7     Run only the specified check IDs
  --db-host        Override DB host
  --db-port        Override DB port
  --db-name        Override DB name
  --db-user        Override DB user
  --db-password    Override DB password
    --disk-min-gb    Warning threshold for free disk space in GB. Default: 10
    --disk-min-pct   Warning threshold for free disk percentage. Default: 15
    --mem-warn-pct   Warning threshold for memory usage percentage. Default: 85
    --cpu-warn-pct   Warning threshold for CPU usage percentage. Default: 85
  --help           Show this help
TXT;

    fwrite(STDOUT, $usage . PHP_EOL);
}

function ensureDirectory(string $path): void
{
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('Unable to create report directory: ' . $path);
    }
}

function loadDotEnv(string $envPath): array
{
    if (!is_readable($envPath)) {
        return [];
    }

    $result = [];
    foreach (file($envPath, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim((string) $line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $result[$key] = trim($value, "\"'");
    }

    return $result;
}

function buildSettings(array $env, array $options): array
{
    return [
        'dbHost' => $options['db-host'] ?? '127.0.0.1',
        'dbPort' => (int) ($options['db-port'] ?? ($env['MYSQL_PORT'] ?? 3306)),
        'dbName' => $options['db-name'] ?? ($env['MYSQL_DATABASE'] ?? ''),
        'dbUser' => $options['db-user'] ?? ($env['MYSQL_USER'] ?? 'root'),
        'dbPassword' => $options['db-password'] ?? ($env['MYSQL_PASSWORD'] ?? ''),
        'diskMinGb' => (float) ($options['disk-min-gb'] ?? 10),
        'diskMinPct' => (float) ($options['disk-min-pct'] ?? 15),
        'memWarnPct' => (float) ($options['mem-warn-pct'] ?? 85),
        'cpuWarnPct' => (float) ($options['cpu-warn-pct'] ?? 85),
    ];
}

function parseOnlyOption(string $only): array
{
    if ($only === '') {
        return [];
    }

    $parts = array_filter(array_map('trim', explode(',', $only)), static function ($value) {
        return $value !== '';
    });

    return array_values(array_unique($parts));
}

function connectPdo(array $settings): ?PDO
{
    if ($settings['dbName'] === '') {
        return null;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $settings['dbHost'],
        $settings['dbPort'],
        $settings['dbName']
    );

    try {
        return new PDO($dsn, $settings['dbUser'], $settings['dbPassword'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        return null;
    }
}

function addCheck(array &$report, array $check): void
{
    $report['checks'][] = $check;
    if (!isset($report['summary'][$check['status']])) {
        $report['summary'][$check['status']] = 0;
    }
    $report['summary'][$check['status']]++;
}

function runSelectedCheck(array &$report, array $execution, string $id, string $title, callable $runner): void
{
    if (!empty($execution['only']) && !in_array($id, $execution['only'], true)) {
        return;
    }

    if (!empty($execution['dryRun'])) {
        addCheck($report, makeCheck($id, $title, 'info', 'Dry-run: check execution skipped.'));
        return;
    }

    addCheck($report, $runner());
}

function makeCheck(string $id, string $title, string $status, string $summary, array $details = []): array
{
    return [
        'id' => $id,
        'title' => $title,
        'status' => $status,
        'summary' => $summary,
        'details' => $details,
    ];
}

function checkSuspiciousFilenames(array $paths): array
{
    $matches = [];
    $pattern = '/nics115pt|nics\d+|56903|5692733992203/i';
    $iterator = buildRecursiveIterator($paths['publicRoot']);

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $basename = $fileInfo->getBasename();
        if (preg_match($pattern, $basename)) {
            $matches[] = relativePath($paths['projectRoot'], $fileInfo->getPathname());
        }
    }

    if (empty($matches)) {
        return makeCheck('1', '異常檔名', 'ok', 'No suspicious filenames found under public directories.');
    }

    return makeCheck('1', '異常檔名', 'warn', 'Suspicious filenames found under public directories.', $matches);
}

function checkSuspiciousAccounts(?PDO $pdo): array
{
    if (!$pdo) {
        return makeCheck('2', '異常註冊帳號', 'error', 'Database connection unavailable; account checks skipped.');
    }

    $details = [];
    foreach (['tbl_member', 'tbl_staff'] as $table) {
        if (!tableExists($pdo, $table)) {
            continue;
        }

        $sql = "SELECT id, account FROM {$table} WHERE account REGEXP :pattern ORDER BY id DESC LIMIT 50";
        $rows = runQuery($pdo, $sql, [':pattern' => 'nics|56903']);
        foreach ($rows as $row) {
            $details[] = sprintf('%s#%s %s', $table, $row['id'], $row['account']);
        }
    }

    if (empty($details)) {
        return makeCheck('2', '異常註冊帳號', 'ok', 'No suspicious account names found in tbl_member/tbl_staff.');
    }

    return makeCheck('2', '異常註冊帳號', 'warn', 'Suspicious account names found.', $details);
}

function checkTamperedContentMarkers(?PDO $pdo): array
{
    if (!$pdo) {
        return makeCheck('3', '頁面遭插入文字', 'error', 'Database connection unavailable; content marker checks skipped.');
    }

    $details = [];
    $map = [
        'tbl_post_lang' => 'tbl_post_lang',
        'tbl_press_lang' => 'tbl_press_lang',
    ];
    foreach ($map as $label => $table) {
        if (!tableExists($pdo, $table)) {
            continue;
        }

        $sql = "SELECT id, parent_id, LEFT(content, 120) AS snippet FROM {$table} WHERE content REGEXP :pattern ORDER BY id DESC LIMIT 50";
        $rows = runQuery($pdo, $sql, [':pattern' => 'nics115pt(_xss)?56903|nics56903|5692733992203']);
        foreach ($rows as $row) {
            $details[] = sprintf('%s#%s parent:%s %s', $label, $row['id'], $row['parent_id'], compactSnippet($row['snippet']));
        }
    }

    if (empty($details)) {
        return makeCheck('3', '頁面遭插入文字', 'ok', 'No known marker strings found in post/press content.');
    }

    return makeCheck('3', '頁面遭插入文字', 'warn', 'Known marker strings found in content tables.', $details);
}

function checkTinyRecentImages(array $paths): array
{
    $root = $paths['uploadImgRoot'];
    if (!is_dir($root)) {
        return makeCheck('4', '異常微型圖片', 'info', 'Upload image directory not found: ' . $root);
    }

    $details = [];
    $cutoff = time() - 86400;
    $iterator = buildRecursiveIterator($root);
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        if ($fileInfo->getMTime() >= $cutoff && $fileInfo->getSize() < 15 * 1024) {
            $details[] = sprintf(
                '%s size=%d mtime=%s',
                relativePath($paths['projectRoot'], $fileInfo->getPathname()),
                $fileInfo->getSize(),
                date('Y-m-d H:i:s', $fileInfo->getMTime())
            );
        }
    }

    if (empty($details)) {
        return makeCheck('4', '異常微型圖片', 'ok', 'No images under 15KB modified in the last 24 hours.');
    }

    return makeCheck('4', '異常微型圖片', 'warn', 'Recent tiny images found under upload/img.', $details);
}

function checkDisposableEmails(?PDO $pdo): array
{
    if (!$pdo) {
        return makeCheck('5', '一次性或異常信箱', 'error', 'Database connection unavailable; email checks skipped.');
    }

    $domains = ['hidingmail.net', 'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'tempmail.plus', 'yopmail.com'];
    $pattern = implode('|', array_map(static function ($domain) {
        return preg_quote('@' . $domain, '/');
    }, $domains));

    $details = [];
    foreach (['tbl_member', 'tbl_staff'] as $table) {
        if (!tableExists($pdo, $table)) {
            continue;
        }

        $sql = "SELECT id, email FROM {$table} WHERE LOWER(email) REGEXP :pattern ORDER BY id DESC LIMIT 50";
        $rows = runQuery($pdo, $sql, [':pattern' => $pattern]);
        foreach ($rows as $row) {
            $details[] = sprintf('%s#%s %s', $table, $row['id'], $row['email']);
        }
    }

    if (empty($details)) {
        return makeCheck('5', '一次性或異常信箱', 'ok', 'No disposable email domains found in member/staff records.');
    }

    return makeCheck('5', '一次性或異常信箱', 'warn', 'Disposable or suspicious email domains found.', $details);
}

function checkPersistenceAndProcesses(array $paths): array
{
    $details = [];
    $commandPattern = '/wget|curl|nc\s|bash -i|python -c|perl -e|php -r|base64_decode|eval\(|shell_exec|passthru|proc_open/i';

    $processOutput = runShell('ps axww -o pid=,command=');
    foreach (preg_split('/\R/', $processOutput) as $line) {
        $line = trim($line);
        if ($line !== '' && preg_match($commandPattern, $line)) {
            $details[] = 'process: ' . $line;
        }
    }

    $crontabOutput = runShell('crontab -l 2>/dev/null');
    foreach (preg_split('/\R/', $crontabOutput) as $line) {
        if ($line !== '' && preg_match($commandPattern, $line)) {
            $details[] = 'crontab: ' . trim($line);
        }
    }

    foreach (['/etc/crontab', '/etc/cron.d'] as $cronPath) {
        if (is_file($cronPath)) {
            foreach (file($cronPath, FILE_IGNORE_NEW_LINES) as $line) {
                if ($line !== '' && preg_match($commandPattern, $line)) {
                    $details[] = $cronPath . ': ' . trim($line);
                }
            }
        }
    }

    $fileIterator = buildRecursiveIterator($paths['publicRoot']);
    $recentCutoff = time() - 86400;
    foreach ($fileIterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $extension = strtolower($fileInfo->getExtension());
        if (!in_array($extension, ['php', 'phtml', 'php5', 'js', 'sh'], true)) {
            continue;
        }

        if ($fileInfo->getMTime() < $recentCutoff) {
            continue;
        }

        $contents = @file_get_contents($fileInfo->getPathname(), false, null, 0, 4096);
        if ($contents !== false && preg_match($commandPattern, $contents)) {
            $details[] = 'file: ' . relativePath($paths['projectRoot'], $fileInfo->getPathname());
        }
    }

    if (empty($details)) {
        return makeCheck('6', '異常程式與持久化', 'ok', 'No suspicious processes, cron jobs, or recent webshell-like files found.');
    }

    return makeCheck('6', '異常程式與持久化', 'warn', 'Suspicious process, cron, or recent executable file indicators found.', array_values(array_unique($details)));
}

function checkXssIndicators(?PDO $pdo): array
{
    if (!$pdo) {
        return makeCheck('7', 'XSS 指標', 'error', 'Database connection unavailable; XSS checks skipped.');
    }

    $details = [];
    $pattern = '<script|javascript:|onerror=|onload=|<iframe|srcdoc=|document.cookie|alert\s*\(';
    foreach (['tbl_post_lang', 'tbl_press_lang'] as $table) {
        if (!tableExists($pdo, $table)) {
            continue;
        }

        $sql = "SELECT id, parent_id, LEFT(content, 120) AS snippet FROM {$table} WHERE LOWER(content) REGEXP :pattern ORDER BY id DESC LIMIT 50";
        $rows = runQuery($pdo, $sql, [':pattern' => strtolower($pattern)]);
        foreach ($rows as $row) {
            $details[] = sprintf('%s#%s parent:%s %s', $table, $row['id'], $row['parent_id'], compactSnippet($row['snippet']));
        }
    }

    if (empty($details)) {
        return makeCheck('7', 'XSS 指標', 'ok', 'No obvious XSS indicator strings found in post/press content.');
    }

    return makeCheck('7', 'XSS 指標', 'warn', 'Potential XSS indicator strings found in content tables.', $details);
}

function checkConfigDrift(array $paths): array
{
    $details = [];
    $cutoff = time() - 86400;
    $monitoredFiles = [
        $paths['phpConfRoot'] . '/php-ini-overrides.ini',
        $paths['phpConfRoot'] . '/www.conf',
        $paths['nginxConfRoot'] . '/nginx.conf',
        $paths['nginxConfRoot'] . '/sites/default.conf',
        $paths['appRoot'] . '/robots.txt',
    ];

    foreach ($monitoredFiles as $file) {
        if (!file_exists($file)) {
            $details[] = 'missing monitored file: ' . relativePath($paths['projectRoot'], $file);
            continue;
        }

        if (filemtime($file) >= $cutoff) {
            $details[] = 'modified within 24h: ' . relativePath($paths['projectRoot'], $file);
        }
    }

    $phpOverride = $paths['phpConfRoot'] . '/php-ini-overrides.ini';
    if (is_readable($phpOverride)) {
        $content = file_get_contents($phpOverride);
        if ($content !== false) {
            foreach (['display_errors = On', 'allow_url_include = On', 'expose_php = On'] as $needle) {
                if (stripos($content, $needle) !== false) {
                    $details[] = 'risky php setting detected: ' . $needle;
                }
            }
        }
    }

    if (empty($details)) {
        return makeCheck('8', '網站組態檔檢查', 'ok', 'No config drift or risky monitored changes detected.');
    }

    return makeCheck('8', '網站組態檔檢查', 'warn', 'Config drift or risky settings detected.', $details);
}

function checkDirectoryExposure(array $paths): array
{
    $details = [];

    $nginxFiles = [
        $paths['nginxConfRoot'] . '/nginx.conf',
        $paths['nginxConfRoot'] . '/sites/default.conf',
    ];
    foreach ($nginxFiles as $file) {
        if (!is_readable($file)) {
            continue;
        }

        $content = file_get_contents($file);
        if ($content !== false && preg_match('/autoindex\s+on\s*;/i', $content)) {
            $details[] = 'autoindex on: ' . relativePath($paths['projectRoot'], $file);
        }
    }

    $sensitiveFiles = ['.env', '.git', '.svn', '.DS_Store', 'composer.lock', 'package-lock.json'];
    foreach ($sensitiveFiles as $name) {
        $target = $paths['appRoot'] . '/' . $name;
        if (file_exists($target)) {
            $details[] = 'public sensitive artifact: ' . relativePath($paths['projectRoot'], $target);
        }
    }

    $artifactPattern = '/\.(bak|old|orig|save|swp|dist|zip|tar|gz|sql)$/i';
    $iterator = buildRecursiveIterator($paths['appRoot']);
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        if (preg_match($artifactPattern, $fileInfo->getFilename())) {
            $details[] = 'public artifact: ' . relativePath($paths['projectRoot'], $fileInfo->getPathname());
        }
    }

    if (empty($details)) {
        return makeCheck('9', '網頁目錄外洩檢查', 'ok', 'No obvious directory exposure or public artifact issues found.');
    }

    return makeCheck('9', '網頁目錄外洩檢查', 'warn', 'Potential public exposure indicators found.', array_values(array_unique($details)));
}

function checkDiskAvailability(string $path, array $settings): array
{
    $freeBytes = @disk_free_space($path);
    $totalBytes = @disk_total_space($path);

    if ($freeBytes === false || $totalBytes === false || $totalBytes <= 0) {
        return makeCheck('10', '硬碟可用空間', 'error', 'Unable to read disk usage metrics.');
    }

    $freeGb = round($freeBytes / 1024 / 1024 / 1024, 2);
    $freePct = round(($freeBytes / $totalBytes) * 100, 2);
    $summary = sprintf('Free disk space: %.2f GB (%.2f%% free).', $freeGb, $freePct);

    if ($freeGb < $settings['diskMinGb'] || $freePct < $settings['diskMinPct']) {
        return makeCheck('10', '硬碟可用空間', 'warn', $summary, [
            sprintf('Thresholds: min %.2f GB or %.2f%% free.', $settings['diskMinGb'], $settings['diskMinPct']),
        ]);
    }

    return makeCheck('10', '硬碟可用空間', 'ok', $summary);
}

function checkMemoryUsage(array $settings): array
{
    $memInfoPath = '/proc/meminfo';
    if (!is_readable($memInfoPath)) {
        return makeCheck('11', '記憶體用量', 'info', 'Memory metrics unavailable on this host.');
    }

    $memInfo = parseMemInfo(file($memInfoPath, FILE_IGNORE_NEW_LINES) ?: []);
    if (!isset($memInfo['MemTotal'])) {
        return makeCheck('11', '記憶體用量', 'error', 'Unable to parse /proc/meminfo.');
    }

    $total = (float) $memInfo['MemTotal'];
    $available = isset($memInfo['MemAvailable']) ? (float) $memInfo['MemAvailable'] : max(0.0, $total - ((float) ($memInfo['MemFree'] ?? 0)));
    $used = max(0.0, $total - $available);
    $usedPct = round(($used / $total) * 100, 2);
    $summary = sprintf('Memory usage: %.2f%% used (%.2f GB / %.2f GB).', $usedPct, $used / 1024 / 1024, $total / 1024 / 1024);

    if ($usedPct >= $settings['memWarnPct']) {
        return makeCheck('11', '記憶體用量', 'warn', $summary, [
            sprintf('Threshold: %.2f%% used.', $settings['memWarnPct']),
        ]);
    }

    return makeCheck('11', '記憶體用量', 'ok', $summary);
}

function checkCpuUsage(array $settings): array
{
    $first = readCpuSample();
    if ($first === null) {
        return makeCheck('12', 'CPU 用量', 'info', 'CPU metrics unavailable on this host.');
    }

    usleep(200000);
    $second = readCpuSample();
    if ($second === null) {
        return makeCheck('12', 'CPU 用量', 'error', 'Unable to capture CPU usage sample.');
    }

    $idle = $second['idle'] - $first['idle'];
    $total = $second['total'] - $first['total'];
    if ($total <= 0) {
        return makeCheck('12', 'CPU 用量', 'error', 'CPU counters did not advance as expected.');
    }

    $usagePct = round((1 - ($idle / $total)) * 100, 2);
    $summary = sprintf('CPU usage: %.2f%% busy.', $usagePct);

    if ($usagePct >= $settings['cpuWarnPct']) {
        return makeCheck('12', 'CPU 用量', 'warn', $summary, [
            sprintf('Threshold: %.2f%% busy.', $settings['cpuWarnPct']),
        ]);
    }

    return makeCheck('12', 'CPU 用量', 'ok', $summary);
}

function renderTextReport(array $report): string
{
    $lines = [];
    $lines[] = str_repeat('=', 72);
    $lines[] = 'Daily Security Check Report';
    $lines[] = 'Generated At: ' . $report['generatedAt'];
    $lines[] = 'Project Root: ' . $report['projectRoot'];
    $lines[] = 'Dry Run: ' . ($report['dryRun'] ? 'yes' : 'no');
    if (!empty($report['selectedChecks'])) {
        $lines[] = 'Selected Checks: ' . implode(',', $report['selectedChecks']);
    }
    $lines[] = str_repeat('=', 72);
    $lines[] = sprintf(
        'Summary: ok=%d warn=%d error=%d info=%d',
        $report['summary']['ok'],
        $report['summary']['warn'],
        $report['summary']['error'],
        $report['summary']['info']
    );

    foreach ($report['checks'] as $check) {
        $lines[] = '';
        $lines[] = sprintf('[%s] %s. %s', strtoupper($check['status']), $check['id'], $check['title']);
        $lines[] = $check['summary'];
        foreach ($check['details'] as $detail) {
            $lines[] = '  - ' . $detail;
        }
    }

    $lines[] = '';
    return implode(PHP_EOL, $lines) . PHP_EOL;
}

function runQuery(PDO $pdo, string $sql, array $params = []): array
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function tableExists(PDO $pdo, string $table): bool
{
    static $cache = [];
    if (isset($cache[$table])) {
        return $cache[$table];
    }

    $stmt = $pdo->prepare('SHOW TABLES LIKE :table');
    $stmt->execute([':table' => $table]);
    $cache[$table] = (bool) $stmt->fetchColumn();

    return $cache[$table];
}

function buildRecursiveIterator(string $path): RecursiveIteratorIterator
{
    $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
    return new RecursiveIteratorIterator($iterator);
}

function relativePath(string $root, string $path): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $normalizedPath = str_replace('\\', '/', $path);
    if (strpos($normalizedPath, $normalizedRoot) === 0) {
        return substr($normalizedPath, strlen($normalizedRoot));
    }

    return $normalizedPath;
}

function compactSnippet(string $snippet): string
{
    $snippet = preg_replace('/\s+/', ' ', $snippet);
    return trim((string) $snippet);
}

function runShell(string $command): string
{
    $output = @shell_exec($command);
    return $output === null ? '' : (string) $output;
}

function parseMemInfo(array $lines): array
{
    $result = [];
    foreach ($lines as $line) {
        if (preg_match('/^([A-Za-z_]+):\s+(\d+)\s+kB$/', $line, $matches)) {
            $result[$matches[1]] = (int) $matches[2];
        }
    }

    return $result;
}

function readCpuSample(): ?array
{
    $statPath = '/proc/stat';
    if (!is_readable($statPath)) {
        return null;
    }

    $line = strtok((string) file_get_contents($statPath), PHP_EOL);
    if (!is_string($line) || strpos($line, 'cpu ') !== 0) {
        return null;
    }

    $parts = preg_split('/\s+/', trim($line));
    if ($parts === false || count($parts) < 5) {
        return null;
    }

    array_shift($parts);
    $values = array_map('intval', $parts);
    $idle = ($values[3] ?? 0) + ($values[4] ?? 0);
    $total = array_sum($values);

    return [
        'idle' => $idle,
        'total' => $total,
    ];
}