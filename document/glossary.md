# F3CMS 常用術語表

## Purpose
- 整理 F3CMS 在架構、資料建模、模組設計與日常開發中最常出現的術語。
- 幫助新工程師、SD、SA 與 reviewer 快速對齊用字。
- 作為 guides 與 reference 之間的共用名詞表。

## 使用方式
- 若你是第一次接觸 F3CMS，先讀「架構層級」與「資料建模」兩節。
- 若你正在開發模組，優先查「模組與命名」及「常用常數」。
- 若你在 review 文件或 PR，可用本頁確認用字是否一致。

## 架構層級

### F3CMS
一套以模組化、分層責任與實體導向建模為核心的 CMS 架構，適合中小型網站、內容型站點與一般政府網站。

### Hierarchical FORK
F3CMS 的核心架構思維，強調將不同責任拆到不同層級與元件中，避免把資料、流程、畫面與工具邏輯混在同一處。

### Module
系統中的功能單位，通常以單一 Entity 為中心組成。常見檔案包含 feed.php、reaction.php、outfit.php、kit.php。

### Feed
資料層。負責資料讀寫、查詢、分頁、欄位處理、語系表與 meta 的保存，以及與資料表結構直接對應的邏輯。

### Reaction
互動處理層。負責 AJAX / JSON 請求、權限檢查、呼叫 Feed 執行 CRUD，並以統一格式回傳結果。

### Outfit
頁面輸出層。負責 HTML / XML 頁面渲染、SEO 設定、Twig 模板整合、頁面快取與前台輸出流程。

### Kit
工具或服務層。負責不適合放進單一實體 Feed/Reaction/Outfit 的共用工具、外部整合或支援性流程。

### libs
F3CMS 的 shared library 目錄。只應放 shared runtime、parser、evaluator、generic helper 或其他不涉及特定實體操作的基礎設施。若邏輯已涉及 entity truth、payload ownership、workflow / duty 判讀、task writeback、狀態寫回或其他 module-owned business coordination，則不應放在 `libs/`，而應回到對應 owning module。

### WorkflowEngine
位於 `libs/` 的共用 workflow 規則引擎。由 module 先提供 workflow JSON 與 runtime context，再由 engine 進行 definition 驗證、projection、transition guard 與 transition 判定。它不擁有 module 的業務資料表，也不應反向主導 module 的 persistence 設計。

### EventRuleEngine
位於 `libs/` 的共用 event rule 規則引擎。由 owning module 先提供 duty claim payload、player context 與後續 writeback coordination，再由 engine 進行 payload 驗證、AST traversal 與 evaluator 判定。它不擁有 `duty`、`task`、`member_seen` 或 reward persistence，也不應反向要求建立單一 shared EventRuleEngine owning module。

### Guide
偏向設計與決策的文件類型，用來回答「應該怎麼設計」、「邏輯應該放哪裡」。

### Reference
偏向查表與操作的文件類型，用來回答「有哪些方法可用」、「現有流程怎麼運作」。

## 資料建模

### Entity
業務上可獨立理解、可長期存在的核心資料單位，例如 Post、Press、Staff。F3CMS 的模組通常從 Entity 開始拆解。

### Entity-first
先辨識 Entity，再決定模組、資料表與流程分層的設計方法。這是 F3CMS 的主要建模思路。

### Main Table
主表。儲存該 Entity 最穩定、最常查詢、非語系化的核心欄位。命名通常為 tbl_{entity}。

### Language Table
語系表。儲存會依語言變化的內容欄位，例如 title、summary、content。命名通常為 tbl_{entity}_lang。

### Meta Table
中繼表。儲存延伸但不夠穩定、不適合直接進主表的 key-value 屬性，例如 SEO、可選設定。命名通常為 tbl_{entity}_meta。

### Relation Table
關聯表。用來表示兩個 Entity 之間的對應關係，常見於多對多或需要獨立排序的關聯場景。

### Audit Fields
稽核欄位。通常包含 insert_ts、last_ts、insert_user、last_user，用來追蹤資料建立與最後更新資訊。

### Runtime Context
由 module 或既有業務資料組出的 workflow 執行期資訊，例如 current_state_code、current_stage_codes、operator_role_constant、operator_id 與 trace_rows。這些資料是 WorkflowEngine 做判定所需的輸入，但不代表 WorkflowEngine 必須擁有專屬 runtime 資料表。

### First-hit Truth
由 owning module 寫入、且一旦第一次成立後就作為穩定判定來源重複使用的 truth row 模式。以 `member_seen` 為例，穩定語意是同一 `(member_id, target, row_id)` 只留下第一次達標紀錄，後續 evaluator 只讀這筆 truth，而不是重算事件歷史。

### Module-owned Workflow Log
由各 module 自己擁有的 workflow audit log 概念實體，例如 press_log、order_log。討論概念時可不帶 `tbl_` 前綴；落到實際資料表時，仍應遵守 F3CMS 命名慣例，例如 `tbl_press_log`。

### Retired API
仍保留在程式中、但只用來明確拒絕新呼叫路徑的舊 API。以 WorkflowEngine 為例，舊的 instance persistence 入口屬於 retired API，新的 module 整合不應再依賴它們。

### Stable Field
穩定欄位。長期存在、常被查詢、常被排序或過濾的欄位，通常應放在主表。

### Localized Field
語系欄位。內容會隨語言改變的欄位，通常應放在 _lang 表。

### Extensible Field
延伸欄位。屬性有效但結構不穩定、不值得升格為主表欄位的資料，通常應放在 _meta 表。

### slug
通常用於網址路徑的語意化識別字串。應保持穩定、可讀、可作為前台路徑的一部分。

## 模組與命名

### f{Module}
Feed 類別命名方式，例如 fPost、fPress、fDraft。

### r{Module}
Reaction 類別命名方式，例如 rPost、rPress。

### o{Module}
Outfit 類別命名方式，例如 oPost、oPress。

### k{Module}
Kit 類別命名方式，例如 kStaff、kSender。

### 模組邊界
指某個需求、資料與流程應屬於哪個 Module，以及哪些責任應放在該 Module 內處理。

### 表名慣例
F3CMS 常以 tbl_ 作為資料表前綴，並以 Entity 名稱作為主體，例如 tbl_post、tbl_post_lang、tbl_post_meta。

## 常用常數

### MTB
Feed 常數，代表主表代碼，不含 tbl_ 前綴。例如 MTB = 'post' 時，主表通常為 tbl_post。

### MULTILANG
Feed 常數，用來宣告模組是否支援多語。若為 1，通常代表系統會結合 _lang 表處理資料。

### BE_COLS
Backend Columns。後台列表查詢常用的預設欄位集合，通常會影響列表效率與顯示內容。

### PAGELIMIT
分頁預設筆數，用於列表與查詢的預設限制。

### PV_R / PV_U / PV_D
權限常數，分別代表讀取、更新、刪除所需的權限值，常由 Reaction 在進入流程前檢查。

### HARD_DEL
控制是否允許硬刪除的常數。若未開啟，某些刪除流程會被阻擋或需改採軟刪策略。

### PK_COL
主鍵欄位常數，預設通常是 id；在特殊情境下可用於調整主鍵欄位名稱或查找方式。

## 常見流程與操作名詞

### CRUD
Create、Read、Update、Delete 的縮寫，即新增、讀取、更新、刪除的基本資料操作。

### do_list / do_save / do_get / do_del
Reaction 中常見的標準動作名稱，分別代表列表、保存、取單筆、刪除等既有互動流程。

### render
Outfit 常用輸出方法，用來渲染頁面模板並帶入 SEO、麵包屑、canonical 等頁面資訊。

### wrapper
Outfit 的包覆式渲染方法，通常用於需要共用頁首、頁尾、導覽等標準頁面框架的輸出。

### _staticFile
Outfit 的靜態化輸出流程，會依環境與快取狀態決定直接渲染或讀取靜態快取內容。

### handleIteratee
Reaction 在列表輸出時針對每筆資料的 response transform hook。適合做列表 row 的顯示層級整形；若同一個 transform 被多個 module 穩定共用，應升格為 module-owned presenter/helper，而不是直接跨調另一個 Reaction 的 `handleIteratee()`。

### handleRow
Reaction 在單筆資料輸出時的 response transform hook。適合補 detail row 的表單或畫面結構；若多個 caller 需要同一個 detail transform，應升格為 module-owned presenter/helper。

### beforeSave
Reaction 在進入 Feed::save() 之前的 input normalization hook，可用於正規化輸入、補預設值與必要 lookup。它可以發生資料庫讀取，但不應承擔跨 module 協調，也不應在此決定 workflow / event side effect。

### _return
Reaction 的 response emitter。負責統一輸出 `code / data / csrf` envelope，並處理 JSON / JSONP transport；它不是 row transform hook，而是 response pipeline 的終點。

### Module-owned Presenter
由單一 module 擁有的 response transform helper，用於承接穩定、可被多個 caller 共用的 row-decoration 或輸出 shape 邏輯，例如 `decorateListRow()`、`decorateDetailRow()`。它的角色是避免多個模組直接跨調另一個 Reaction 的 `handleIteratee()` / `handleRow()`。

### Smoke Suite
位於 `www/tests/smoke/` 下、可直接執行的一個最小驗證契約或一組高度相關情境。它是測試本體，而不是 CLI、Lab 或 legacy scripts 的附屬物。

### Canonical Smoke Path
smoke suite 的正式主路徑，通常採用 `www/tests/smoke/<domain>/<contract>.php` 形式，例如 `workflow_engine/instance_api.php`、`event_rule_engine/basic_or_rule.php`。一旦 canonical path 建立完成，它就成為 source of truth，不應再由舊扁平命名或過渡 wrapper 取代。

### Thin Wrapper
只保留舊命令相容性的過渡層檔案。它只能做參數轉發、載入對應 suite 或回傳 exit code，不應承載獨立 bootstrap、fixture、helper 或 assertion 邏輯。

### Wrapper Retirement
在 canonical path、文件引用與驗證命令都已完成切換後，批次移除 legacy thin wrapper 的正式收尾階段。進入這個階段前，必須先確認現行程式入口與文件不再把 wrapper 視為 source of truth。

## 文件與角色用語

### SA
System Analyst。偏向需求分析、業務拆解與系統需求整理。

### SD
System Designer。偏向模組拆解、資料建模、欄位規劃與技術方案設計。

### Reviewer
負責檢查設計、文件或程式變更是否符合分層責任、建模原則與團隊規範的人。

## 常見判斷句

### 邏輯應該放在 Feed
通常代表該邏輯直接與資料保存、查詢、欄位處理、資料表結構或關聯處理有關。

### 邏輯應該放在 Reaction
通常代表該邏輯與請求流程、權限檢查、輸入整理或 API 回傳格式有關。

### 邏輯應該放在 Outfit
通常代表該邏輯與頁面輸出、模板變數、SEO、靜態快取或前台呈現有關。

### 這是建模問題，不是函式問題
表示目前應先回頭確認 Entity、欄位歸屬與資料表拆分，而不是直接找某個現成方法來硬套。

## Related Documents
- [guides/index.md](guides/index.md)
- [guides/overall.md](guides/overall.md)
- [guides/data_modeling.md](guides/data_modeling.md)
- [guides/module_design.md](guides/module_design.md)
- [reference/intro.md](reference/intro.md)

## Status
- Draft v1
