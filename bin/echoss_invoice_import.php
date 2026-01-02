#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../libs/Autoload.php';

use F3CMS\ECHOSShelper;

$scriptName = basename(__FILE__);
$csvPath    = $argv[1] ?? __DIR__ . '/../upload/sample.csv';
$dryRun     = !in_array('--execute', $argv, true);
$apiSecret  = getenv('ECHOSS_API_SECRET') ?: '';
$apiBase    = getenv('ECHOSS_API_BASE') ?: 'https://ts-api.12cm.com.tw';

if (!is_readable($csvPath)) {
    fwrite(STDERR, sprintf("無法讀取 CSV：%s\n", $csvPath));
    exit(1);
}

if (empty($apiSecret)) {
    fwrite(STDERR, "請先設定環境變數 ECHOSS_API_SECRET，再重試。\n");
    exit(1);
}

printf("載入檔案：%s\n", realpath($csvPath));
printf("執行模式：%s (加上 --execute 可實際呼叫 API)\n", $dryRun ? 'Dry-run' : 'Execute');

$rows = loadCsv($csvPath);
if (empty($rows)) {
    fwrite(STDERR, "CSV 沒有有效資料。\n");
    exit(1);
}

$orders = buildInvoicePayloads($rows);
printf("解析完成，共 %d 筆訂單。\n", count($orders));

$helper = new ECHOSShelper();
$helper->setVipBase($apiBase);
$helper->setBearerToken($apiSecret);

foreach ($orders as $orderNo => $payload) {
    echo str_repeat('-', 60) . PHP_EOL;
    printf("訂單 %s / 手機 %s / 總額 %d / 明細 %d 筆\n",
        $payload['order_no'],
        $payload['phone_number'],
        $payload['amount'],
        count($payload['details'])
    );

    if ($dryRun) {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        continue;
    }

    $response = $helper->createInvoiceOrder($payload);
    echo "API Response:\n";
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
}

/**
 * 讀取 CSV 並回傳關聯陣列列表。
 */
function loadCsv(string $path): array
{
    $file = new SplFileObject($path, 'r');
    $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
    $file->setCsvControl(',');

    $originalHeaders = [];
    $columnKeys      = [];
    $rows            = [];

    foreach ($file as $index => $row) {
        if (!is_array($row)) {
            continue;
        }

        if (0 === $index) {
            $originalHeaders = array_map(static fn($value) => trim((string) $value), $row);
            $columnCount     = count($originalHeaders);

            if ($columnCount === 0) {
                break;
            }

            $columnKeys = generateColumnLabels($columnCount);
            continue;
        }

        if (empty($columnKeys)) {
            continue;
        }

        if (empty(array_filter($row, fn($value) => $value !== null && $value !== ''))) {
            continue;
        }

        $rows[] = mapRow($columnKeys, $originalHeaders, $row);
    }

    return $rows;
}

/**
 * 將欄位名稱與資料列組成關聯陣列。
 */
function mapRow(array $columnKeys, array $originalHeaders, array $row): array
{
    $assoc = [];

    foreach ($columnKeys as $idx => $columnKey) {
        $value = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
        $assoc[$columnKey] = $value;

        $originalKey = trim($originalHeaders[$idx] ?? '');
        if ($originalKey !== '' && !array_key_exists($originalKey, $assoc)) {
            $assoc[$originalKey] = $value;
        }
    }

    return $assoc;
}

/**
 * 將 CSV 列表整理成建立發票訂單所需的 payload。
 */
function buildInvoicePayloads(array $rows): array
{
    $orders = [];

    foreach ($rows as $row) {
        $orderNo = getColumnValue($row, 'C', '交易ID');
        if ($orderNo === '') {
            continue;
        }

        if (!isset($orders[$orderNo])) {
            $orders[$orderNo] = [
                'phone_number'   => getColumnValue($row, 'A', '手機'),
                'order_datetime' => formatOrderDatetime(getColumnValue($row, 'B', '訂單時間')),
                'order_no'       => $orderNo,
                'store_open_id'  => getColumnValue($row, 'D', '門市代碼'),
                'invoice_no'     => getColumnValue($row, 'E', '發票號碼'),
                'amount'         => toInt(getColumnValue($row, 'F', '發票金額', '0')),
                'details'        => [],
            ];
        }

        $orders[$orderNo]['details'][] = buildDetail($row);
    }

    return $orders;
}

/**
 * 建立單筆商品明細。
 */
function buildDetail(array $row): array
{
    return [
        'record_no'            => getColumnValue($row, 'G', 'Record Number(key值)'),
        'product_name'         => getColumnValue($row, 'H', '產品名稱'),
        'product_category'     => getColumnValue($row, 'I', '產品類別'),
        'product_category_code'=> getColumnValue($row, 'J', '產品類別代碼'),
        'sub_category_name'    => getColumnValue($row, 'K', '子類別名稱'),
        'sub_category_code'    => getColumnValue($row, 'L', '子類別名稱代碼'),
        'series'               => getColumnValue($row, 'M', '系列'),
        'model'                => getColumnValue($row, 'N', '型號'),
        'product_spec'         => getColumnValue($row, 'O', '規格'),
        'sn'                   => getColumnValue($row, 'P', 'S/N'),
        'imei'                 => getColumnValue($row, 'Q', 'IMEI'),
        'quantity'             => (int) getColumnValue($row, 'R', '數量', '0'),
        'suggested_price'      => toInt(getColumnValue($row, 'S', '建議售價', '0')),
        'total_price'          => toInt(getColumnValue($row, 'T', '總金額', '0')),
        'total_discount'       => toInt(getColumnValue($row, 'U', '交易總折扣', '0')),
        'promotion_id'         => normalizeNullable(getColumnValue($row, 'V', '促銷ID')),
        'voucher_no'           => normalizeNullable(getColumnValue($row, 'W', '券號')),
    ];
}

/**
 * 將 YYYYMMDDHHIISS 轉為 Y-m-d H:i:s。
 */
function formatOrderDatetime(string $raw): string
{
    $raw = preg_replace('/[^0-9]/', '', $raw);
    if (strlen($raw) === 14) {
        $dt = DateTime::createFromFormat('YmdHis', $raw);
        if ($dt !== false) {
            return $dt->format('Y-m-d H:i:s');
        }
    }

    return $raw;
}

/**
 * 將數字字串轉為整數，會自動去除逗號。
 */
function toInt(string $value): int
{
    $value = str_replace(',', '', $value);
    return (int) $value;
}

/**
 * 轉換空字串為 null，並移除多餘空白。
 */
function normalizeNullable(?string $value): ?string
{
    if ($value === null) {
        return null;
    }
    $value = trim($value);
    return $value === '' ? null : $value;
}

/**
 * 依照欄位代號取得資料，優先使用 A..ZZ 欄位名稱，必要時回退到原始表頭。
 */
function getColumnValue(array $row, string $columnKey, string $fallbackKey = '', string $default = ''): string
{
    if (isset($row[$columnKey])) {
        return $row[$columnKey];
    }

    if ($fallbackKey !== '' && isset($row[$fallbackKey])) {
        return $row[$fallbackKey];
    }

    return $default;
}

/**
 * 產生 A..Z, AA..ZZ 欄位名稱列表。
 */
function generateColumnLabels(int $count): array
{
    $labels = [];

    for ($index = 0; $index < $count; $index++) {
        $labels[] = columnIndexToLabel($index);
    }

    return $labels;
}

/**
 * 將數字索引轉換為 Excel 風格的欄位名稱 (A..Z, AA..ZZ)。
 */
function columnIndexToLabel(int $index): string
{
    $label = '';

    do {
        $remainder = $index % 26;
        $label     = chr(65 + $remainder) . $label;
        $index     = intdiv($index, 26) - 1;
    } while ($index >= 0);

    return $label;
}
