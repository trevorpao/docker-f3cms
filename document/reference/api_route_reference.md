# API Route Contract Reference

## Purpose
- 作為 F3CMS `/api/@module/@method` route contract 的快速查閱文件。
- 幫助工程師快速確認 module / method 命名如何映射到 Reaction class 與 `do_*` 方法。
- 提供常見 request 範例與 response envelope 摘要，避免 route-level API 使用方式各自猜測。

## Primary Readers
- Backend programmers
- 需要串接或驗證 Reaction API 的工程師
- LLMs 查詢 route naming 與 request shape 時

## Scope
- `/api/@module/@method` route contract
- module / method naming 規則
- class / method 映射方式
- request shape 常見範例
- response envelope 摘要

## LLM Reading Contract
- 將本文件視為 `/api/@module/@method` 的查表型 technical reference。
- 若問題是 API 應不應該存在、邏輯應該放在哪一層、或如何做正式驗證流程，先回 guides。
- 若問題是「這條 API path 會打到哪個 class / method」、「module 跟 method 該怎麼命名」、「request body 常見長什麼樣」，優先閱讀本文件。

## Core Thesis
- F3CMS 的 generic backend API 入口是 `/api/@module/@method`。
- `module` 會映射到 `r{Module}` 類別，`method` 會映射到 `do_{method}` 方法。
- route path 應保持穩定、可預測、可從 Reaction class 名稱直接反推。

## Canonical Route Pattern

定義位置：`www/f3cms/routes.ini`

```ini
GET|HEAD|POST /api/@module/@method=\F3CMS\Reaction->do_rerouter
```

可直接理解為：

```text
/api/<module>/<method>
```

## Naming Contract

### Module Naming

- route 中的 `module` 使用小寫 path segment。
- rerouter 會把 `module` 做 `ucfirst()`，再映射到 `r{Module}`。
- 因此：
  - `mobile` -> `\F3CMS\rMobile`
  - `phonebook` -> `\F3CMS\rPhonebook`
  - `campaign` -> `\F3CMS\rCampaign`

實務規則：

- 若 module class 是 `rCampaign`，path 就應寫成 `campaign`。
- 不要在 route path 裡混用 class prefix，例如不要寫成 `/api/rCampaign/...`。
- 不要把 route path 命名成頁面語意或臨時流程語意，只要對應 module owner 名稱。

### Method Naming

- route 中的 `method` 使用 snake_case path segment。
- rerouter 會把它映射到 `do_{method}`。
- 因此：
  - `create_or_ensure` -> `do_create_or_ensure()`
  - `create_with_phones` -> `do_create_with_phones()`
  - `create_from_phonebook` -> `do_create_from_phonebook()`

實務規則：

- path 用 snake_case，class method 保持 `do_` 前綴。
- 若公開 API 需要對外呼叫，應優先提供明確、語意穩定的 `do_*` 方法。
- 不要把 helper 名稱直接當成 route 名稱，route contract 應以公開 action 為準。

## Resolution Flow

當收到 `/api/campaign/create_from_phonebook`：

1. `@module = campaign`
2. rerouter 先組出 `\PCMS\rCampaign`
3. 若不存在，再 fallback 到 `\F3CMS\rCampaign`
4. `@method = create_from_phonebook`
5. 實際執行 `do_create_from_phonebook($f3, $args)`

若 class 或 method 找不到，rerouter 會回傳錯誤碼 `1004`。

## Request Contract

### Input Source

Reaction action 的 request 資料主要來自 `parent::_getReq()`。

一般順序可概念化為：

1. CORS / raw body 內容
2. `POST`
3. `GET`
4. `REQUEST`

穩定做法：

- 對 mutation 類 API 優先送 `POST`
- 一般 route 驗證優先用 `application/x-www-form-urlencoded`

### Response Envelope

公開 route 應透過 `_return()` 統一輸出，常見 shape 如下：

```json
{
  "code": 1,
  "data": {},
  "csrf": "..."
}
```

解讀方式：

- `code = 1` 代表成功
- 非 `1` 的 code 代表 validation 或 business failure
- `data` 是 route-specific payload
- `csrf` 是標準 response envelope 的一部分

### Common Failure Shape

常見 failure response 仍沿用相同 envelope，只是 `code != 1`：

```json
{
  "code": 8004,
  "data": {
    "msg": "member_id, title, and phones are required"
  },
  "csrf": "..."
}
```

實務解讀：

- HTTP transport 可能仍然是 `200`
- 真正的成功 / 失敗要以 response body 的 `code` 為準
- 若 route 額外補了 `data.msg`，應優先看該訊息理解失敗原因

## Common Error Codes

| Code | Common Meaning | Typical Cause | Common Shape |
| --- | --- | --- | --- |
| `1004` | route target not found | `module` 或 `method` 無法映射到有效的 Reaction class / `do_*` method | `data.class`, `data.method` |
| `8004` | missing required input | 必填欄位缺失、request 不完整、輸入為空 | 常見為 `data.msg` 或空 `data` |
| `8204` | invalid or failed business input | 資料不合法、business create flow 失敗、owner-side action 無法建立結果 | 常見為 `data.msg` 或 framework 預設錯誤訊息 |

### `1004`

常見語意：

- 這條 route path 找不到對應的 Reaction class 或公開 `do_*` 方法

在 rerouter 中的典型來源：

- `module` 對不到 `\F3CMS\rXxx`
- `method` 對不到 `do_xxx()`

常見回傳 shape：

```json
{
  "code": 1004,
  "data": {
    "class": "\\F3CMS\\rCampaign",
    "method": "do_create_from_phonebok"
  },
  "csrf": "..."
}
```

判讀重點：

- 先檢查 route path 的 `module` 拼字
- 再檢查 `method` 是否使用 snake_case，且 class 內是否真的存在對應的 `do_*`

### `8004`

常見語意：

- 必填欄位缺失
- request body 存在，但不足以通過最基本 validation

常見來源：

- `member_id`、`phonebook_id`、`content` 等欄位缺失
- `phone` / `phone_number` 空值
- 某些舊 route 只回 `8004` 而不補 `msg`

常見回傳 shape：

```json
{
  "code": 8004,
  "data": {
    "msg": "member_id, phonebook_id, and content are required"
  },
  "csrf": "..."
}
```

也可能是較舊的簡化 shape：

```json
{
  "code": 8004,
  "csrf": "..."
}
```

判讀重點：

- 先看 route contract 的必填欄位是否齊全
- 再看 request payload 是否真的被送成 route 可解析的 form shape

### `8204`

常見語意：

- 欄位資料有誤
- owner-side create / ensure flow 執行後沒有得到有效結果

框架對應語意：

- `Reaction::formatMsgs()` 內的 `WrongData` 對應 `8204`

常見回傳 shape：

```json
{
  "code": 8204,
  "data": {
    "msg": "failed to create campaign from phonebook"
  },
  "csrf": "..."
}
```

判讀重點：

- 這通常不是 route 找不到，而是 route 進到了 owner-side flow 之後，資料或 business state 不允許完成動作
- 若同時有 `data.msg`，應以該訊息定位是 validation failure、owner-side create failure，還是資料狀態異常

## Common Naming Examples

| Route Path | Reaction Class | Method |
| --- | --- | --- |
| `/api/mobile/create_or_ensure` | `\F3CMS\rMobile` | `do_create_or_ensure()` |
| `/api/phonebook/create_with_phones` | `\F3CMS\rPhonebook` | `do_create_with_phones()` |
| `/api/campaign/create_from_phonebook` | `\F3CMS\rCampaign` | `do_create_from_phonebook()` |

## Common Request Examples

### 1. Mobile: Create Or Ensure

Route:

```text
/api/mobile/create_or_ensure
```

Typical request body:

```sh
phone_number=0912345678
insert_user=1
```

Equivalent curl example:

```sh
curl https://loc.f3cms.com:4433/api/mobile/create_or_ensure \
  --data-urlencode 'phone_number=0912345678' \
  --data-urlencode 'insert_user=1'
```

Notes:

- `rMobile::createOrEnsureRequest()` 同時接受 `phone` 與 `phone_number`
- 若未提供門號，常見回傳是 `code = 8004`

### 2. Phonebook: Create With Phones

Route:

```text
/api/phonebook/create_with_phones
```

Typical request body:

```sh
member_id=123
title=API Test Phonebook
phones[]=0912345678
phones[]=+14155550123
remark=route contract example
insert_user=1
```

Equivalent curl example:

```sh
curl https://loc.f3cms.com:4433/api/phonebook/create_with_phones \
  --data-urlencode 'member_id=123' \
  --data-urlencode 'title=API Test Phonebook' \
  --data-urlencode 'phones[]=0912345678' \
  --data-urlencode 'phones[]=+14155550123' \
  --data-urlencode 'remark=route contract example' \
  --data-urlencode 'insert_user=1'
```

Notes:

- `phones` 需要能被解析成 array
- 此 action 會在 owner boundary 內進行 phone normalization 與 dedupe

### 3. Campaign: Create From Phonebook

Route:

```text
/api/campaign/create_from_phonebook
```

Typical request body:

```sh
member_id=123
phonebook_id=456
content=API route test content
scheduled_ts=2026-05-21 10:00:00
insert_user=1
```

Equivalent curl example:

```sh
curl https://loc.f3cms.com:4433/api/campaign/create_from_phonebook \
  --data-urlencode 'member_id=123' \
  --data-urlencode 'phonebook_id=456' \
  --data-urlencode 'content=API route test content' \
  --data-urlencode 'scheduled_ts=2026-05-21 10:00:00' \
  --data-urlencode 'insert_user=1'
```

Notes:

- 這條 route 對應 owner-side campaign creation，而不是 provider dispatch
- 若缺少 `member_id`、`phonebook_id` 或 `content`，常見回傳是 `code = 8004`

## Practical Contract Rules

- route path 要能從 owner module 名稱直接反推，不要使用臨時別名
- `method` 命名應描述公開 action，不要暴露內部 helper 命名漂移
- create / update 類 route 優先用 `POST`
- request example 應以 form payload 為主，因為這是目前最穩定的 common path
- 若需求是驗證 external route flow，不應只測 static request helper，應再補 route-level 驗證

## Common Mistakes

- 把 class 名稱直接放進 path，例如 `/api/rCampaign/...`
- 把 camelCase method 直接放進 path，例如 `/api/campaign/createFromPhonebook`
- 以為 route path 會直接對應 helper method，而不是 `do_*` public action
- 只記錄 payload，不記錄對應的 module / method contract
- 把 API 測試流程文件和 route contract 文件混成同一層；前者應回 guides，後者留在 reference

## Related Documents
- [intro.md](intro.md)
- [reaction_reference.md](reaction_reference.md)
- [../guides/api_testing_guide.md](../guides/api_testing_guide.md)
- [../guides/module_design.md](../guides/module_design.md)

## Status
- Draft v1