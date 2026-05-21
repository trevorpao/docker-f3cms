# SMSSystem API 範例

## 共通說明

### API Base URL

    https://loc.f3cms.com:4433/api/

### 共通規則
+ 建議使用 `POST`
+ 建議使用 `application/x-www-form-urlencoded`
+ Reaction 標準回傳 envelope 為 `code / data / csrf`
+ `code = 1` 代表 route 呼叫成功
+ `code != 1` 代表 validation 或 business failure
+ 這兩支 API 預期在會員登入後使用；前端不應自行傳 `member_id`
+ route 會優先透過 `fMember::_current('id')` 從 session 取得目前會員
+ `insert_user` / `last_user` 是 staff audit 欄位；會員自行操作時應為 `0`，前端不應自行傳值
+ 建立 `Campaign` 成功只代表資料已進入 `Queued` 與 `Pending` 佇列，不代表簡訊已送出
+ `Opt-out` 與 `Rate Limited (5 mins)` 這類 SBE 結果發生在背景 worker 消化 `CampaignLog` 時，不會在建立 `Campaign` 當下直接回傳

## 通訊錄

### 取得我的通訊錄
#### 流程說明
列出目前登入會員建立過的 `Phonebook`。這支 API 只看會員 session，不接受前端自行指定 `member_id`。

    網址：/api/phonebook/mine

#### 傳入 POST 參數
+ 無

#### 會員上下文
+ **member_id**: 由登入後的 session 自動取得，前端不用傳

#### 回傳參數
+ **code**: (int)
+ **data.total**: (int) 總筆數
+ **data.limit**: (int) 本次查詢筆數上限
+ **data.page**: (int) 目前頁碼；未傳時預設回傳 `0`，若前端傳入頁碼則 route 會先轉成內部 0-based page
+ **data.subset**: (array[object]) 目前會員的通訊錄列表
+ **data.subset[].id**: (int) phonebook id
+ **data.subset[].member_id**: (int) 會員 ID
+ **data.subset[].title**: (string) 通訊錄標題
+ **data.subset[].remark**: (string) 備註
+ **data.subset[].status**: (string) 通訊錄狀態
+ **csrf**: (string)

#### 回傳結果
```json
// sample
// success
{
    "code": 1,
    "data": {
        "total": 1,
        "limit": 8,
        "page": 0,
        "subset": [
            {
                "id": 101,
                "member_id": 123,
                "title": "SBE 測試通訊錄",
                "remark": "給前端 llm 的 API sample",
                "status": "Enabled"
            }
        ]
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}

// fail
{
    "code": 8201,
    "data": {
        "msg": "Member login required."
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
```

### 建立通訊錄
#### 流程說明
建立一筆 `Phonebook`，並同步將傳入的門號整理進 `Mobile` 主檔與 `tbl_phonebook_mobile` 關聯。若同一批輸入內有重複門號，系統會依正規化結果自動去重。

    網址：/api/phonebook/create_with_phones

#### 傳入 POST 參數
+ **title**: (string) 通訊錄標題
+ **phones[]**: (array[string]) 門號清單，可傳多筆
+ **remark**: (string) 備註 *option*

#### 會員上下文
+ **member_id**: 由登入後的 session 自動取得，前端不用傳
+ **insert_user**: 此欄位屬 staff audit；會員 route 會固定寫 `0`，前端不用傳

#### 回傳參數
+ **code**: (int)
+ **data**: (object)
+ **data.id**: (int) phonebook id
+ **data.member_id**: (int) 會員 ID
+ **data.title**: (string) 通訊錄標題
+ **data.remark**: (string) 備註
+ **data.status**: (string) 通訊錄狀態，預設 `Enabled`
+ **data.insert_ts**: (string) 建立時間
+ **data.insert_user**: (int) 建立者 ID
+ **data.last_ts**: (string) 最後更新時間
+ **data.last_user**: (int) 最後更新者 ID
+ **data._mobile_ids**: (array[int]) 本次綁定到的 mobile ids
+ **data._phone_numbers**: (array[string]) 正規化後的門號清單
+ **csrf**: (string)

#### 回傳結果
```json
// sample
// success
{
    "code": 1,
    "data": {
        "id": 101,
        "member_id": 123,
        "title": "SBE 測試通訊錄",
        "remark": "給前端 llm 的 API sample",
        "status": "Enabled",
        "last_ts": "2026-05-21 14:00:00",
        "last_user": 0,
        "insert_ts": "2026-05-21 14:00:00",
        "insert_user": 0,
        "_mobile_ids": [301, 302],
        "_phone_numbers": ["+886912345678", "+14155550123"]
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}

// fail
{
    "code": 8004,
    "data": {
        "msg": "title and phones are required"
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
{
    "code": 8201,
    "data": {
        "msg": "Member login required."
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
{
    "code": 8204,
    "data": {
        "msg": "failed to create phonebook with phones"
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
```

## 發送活動

### 取得我的發送活動
#### 流程說明
列出目前登入會員建立過的 `Campaign`。這支 API 同樣只吃會員 session，不接受前端自行指定 `member_id`。

    網址：/api/campaign/mine

#### 傳入 POST 參數
+ 無

#### 會員上下文
+ **member_id**: 由登入後的 session 自動取得，前端不用傳

#### 回傳參數
+ **code**: (int)
+ **data.total**: (int) 總筆數
+ **data.limit**: (int) 本次查詢筆數上限
+ **data.page**: (int) 目前頁碼；未傳時預設回傳 `0`，若前端傳入頁碼則 route 會先轉成內部 0-based page
+ **data.subset**: (array[object]) 目前會員的發送活動列表
+ **data.subset[].id**: (int) campaign id
+ **data.subset[].member_id**: (int) 會員 ID
+ **data.subset[].phonebook_id**: (int) 通訊錄 ID
+ **data.subset[].status**: (string) 活動狀態
+ **data.subset[].total_targets**: (int) 目標總數
+ **csrf**: (string)

#### 回傳結果
```json
// sample
// success
{
    "code": 1,
    "data": {
        "total": 1,
        "limit": 8,
        "page": 0,
        "subset": [
            {
                "id": 501,
                "member_id": 123,
                "phonebook_id": 101,
                "status": "Queued",
                "total_targets": 2
            }
        ]
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}

// fail
{
    "code": 8201,
    "data": {
        "msg": "Member login required."
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
```

### 從通訊錄建立發送活動
#### 流程說明
建立一筆 `Campaign`，並同步依通訊錄展開 `CampaignLog`。API 成功時，代表活動已建立且目標已進入待處理佇列；真正的簡訊發送、拒收攔截與 5 分鐘防刷判定，會在背景 worker 處理。

    網址：/api/campaign/create_from_phonebook

#### 傳入 POST 參數
+ **phonebook_id**: (int) 通訊錄 ID
+ **content**: (string) 簡訊內容
+ **scheduled_ts**: (string) 排程時間，格式 `Y-m-d H:i:s` *option*
+ **provider_policy**: (string) provider routing policy *option*，目前預設 `TW_TO_MITAKE_ELSE_AWS`

#### 會員上下文
+ **member_id**: 由登入後的 session 自動取得，前端不用傳
+ **insert_user**: 此欄位屬 staff audit；會員 route 會固定寫 `0`，前端不用傳

#### 開發測試通道
+ 若開發環境已有登入 session，直接使用 session
+ 若開發環境未帶 session，可在 `APP_ENV=develop` 時透過 request header `HTTP_MOBILE_TOKEN` 走測試通道
+ 測試通道需由後端預先設定 `DEV_TOKEN` 與 `DEV_MEMBER_ID`，成功後 route 會以該 member 建立 session
+ 這條測試通道只應作為開發驗證用途，正式前端串接仍以正常會員登入 session 為準

```sh
curl https://loc.f3cms.com:4433/api/phonebook/create_with_phones \
    -H 'Mobile-Token: f3cms-dev-mobile-token' \
    --data-urlencode 'title=API Test Phonebook' \
    --data-urlencode 'phones[]=0912345678' \
    --data-urlencode 'phones[]=+14155550123' \
    --data-urlencode 'remark=develop token test'
```

#### 回傳參數
+ **code**: (int)
+ **data**: (object)
+ **data.id**: (int) campaign id
+ **data.member_id**: (int) 會員 ID
+ **data.phonebook_id**: (int) 通訊錄 ID
+ **data.provider_policy**: (string) provider policy snapshot
+ **data.content**: (string) 簡訊內容
+ **data.scheduled_ts**: (string|null) 排程時間
+ **data.status**: (string) 活動狀態，建立成功時預設 `Queued`
+ **data.total_targets**: (int) 本次展開的唯一目標數
+ **data.sent_count**: (int) 已送成功數，建立當下預設 `0`
+ **data.failed_count**: (int) 已失敗數，建立當下預設 `0`
+ **data.insert_ts**: (string) 建立時間
+ **data.insert_user**: (int) 建立者 ID
+ **data.last_ts**: (string) 最後更新時間
+ **data.last_user**: (int) 最後更新者 ID
+ **data._targets**: (array[object]) 本次展開目標摘要
+ **data._targets[].mobile_id**: (int) mobile id
+ **data._targets[].phone_number**: (string) 正規化後門號
+ **data._targets[].provider_alias**: (string) 實際分配 provider，`mitake` 或 `sns`
+ **csrf**: (string)

#### 回傳結果
```json
// sample
// success
{
    "code": 1,
    "data": {
        "id": 501,
        "member_id": 123,
        "phonebook_id": 101,
        "provider_policy": "TW_TO_MITAKE_ELSE_AWS",
        "content": "SMSSystem mainline route test",
        "scheduled_ts": "2026-05-21 14:05:00",
        "status": "Queued",
        "total_targets": 2,
        "sent_count": 0,
        "failed_count": 0,
        "last_ts": "2026-05-21 14:00:05",
        "last_user": 0,
        "insert_ts": "2026-05-21 14:00:05",
        "insert_user": 0,
        "_targets": [
            {
                "mobile_id": 301,
                "phone_number": "+886912345678",
                "provider_alias": "mitake"
            },
            {
                "mobile_id": 302,
                "phone_number": "+14155550123",
                "provider_alias": "sns"
            }
        ]
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}

// fail
{
    "code": 8004,
    "data": {
        "msg": "phonebook_id and content are required"
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
{
    "code": 8201,
    "data": {
        "msg": "Member login required."
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
{
    "code": 8204,
    "data": {
        "msg": "failed to create campaign from phonebook"
    },
    "csrf": "3i0nes78ppusk.32e9pgtmy24gc"
}
```

## 錯誤清單

### Route / 輸入錯誤
+ **1004**: route target 不存在，通常是 module 或 method naming 錯誤
+ **8004**: 缺少必要參數
+ **8204**: business input 不合法，或 owner-side create flow 建立失敗

### SBE 相關背景狀態
以下狀態不是建立 `Campaign` API 當下直接回傳的錯誤碼，而是背景 worker 後續寫入 `tbl_campaign_log` 的結果：

+ **Failed / Opt-out Number**: 目標門號為拒收
+ **Failed / Rate Limited (5 mins)**: 目標門號命中 5 分鐘防刷
+ **Sent**: worker 已成功呼叫 provider 並回寫 `sent_ts`

## 建議串接順序
1. 可先呼叫 `/api/phonebook/mine` 與 `/api/campaign/mine` 取得目前會員既有資料
2. 若沒有可用通訊錄，再呼叫 `/api/phonebook/create_with_phones` 建立通訊錄
3. 取得 `data.id` 後，再呼叫 `/api/campaign/create_from_phonebook`
4. 將 `Queued` 視為「排程建立成功」，不要視為「簡訊已送出」
5. 若前端之後需要查每筆發送結果，應另外補一條 campaign / campaign log 查詢 API；目前這份文件只涵蓋通訊錄與活動主清單、以及 SBE mainline 建立流程
