# Sender 全信件實戰指南

> 目標讀者：剛導入 `F3CMS\Sender` 的初階工程師，想用 **DI 註冊多個寄信 Provider**，並以同一個介面完成 SMTP、Mailgun、SES、PHP mail 等情境。
>
> 本篇延續 `cashier_usage.md` 的寫法，整理常見設定、程式碼片段與除錯提示，讓控制器只需要「寄信情境 + 選擇 Provider」。

## 基礎需求
- PHP 8.1+，且專案已執行 `composer install`。
- 載入 `vendor/autoload.php` 與 `www/f3cms/libs/Autoload.php`。
- 設定檔需提供各 Provider 的憑證（.env、f3 config 均可）。
- 建議把 `Sender` 註冊到 Service Container，讓控制器/Job 直接 type-hint。

### 必填環境變數
| Provider | 主要設定 |
| --- | --- |
| SMTP | `smtp_host`, `smtp_port`, `smtp_account`, `smtp_password`, `smtp_from`, `smtp_name`, `smtp_security` (可選) |
| PHP mail | `smtp_from`, `smtp_name`, `webmaster` |
| Mailgun | `mailgun.key`, `mailgun.domain`, `mailgun.from`, `webmaster` |
| SES | `ses.from`、AWS 凭證 (由 `SesMailer` 或 IAM 設定) |

### 範例寄信資料
```php
$subject = 'F3CMS Sender 測試';
$content = '<p>Hello, this is a demo.</p>';
$recipients = ['demo.user@example.com'];
$options = [
    'cc'  => ['cc@example.com'],
    'bcc' => ['audit@example.com'],
    'meta' => ['campaign' => 'sender-demo'],
];
```

## 建立 Sender 與 Provider
```php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../libs/Autoload.php';

use F3CMS\Sender;
use F3CMS\MailHandler\{SmtpMailProvider, PhpMailProvider, MailgunMailProvider, SesMailProvider};

$sender = new Sender([
    'smtp'    => new SmtpMailProvider(),
    'mail'    => new PhpMailProvider(),
    'mailgun' => new MailgunMailProvider(),
    'ses'     => new SesMailProvider(),
], Sender::DEFAULT_PROVIDER);

// 也可指定 fallback 收件者或客製模板 renderer
ew Sender(providers: [...], fallbackRecipient: 'ops@example.com', bodyRenderer: fn($tpl, $data) => renderViaTwig($tpl, $data));
```
> `Sender::instance()` 會自動生成內建 Provider；若已透過 DI 建立實例，可呼叫 `Sender::setInstance($sender)` 讓舊有 `Sender::sendmail()` 保持可用。

## 寄信方式
- **DI 管道**：`$sender->send($subject, $content, $recipients, $options, 'mailgun');`
- **Legacy 靜態方法**：`Sender::sendmail($subject, $content, 'demo@example.com', 'smtp');`
- **Template 協助器**：`Sender::renderBody('invite', ['name' => 'Demo']);`

傳入 `provider` 參數即可切換不同 Handler；若省略，會使用建構子中的 `defaultProvider`（預設 `smtp`）。

## SMTP Provider
```php
$result = $sender->send($subject, $content, $recipients, [
    'from'      => 'no-reply@example.com',
    'from_name' => 'F3CMS Bot',
    'cc'        => ['boss@example.com'],
], 'smtp');
```
- `SmtpMailProvider` 會自動設置 `CC/BCC`，並記錄 `smtp.log`。
- 若所有收件者只剩一位且不是 `webmaster`，會自動將 `webmaster` 塞進 BCC。

## PHP mail Provider
```php
$result = $sender->send($subject, $content, 'ops@example.com', [
    'bcc' => ['security@example.com'],
], 'mail');
```
- 適合本機或沒有 SMTP/雲端憑證時快速測試。
- 仍建議設定 `webmaster`，用於自動 BCC 稽核。

## Mailgun Provider
```php
$result = $sender->send($subject, $content, $recipients, [
    'attachments' => [
        ['path' => 'storage/manual.pdf', 'name' => 'Manual.pdf'],
    ],
], 'mailgun');
```
- 會自動補上 `webmaster` BCC（若不在主收件人列表）。
- 支援 `attachment`/`inline` 檔案，記得提供絕對路徑或相對於專案根目錄。

## SES Provider
```php
$result = $sender->send($subject, $content, $recipients, [
    'attachments' => [__DIR__ . '/demo.csv'],
], 'ses');
```
- 透過 `SesMailer::send()` / `sendRaw()`，若傳入附件會自動切換 raw email。
- 回傳陣列含 `message_id`，可用於 AWS CloudWatch 追蹤。

## 套用模板
```php
$html = $sender->render('invite', [
    'name'  => 'Demo User',
    'token' => 'abc123',
]);

$sender->send('邀請信', $html, $recipients);
```
- 預設 renderer 會呼叫 `mail/{template}.html` 並把 `data` 寫入 f3 hive。
- 若要改用 Blade/Twig，建構 `Sender` 時傳入自訂的 `$bodyRenderer`。

## 常見錯誤排查
| 情境 | 排查方向 |
| --- | --- |
| `InvalidArgumentException: No recipient` | 檢查呼叫是否傳遞空陣列；必要時設定 `fallbackRecipient`。|
| SMTP 失敗 | 查看 `tmp/logs/smtp.log` 或確認 SSL/TLS port。|
| Mailgun 401 | 核對 `mailgun.key/domain`，Sandbox 網域只允許驗證過的收件者。|
| SES `MessageRejected` | 寄件者未通過認證、或超出寄信配額。|
| PHP mail 無回應 | 檢查伺服器是否阻擋 `sendmail`，必要時切換 SMTP provider。|

## 建議工作流程
1. 在 Service Container 綁定：`$container->singleton(Sender::class, fn () => new Sender([...], 'mailgun'));`
2. 控制器/Job 直接注入 `Sender`，並依情境指定 `provider`。
3. 針對系統關鍵信件（登入/結帳）設定 BCC 給 `webmaster`。
4. 於 CI 或 QA 階段以 `mail` provider 測試，正式環境再切換至 SMTP/Mailgun/SES。

> 更進階的設定（重試隊列、信件追蹤）可在各 Provider 上層再包裝 Service，或把 `send()` 的結果寫入資料庫方便稽核。