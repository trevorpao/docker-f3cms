# EventRuleEngine Plan

## Purpose
- 將 `idea.md` 中已收斂的 Event Rule Engine 方向拆成可執行階段。
- 讓後續 `(done)`、`check` 與 `(Optimization)` 有明確承接基準。
- 控制第一版範圍，避免從 JSON DSL 與 Schema 基準直接滑向過大實作面。

## Plan Basis

本計畫目前直接承接 `idea.md` 中已明確存在的核心方向：
- 以 JSON DSL / AST 表達規則
- 以 Rule Engine + Evaluator 模式做遞迴解析與短路求值
- 以 PlayerContext 作為無狀態資料提供者
- 以原生 JSON 欄位承接規則 payload
- 補齊多對多關聯表與 module-owned log 的資料落點

## Current Stage Assessment

目前 feature 已完成 Stage 1 到 Stage 3 的規劃收斂：
- Stage 1 已確立 DSL contract、rule types、資料邊界與 validator 基準
- Stage 2 已確立 MariaDB 相容的 schema / index / query baseline
- Stage 3 已確立 engine 執行骨架、registry、context contract 與錯誤回傳策略
- 第一版 validator / parser / registry / traversal 骨架已完成實作，並已有 Docker smoke 可驗證最小判斷路徑

因此目前承接點為：
- feature 已從純 `plan` 推進到 `(done)` 後可進入 `check` 的承接點
- 目前尚未進入 `(Optimization)`，因為仍有 integration 與驗收缺口待盤點
- 本文件目前的角色是維持 EventRuleEngine 第一版骨架與後續收斂順序的正式基線

## Stage Plan

### Stage 1: 收斂 DSL 與資料邊界

目標：
- 明確定義 JSON DSL / AST 的第一版 contract
- 收斂 RuleEngine、PlayerContext、Evaluator 的責任邊界
- 收斂 duty、task、task log、member relation 等核心資料落點

主要工作：
- 定義 JSON payload 結構與欄位語意
- 確認哪些條件型別屬於第一版
- 確認 current state、history、log、relation 的資料承接原則

輸出：
- 第一版 DSL contract
- 第一版資料邊界結論

#### Stage 1 收斂結果

##### A. JSON DSL 第一版 Contract

第一版採單一 AST 結構，分為兩種節點：
- group node：負責邏輯彙總
- leaf node：負責單一條件判定

group node 最小欄位：
- `operator`
- `rules`

leaf node 最小欄位：
- `type`
- 視條件型別需要 `target`、`operator`、`value`

第一版 JSON payload 規則：
- root 必須是 group node 或單一 leaf node
- `operator` 目前第一版只收斂 `AND`、`OR`
- `rules` 必須是非空陣列
- `type` 為 leaf node 必填欄位
- 比較型 leaf node 才能使用 `operator` + `value`
- 參考型 leaf node 才能使用 `target`

第一版標準範例如下：

```json
{
	"operator": "OR",
	"rules": [
		{
			"operator": "AND",
			"rules": [
				{
					"type": "WATCHED_VIDEO",
					"target": "vid_001"
				},
				{
					"type": "EXAM_SCORE",
					"operator": ">",
					"value": 80
				}
			]
		},
		{
			"type": "HAS_BADGE",
			"target": "badge_novice"
		}
	]
}
```

##### B. 第一版支援的 Rule Types

依 `idea.md` 現況，第一版先明確收斂以下條件型別：
- `WATCHED_VIDEO`
- `EXAM_SCORE`
- `HAS_BADGE`

第一版不預設支援：
- 動態腳本條件
- 跨服務即時聚合條件
- 需要反查索引才能高效運作的全域條件搜尋

##### C. RuleEngine / PlayerContext / Evaluator 邊界

`RuleEngine`：
- 接收 JSON AST payload 與 `PlayerContext`
- 負責遞迴遍歷、group operator 彙總與短路求值
- 不直接負責資料讀取

`PlayerContext`：
- 作為無狀態讀取模型
- 在進入引擎前先把驗證所需資料整理完成
- 至少需能提供影片觀看狀態、測驗結果、持有紋章、必要帳戶狀態等欄位

`Evaluator`：
- 每個 `type` 對應一個具體 evaluator
- evaluator 只處理單一 leaf node 的判定
- 新增條件時應以註冊新 evaluator 為主，不修改核心遞迴引擎

##### D. Payload Validator 與防禦策略

第一版 payload validator 至少需檢查：
- AST 最大深度
- `operator` 是否為允許值
- `rules` 是否為非空陣列
- leaf node 必填欄位是否齊全
- 不允許 group / leaf 欄位混用成模糊結構

目前建議的最小防禦基準：
- `max_depth = 5`
- 不允許空 group
- 不允許未知 `type`

##### E. 資料承接原則

第一版資料承接採以下分層：
- `tbl_duty.claim` / `tbl_duty.factor`：承接 JSON DSL payload
- `tbl_task.status`：承接任務當前狀態
- `tbl_task_log`：承接任務狀態異動歷程
- `tbl_member_heraldry`：承接 badge / heraldry 類多對多關聯
- `tbl_manaccount` / `tbl_manaccount_log`：承接點數或帳戶狀態與其異動歷程

資料邊界原則：
- current state 與 history / log 必須分開
- relation state 不應塞入 member 主表 JSON
- task / account log 應保留新舊狀態或新舊餘額，確保 audit trail 可追溯

##### F. 第一版查詢需求

第一版至少需支撐以下查詢形態：
- 依 `tbl_task.status` 查詢待處理 / 已完成任務
- 依 `parent_id` 查詢單一 task 的完整 history
- 依 `member_id` 查詢玩家持有的 heraldry 關聯
- 依 `member_id` + 時間區間查詢點數異動歷程

第一版先不承接：
- 從 JSON payload 直接做高頻全域反查
- 依任意 rule type 對 `tbl_duty` 做即時報表式篩選

### Stage 2: 規劃 schema 與查詢需求

目標：
- 把 `idea.md` 中的 schema 修正與 relation / log 需求整理成可實作的 DB 規劃

主要工作：
- 收斂 JSON 欄位型態需求
- 收斂 `tbl_member_heraldry` 之類的 relation table 落點
- 收斂 task / account log 的最小審計欄位
- 盤點待審查詢、history 查詢與 reviewer 稽核查詢需求

輸出：
- schema 與查詢需求清單
- 第一版索引 / 效能注意事項

#### Stage 2 收斂結果

##### A. DB Baseline 與 JSON 落地策略

目前專案執行基線不是 PostgreSQL，而是 docker-compose 所定義的 MariaDB 10.4.6，因此第一版 schema 規劃必須以 MariaDB 相容行為為準，而不是以 PostgreSQL JSONB / GIN 能力為假設。

第一版 JSON 欄位決策如下：
- `tbl_duty.claim` 使用 `JSON` 型態語意承接 DSL payload，但規劃上視為 MariaDB `LONGTEXT` alias 行為
- `tbl_duty.factor` 採相同策略
- `tbl_duty.next` 若仍承接結構化後續任務資訊，也應走相同 JSON 承接方式
- payload 正確性主要依賴 application-side validator，不把 JSON 內容查詢能力當成第一版資料庫責任

第一版明確不依賴：
- PostgreSQL `JSONB`
- GIN / inverted index
- 以 generated column 對任意 AST 片段做通用索引

##### B. 第一版資料表基線

`tbl_duty`：
- 保留任務定義主表角色
- `claim`、`factor`、`next` 承接 JSON 結構內容
- `slug` 應維持可讀識別碼與唯一約束

建議最小欄位：
- `id`
- `slug`
- `claim`
- `factor`
- `next`
- `status`
- `last_ts`
- `last_user`
- `insert_ts`
- `insert_user`

建議最小索引：
- `PRIMARY KEY (id)`
- `UNIQUE KEY uniq_slug (slug)`
- `KEY idx_status (status)`

`tbl_task`：
- 承接會員對 duty 的當前任務狀態
- 第一版先假設同一 `member_id` 對同一 `duty_id` 只有一筆當前任務，不處理 repeatable 任務週期

建議最小欄位：
- `id`
- `duty_id`
- `member_id`
- `status`
- `last_ts`
- `last_user`
- `insert_ts`
- `insert_user`

建議最小索引：
- `PRIMARY KEY (id)`
- `UNIQUE KEY uniq_duty_member (duty_id, member_id)`
- `KEY idx_member_status (member_id, status)`
- `KEY idx_duty_status (duty_id, status)`

`tbl_task_log`：
- 採 module-owned log，使用 `parent_id` 指向 `tbl_task.id`
- 結構比照現有 `tbl_press_log` 的 F3CMS 模式，不引入獨立 workflow runtime table

建議最小欄位：
- `id`
- `parent_id`
- `action_code`
- `old_state_code`
- `new_state_code`
- `remark`
- `insert_ts`
- `insert_user`

建議最小索引：
- `PRIMARY KEY (id)`
- `KEY idx_parent_ts (parent_id, insert_ts)`
- `KEY idx_action_ts (action_code, insert_ts)`

`tbl_member_heraldry`：
- 作為 member 與 heraldry 的 relation table
- 不把 badge / heraldry 狀態回塞到 member JSON 欄位

建議最小欄位：
- `id`
- `member_id`
- `heraldry_id`
- `insert_ts`
- `insert_user`

建議最小索引：
- `PRIMARY KEY (id)`
- `UNIQUE KEY uniq_member_heraldry (member_id, heraldry_id)`
- `KEY idx_heraldry_member (heraldry_id, member_id)`

`tbl_manaccount`：
- 承接會員點數或帳戶 current state
- 第一版先以單一主要帳戶餘額為前提，不處理多帳戶別拆分

建議最小欄位：
- `id`
- `member_id`
- `balance`
- `status`
- `last_ts`
- `last_user`
- `insert_ts`
- `insert_user`

建議最小索引：
- `PRIMARY KEY (id)`
- `UNIQUE KEY uniq_member (member_id)`

`tbl_manaccount_log`：
- 同樣採 module-owned log，使用 `parent_id` 指向 `tbl_manaccount.id`
- audit trail 以舊餘額 / 新餘額為主，不沿用 task state 欄位命名

建議最小欄位：
- `id`
- `parent_id`
- `action_code`
- `delta_point`
- `old_balance`
- `new_balance`
- `remark`
- `insert_ts`
- `insert_user`

建議最小索引：
- `PRIMARY KEY (id)`
- `KEY idx_parent_ts (parent_id, insert_ts)`
- `KEY idx_action_ts (action_code, insert_ts)`

##### C. 第一版查詢基線

第一版正式承接以下查詢：

任務查詢：
- 依 `member_id + status` 查玩家當前待領取 / 已完成 / 已失效任務
- 依 `duty_id + status` 查特定 duty 的任務分佈

任務稽核查詢：
- 依 `parent_id` 讀單一 task 的完整歷程，排序 `insert_ts ASC`
- 依 `action_code + 時間區間` 查某類事件造成的 task 狀態變更
- 依 `insert_user + 時間區間` 查 reviewer / 系統帳號做過哪些狀態異動

玩家條件查詢：
- 依 `member_id` 取持有 heraldry 清單，供 `HAS_BADGE` evaluator 使用
- 依 `member_id` 取當前帳戶餘額，供點數條件或獎勵核發使用

帳戶稽核查詢：
- 依 `parent_id` 讀單一 manaccount 的餘額異動歷程
- 依 `member_id + 時間區間` 透過 `tbl_manaccount` join `tbl_manaccount_log` 查玩家點數變動
- 依 `action_code + 時間區間` 查特定事件造成的點數發放 / 扣除

第一版明確不承接：
- 從 `tbl_duty.claim` / `factor` 直接做全域營運報表查詢
- 對 AST 任意節點做 ad hoc SQL 過濾

##### D. Reviewer 與 Audit Trail 基線

第一版 reviewer 關注的不是 AST 內文搜尋，而是「誰在什麼時間，讓哪個 task / account 發生了什麼狀態變更」。

因此 reviewer baseline 應以 log table 為核心：
- task reviewer 主要看 `tbl_task_log`
- account reviewer 主要看 `tbl_manaccount_log`
- 若需顯示會員或 duty 識別資訊，應透過 join 主表補足，不反向掃描 JSON

第一版 log table 至少需支撐以下欄位語意：
- `action_code`：此次異動由何種事件或命令觸發
- `old_state_code` / `new_state_code` 或 `old_balance` / `new_balance`
- `insert_user`：是人員操作、排程帳號或系統程序
- `insert_ts`：異動發生時間
- `remark`：必要時補充人工註記或例外原因

##### E. 索引與歸檔風險記錄

第一版先記錄以下風險，但不提前做過度設計：
- `tbl_task_log` 與 `tbl_manaccount_log` 會隨事件量成長，長期不能只靠 `PRIMARY KEY` 生存
- reviewer 查詢會集中在 `parent_id + insert_ts` 與 `action_code + insert_ts`
- 若營運端頻繁查長時間區間的稽核紀錄，未來可能需要月分段歸檔或冷熱資料切分

第一版決策：
- 先建立複合索引，不先引入 partition
- 先保留主表 + log 表 join 查詢模式，不額外做 summary table
- 若 log 成長已影響 reviewer 查詢，再於後續 stage 評估 archive table 或時間分段策略

### Stage 3: 規劃 engine 與 evaluator 執行路徑

目標：
- 定義 EventRuleEngine 的最小執行骨架與 evaluator 擴充方式

主要工作：
- 收斂 RuleEngine、Evaluator registry、PlayerContext 的協作方式
- 確認短路求值與最大深度防禦策略
- 確認第一版錯誤處理與 payload validator 的責任落點

輸出：
- engine 執行路徑草案
- evaluator 擴充規則

#### Stage 3 收斂結果

##### A. 第一版最小執行骨架

第一版執行路徑應明確切成五段，而不是讓 controller / reaction 直接把原始 JSON 丟進 evaluator：
- `DutyRuleLoader`：從 `tbl_duty.claim` 或 `factor` 取出原始 payload
- `PayloadValidator`：驗證 payload 結構是否合法
- `RuleParser`：將 raw array 正規化成可遞迴處理的 AST node 結構
- `RuleEngine`：只負責 group traversal、short-circuit 與 evaluator dispatch
- `EvaluationResult`：回傳本次判斷結果、失敗原因類別與必要 trace

最小執行順序：
1. 讀出 duty payload
2. 做 payload validation
3. parse / normalize 成 AST
4. 建立已 preload 的 `PlayerContext`
5. 交由 `RuleEngine` evaluate
6. 回傳 `EvaluationResult`

第一版刻意不讓 `RuleEngine` 直接處理：
- DB query
- Redis query
- 原始 request payload 清洗
- 任務狀態寫回

##### B. Evaluator Registry Contract

第一版採 registry-by-type，而不是在核心引擎用 `switch(type)`：
- registry key 為 leaf node 的 `type`
- registry value 為對應 evaluator instance 或可建構 evaluator 的 class
- `RuleEngine` 只依 `type` 向 registry 取 evaluator，不直接知道條件細節

第一版 evaluator 介面最小責任：
- 接收單一 leaf node
- 接收 `PlayerContext`
- 回傳單一布林判斷結果與可選 trace 資訊

建議最小介面語意：
- `evaluate(array $leafNode, PlayerContext $context): EvaluationLeafResult`

`EvaluationLeafResult` 第一版最小欄位：
- `matched`
- `type`
- `target`
- `detail`

registry 規則：
- 未註冊的 `type` 視為 configuration error，不當成 business false
- 新增 rule type 以新增 evaluator + registry mapping 為主，不改 `RuleEngine`
- 若 payload validator 已限制 type 白名單，runtime registry 仍保留最後一道防線

##### C. PlayerContext Preload Contract

`PlayerContext` 第一版不是自由形狀陣列，而是有固定欄位責任的 preload contract。

最小必備欄位：
- `member_id`
- `watched_video_codes`
- `exam_scores`
- `heraldry_codes`
- `account_balance`
- `account_status`

欄位語意：
- `watched_video_codes`：已完成觀看的影片代碼集合
- `exam_scores`：以 exam / target code 對應分數 map
- `heraldry_codes`：已持有 badge / heraldry code 集合
- `account_balance`：當前主要帳戶餘額
- `account_status`：帳戶是否可用或受限

第一版 preload 原則：
- preload 在進入 `RuleEngine` 前完成
- evaluator 不自行發 DB / Redis 查詢
- 缺資料時以空集合、`null` 或明確 default 值呈現，不在 evaluator 內補查
- 若某個 use case 需要額外欄位，應先擴充 `PlayerContext` contract，再加入 evaluator

##### D. Validator / Parser / Engine 邊界

第一版責任切分如下：

`PayloadValidator`：
- 處理 schema 合法性
- 處理最大深度、欄位組合、允許的 operator / type
- 輸出 validation pass / fail 與錯誤清單

`RuleParser`：
- 將 raw JSON array 正規化
- 補齊 group node / leaf node 的內部一致格式
- 建立可供 engine 遞迴的 AST 表示

`RuleEngine`：
- 假設輸入已通過 validator / parser
- 只負責 traverse、short-circuit、dispatch evaluator、彙總結果
- 不再重做 request-level schema 驗證

這個切分的目的，是避免同一份規則同時在 validator、parser、engine 重複判斷，造成三套規則漂移。

##### E. 錯誤回傳與 Fail-Closed 策略

第一版必須明確區分以下三類結果：
- business false：規則合法，但玩家條件不符合
- validation / configuration error：payload 或 registry 設定錯誤
- infrastructure error：context preload 或依賴服務失敗

回傳策略：
- business false：正常回傳 `matched = false`
- validation / configuration error：視為 fail-closed，不自動放行任務
- infrastructure error：同樣 fail-closed，並保留錯誤碼供上層記錄

`EvaluationResult` 第一版建議最小欄位：
- `matched`
- `result_type`，例如 `matched`、`not_matched`、`invalid_payload`、`missing_evaluator`、`context_error`
- `failed_node_path`
- `trace`

第一版原則：
- 不因 evaluator 缺失而默默回傳 false
- 不因 context 欄位缺失而在 runtime 自動補查 DB
- 不把 validation error 偽裝成使用者條件不符合

##### F. 與上層模組的整合邊界

比照現有 WorkflowEngine 與 Press reaction 的責任切分，EventRuleEngine 應維持以下邊界：
- module / reaction 負責載入 duty、建立 `PlayerContext`、決定要驗證 `claim` 或 `factor`
- RuleEngine 負責純判斷
- task / account 狀態寫回與 log 寫入由 module service 或 reaction 負責

第一版不把以下責任塞回 engine：
- 任務發放
- 點數入帳
- log insert
- reviewer 查詢組裝

### Stage 4: 驗收與風險收斂

目標：
- 將第一版需要驗證的規則、資料與效能風險整理為 check 清單

主要工作：
- 將功能驗收點寫入 `check.md`
- 將高風險點與後續非範圍項目明文化

輸出：
- 第一版驗收清單
- 下一輪最小可執行實作起點

#### Stage 4 收斂結果

##### A. 第一版驗收切面

在進入 `(done)` 前，第一版驗收必須至少分成四個切面，而不是只驗單一 happy path：
- payload 結構驗收
- engine 判斷驗收
- context 缺值 / preload 驗收
- registry / integration 失配驗收

第一版 acceptance baseline：
- 合法 payload + 合法 context + 已註冊 evaluator 時，可得穩定 `matched` / `not_matched` 結果
- 非法 payload 不得進入 evaluator 執行
- 缺 evaluator 不得被當成 business false
- context 缺值不得在 runtime 默默補查 DB 後繼續執行

##### B. Fail-Closed 驗收基線

第一版必須把 fail-closed 視為正式驗收點，而不是實作時臨時決定。

應驗證的最小情境：
- payload 深度超過 `max_depth`
- group node 為空陣列
- leaf node 缺 `type`
- leaf node 帶入不允許的欄位組合
- registry 找不到對應 evaluator
- `PlayerContext` 缺少 evaluator 所需欄位
- preload 階段無法取得必要資料

上述情境的驗收要求：
- 任務不得自動放行
- 不得退化成一般 `matched = false` 而失去錯誤語意
- 必須保留可供 module / reaction 記錄的 `result_type`

##### C. Context 缺值與 Default 規則

第一版先明確定義以下 fallback / default 原則：
- 集合型欄位缺值時，可用空集合表達，例如 `watched_video_codes = []`
- 單值型欄位缺值時，可用 `null`，但 evaluator 必須明確定義 `null` 是否可比較
- 缺少 contract 內必要欄位時，視為 context error，不讓 evaluator 自由猜測
- 任何 fallback 都不能隱含額外 DB query

第一版特別要避免：
- 以 `0` 假裝所有缺值分數
- 以空字串假裝合法 target code
- 由 evaluator 自行補 context 欄位

##### D. Registry 缺漏與型別擴充規則

第一版 registry 驗收要求：
- 所有被 payload validator 允許的 `type`，都必須能在 runtime registry 中被解析
- 若 validator 白名單與 registry 註冊清單不一致，優先視為 configuration drift
- 新增 rule type 時，必須同步更新 validator 白名單、registry mapping 與測試情境

最小 fallback 原則：
- 不自動跳過未知 `type`
- 不將未知 `type` 當成 false branch 繼續 AND / OR 運算
- 一旦遇到未知 `type`，整次 evaluate 應回到 fail-closed 結果

##### E. Module Integration 驗收基線

module / reaction 與 engine 的整合至少需驗證以下責任邊界：
- duty payload 是由上層決定要讀 `claim` 或 `factor`
- `PlayerContext` 是由上層 preload 完成後再交給 engine
- `EvaluationResult` 是由上層解讀並決定是否寫回 task / account 狀態
- log insert 仍由 module service / reaction 負責

第一版不可接受的整合退化：
- engine 內直接寫 task log
- engine 內直接寫 manaccount log
- engine 內直接判斷並執行 reward 發放
- reaction 直接繞過 validator 呼叫單一 evaluator

##### F. 實作前的最小落地順序

當前已具備進入 `(done)` 前的最小規劃基線，第一版建議落地順序如下：
1. 先實作 payload validator + parser
2. 再實作 registry 與 `RuleEngine` traversal
3. 再實作三個第一版 evaluator
4. 再實作 `PlayerContext` preload contract 與 module integration adapter
5. 最後補齊驗收案例與 smoke / fixture

這個順序的目的，是讓 fail-closed 與 contract drift 先被固定，不要等到 module integration 後才發現核心判斷規則仍在變動。

## Current Next Step

下一步應承接 `check`，先盤點目前第一版骨架已完成與未完成項，特別是 module integration adapter、payload source 與更多 rule / fixture 驗證是否要進入下一輪實作。