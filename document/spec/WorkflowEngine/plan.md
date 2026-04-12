# WorkflowEngine Plan

## Purpose
- 將 `idea.md` 中已確認的 WorkflowEngine 決議拆成可執行的實作階段。
- 控制第一版範圍，避免直接滑向過大重構案。
- 讓後續 `(done)` 與 `check` 都能有明確依據。

## Plan Basis

本計畫直接沿用 `idea.md` 中已確定的 discuss 結論，不重複討論以下事項：
- engine 放在 `libs/`，供各 Module 呼叫
- runtime 資料來源採 JSON
- 支援退回、分支、並行、擇一等進階節點類型
- 權限改為具業務語意的角色代碼常數
- 流程定義與流程實例分離
- 必須處理歷史 edge cases
- 驗收需包含實際 module integration

## Scope

第一版要完成：
- WorkflowEngine 的資料模型與 JSON 結構定義
- `libs/` 內可被模組呼叫的 engine 核心介面
- definition / instance 分離的資料設計
- 角色代碼常數化策略
- 基本 stage / transition / role guard 能力
- 進階節點類型支援策略
- 至少一個實際模組呼叫情境

第一版不預設完成：
- 全系統舊 XML 一次性全面遷移
- 完整後台管理 UI
- 所有歷史流程一次性導入上線

## High-Risk Areas

- 進階節點類型的資料模型若定義不清，後續 parser 與 instance 執行邏輯會反覆重寫。
- 權限魔術數字改成角色常數後，若缺少 mapping 策略，舊資料難以落地。
- 若 definition / instance 分離不夠清楚，容易把設定資料與執行中的案件資料混在一起。
- 若先做 engine 類別、後補 schema，可能會出現 API 與資料模型對不起來的情況。
- 歷史 edge cases 若只口頭支持、未進入驗收案例，第一版很容易實際不支援。

## Dependencies

- [idea.md](idea.md)
- [../../flow.md](../../flow.md)
- [../../guides/data_modeling.md](../../guides/data_modeling.md)
- [../../guides/module_design.md](../../guides/module_design.md)
- [../../guides/create_new_module.md](../../guides/create_new_module.md)
- [../../guides/pr_review_checklist.md](../../guides/pr_review_checklist.md)
- 現有 legacy XML / flow 樣本

## Stage Plan

### Stage 1: 定義第一版資料模型與 JSON Contract

目標：
- 明確定義 workflow definition 與 workflow instance 的資料邊界
- 定出第一版 JSON contract
- 收斂 stage、state、transition、role constant、edge case 所需欄位

主要工作：
- 定義 workflow definition 的必要欄位
- 定義 workflow instance 的必要欄位
- 設計 stage type、transition、rollback、parallel、branch、any-of 的 JSON 表達方式
- 定義角色代碼常數命名規則與 mapping 策略
- 選定 1 至 2 個歷史流程作為驗證樣本

輸出：
- JSON contract 草案
- definition / instance 資料結構草案
- 角色常數策略草案

驗收重點：
- 能明確回答 definition 與 instance 各存什麼
- 能表示退回、分支、並行、擇一
- 不再依賴 magic numbers 作為長期設計語言

#### Stage 1 草案輸出

##### A. Definition 與 Instance 的資料邊界

workflow definition 負責描述「流程本身的規則」，至少包含：
- `workflow_code`：流程代碼，例如 `PSC`、`SJSE`
- `workflow_title`：流程名稱
- `workflow_version`：流程版本
- `workflow_status`：definition 狀態，例如 `draft`、`active`、`archived`
- `source_type`：目前來源型別，第一版預期為 `json`
- `entry_stage_code`：起始 stage
- `terminal_state_codes`：終端狀態代碼集合
- `role_constants`：本流程可用的角色常數定義
- `stages`：stage 定義集合
- `transitions`：transition 定義集合
- `meta`：備註、顯示設定、相容資訊等擴充欄位

這裡的 workflow instance 指的是「邏輯上的執行狀態模型」，用來說明 engine 判定所需的 runtime information；它不等於第一版必須由 WorkflowEngine 擁有或持久化的專屬 instance table。

workflow instance / runtime context 負責描述「某一筆案件正在怎麼跑」，至少包含：
- `instance_id`
- `workflow_code`
- `workflow_version`
- `biz_module`：由哪個 F3CMS module 呼叫
- `biz_entity_type`：對應的業務主體類型
- `biz_entity_id`：對應的業務主體 id
- `current_stage_codes`：目前所在 stage，可支援 parallel 情境
- `current_state_code`：目前狀態代碼
- `available_action_codes`：當前可執行動作
- `payload`：執行期資料
- `started_by`
- `started_at`
- `updated_at`
- `closed_at`

補充：
- definition 與 instance 在邏輯模型上必須分開
- 第一版的 runtime context 可由 module 或既有業務資料承接，不要求 WorkflowEngine 擁有專屬 instance persistence
- 若 module 需要長期保存流轉紀錄，應由 module-owned log / trace 類型資料承接，而不是回頭替 WorkflowEngine 建共用 runtime 表

##### B. Stage 定義草案

第一版 stage 建議至少包含以下欄位：
- `stage_code`
- `stage_title`
- `stage_type`
- `allowed_role_constants`
- `description`
- `ui_hint`
- `action_mode`
- `join_policy`：給 parallel / any-of 類型使用
- `rollbackable_to`
- `meta`

第一版 `stage_type` 建議先收斂為：
- `single`：單一路徑節點
- `any_of`：擇一可通過
- `parallel`：並行節點
- `branch`：條件分支節點
- `terminal`：流程終點

補充：
- 「退回」較適合作為 transition 行為，而不是 stage type
- 「擇一」與「並行」本質上是 stage 的處理規則，因此保留在 `stage_type` / `join_policy`

##### C. Transition 定義草案

第一版 transition 建議至少包含：
- `transition_code`
- `from_stage_code`
- `action_code`
- `action_label`
- `transition_kind`
- `to_stage_code`
- `to_state_code`
- `guard_rule`
- `effect_rule`
- `priority`
- `meta`

第一版 `transition_kind` 建議支援：
- `forward`
- `rollback`
- `branch`
- `parallel_fork`
- `parallel_join`
- `terminate`

##### D. 角色常數策略草案

第一版不再直接使用 `2`、`4`、`8`、`16` 這類 magic numbers 作為流程配置語言。

建議改為：
- `ROLE_APPLICANT`
- `ROLE_DEPT_ADMIN`
- `ROLE_DEPT_CHAIR`
- `ROLE_EDITOR`
- `ROLE_REVIEWER`
- `ROLE_COURSE_ADMIN`

補充策略：
- 若 legacy XML 仍使用數字，應透過 mapping layer 轉為角色常數
- role constants 應先做流程層可讀命名，再由系統層對應到實際權限系統

##### E. JSON Contract 草案

```json
{
	"workflow": {
		"code": "PSC",
		"title": "任教專門科目認證申請流程",
		"version": 1,
		"status": "active",
		"sourceType": "json",
		"entryStageCode": "LOCK",
		"terminalStateCodes": ["DONE", "REJECTED", "ABORTED"],
		"roleConstants": {
			"ROLE_APPLICANT": "申請人",
			"ROLE_DEPT_ADMIN": "系辦",
			"ROLE_DEPT_CHAIR": "系主任",
			"ROLE_COURSE_ADMIN": "課程組"
		},
		"stages": [
			{
				"stageCode": "LOCK",
				"stageTitle": "鎖定",
				"stageType": "single",
				"allowedRoleConstants": ["ROLE_APPLICANT"]
			},
			{
				"stageCode": "DEPT_REVIEW",
				"stageTitle": "系辦審查",
				"stageType": "any_of",
				"allowedRoleConstants": ["ROLE_DEPT_ADMIN"]
			}
		],
		"transitions": [
			{
				"transitionCode": "LOCK_TO_DEPT_REVIEW",
				"fromStageCode": "LOCK",
				"actionCode": "SUBMIT",
				"actionLabel": "送出",
				"transitionKind": "forward",
				"toStageCode": "DEPT_REVIEW",
				"toStateCode": "LOCKED"
			},
			{
				"transitionCode": "DEPT_REVIEW_REJECT",
				"fromStageCode": "DEPT_REVIEW",
				"actionCode": "REJECT",
				"actionLabel": "未通過",
				"transitionKind": "terminate",
				"toStageCode": null,
				"toStateCode": "REJECTED"
			}
		]
	}
}
```

##### F. Stage 1 驗證樣本

第一版先用以下歷史流程做資料模型驗證：
- `PSC`：驗證線性流程與角色審核鏈
- `SJSE`：驗證退回、分支、修正稿件、送三審等 edge cases

`QEII` 可作為後續補充樣本，用於驗證特定評審指派或特殊權限場景。

##### G. Stage 1 未解項目

以下項目已於本輪補細，作為 Stage 1 的正式收斂結果。

##### H. Join Policy 草案

`parallel` 與 `any_of` 不只需要 `stage_type`，還需要明確的 join 規則，避免 engine 在執行期無法判斷何時可進入下一步。

第一版建議在 stage 中加入：
- `joinPolicy.type`
- `joinPolicy.requiredBranches`
- `joinPolicy.successRule`
- `joinPolicy.failRule`

建議語意如下：

```json
{
	"joinPolicy": {
		"type": "all_of",
		"requiredBranches": ["REVIEW_A", "REVIEW_B"],
		"successRule": "all_pass",
		"failRule": "any_reject"
	}
}
```

第一版先支援：
- `all_of`：所有分支都完成才可 join
- `any_of`：任一分支達成即可往下
- `quorum`：達到指定數量才可往下

補充規則：
- `parallel` stage 預設使用 `all_of`
- `any_of` stage 預設使用 `any_of`
- 若為 `quorum`，需額外定義 `requiredCount`

因此 `joinPolicy` 最小表達可擴充為：

```json
{
	"joinPolicy": {
		"type": "quorum",
		"requiredBranches": ["R1", "R2", "R3"],
		"requiredCount": 2,
		"successRule": "reach_quorum",
		"failRule": "no_possible_quorum"
	}
}
```

##### I. Instance Trace / History 最小資料結構

workflow instance 不應只記錄 `current_stage_codes` 與 `current_state_code`，否則無法處理 audit、rollback、parallel trace 與 edge cases。

第一版最小 trace 結構建議獨立於 instance 主物件，至少包含：
- `trace_id`
- `instance_id`
- `from_stage_codes`
- `to_stage_codes`
- `from_state_code`
- `to_state_code`
- `action_code`
- `action_label`
- `transition_kind`
- `operator_role_constant`
- `operator_id`
- `comment`
- `payload_snapshot`
- `created_at`

建議 JSON 表達如下：

```json
{
	"traceId": "trace_0001",
	"instanceId": "inst_1001",
	"fromStageCodes": ["DEPT_REVIEW"],
	"toStageCodes": ["CHAIR_REVIEW"],
	"fromStateCode": "IN_REVIEW",
	"toStateCode": "PASSED_DEPT",
	"actionCode": "APPROVE",
	"actionLabel": "通過",
	"transitionKind": "forward",
	"operatorRoleConstant": "ROLE_DEPT_ADMIN",
	"operatorId": 35,
	"comment": "資料齊全，送下一關",
	"payloadSnapshot": {},
	"createdAt": "2026-04-10T10:00:00+08:00"
}
```

第一版結論：
- trace / history 應視為獨立結構，而不是只塞進 instance 的單一 JSON 欄位
- schema 階段可再決定是獨立資料表還是 append-only JSON log，但邏輯模型上必須獨立

##### J. Legacy XML 轉新模型的最小 Mapping 欄位

第一版不要求全面遷移全部舊 XML，但至少要先定義最小 mapping 欄位集合，讓 schema 與匯入策略可落地。

建議最小 mapping 如下：
- `legacy_flow_code`：原 flow code，例如 `PSC`
- `legacy_stage_code`：原 stage code，例如 `1`、`2`、`A`
- `legacy_stage_type`：原 type 數值
- `legacy_state_code`：原 state code，例如 `ff`、`fc`、`9`
- `legacy_role_levels`：原 `Privlevellist` 數字集合
- `mapped_role_constants`：轉換後角色常數集合
- `legacy_label`：原始中文標題
- `mapping_note`：人工補充說明
- `source_version`：來源規格版本或匯入批次

對應原則：
- legacy code 不直接作為新系統長期語意，但必須保留可追溯性
- `legacy_stage_type` 應透過 mapping 規則轉成新系統的 `stage_type` 與 `joinPolicy`
- `legacy_role_levels` 應透過 mapping layer 轉成 `mapped_role_constants`

##### K. Guard / Effect Rule 暫行結論

`guard_rule` / `effect_rule` 先保留在 contract 欄位中，但第一版不要求完整 declarative engine。

第一版策略：
- contract 中保留欄位
- 先允許簡單 declarative 條件，例如欄位存在、狀態符合、角色符合
- 複雜效果先由程式層處理，不在 Stage 1 強行設計完畢

這樣可避免 Stage 1 因為過度設計 rule engine 而失焦。

### Stage 2: 規劃 F3CMS 整合邊界與檔案落點

目標：
- 明確定義 WorkflowEngine 在 F3CMS 內的實際落點與呼叫方式
- 決定 `libs/` engine 與模組層之間如何互動

主要工作：
- 定義 `libs/WorkflowEngine` 類別責任
- 定義哪些邏輯在 engine、哪些留給 Feed / Reaction / Outfit / Kit
- 規劃至少一個 module integration 情境
- 確認是否需要新增共用 Feed、Kit 或 helper 支撐 workflow definition / instance

輸出：
- engine 類別責任說明
- module integration 草案
- 檔案落點與分層責任草案

驗收重點：
- 可以明確說明 Module 要如何呼叫 engine
- 不會把 workflow engine 做成難以重用的單一模組私有邏輯

#### Stage 2 邊界收斂

依最新用例，Stage 2 的呼叫契約固定如下：

- `WorkflowEngine` 位於 `libs/`
- 各 module 的 `Reaction` / `Outfit` / `Kit` 都可以初始化並呼叫 engine
- module 在初始化 engine 前，需先取得「目標流程 JSON」
- engine 本身負責流程判定、檢查與操作
- `Feed` 不負責 workflow 規則判定；`Feed` 只負責業務資料持久化與查詢
- `Reaction` 負責 action request 與 backend response 協調
- `Outfit` 負責顯示用途的 stage / action / next-step projection
- `Kit` 負責 module-local 的 workflow helper 或共用包裝

### Stage 3: 設計 definition 來源與非專屬持久化策略

目標：
- 在維持 `libs/WorkflowEngine` 為共用元件的前提下，定義 workflow JSON 的來源方式與 runtime context 的承接邊界。

主要工作：
- 定義 module 如何提供 workflow definition JSON
- 定義 runtime state 應由哪一層承接，而不是由 WorkflowEngine 自帶專屬資料表
- 定義 legacy XML 轉入新模型時需要保留的 mapping 資訊，但不落成 WorkflowEngine 專屬表
- 定義 branch / parallel / trace 所需資訊應如何由 module 或既有業務資料承接

輸出：
- definition source strategy 草案
- runtime ownership 草案
- legacy mapping 與 trace 承接原則

驗收重點：
- definition / instance 在邏輯模型上仍保持分離
- WorkflowEngine 不依賴專屬 `tbl_workflow_*` 資料表
- module 仍可提供足夠 runtime context 讓 engine 支援進階節點類型

#### Stage 3 草案輸出

##### A. Definition 來源策略

第一版的 workflow definition 不再由 WorkflowEngine 專屬資料表承接，而改由 module 或既有系統來源提供。

允許的來源：
- module-local `flow.json`
- 既有業務資料中的 JSON 欄位
- cache / import result / 設定檔產物

原則：
- WorkflowEngine 只接收 workflow JSON，不擁有 definition 專屬表
- definition 的保存位置由 module 或既有資料模型決定，不由 lib 反向主導
- `definition_json` 仍是 canonical source，但它是邏輯概念，不等於必須有 `tbl_workflow_definition`

##### B. Runtime Context 承接策略

第一版的 runtime state 仍需要以下資訊，但不再以 WorkflowEngine 專屬表保存：
- 目前 state
- 目前 stage codes
- 可執行 action 的判定所需 context
- operator role / operator id
- branch / parallel 所需的最小判定資訊

承接原則：
- 這些資訊應由 module 的既有業務表、業務欄位或既有 log / trace 機制承接
- WorkflowEngine 只負責判定與操作，不負責自建 runtime persistence schema
- 若某個 module 無法提供足夠 runtime context，需回到 module integration 設計補足，而不是回頭替 lib 建專屬資料表

第一版新增收斂：
- 各 module 在導入 WorkflowEngine 控制 flow 時，應有自己的 log table 來承接 workflow 操作紀錄
- log table 應跟隨 module 業務命名，例如 `press_log`、`order_log`
- `press_log`、`order_log` 是 module-owned workflow log 的概念名稱；實際落地時仍以 module 當前資料表名為準，例如 `Press` 目前使用 `tbl_press_log`
- 這些 log table 仍屬 module-owned persistence，不是 WorkflowEngine 專屬 schema

##### C. Legacy Mapping 策略

legacy XML 的 mapping 需求仍保留，但第一版不再以 WorkflowEngine 專屬對照表落地。

第一版做法：
- 先保留在 workflow JSON 的 `meta` 區塊
- 或由 module / import tool 的既有資料結構承接

原則：
- lib 需要看得到 mapping 結果
- 但 mapping 的持久化責任不落在 WorkflowEngine 專屬 schema

##### D. Trace / Branch / Parallel 承接原則

branch、parallel、join 所需資訊仍是第一版必要能力。

第一版原則：
- `branch_token`
- `parallel_group_code`
- `join_group_code`
- `staff_id`
- `old_state_code`
- `new_state_code`
- `action_code`
- `acted_at`

以上資訊可存在 runtime context、module log、既有業務 trace 或其他非 WorkflowEngine 專屬保存位置；若 module 需要長期可稽核紀錄，第一版預期是落在 module 自己的 log table。

這代表：
- 被推翻的是「WorkflowEngine 專屬 trace table」
- 不是被推翻「需要 trace 資訊才能支撐 edge cases」

##### D-1. Module Log 最小要求

當 module 透過 WorkflowEngine 控制 flow 時，第一版建議至少準備一張 module-owned log table。

例如：
- `Press` 對應 `press_log`
- `Order` 對應 `order_log`

最小欄位建議：
- `module_entity_id` 或等價業務主鍵
- `staff_id`
- `action_code`
- `old_state_code`
- `new_state_code`
- `created_at`
- `remark` 或等價補充欄位

若 module 已有既有 log table，可優先沿用；若沒有，則應由 module 自己新增，而不是回頭替 WorkflowEngine 建共用 log table。

##### E. Stage 3 收斂結果

經本輪回退後，Stage 3 前置決策如下：
- workflow JSON 仍是 canonical source
- WorkflowEngine 不應擁有專屬 definition / instance / trace 資料表
- runtime state 與 trace 應由 module 或既有業務資料承接
- 各 module 在需要 workflow audit trail 時，應以自己的 log table 承接 staff id、時間、新舊狀態與 action 記錄
- legacy mapping 先保留在 JSON `meta` 或 import tool context，不另開 WorkflowEngine 專屬表
- 是否需要長期持久化，屬 module / 業務資料設計議題，不由 lib 層先行決定

以上決策足以支撐下一輪繼續收斂 module integration 與最小驗證路徑，不需回退到 `idea` 或 `discuss`

### Stage 4: 實作 engine 核心能力

目標：
- 建立可被模組呼叫的 WorkflowEngine 核心能力

主要工作：
- 實作 JSON 載入與基本驗證
- 實作 stage / transition 查詢能力
- 實作 role guard
- 實作 transition guard
- 實作退回、分支、並行、擇一的核心判斷邏輯

輸出：
- `libs/` 內可呼叫的 engine 類別初版
- 對應的最小使用範例

驗收重點：
- 可依 definition 與 instance 判斷當前可執行步驟
- 非法 transition 與非法角色會被阻擋
- 進階節點類型至少有可驗證的核心行為

### Stage 5: 實作 F3CMS 模組整合情境

目標：
- 讓 engine 真正被某個 F3CMS 模組呼叫，而非停留在孤立 library

主要工作：
- 選定示範模組或建立最小整合模組
- 串接 workflow definition 與 module-owned runtime context
- 串接 workflow action 觸發流程
- 驗證模組端如何取得當前 stage、可執行 action 與下一步判斷

輸出：
- 一個實際 module integration 路徑
- 最小 smoke test 情境

驗收重點：
- engine 已被 F3CMS 模組實際呼叫
- 非只是 parser demo

### Stage 6: 歷史 edge cases 驗證與文件同步

目標：
- 以歷史複雜流程驗證第一版設計是否站得住腳
- 補齊 check 與需要同步的文件

主要工作：
- 以代表性流程驗證退回、分支、並行、擇一等情境
- 檢查角色常數與 legacy mapping 是否可行
- 補齊 `check.md`
- 視需要更新 glossary、guides、references 或其他相關文件

輸出：
- edge case 驗證結果
- 文件同步清單

驗收重點：
- 至少 1 至 2 個複雜歷史流程可被合理表達或驗證
- 第一版限制與未解項目有被明確記錄

## Suggested Commit / PR Cut

- PR 1: Stage 1-2，先做資料模型、JSON contract、邊界定義
- PR 2: Stage 3-4，補 schema 與 engine 核心能力
- PR 3: Stage 5-6，補 module integration、edge case 驗證與文件同步

## 實作起手順序決議

第一版採用：先落 schema，再落 `libs/WorkflowEngine` 類別骨架，最後再做 module integration。

### 採用理由
- Stage 1 與 Stage 3 已經把 definition 與 module-owned runtime / trace 承接策略收斂到足以支撐實作。
- WorkflowEngine 的 runtime 雖以 JSON 為輸入，但 definition source 與 runtime context 的責任邊界若未先定下，engine 類別的載入介面會很容易反覆改寫。
- 先把 definition 來源與 runtime 承接策略定下來，可以讓後續 `libs/WorkflowEngine` 的 API 更清楚地圍繞 workflow JSON 與 runtime context 運作，而不是先寫一個脫離 repo 的抽象 parser。

### 第一版建議順序
1. 先完成 workflow definition 與 module-owned runtime / trace 承接策略
2. 再建立 `libs/WorkflowEngine` 類別骨架與最小介面
3. 再串接第一個 module integration 樣本

### 不採用「先寫 engine、後補 schema」的原因
- 容易讓 engine 直接綁死暫時性的 payload 結構
- 會讓 definition 與 projection 的責任分界在程式碼中變得模糊
- 之後接 schema 時，很容易出現 API 與資料表設計互相牽制的問題

## 第一個 Module Integration 樣本決議

第一個 integration 樣本選擇：`Press`

### 選擇理由
- `Press` 屬於內容型模組，較接近 workflow engine 常見的審核 / 發布 / 狀態流轉情境。
- 相較於 `Draft` 這類較偏工作草稿或暫存用途的模組，`Press` 更適合作為「實際業務模組呼叫 workflow engine」的驗收樣本。
- `Press` 可用來驗證內容項目在不同審核階段的狀態控制，而不需要一開始就引入過於特殊的業務規則。

### 第一版 integration 範圍
- 不要求將整個 `Press` 模組改造成完整 workflow 系統
- 只要求建立一個最小整合路徑，能驗證：
	- module 可讀取 workflow definition
	- module 可組出 / 讀取 workflow runtime context
	- module 可向 engine 詢問目前可執行步驟與下一步判斷

### `Press` 最小 workflow 掛點

目前最適合作為第一個 integration hook 的單一入口為：
- 檔案：`www/f3cms/modules/Press/reaction.php`
- 方法：`rPress::do_published()`

### 採用理由
- 這是一個明確的「狀態切換 action」，不是泛用的 save/update 流程，因此比 `do_save()` 更適合做第一個 workflow hook。
- 現有實作已在此方法內完成登入檢查、讀取目標資料、依 `status` 做分支，最後呼叫 `fPress::published($req)` 寫回狀態，責任邊界清楚。
- 目前 `tbl_press.status` 已有穩定列舉：`Draft`、`Published`、`Scheduled`、`Changed`、`Offlined`，足以作為第一版 workflow state 對照樣本。
- 若先在這個點導入 engine，可先驗證「是否允許從當前狀態執行某個 action」與「合法 transition 後才真正寫回狀態」這兩件核心能力。

### 第一版整合方式
- 第一版不直接改造整個 `Press` 的 save 流程。
- 先把 `do_published()` 視為 module 對 workflow engine 發出 action request 的入口。
- 由該入口帶入：`biz_module = Press`、`biz_entity_type = press`、`biz_entity_id = $req['id']`、`action_code` 與目標狀態切換資訊。
- engine 驗證通過後，再進入原本的 `fPress::published($req)` 狀態寫回路徑。

### 目前可直接對照的狀態
- `fPress::ST_DRAFT`
- `fPress::ST_PUBLISHED`
- `fPress::ST_SCHEDULED`
- `fPress::ST_CHANGED`
- `fPress::ST_OFFLINED`

### 目前已知限制
- `do_published()` 現況仍是直接接收外部傳入的 `status` 後寫回，因此真正導入 engine 時，應由 workflow transition 決定允許的 target state，而不是單純信任 request。
- 若後續確認 `Press` 還有另一個更早的後台 action 會先改變 `status`，則需回退調整的是本段「`Press` 最小 workflow 掛點」，不是回退到 `idea`。

## 第二批 Module-owned Runtime Context 決議

以 `rPress::do_published()` 目前的實際路徑來看，第二批不再討論 WorkflowEngine 自有 instance / trace table，而是定義 `Press` 最小整合時必須交給 engine 的 runtime context。

### `Press` 第一版最小 runtime context
- `biz_module = Press`
- `biz_entity_type = press`
- `biz_entity_id = press_id`
- `current_state_code`
- `current_stage_codes`
- `operator_id`
- `operator_role_constants`

### 視需要再補的 context
- `available_action_codes`
- `branch_token`
- `parallel_group_code`
- `join_group_code`
- `comment`
- `payload_snapshot`

### `Press` 第一版最小 log 承接
- `tbl_press_log` 這類 module-owned log table
- 至少能記錄：`insert_user`（staff id）、`action_code`、`old_state_code`、`new_state_code`、`insert_ts`
- 若有需要，再補 `remark`、`branch_token`、`parallel_group_code`、`join_group_code`

### `Press` 的 `tbl_press_log` 最小欄位策略

在本 spec 中，`press_log` 用來表示 `Press` 的 module-owned workflow log 這個概念實體；落到目前 F3CMS 的實際資料表名時，`Press` 端對應的是 `tbl_press_log`。這不是新舊命名規則切換，而是概念層名稱與實體表名的區分。另因 `_trace` 已保留給另一套子表機制，所以這裡的實際落地表名採 `tbl_press_log`，並沿用統一的 `parent_id` schema。

最小欄位建議：
- `id`
- `parent_id`
- `insert_user`
- `action_code`
- `old_state_code`
- `new_state_code`
- `insert_ts`

視需要再補的欄位：
- `remark`
- `workflow_stage_codes_json`
- `branch_token`
- `parallel_group_code`
- `join_group_code`
- `extra_context_json`

欄位原則：
- `parent_id`：對應業務主體，讓不同 module 的 log table 可沿用相同 schema
- `insert_user`：沿用既有 F3CMS 欄位慣例，作為 staff id 的最小責任追蹤欄位
- `old_state_code` / `new_state_code`：以 workflow state code 為準，不只記 UI label
- `insert_ts`：記錄實際寫入 trace 的時間點
- 進階 branch / parallel context 若當下路徑尚未用到，可先保留為可選欄位

### `tbl_press_log` 寫入時機

第一版先採單一明確時機：
- engine 已完成 transition 判定並回傳成功結果後
- `Press` 尚未正式寫回 `tbl_press.status` 前

採用理由：
- 先拿到 engine 的正式 transition 結果，才能確定要寫入哪個 `old_state_code` / `new_state_code`
- 若先寫 `tbl_press_log` 再做 engine 判定，容易留下不成立的假紀錄
- 若等 `tbl_press.status` 寫回完成後才補 log，失敗時會更難界定 audit trail 與業務狀態是否一致

### `tbl_press_log` 與 `tbl_press` 的 transaction 邊界

第一版原則：
- `tbl_press_log` 寫入與 `tbl_press.status` 寫回應在同一個 transaction 中
- 若 `tbl_press_log` 寫入失敗，`tbl_press.status` 不應單獨提交
- 若 `tbl_press.status` 寫回失敗，`tbl_press_log` 也應一併 rollback

最小順序建議：
1. 讀取 `tbl_press` 目前狀態並組出 runtime context
2. 呼叫 WorkflowEngine 完成 transition 判定
3. 開啟 transaction
4. 寫入 `tbl_press_log`
5. 寫回 `tbl_press.status` 與必要欄位
6. commit

若任一步驟失敗：
- rollback transaction
- 不留下只有 log、沒有狀態更新的半套結果
- 也不留下只有狀態更新、沒有 log 的半套結果

採用理由：
- `rPress::do_published()` 第一版最需要的是知道「目前在哪個 state / stage」以及「操作者是否有權限執行 action」。
- branch / parallel / trace 所需資訊仍重要，但在第一步不必等同於 WorkflowEngine 自建資料表。
- 若 `Press` 或其他 module 需要長期追蹤這些資訊，應由 module 的既有資料模型或既有 log 機制承接。

### `rPress::do_published()` 對應的最小操作順序
1. 由 `rPress::do_published()` 取得 `press_id`、request 內的 target status、目前登入者。
2. 讀取 `tbl_press.status` 與 module-local workflow definition，將 request 內 target status 轉成 workflow action request，而不是直接信任 status。
3. 由 `Press` 自己組出最小 runtime context，至少包含目前 state / stage 與操作者資訊。
4. 由 engine 根據 workflow JSON 與 runtime context 判斷此 action 是否合法，並產出 transition 結果。
5. module 需把本次 transition 的最小 audit trail 寫入自己的 log table，例如 `tbl_press_log`，至少包含 staff id、操作時間、新舊狀態與 action code。
6. 最後才呼叫既有 `fPress::published($req)`，將 `tbl_press.status` 與必要的 `online_date` 寫回業務表。

補充說明：
- 對 `Press` 而言，這裡的「寫入自己的 log table」應具體化為寫入 `tbl_press_log`
- `tbl_press_log` 與 `tbl_press` 寫回必須在同一個 transaction 中完成

### 若後續需要回退，應回退哪一段
- 若實作時發現 `Press` 的 `Scheduled -> Published` 其實必須依賴更多 payload 或 branch / parallel runtime 資訊，需回退的是本段「Module-owned Runtime Context 決議」。
- 若實作時發現 `rPress::do_published()` 並不是最早的實際狀態切換入口，需回退的是前段「`Press` 最小 workflow 掛點」。
- 以上都不需要回退到 `idea.md`，因為產品方向、分層策略與 definition / instance 分離前提都沒有被推翻。

## 第二批實作切入點決議

本輪不再討論 lib 專屬 schema，而是把第二批工作切成「module 提供 context」與「engine 消費 context」兩個檔案級任務。

### 第二批切入點
1. `www/f3cms/modules/Press/reaction.php`：決定最小 runtime context 的取得與傳入方式
2. `www/f3cms/libs/WorkflowEngine.php`：消費 runtime context，做 guard / projection / transition 判定
3. `www/f3cms/modules/Press` 的 module-owned log 策略：決定 `press_log` 如何承接 workflow audit trail
4. `www/tests/smoke/workflow_engine/instance_api.php`：維持不依賴 WorkflowEngine 專屬資料表的最小驗證

### 不納入同批的項目
- 不回補 `workflow_engine_runtime.sql`
- 不回補 `workflow_engine_definition.sql`
- 不回補 `workflow_engine_seed.sql`
- 不新增 WorkflowEngine 專屬 migration 或 seed 檔

### `libs/` 第一個實作入口

第一個實作入口定為：`www/f3cms/libs/WorkflowEngine.php`

#### 第一版責任
- 提供 module 可呼叫的單一入口，避免 workflow 判斷散落在 `Reaction`、`Outfit` 與 `Kit` 之間。
- 接受 module 傳入的目標流程 JSON，並在 engine 內完成 definition 驗證、流程判定與操作。
- 封裝 runtime context 正規化、transition 驗證與 projection / next-step judgment。
- 對 `Press` 這類 module 暴露「初始化 engine -> 送 action request -> 取得 transition 結果」的介面，而不是讓 module 自己組 SQL。

#### 第一版不另外拆 WorkflowEngine 專屬 repository 類
- 既然本輪已確認 WorkflowEngine 不擁有專屬 persistence schema，就不應再以 `WorkflowDefinitionRepository`、`WorkflowInstanceRepository` 這類 lib 專屬 repository 作為前提。
- 若某個 module 需要額外資料存取，應由該 module 或既有業務資料層承接，再把整理好的 context 傳給 engine。

### `WorkflowEngine.php` 第一批最小介面

第一版最小介面應以「module 先傳入目標流程 JSON 初始化 engine」為主：
1. `__construct($workflowJson, $options = [])`
2. `validateDefinition()`
3. `project($runtimeContext = [])`
4. `canTransit($actionCode, $runtimeContext = [])`
5. `transit($actionCode, $runtimeContext = [])`

#### 介面用途
- `__construct(...)`：接受 module 傳入的目標流程 JSON，建立 engine runtime context。
- `validateDefinition()`：驗證 definition JSON 是否符合 contract。
- `project(...)`：供 `Reaction` / `Outfit` / `Kit` 查詢目前 stage、可執行 action 與 next-step judgment。
- `canTransit(...)`：供 module 在送出實際操作前做 guard 判定。
- `transit(...)`：統一處理 action 驗證、trace 寫入、instance 更新，並回傳 transition 結果給 module。

#### 目前實作狀態
- `www/f3cms/libs/WorkflowEngine.php` 已建立最小骨架。
- 現況程式仍保留 definition 載入與 instance / trace 操作骨架，但規格用例已收斂為「module 先取得 workflow JSON，再初始化 engine」。
- 因此後續若要讓程式完全貼齊本規格，需調整的是 `WorkflowEngine.php` 的 public API 形態，不是回退到 `idea.md`。

#### 單一路徑 API 收斂決議

本輪已正式收斂：WorkflowEngine 對 module 的目標 public API 只保留單一路徑，不再接受「新舊入口並存」作為規格方向。

收斂結果如下：
- 唯一入口為 `__construct($workflowJson, $options = [])`
- module 需先取得目標流程 JSON，再初始化 engine
- 對 module 公開的最小方法集合為：`validateDefinition()`、`project($runtimeContext = [])`、`canTransit($actionCode, $runtimeContext = [])`、`transit($actionCode, $runtimeContext = [])`
- `WorkflowEngine::loadDefinition($workflowCode, $version)` 不再作為規格主路徑，但第一個 module-facing 替換點改定為 `www/f3cms/modules/Press/reaction.php` 的 workflow 呼叫路徑

此處的收斂只定義目標 public API，不代表本輪立即重寫既有 runtime helper；`getOrCreateInstance(...)`、`syncInstanceState(...)`、`projectInstance(...)` 目前仍可視為既有內部能力或過渡期 implementation detail。

### `Press` 最小整合落地狀態

目前已完成：
1. `www/f3cms/modules/Press/reaction.php` 的 `rPress::do_published()` 已不再直接信任 request 內的 `status` 作為最終寫回依據。
2. `do_published()` 代表了 `Reaction` 可作為 workflow action request 入口。
3. 第一版規格用例中，`Reaction` 應先取得目標流程 JSON，再初始化 `libs/WorkflowEngine`，之後再呼叫 engine 做流程判定與 transition。
4. 只有在 engine transition 成功後，才會呼叫既有 `fPress::published($req)` 寫回業務表。
5. `Press` 的 workflow transition 已有明確入口，但 `press_log` 與 `tbl_press.status` 是否共用同一個 transaction，仍屬本輪後續需要具體化的整合策略，尚不能視為已完成。

目前尚未完成：
- `Scheduled`、`Changed` 尚未納入 workflow 驗證；但這兩個狀態未必應直接掛在 `rPress::do_published()`，需先確認正確整合入口。
- 先前依賴 `tbl_workflow_*` 專屬資料表的 schema / smoke 驗證，已因持久化前提被推翻而不再視為有效完成項。
- `PSC_CHAIN` 與 `SJSE_EDGE` 仍可作為歷史樣本，但後續驗證方式需改為不依賴 WorkflowEngine 專屬資料表。
- module 端目前 stage、available actions 與 next-step judgment 的 JSON 契約已補齊；目前的最小缺口已改為重新收斂「module 自己用 log table 承接 workflow audit trail」的整合與驗證方式，而不是直接進入 `(Optimization)`。

已確認的前置假設修正：
- `Scheduled` 目前走 `fPress::batchRenew()` / `fPress::cronjob()`，不是 `rPress::do_published()`。
- `Changed` 目前在 `Press` 模組內主要表現為查詢 / 顯示狀態，尚未確認對應的最小 workflow action 入口。
- 因此若要直接把 `Scheduled` / `Changed` 納入本段 `Press` 最小掛點，需要回退調整的是本段「`Press` 最小 workflow 掛點」，不是回退到 `idea.md`。

### 若後續需要回退，應回退哪一段
- 若實作時發現 `instance` 與 `trace` 必須分開 migration 才能符合既有部署流程，需回退的是本段「Migration 切法」。
- 若實作時發現 repo 其實已有穩定的 repository / service 分層慣例，需回退的是本段「第一版不另外拆獨立 repository 類」。
- 若實作時發現 `WorkflowEngine.php` 無法承擔最小整合責任，需回退的是本段「`libs/` 第一個實作入口」。
- 若實作時發現 `current_state_code` 不能暫時以 entry stage 推定，需回退的是 `WorkflowEngine.php` 骨架中的初始化策略，不是回退到 definition / instance 分離這個總體決議。

### 備選樣本
- `Post`：若後續發現 `Press` 的現有流程不適合作為最小整合樣本，可改用 `Post` 作為第二選擇。

## 第一批 Definition Source 落地決議

第一批真正落地的不是 WorkflowEngine 專屬資料表，而是 definition source 與 module ownership。

### 第一批落地項
1. `Press` 的 module-local `flow.json`
2. `WorkflowEngine` 的 instance-based public API
3. module 傳入 workflow JSON 的初始化路徑

### 暫緩項目
- 若未來真的需要長期持久化 definition / runtime / trace，應由 module 或既有業務資料模型承接
- legacy mapping 的保存位置暫不固定為單一資料表

### 採用理由
- `libs/WorkflowEngine` 的第一版責任是判定與操作，不是自帶 persistence schema
- 先讓 module 提供 workflow JSON，可直接驗證主契約，而不必先落 lib 專屬表
- 若後續直接替 lib 補資料表，會再次違反第 3 輪已確認的前提

### 對後續 Stage 的影響
- Stage 4 應圍繞 workflow JSON 載入、projection、guard、transition 判定持續收斂
- Stage 5 的整合驗證應改為由 module 或既有業務資料承接 runtime context，而不是重建 `tbl_workflow_*`
- 若後續發現某個 module 確實需要持久化，需回退調整的是該 module 的資料模型與 integration 策略，不是回退到 `idea`

## Entry Criteria For Done

進入 `(done)` 前，至少要滿足：
- 本 `plan.md` 已經有明確 stage 與驗收點
- 下一輪只需選定要執行哪一個 stage
- `check.md` 已建立，且可用來標示已完成 / 未完成項

## Current Next Step

下一步不是重做設計，也不是回頭重談 `Press/reaction.php`、definition source 或 legacy helper 去留。這些結論都已在前輪定案，且本輪已把剩餘 instance-table API 正式退場。現在應直接沿用既有結論，處理 WorkflowEngine 第一版的文件同步收尾。

建議只往前做一件事：
- 先盤點 runtime-only 主契約、module-owned log 邊界、retired API 語意與 definition source 策略中，哪些已值得正式回寫到 glossary、guides、references

### `Press/reaction.php` 最小實作切入方案

本輪已確認 `Press/reaction.php` 適合作為第一個 module-facing 替換點；最小切入方案如下：

1. 在 `Press/reaction.php` 內先新增一個 module-local 的 workflow JSON 取得步驟。
	- 目的不是先改整個 definition storage，而是先把「module 先取得目標流程 JSON」這個新契約落到真實整合路徑。
	- 第一版可先由 `Press` 端透過既有 definition source 取回 `PRESS_BASIC` 的 `definition_json`，但這個讀取責任要移到 module 端，而不是由 engine 入口自行決定來源。

2. 在 `WorkflowEngine.php` 新增最小的 instance-based 入口與方法骨架。
	- 入口：`__construct($workflowJson, $options = [])`
	- 最小方法：`validateDefinition()`、`project($runtimeContext = [])`、`canTransit($actionCode, $runtimeContext = [])`、`transit($actionCode, $runtimeContext = [])`
	- 第一版允許這些 instance methods 內部暫時轉接既有 runtime helper，但這層轉接不對 module 暴露。

3. 先只改 `rPress::applyWorkflowPublishedTransition(...)` 這一條 workflow 呼叫路徑。
	- 將目前「直接以 `WORKFLOW_CODE` / `WORKFLOW_VERSION` 呼叫 WorkflowEngine static helper」的做法，改為：
	  - 由 `Press` 先取得 workflow JSON
	  - 初始化 engine
	  - 由 engine 的 `project()` / `canTransit()` 與 definition projection 完成 transition 判定，不依賴 `tbl_workflow_instance`
	- 在下一步整合時，需把 `tbl_press_log` 寫入與 `tbl_press.status` 寫回一起放進同一個 transaction。

4. 驗收先只維持既有 `Press` smoke 路徑。
	- 目標是確認 public API 契約已由 module-facing 路徑落地
	- 不是本輪就要重寫所有 smoke 或一次替換所有 static helper

### 本輪不做的事

- 不先替換 `loadDefinition(...)` 在 engine 內核與 smoke scripts 的所有使用點
- 不先重寫 `getOrCreateInstance(...)`、`syncInstanceState(...)`、`projectInstance(...)` 的內部實作
- 不擴散到 `Scheduled` / `Changed` 或其他 module

### 若此切入方案失敗，應回退哪一段

- 若發現 `Press` 端無法合理承擔「先取得 workflow JSON」的責任，需回退調整的是本段 `Press/reaction.php` 的 module-facing 切入策略
- 若發現 instance-based methods 無法以薄轉接方式承接既有 runtime helper，需回退調整的是 `plan.md` 中 WorkflowEngine public API 與內部 runtime helper 的銜接策略
- 都不是回退到 `idea.md`，也不是回退前面 schema / runtime 驗收結論

目前這一版仍可沿用的承接點：
- `Press` 已有 module-local `flow.json`，符合「module 先取得 workflow JSON」主契約
- `WorkflowEngine` 已有 instance-based public API 形態
- role guard、transition guard、projection 與進階節點類型的邏輯需求仍然成立
- instance API smoke 已補上，能作為後續不依賴專屬資料表的最小驗證基礎

目前已失效、需視為回退完成的承接點：
- 所有依賴 `tbl_workflow_*` 專屬資料表的 schema / SQL / seed / smoke 驗證
- 所有以 `workflow_engine_runtime.sql`、`workflow_engine_definition.sql`、`workflow_engine_seed.sql` 為前提的落地敘述
- 所有把 lib 專屬資料表視為 WorkflowEngine 正式持久化方案的描述

下一個最小承接點：
- 先依這份更新後的 `plan.md` 與 `check.md`，盤點並收斂 `getOrCreateInstance()`、`getOrCreateInstanceFromDefinition()`、`projectInstance()` 與相關常數/文件敘述，決定如何把剩餘的 `tbl_workflow_*` legacy 入口正式收尾

### `Press/reaction.php` 最小 transaction 整合方案

本輪只承接 `Press` 的第一條 workflow 實作路徑，不擴散到其他 module。

最小順序：
1. 在 transaction 外讀取 `tbl_press` 現況、操作者 `staff_id`、並載入 `flow.json`
2. 以這些資料組出 runtime context，呼叫 WorkflowEngine 完成 transition 判定
3. 僅在 transition 判定成功後才開啟 transaction
4. 在 transaction 內先寫入 `tbl_press_log`
5. 再寫回 `tbl_press.status` 與必要欄位
6. 兩者都成功才 commit，否則 rollback

在 `tbl_press_log` 這一步至少要寫入：
- `parent_id`
- `insert_user`
- `action_code`
- `old_state_code`
- `new_state_code`
- `insert_ts`

若後續需要回退，應回退哪一段：
- 若發現 `fPress::published($req)` 不能安全地放進同一個 transaction，需回退的是本段「`Press/reaction.php` 最小 transaction 整合方案」
- 若發現 `applyWorkflowPublishedTransition(...)` 並不是實際唯一入口，需回退的是 `Press` 最小 workflow 掛點，而不是回退到 `idea.md`

本輪回退補充：
- 已確認「WorkflowEngine 是 lib，不應有專屬資料表」會直接推翻先前的 schema 落地前提
- 因此需要回退的是 WorkflowEngine 專屬持久化策略，不是 public API 單一路徑收斂本身
