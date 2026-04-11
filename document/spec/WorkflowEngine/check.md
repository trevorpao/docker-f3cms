# WorkflowEngine Check

## Purpose
- 作為 WorkflowEngine 第一版的驗收清單。
- 確保後續 `(done)` 階段不只做實作，也會對照既定決議、分層邊界與高風險點。
- 讓每個 stage 完成後都能用同一份 checklist 做回看。

## Check Basis

本文件依據以下內容建立：
- [idea.md](idea.md) 中的 discuss 決議
- [plan.md](plan.md) 中的 stage 拆分與驗收方向
- [history.md](history.md) 中記錄的目前狀態與已確認結論

## 全域驗收清單

### A. 決議承接
- [x] `plan.md` 是否完整承接 `idea.md` 的已定案結論
- [x] 是否仍維持 engine 位於 `libs/` 並可供各 Module 呼叫
- [x] 是否明確承接「module 先取得目標流程 JSON，再初始化 engine」的用例
- [x] 是否已將 WorkflowEngine 對 module 的 public API 收斂為單一路徑
- [x] runtime 資料來源是否維持以 JSON 為主
- [x] 是否明確要求支援退回、分支、並行、擇一等進階節點類型
- [x] 權限模型是否已轉向具業務語意的角色代碼常數，而非長期依賴 magic numbers
- [x] 流程定義與流程實例是否保持分離
- [x] 驗收是否包含至少一個實際 module integration 情境

### B. 分層與邊界
- [x] WorkflowEngine 的責任是否清楚，沒有把它做成單一模組的私有邏輯
- [x] Feed / Reaction / Outfit / Kit / libs 的責任是否明確區分
- [x] 是否避免把 workflow 判斷散落在多個模組而無單一入口
- [x] 是否明確允許 `Reaction` / `Outfit` / `Kit` 初始化並呼叫 engine

### C. 資料建模與 schema
- [x] workflow definition 與 workflow instance 是否分開設計
- [x] 是否已收斂出不依賴 WorkflowEngine 專屬資料表的 definition source 策略
- [x] 是否已收斂出由 module / 既有業務資料承接 runtime state 與 trace 的策略
- [x] 是否已明確要求各 module 以自己的 log table 承接 workflow audit trail
- [x] 是否能合理表達 rollback、branch、parallel、any-of 等節點類型
- [x] 是否已考慮 legacy XML 轉入新模型時所需的 mapping

### D. 風險與回歸
- [x] 歷史 edge cases 是否已納入設計或驗證範圍
- [x] 是否有針對非法 transition 的防護
- [x] 是否有針對非法角色或越權操作的防護
- [x] 是否有避免 definition / instance 混用的防護

### E. 文件同步
- [x] `history.md` 是否反映最新進度
- [x] `plan.md` 是否仍與目前執行狀態一致
- [x] 若有穩定新術語，是否應補入 `glossary.md`
- [x] 若有形成穩定規則，是否需回寫 guides / references

## Stage 驗收清單

### Stage 1: 定義第一版資料模型與 JSON Contract
- [x] 已明確列出 workflow definition 必要欄位
- [x] 已明確列出 workflow instance 必要欄位
- [x] JSON contract 可表達 stage、state、transition、role constant
- [x] JSON contract 可表達 rollback、branch、parallel、any-of
- [x] 已選定至少 1 至 2 個歷史流程作為驗證樣本
- [x] 不再以 magic numbers 作為長期設計語言

### Stage 2: 規劃 F3CMS 整合邊界與檔案落點
- [x] 已定義 `libs/WorkflowEngine` 的核心責任
- [x] 已定義 engine 與 Feed / Reaction / Outfit / Kit 的責任邊界
- [x] 已提出至少一個 module integration 情境
- [x] 已說明 module 將如何呼叫 engine
- [x] 已說明 module 需先取得目標流程 JSON，再初始化 engine
- [x] 已明確定下唯一 public API 入口與最小方法集合
- [x] 已標出第一個 module-facing 替換點 `Press/reaction.php`

### Stage 3: 設計 schema 與持久化策略
- [x] 已規劃 definition JSON 的保存方式
- [x] 已收斂出不依賴 WorkflowEngine 專屬資料表的 definition source 策略
- [x] 已收斂出由 module / 既有業務資料承接 instance state、history、operator、timestamp 的策略
- [x] 已定義 module-owned log table 的最小欄位需求，例如 staff id、時間、新舊狀態、action code
- [x] 已定義 `Press` 的 `tbl_press_log` 最小欄位，例如 `parent_id`、`insert_user`、`action_code`、`old_state_code`、`new_state_code`、`insert_ts`
- [x] 已定義 `tbl_press_log` 的寫入時機與 `tbl_press` 寫回的 transaction 邊界
- [x] 已移除對 WorkflowEngine 專屬 schema / SQL 落地的依賴
- [x] 目前的持久化策略仍足以支撐進階節點類型與 trace

### Stage 4: 實作 engine 核心能力
- [x] engine 可載入與驗證 JSON definition
- [x] engine 可查詢 stage / transition
- [x] engine 可做 role guard
- [x] engine 可做 transition guard
- [x] engine 已實作 rollback、branch、parallel、any-of 的核心判斷能力
- [x] 非法 transition 與非法角色會被阻擋
- [x] `transit()` runtime path 已不再依賴 `tbl_workflow_instance` / `tbl_workflow_instance_trace`

### Stage 5: 實作 F3CMS 模組整合情境
- [x] 已有至少一個實際 F3CMS module integration 路徑
- [x] module 可讀取 workflow definition JSON
- [x] module 可透過 engine 取得當前 stage、可執行 action 與下一步判斷
- [x] module integration 用例已明確收斂為「module 初始化 engine 時傳入目標流程 JSON」
- [x] 驗收結果證明 engine 不只是 parser demo
- [x] module 是否已明確定義自己的 workflow log table，例如 `tbl_press_log`
- [x] `Press` 是否已明確定義 `tbl_press_log` 與 `tbl_press` 共用 transaction 的整合策略
- [x] `Press/reaction.php` 是否已明確定義 transaction 起點、`tbl_press_log` 寫入點與 `tbl_press.status` 寫回點
- [x] module integration 的成功 / 失敗 transaction 路徑是否已改成不依賴 WorkflowEngine 專屬資料表

### Stage 6: 歷史 edge cases 驗證與文件同步
- [x] 至少 1 至 2 個複雜歷史流程已被驗證
- [x] edge cases 包含退回、分支、並行、擇一或其他特殊節點情境
- [x] 角色常數與 legacy mapping 的可行性已被驗證
- [x] 第一版限制與未解項目已被明確記錄
- [x] 相關文件同步清單已完成或已列出

## Review Output Format

每次執行本文件作為驗收時，建議至少輸出：
- 已完成項
- 未完成項
- 高風險項
- 可延後但需記錄項
- 下一步

## Current Status

- [x] `check.md` 初版已建立
- [x] 已完成第一輪文件回看，將已完成與未完成項目標記到 checklist

## Current Next Step

WorkflowEngine 第一版的共享文件同步已完成，runtime-only 主契約、module-owned workflow log 與 retired API 邊界不再只存在於 spec 與程式碼。

建議優先順序：
1. 正式把目前 stage 由 `check` 後承接點推進到 `(Optimization)`
2. 依 `optimization.md` 補齊封存前的穩定規則摘要與後續優化方向
3. 在封存前做一次 `history.md` 壓縮整理，保留最低承接成本
