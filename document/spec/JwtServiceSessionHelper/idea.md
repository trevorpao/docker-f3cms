# JWT Service Session Helper - idea.md

## 1. 背景與問題定義 (Problem Statement)
目前系統已有 member center 與多個下游服務的分流需求。會員登入後，member center 需要把已登入身份安全地傳遞到服務 A、服務 B 等下游服務，讓各服務可以在不共享 PHP session storage 的前提下，快速知道「這是誰」以及「這個身份有哪些 scopes」。

若直接共用 session cookie 或把 member 資料拼在 query string 內，會有下列問題：
- 服務邊界不清，session 生命週期與儲存位置難以治理
- 下游服務無法獨立驗證身份真實性，只能被動相信上游轉送資料
- 一旦需要跨服務擴充，容易演變成多套不一致的 token / session 格式
- 缺少統一的短效、可驗簽、可比對 token version 的安全慣例

因此需要在 F3CMS 內提供一個統一的 firebase/php-jwt helper，專門處理 member center 發行、下游服務驗簽的短效 service session token。

## 2. 目標結果 (Target Outcome)
在 F3CMS `libs` 中建立一個簡單可用的 JWT helper，讓 member center 可用固定安全慣例簽發短效 RS256 token，下游服務可用相同 helper 驗簽並取得標準化後的 session claims。

第一版目標：
- member center 為登入會員簽發 RS256 token
- token 預設短效，TTL 為 5 分鐘
- token 內含 `user_id`、`scopes`、`token_version` 與必要標準 claims
- 服務 A / B 驗簽後可知道 user id、scope、token version、受眾是否合法
- helper 預設提供較安全的慣例用法，而不是要求每個呼叫端自己組 claims

## 3. 範圍 (Scope)
- 在 F3CMS `libs` 建立一個 JWT helper 類別，封裝 firebase/php-jwt 的 `encode` / `decode`
- 固定使用 RS256，不在第一版開放 HS256 或其他演算法切換
- 固定面向 service-to-service session token，不做 generic 任意 JWT builder
- helper 自動補上 `iss`、`aud`、`sub`、`iat`、`nbf`、`exp`、`jti`
- helper 接收外部傳入的 `user_id`、`scopes`、`token_version`、`audience`
- helper 提供 bearer token 抽取函式，降低 controller 重複解析 header
- 驗簽時支援 audience 驗證與 optional expected token version 驗證
- key material 可支援直接給 PEM 字串或 key file path

## 4. 非範圍 (Non-Scope)
- 不建立完整 OAuth / OIDC server
- 不處理 refresh token 流程
- 不處理多租戶 key rotation orchestration，只允許透過 `kid` 帶出目前 key id
- 不提供加密型 JWT (JWE)
- 不把可變 session 狀態直接塞進 token，例如購物車、流程暫存、完整 member profile
- 不在第一版實作 token blacklist、central revocation store 或 Redis session bridge
- 不在第一版處理 service 間自動跳轉網址組裝或 SSO portal UI

## 5. 核心物件與流程 (Core Objects or Processes)
- **Member Center**：唯一簽發 token 的上游服務，持有 RS256 private key
- **Service Session Token**：短效 JWT，作為跨服務身份信封，而不是完整 session store
- **JWT Helper**：封裝 token issue / verify / header parsing 的 shared helper
- **Downstream Service**：只持有 public key，負責驗簽並將 claims 轉為本地可用的 session identity
- **Token Version Contract**：由外部傳入與驗證，讓 calling service 可對照 member 現行 token version，拒絕舊 token

主要流程：
1. member center 確認會員已登入
2. member center 呼叫 JWT helper issue token
3. member center 把 token 帶到服務 A 或 B
4. 服務 A / B 解析 bearer token 並驗簽
5. 驗證通過後，各服務取得標準化的 user id / scopes / token version

## 6. 角色與參與者 (Actors and Roles)
- **會員**：已在 member center 完成登入的終端使用者
- **Member Center**：登入入口與 token issuer
- **服務 A / 服務 B**：token consumer，僅驗簽，不自行簽發同類 session token
- **系統管理者 / 維運**：配置 issuer、public/private key、kid、ttl、leeway

## 7. 資料與狀態影響 (Data and State Implications)
第一版 token payload 應採最小必要集合：
- `iss`：issuer，預設代表 member center
- `aud`：目標服務識別，例如 `service-a`、`service-b`
- `sub`：user id
- `iat`：issued at
- `nbf`：not before
- `exp`：expiry，預設 5 分鐘
- `jti`：隨機 token id
- `scopes`：字串陣列
- `token_version`：整數
- `token_use`：固定標示此 token 為 service session token

資料與狀態設計原則：
- token 內不放高敏感或可頻繁變動的大型 session state
- `token_version` 的真實來源由外部業務系統管理，helper 只負責攜帶與驗證
- 下游服務若要即時撤銷，應由後續版本評估 central revocation 或 version lookup，而不是要求 helper 自己保存狀態

## 8. 限制與依賴 (Constraints and Dependencies)
- 依賴 `firebase/php-jwt`
- 依賴 OpenSSL 與 RS256 key pair
- 以 F3CMS `f3()` config 作為 issuer、key material、kid、ttl、leeway 的預設來源
- 第一版以 short-lived token 為安全基線，TTL 預設 300 秒
- 驗簽端需明確指定 audience，不接受只驗簽不驗 audience 的寬鬆模式
- 不允許呼叫端任意更換 algorithm，避免錯誤使用與演算法降級風險

## 9. 風險與未決問題 (Risks and Open Questions)
- **Revocation 風險**：5 分鐘內的 token 仍可能在 user 被停權後短暫有效；第一版只靠 short TTL 與 token version，未做到即時撤銷
- **Key Rotation 問題**：第一版可攜帶 `kid`，但尚未定義完整 rotation SOP
- **Service Identity 命名**：`audience` 的 canonical naming 規範需後續共識，例如 `service-a` 是否應改為正式 slug
- **Direct Dependency Ownership**：目前 repo 已安裝 php-jwt，但可能仍是 transitive dependency；後續需確認是否升級為 direct require

## 10. 早期範例或情境 (Early Examples or Scenarios)

### Mainline Scenario: Member Center 發 token 給服務 A
情境：
- 會員 `12345` 已登入 member center
- member center 要導向服務 A
- 該會員目前 scopes 為 `member.read`、`order.read`
- 該會員當前 token version 為 `7`

預期：
- member center 呼叫 helper issue token，audience 設為 `service-a`
- token 使用 RS256 簽章
- token 含 `sub = 12345`、`scopes = [member.read, order.read]`、`token_version = 7`
- token `exp - iat = 300` 秒
- 服務 A 驗簽成功後，取得標準化 session payload，可辨識該會員身份

### Mainline Scenario: 同一會員導向服務 B
情境：
- 同一會員 `12345` 從 member center 導向服務 B
- member center 改以 `service-b` 作為 audience 發 token

預期：
- 服務 B 驗簽成功
- 服務 B 得知這是 user `12345`
- 服務 B 可讀到相同 `scopes` 與 `token_version`
- 服務 B 不需要與服務 A 共用 PHP session storage

### Boundary Scenario: Audience 不符時拒絕
情境：
- member center 發出給 `service-a` 的 token
- 該 token 被帶去 `service-b` 驗證

預期：
- 驗簽流程必須拒絕此 token
- 拒絕原因是 `aud` 與預期服務不符，而不是只要簽章正確就放行

### Boundary Scenario: Token Version 已過期
情境：
- token 內 `token_version = 6`
- 服務端目前要求的 expected token version 為 `7`

預期：
- 驗簽雖可通過，但最終 normalization / verify 階段必須拒絕
- 拒絕原因需明確對應 token version mismatch

### Boundary Scenario: 不應將大型 session 狀態塞進 JWT
情境：
- 呼叫端想把 member profile、購物車、完整權限快照一起塞進 token

預期：
- 此做法不屬於第一版 scope
- helper 的設計應鼓勵只攜帶 `user_id`、`scopes`、`token_version` 與必要標準 claims
- 若需要可變 session state，應改由 central store 或後續 revocation/session design 承接

### 建議 Helper 使用範例
```php
$jwt = new \F3CMS\JwtHelper([
    'issuer' => 'member-center',
    'private_key' => '/secure/member-center-private.pem',
    'public_key' => '/secure/member-center-public.pem',
    'kid' => 'member-center-rs256-v1',
]);

$token = $jwt->issueServiceSessionToken(
    12345,
    ['member.read', 'order.read'],
    7,
    'service-a'
);

$claims = $jwt->verifyServiceSessionToken($token, 'service-a', 7);
```
