# smSender 全簡訊實戰指南

> 適用對象：剛導入 `F3CMS\smSender` 的工程師，希望用單一 Orchestrator 同時串接 **三竹 (Mitake) / Every8d / Amazon SNS**，並在需要時切換同步與佇列流程。
>
> 本篇沿用 `cashier_usage.md` 的敘述方式，整理必要環境、DI 範例、佇列與 fallback 設計，以及常見排錯策略。

## 基礎需求
- PHP 8.1+，專案已載入 `vendor/autoload.php` 與 `www/f3cms/libs/Autoload.php`。
- `smSender`、`QueueHelper`、各 `SmsHandler` 已可自動 autoload。
- 建議將 `smSender` 註冊於 Service Container，控制器僅需注入並呼叫 `dispatch()`。

### 必填環境變數
| Provider | 需要的設定 |
| --- | --- |
| Mitake | `sms.mitake.username`, `sms.mitake.password`, `sms.mitake.domain` |
| Every8d | `sms.every8d.uid`, `sms.every8d.pwd` |
| Amazon SNS | `aws.region`, `aws.key`, `aws.secret` |
| Queue (可選) | `rabbitmq.sms.*` 供 `QueueHelper` 建立佇列 |

### 範例簡訊資料
```php
$phone   = '0912-345-678';
$message = 'F3CMS OTA: 您的登入驗證碼 123456';
$options = [
    'queue'               => false,
    'provider'            => 'mitake',
    'fallback_providers'  => ['every8d', 'sns'],
    'meta'                => ['campaign' => 'login-otp'],
];
```

## 建立 smSender 與 Providers
```php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../libs/Autoload.php';

use Aws\Sns\SnsClient;
use F3CMS\smSender;
use F3CMS\SmsHandler\{MitakeProvider, Every8dProvider, AmazonSnsProvider};

$sender = new smSender([
    'mitake'  => new MitakeProvider(env('SMS_MITAKE_USER'), env('SMS_MITAKE_PASS'), env('SMS_MITAKE_DOMAIN')),
    'every8d' => new Every8dProvider(env('SMS_E8D_UID'), env('SMS_E8D_PWD')),
    'sns'     => new AmazonSnsProvider(new SnsClient([
        'version'     => 'latest',
        'region'      => env('AWS_REGION'),
        'credentials' => ['key' => env('AWS_KEY'), 'secret' => env('AWS_SECRET')],
    ])),
], smSender::DEFAULT_PROVIDER, '+886');

// 若專案需使用靜態方法，可讓單例對應
smSender::setInstance($sender);
```
> 未傳入 providers 時，smSender 會以 f3 config 自動註冊已填好的 Mitake / Every8d / SNS，方便快速啟動。

## 單筆送出 (同步)
```php
$result = $sender->dispatch($phone, $message, $options);

if ($result['status'] === 'failed') {
    // 記錄 $result['error'] 並提醒工程師
}
```
- `provider` 參數可指定首選通道；`fallback_providers` 會依序遞補。
- 回傳結構包含 `status`、`provider`、`message_id`、`meta`（各 handler 自行補充）。

## 批次送出
```php
$batch = [
    ['phone' => '0912345678', 'message' => '課程提醒 A'],
    ['phone' => '+886955123456', 'message' => '課程提醒 B', 'options' => ['provider' => 'sns']],
];

$results = $sender->dispatchBulk($batch, ['queue' => false]);
```
- `dispatchBulk` 會逐筆驗證號碼並執行同一套 fallback 流程。
- 若某筆資料缺欄位或格式錯誤，只影響該筆結果，不會阻斷整批。

## 佇列模式
```php
$sender->enableQueue(true);
$sender->dispatch($phone, $message, [
    'queue'              => true,
    'fallback_providers' => ['mitake', 'sns'],
]);
```
- 會將 envelope 寫入 `smsender.dispatch` 佇列，背景 worker 呼叫 `handleQueueMessage()` 處理。
- 佇列 payload 內含 `pipeline`、`batch_id`、`options`，方便追蹤。

### Worker 範例
```php
$sender = smSender::instance();

while ($msg = $queue->reserve('smsender.dispatch')) {
    $result = $sender->handleQueueMessage($msg->body);
    $queue->ack($msg);
}
```

## E.164 號碼正規化
- `dispatch()` 會自動把 `0912-345-678` 轉成 `+886912345678`。
- 若字串帶有 `+`，會保留原國碼但移除非數字符號。
- 非法字元或空字串會直接丟 `InvalidArgumentException`，可在 Controller 層捕捉處理。

## Provider 回傳格式
| Provider | status | message_id | meta |
| --- | --- | --- | --- |
| Mitake | `sent` / `failed` | 從回應 `ID=` 解析 | `raw`：三竹原始內容 |
| Every8d | `sent` / `failed` | 由 `msgid` 解析 | `raw`：API 回應字串 |
| Amazon SNS | `sent` / `failed` | AWS `MessageId` | `result`：SNS publish 陣列 |

## 常見錯誤排查
| 情境 | 排查方向 |
| --- | --- |
| `InvalidArgumentException: Phone number cannot be empty` | 確認輸入是否含數字；必要時提示會員重新輸入。|
| `No SMS providers registered` | 建構 smSender 時沒傳 provider 且 config 也未填，請補 `.env` 或手動注入。|
| `All providers failed` | 檢查 smsender.log 內的個別 provider 錯誤，通常是憑證或額度不足。|
| 佇列 message 拒收 | 確認 `rabbitmq.sms.*` 設定，或在 `QueueHelper` 中開啟重試。|
| SNS 403 | IAM 權限不足，需開啟 `sns:Publish`。|

## 建議工作流程
1. 在 Service Container 註冊 `smSender` singleton，並於建構時傳入主要 provider ＋ fallback。
2. 控制器呼叫 `dispatch()`，若想統一異步處理，設定 `queue => true` 再由 worker 處理。
3. Webhook / 後台查詢可直接讀取 `smsender.log` 或自行寫 DB audit。
4. 測試環境建議先用 Every8d sandbox，正式上線時才切換至三竹或 SNS。

> 更多範例可參考 `www/f3cms/libs/smSender.php` 與各 `SmsHandler/*Provider.php`，本篇聚焦於 orchestration 與實務用法。