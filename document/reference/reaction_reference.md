# Reaction 常用函式指南

本文件整理 `www/f3cms/libs/Reaction.php` 中的核心 API 與擴充掛點，協助模組開發者快速掌握 Reaction 層的職責與可覆寫方法。Reaction 主要負責處理 AJAX / JSON 請求、驗證權限、協調 Feed/Kit、並統一輸出格式。

## 架構角色
- **繼承 `Module`**：共用 `_getReq()`、`_shift()` 等基礎工具，方便路由切換 Feed / Kit。
- **與 Feed 緊耦合**：所有 CRUD 與查詢都委派給相對應的 Feed 類別（`f{Module}`）。
- **回傳統一格式**：所有公開方法結尾皆透過 `_return()`，內含 `code`、`data`、`csrf` 欄位並自動處理 JSON / JSONP。

## 內建常數
| 常數 | 意義 | 常見情境 |
| --- | --- | --- |
| `RTN_DONE` | 操作成功 | 自訂動作完成時回傳 `Done` |
| `RTN_MISSCOLS` | 缺少欄位 | 驗證失敗、欄位未填 |
| `RTN_WRONGDATA` | 資料錯誤 | 欄位格式 / 值不符預期 |
| `RTN_UNVERIFIED` | 驗證失敗 | Token 或簽章不正確 |

## 內建 REST 動作
每個方法均接收 Fat-Free Framework 的 `$f3` 物件與 URI `$args`，實際資料從 `parent::_getReq()` 取得。

### `do_rerouter($f3, $args)`
- 依 `module` / `method` 參數動態呼叫對應 Reaction 類別 (`PCMS\rXxx` → `F3CMS\rXxx`)。
- 使用 `ReflectionClass` 執行 `do_{method}`，若找不到對應類別或方法回傳 `1004`。
- 適用於統一入口，減少多條 route 定義。

### `do_list()`
- 讀取 `page`,`limit`,`query` 參數並限制最大筆數（預設 24，上限取決於 `Feed::PAGELIMIT`）。
- 呼叫 `Feed::limitRows()` 取得分頁資料，再以 `handleIteratee()` 後處理每筆資料。
- 權限檢查：`chkAuth($feed::PV_R)`。

### `do_save()`
- 需帶入 `id`（無 `id` 回傳 `8004`）。
- 依序執行：`chkAuth(PV_U)` → `Kit::rule('save')` 驗證 → `beforeSave()` → `Feed::save()` → `Feed::handleSave()`。
- 回傳 `{ code:1, data:{ id } }`，常見於後台更新或部分欄位儲存。

### `do_upload()`
- 確認員工登入（`kStaff::_chkLogin()`）。
- 自動推導縮圖設定（`${module}_thn` → `default_thn`），呼叫 `Upload::savePhoto()`。
- 回傳檔名 `{ filename }`，供前端更新圖片欄位。

### `do_upload_file()`
- 與 `do_upload()` 類似，但不處理縮圖，直接 `Upload::saveFile()`。

### `do_del()`
- 僅在 `Feed::HARD_DEL === 1` 時允許刪除；否則回傳 `8008`。
- 必須提供 `id`，並通過 `PV_D` 權限檢查。
- 呼叫 `Feed::delRow($id)` 後回傳成功碼。

### `do_get()`
- 取得單筆資料；`id = 0` 時回傳預設空資料。
- 若找不到資料回傳 `8106`；成功時呼叫 `handleRow()` 進行欄位整形再回傳。

### `do_get_opts()`
- 依 `query` 關鍵字回傳選項列表（通常用於下拉選單或自動完成）。
- 透過 `Feed::getOpts()` 實作實際查詢邏輯。

## 擴充掛點
| 方法 | 時機 | 用途 |
| --- | --- | --- |
| `beforeSave(array $params)` | `do_save()` 寫入前 | 正規化輸入、補預設值、拆解複合欄位。預設直接回傳原資料。|
| `handleIteratee(array $row)` | `do_list()` 每筆資料 | 針對列表畫面需要的額外欄位（例：狀態換 label、串關聯資料）。|
| `handleRow(array $row)` | `do_get()` 取單筆後 | 格式化單筆資料，例如塞入 `Feed::oneOpt()` 結果或重組 JSON 欄位。|

> 建議：在自訂 Reaction 類別中覆寫上述方法，以保持 `do_*` 流程簡潔並專注在權限/流程控制。

## 輔助工具
- **`_parseBackendQuery($query)`**：把後台 query string（`a=1&b=2`）轉成 `Feed::genFilter()` 可用的陣列，並將 `=`→`:`、`&`→`,`。
- **`formatMsgs()`**：維護錯誤碼與訊息對照，可在前端顯示人性化錯誤文字。
- **`_return($code, $data = [])`**：統一輸出格式並刷新 CSRF token，支援 JSONP callback（`__jp*`、`ng_jsonp_callback_*`）。開發自訂方法時應始終使用此函式。
- **`_hashKey($action, $args, $secret)`**：依 Feed `hashGrid()` 定義的欄位順序組字串，產生 HMAC-SHA256 簽章（每 300 秒滾動一次）。適用於防重播或權限驗證場景。

## 開發建議
1. **集中權限檢查**：所有 `do_*` 方法在進入核心邏輯前應呼叫 `chkAuth()` 與必要的 `kStaff::_chkLogin()`，確保與 Feed 權限旗標一致。
2. **永遠回傳標準格式**：避免直接 `echo json_encode()`，統一走 `_return()` 以免遺漏 CSRF 更新或 JSONP 判斷。
3. **共用掛點**：盡量在覆寫 `beforeSave / handleIteratee / handleRow` 處理欄位轉換，不要把大量資料整形塞入 `do_*` 主流程。
4. **前後端命名同步**：沿用 `create_new_module.md` 的命名規範，保持 API/DB 欄位一致，降低資料映射複雜度。
5. **與 Feed 協作**：若需要額外查詢條件或輸出欄位，優先擴充 Feed（例如新增 `genFilter`、`limitRows` 支援），Reaction 僅負責組合條件與輸出。

> 以上內容涵蓋 Reaction 層最常被覆寫與呼叫的方法。若模組需要自訂流程，可在 `r{Module}` 中延伸 `do_*` 方法並透過本指南掌握既有行為。
