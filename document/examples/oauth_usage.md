# Oauth 第三方登入實戰指南

> 對象：需要在 F3CMS 專案中一次整合 Google / Facebook / LINE / OIDC 的工程師，並希望用同一支 `F3CMS\Oauth` 搭配 DI 的 Handler 與 Opauth Router。
>
> 本篇對照金流指南的寫法，示範如何準備環境、註冊 Handler、建構登入 URL、以及用 `Oauth::byToken()`/`validateToken()` 驗證回傳 token。

## 前置條件
- PHP 8.1+，並已執行 `composer install` 以載入 `vendor/autoload.php`。
- `www/f3cms/libs/Autoload.php` 以及各 Handler 皆已被 Autoloader 發現。
- 以 Service Container 或 DI 工廠建立 `F3CMS\Oauth`，並注入任意組合的 Handler 與 Opauth factory。
- 控制器/Route 可在完成設定後呼叫 `Oauth::setInstance()` 來提供舊版靜態 API。

### 必填環境變數
| Provider | 需要的變數 |
| --- | --- |
| Google | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` |
| Facebook | `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET` |
| LINE | `LINE_CLIENT_ID`, `LINE_CLIENT_SECRET` |
| OIDC | `OIDC_URI`, `OIDC_CLIENT_ID`, `OIDC_CLIENT_SECRET`, `OIDC_CALLBACK_URL` |

> 對應 `f3()` 設定鍵：`google.client_id/secret`、`facebook.client_id/secret`、`line_client_id/secret`、`oidc.uri/client_id/client_secret` 等，可透過 dotenv 或自訂設定檔載入。

### Sample Profile (格式化後)
```json
{
  "provider": "Google",
  "uid": "109792005960748024001",
  "info": {
    "name": "Zoe",
    "image": "https://lh3.googleusercontent.com/a-/AOh14Ghz",
    "email": "zoe@example.com"
  }
}
```

## 建立 Oauth 與四個 Handler
```php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../libs/Autoload.php';

use F3CMS\Oauth;
use F3CMS\OauthHandler\{FBOauthHandler, GoogleOauthHandler, LineOauthHandler};
use F3CMS\OIDCHelper;

$google   = new GoogleOauthHandler();
$facebook = new FBOauthHandler();
$line     = new LineOauthHandler();
$oidc     = new OIDCHelper();

$oauth = new Oauth([
    'google'   => $google,
    'facebook' => $facebook,
    'line'     => $line,
    'oidc'     => $oidc,
]);

Oauth::setInstance($oauth); // 讓既有的 Oauth::byToken() 可使用 DI 版本
```
> 你也可以傳入自訂的 `$opauthFactory`（第二個參數），例如注入 Service Container 內建的 Router，而不用直接呼叫 `OpauthBridge::instance()`。

## 設定 Opauth Route
```php
$oauth->configureRoute(__DIR__ . '/../conf/opauth/google.conf.php');
```
- `configureRoute()` 會讀取設定、以注入的工廠建立 `OpauthBridge` 實例，並預設註冊 `onSuccess` → `\F3CMS\oMember::_oauth`。
- 仍保留原本靜態寫法：`Oauth::setRoute($configPath)` 會轉呼叫實例。
- 若要替換成功 handler，可自行取得工廠結果後註冊事件：
  ```php
  $opauth = $myFactory($config);
  $opauth->onSuccess(fn ($payload) => LoginService::fromOpauth($payload));
  ```

## Google Login Flow
- **建構授權 URL**
  ```php
  [$url, $query] = $google->getURL('auth', [
      'prompt' => 'select_account',
      'hd'     => 'example.com',
  ]);
  header('Location: ' . $url . '?' . $query);
  ```
- **交換 ID Token**：在 callback 取得 `credential` 後，交給 `Oauth` 驗證。
  ```php
  $profile = $oauth->validateToken('google', $idToken);
  // $profile 會是統一格式；若失敗則含 error / error_description
  ```
- **常見錯誤**：`invalid_request` 通常是 redirect URI 未註冊；`http_error` 代碼為 401 表示 client secret 錯誤。

## Facebook Login Flow
- **權限頁**：
  ```php
  [$authUrl, $params] = $facebook->getURL('auth', ['scope' => 'email,public_profile']);
  $loginLink = $authUrl . '?' . $params;
  ```
- **TOKEN→Userinfo**：`Oauth::byToken('facebook', $accessToken)` 會請 Graph `/me` 並格式化 `name/email/avatar`。
- **排錯重點**：`(#200)` 與權限不足相關；記得於 callback 比對 `SESSION.facebook_state`。

## LINE Login Flow
- **Authorize**：
  ```php
  [$authorizeUrl, $query] = $line->getURL('auth', ['scope' => 'profile openid email']);
  header('Location: ' . $authorizeUrl . '?' . $query);
  ```
- **Verify ID Token**：
  ```php
  $lineProfile = $oauth->validateToken('line', $idToken);
  ```
- **排錯重點**：LINE 要求 HTTPS callback；`401` 多半是 channel secret 不符；若 state 驗證失敗會直接回傳 `error_description`。

## OIDC (教育雲 ID)
- **登入頁**：
  ```php
  [$authEndpoint, $query] = $oidc->getURL('auth', ['acr_values' => 'loa-2']);
  ```
- **取得使用者資料**：
  ```php
  $profile = $oauth->validateToken('oidc', $accessToken);
  $eduinfo = $profile['info']['eduinfo'] ?? [];
  ```
- **多階段流程說明**：`validateToken()` 會先打 `userinfo`，若成功再自動加上 `eduinfo`；不需在 controller 另外整併。
- **排錯重點**：`unsupported_provider` 代表 alias 未註冊；`bad response` 則表示 OIDC 端未回傳 JSON。

## 驗證 Token 的統一寫法
```php
public function oauthCallback(Request $request)
{
    $provider = $request->get('provider'); // google/facebook/line/oidc
    $token    = $request->get('credential') ?? $request->get('access_token');

    $result = Oauth::byToken($provider, $token);

    if (isset($result['error'])) {
        logger()->error('Oauth login failed', $result);
        throw new RuntimeException($result['error_description']);
    }

    return $this->memberService->loginOrRegister($result);
}
```
- `Oauth::byToken()` 仍支援舊程式碼，但內部會改由 DI 版本執行，方便測試與注入 mock handler。
- 也可以直接調用 `$oauth->validateToken()` 取得更細的錯誤資訊。

## 整合 Tips
- **Service Container**：建議在 bootstrap 階段全域註冊 `Oauth` singleton，其他地方使用依賴注入，避免重複 new handler。
- **測試**：可為每個 handler 建立 stub（實作 `OauthHandlerInterface`），透過建構子注入到 `Oauth`，快速模擬 tokeninfo API 回傳。
- **記錄日誌**：統一記錄 `provider + action + payload`，對應 Handler 內部也會寫入 `google_auth.log`/`facebook_auth.log` 等檔案，可對照找問題。
- **State/Nonce**：所有 handler 都會在 `auth` 指令時自動產生 state/nonce 並寫入 SESSION，記得在 callback 驗證才安全。

> 需要更細的參數和 API 行為，請開啟 `document/examples/google_usage.md`、`facebook_usage.md`、`line_usage.md` 或 OIDC 專案文件，本指南專注在統一 `F3CMS\Oauth` 的整合流程。
