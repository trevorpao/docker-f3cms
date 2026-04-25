# Reaction 常用函式參考

## Purpose
- 作為 Reaction 層的快速查閱文件。
- 幫助工程師快速找到內建 `do_*` 流程、擴充掛點、回傳格式與常見開發模式。
- 補足 guides 中 Reaction 層責任說明的實作層細節。

## Primary Readers
- Backend programmers
- SD
- 正在撰寫或維護 Reaction 的工程師
- LLMs 需要快速查詢 Reaction 行為時

## Scope
- Reaction 的角色
- 內建 REST 動作
- 擴充掛點
- 輔助工具
- 實作建議與常見踩雷

## LLM Reading Contract
- 將本文件視為 Reaction 的操作型 reference，而不是模組邊界或架構決策文件。
- 若問題是「Reaction 應該負責什麼」，先回 [../guides/module_design.md](../guides/module_design.md) 與 [../guides/feed_guide.md](../guides/feed_guide.md)。
- 若問題是「Reaction 已經提供哪些流程與掛點」，優先閱讀本文件。

## Core Thesis
- guides 解決的是 Reaction 在架構上的責任邊界。
- 本文件解決的是 Reaction 在實作上的既有流程、掛點與回傳方式。
- 因此，這份文件最適合在已經知道邏輯要放在 Reaction 之後使用。

## 什麼時候該讀這份文件

適合的情境：
- 你已經知道這段邏輯屬於後台互動或 JSON 請求處理
- 你需要查 `do_list()`、`do_save()`、`do_get()`、`do_del()` 等內建流程
- 你需要知道該覆寫 `beforeSave()`、`handleIteratee()`、`handleRow()` 的哪一個掛點
- 你想確認權限檢查、回傳格式與 Feed 協作方式

不適合單獨解決的情境：
- 這個功能到底該放 Feed 還是 Reaction
- 這個欄位應該如何建模
- 這個需求應不應該拆成新模組

這些問題請先回：
- [../guides/module_design.md](../guides/module_design.md)
- [../guides/data_modeling.md](../guides/data_modeling.md)
- [../guides/feed_guide.md](../guides/feed_guide.md)

閱讀目標：讓工程師快速掌握 Reaction 既有流程、可覆寫掛點與回傳規格，避免把資料規則或頁面邏輯錯放進來。

本文件整理 `www/f3cms/libs/Reaction.php` 中的核心 API 與擴充掛點，協助模組開發者快速掌握 Reaction 層的職責與可覆寫方法。Reaction 主要負責處理 AJAX / JSON 請求、驗證權限、協調 Feed/Kit、並統一輸出格式。

## 架構角色
- **繼承 `Module`**：共用 `_getReq()`、`_shift()` 等基礎工具，方便路由切換 Feed / Kit。
- **與 Feed 緊耦合**：所有 CRUD 與查詢都委派給相對應的 Feed 類別（`f{Module}`）。
- **回傳統一格式**：所有公開方法結尾皆透過 `_return()`，內含 `code`、`data`、`csrf` 欄位並自動處理 JSON / JSONP。

## 核心常數
| 常數 | 意義 | 常見情境 |
| --- | --- | --- |
| `RTN_DONE` | 操作成功 | 自訂動作完成時回傳 `Done` |
| `RTN_MISSCOLS` | 缺少欄位 | 驗證失敗、欄位未填 |
| `RTN_WRONGDATA` | 資料錯誤 | 欄位格式 / 值不符預期 |
| `RTN_UNVERIFIED` | 驗證失敗 | Token 或簽章不正確 |

## 常用流程與動作
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

## Workflow Action Integration Pattern

When a module action needs workflow control, Reaction is the normal orchestration point.

Recommended sequence:
1. read the target entity and operator context
2. load the workflow JSON from the module's chosen definition source
3. build runtime context from current state, stage, operator role, and any required trace data
4. call WorkflowEngine for `validateDefinition()`, `project()`, `canTransit()`, or `transit()` as needed
5. if the action is accepted, delegate the module-owned workflow log and business-row update to a Feed method that writes them within the same transaction when consistency matters
6. return through `_return()` as usual

Boundaries to keep clear:
- Reaction may coordinate workflow actions, but should not become the long-term owner of workflow schema design
- Reaction should not call `mh()` directly; direct DB access and transaction control belong to Feed
- Feed persists entity data and log rows, but should not absorb generic workflow rule evaluation
- old WorkflowEngine instance persistence entry points should be treated as retired APIs, not as recommended Reaction integration hooks

## Event Trigger Coordination Pattern

When a Reaction handles a user- or content-derived event that may unlock downstream business effects, Reaction should remain the coordination point, not the owner of event truth persistence.

Recommended sequence:
1. read request data and current operator / member context
2. validate the target entity exists and is in an allowed state
3. delegate first-hit truth write to the owning module or module-owned service
4. let the module-owned coordinator decide whether task completion, reward, or log side effects should happen in the same transaction
5. return through `_return()` as usual

Boundaries to keep clear:
- Reaction may validate and coordinate the event, but should not directly own truth tables that belong to another module
- first-hit truth rows and downstream writeback stay in the owning module boundary, even when the entry event originates from another module's route
- evaluators should consume preloaded truth or module-provided context, not query frontend event logs directly inside Reaction

Concrete F3CMS example:
- `rPress::do_seen()` may validate member login and published press existence, then delegate to a module-owned coordinator such as `kDuty::completeTasksForSeenTarget()`
- the actual `member_seen` truth row remains member-owned, while task completion and reward writeback remain duty / task / manaccount-owned side effects

## 擴充掛點

這幾支常被一起提到，但概念上應分成三層，而不是都視為同一種「response hook」。

### 1. Input Normalization Hook
| 方法 | 時機 | 用途 |
| --- | --- | --- |
| `beforeSave(array $params)` | `do_save()` 寫入前 | 正規化輸入、補預設值、拆解複合欄位。預設直接回傳原資料。|

使用邊界：
- 可做欄位正規化、預設值補齊、型別整理與 save 前的輸入重組
- 若 normalization 需要穩定 lookup，可發生資料庫讀取
- 不應在這裡做跨 module 協調
- 不應在這裡決定 workflow / event side effect

### 2. Response Transform Hooks
| 方法 | 時機 | 用途 |
| --- | --- | --- |
| `handleIteratee(array $row)` | `do_list()` 每筆資料 | 將列表 row 轉成 API / 後台列表需要的輸出 shape。|
| `handleRow(array $row)` | `do_get()` 取單筆後 | 將 detail row 轉成單筆 API / 表單需要的輸出 shape。|

使用邊界：
- 這兩支是 row-level response transform hook，不是業務協調入口
- 適合做顯示層級欄位整形、label 補齊、輕量結構重組
- 若同一個 transform 被多個 module 重複使用，應抽到 module-owned helper，例如 `kPress::decorateListRow()`、`kPress::decorateDetailRow()`，再由各自的 Reaction hook 委派
- 不建議直接跨調另一個 Reaction 的 `handleIteratee()` 或 `handleRow()` 當共用 formatter

### 3. Response Emitter
- `_return($code, $data = [])` 不是一般 hook，而是統一 response envelope 與 transport 的 terminal API
- 它負責 `code / data / csrf`、JSON / JSONP 輸出與 response 終止，不應與 row transform hook 混為同類

> 建議：在自訂 Reaction 類別中覆寫 `beforeSave / handleIteratee / handleRow` 以保持 `do_*` 流程簡潔；若 transform 已跨多個 caller 穩定共用，應提升為 module-owned presenter/helper，而不是讓 Reaction 彼此互相依賴。

## 輔助工具
- **`_parseBackendQuery($query)`**：把後台 query string（`a=1&b=2`）轉成 `Feed::genFilter()` 可用的陣列，並將 `=`→`:`、`&`→`,`。
- **`formatMsgs()`**：維護錯誤碼與訊息對照，可在前端顯示人性化錯誤文字。
- **`_return($code, $data = [])`**：統一輸出格式並刷新 CSRF token，支援 JSONP callback（`__jp*`、`ng_jsonp_callback_*`）。開發自訂方法時應始終使用此函式。
- **`_hashKey($action, $args, $secret)`**：依 Feed `hashGrid()` 定義的欄位順序組字串，產生 HMAC-SHA256 簽章（每 300 秒滾動一次）。適用於防重播或權限驗證場景。

## 實作建議
1. **集中權限檢查**：所有 `do_*` 方法在進入核心邏輯前應呼叫 `chkAuth()` 與必要的 `kStaff::_chkLogin()`，確保與 Feed 權限旗標一致。
2. **永遠回傳標準格式**：避免直接 `echo json_encode()`，統一走 `_return()` 以免遺漏 CSRF 更新或 JSONP 判斷。
3. **共用掛點**：盡量在覆寫 `beforeSave / handleIteratee / handleRow` 處理欄位轉換，不要把大量資料整形塞入 `do_*` 主流程；但若 transform 已被多個 module 重用，應抽到 module-owned helper，而不是直接跨調另一個 Reaction 的 hook。
4. **前後端命名同步**：沿用 [../guides/create_new_module.md](../guides/create_new_module.md) 的命名規範，保持 API/DB 欄位一致，降低資料映射複雜度。
5. **與 Feed 協作**：若需要額外查詢條件、輸出欄位、DB write 或 transaction，優先擴充 Feed；Reaction 僅負責組合條件、workflow judgment 與輸出。

## 常見踩雷
- 把資料驗證、查詢拼裝或商業規則大量塞進 `do_*`：這會讓 Reaction 變胖，也會破壞與 Feed 的責任分工。
- 在 Reaction 中直接呼叫 `mh()`：這違反第一級 FORK 分工；直接 DB access 與 transaction 應回到 Feed。
- 直接輸出 JSON 而不走 `_return()`：容易漏掉 CSRF 更新、JSONP 處理與統一回傳格式。
- 忘記先做 `chkAuth()` 或 `kStaff::_chkLogin()`：功能能跑不代表權限正確，這類遺漏通常會在上線後才暴露。
- 在 `handleIteratee()` 或 `handleRow()` 做過重查詢：若需要複雜資料組合，優先回 Feed 補查詢支援，或抽到 module-owned presenter/helper。
- 把 `beforeSave()` 當成 side effect orchestration 入口：它可以做 normalization，甚至可包含必要 lookup，但不應在這裡協調跨 module 流程或決定 workflow / event side effect。
- 直接跨調另一個 Reaction 的 `handleIteratee()` / `handleRow()`：這通常表示 stable response transform 已超出單一 hook 的範圍，應改抽到 module-owned presenter/helper。

以上內容涵蓋 Reaction 層最常被覆寫與呼叫的方法。若模組需要自訂流程，可在 `r{Module}` 中延伸 `do_*` 方法並透過本指南掌握既有行為。

## Related Documents
- [intro.md](intro.md)
- [feed_reference.md](feed_reference.md)
- [outfit_reference.md](outfit_reference.md)
- [../guides/feed_guide.md](../guides/feed_guide.md)
- [../guides/module_design.md](../guides/module_design.md)
- [../guides/create_new_module.md](../guides/create_new_module.md)

## Status
- Draft v1 aligned with reference entry
