# EventRuleEngine Check

## Purpose
- 作為 EventRuleEngine 第一版的驗收與風險清單。
- 確保後續討論與實作不會只停留在 `idea.md` 的敘事層。
- 讓後續 `check` 與 `(Optimization)` 有正式回看基準。

## Current Status

目前狀態：
- [x] 已有 `idea.md`
- [x] 已建立 `history.md`
- [x] 已建立 `plan.md`
- [x] 已建立 `check.md`
- [x] 已完成第一版骨架程式實作
- [x] 已完成 Docker smoke 驗證
- [x] 已明確確認「有實體資料表需求時，資料承接必須落在 owning module；shared pure engine 可留在 `libs`」的 F3CMS 架構規則
- [x] 已建立最小 schema / module baseline：`document/sql/260412.sql` 已補 DDL，且 repo 內已有 `Duty`、`Task`、`Manaccount` Feed
- [x] 已完成 `tbl_member` 與 `Member` Feed，`member_id` 對應主表 / module 邊界已具備最小 baseline
- [x] 已依 `.env` 與 Docker 將 `document/sql/260412.sql` 套用到 live `target_db`，EventRuleEngine baseline tables 已實際存在
- [x] repo 端已把 `tbl_member_seen` 與 duty contract 回讀 baseline 補進 `plan.md` / daily SQL
- [x] live `target_db` 已套用新增的 `tbl_member_seen` DDL
- [x] 新確認的 `Member::Register -> Press seen -> task done -> 100 點 reward` concrete scenario 已同步到既有 code 與 live schema

## 第一版驗收清單

### A. 需求與邊界
- [x] JSON DSL / AST 的第一版 contract 是否已正式收斂
- [x] 第一版支援的 rule types 是否已明確列出
- [x] RuleEngine、PlayerContext、Evaluator 的責任邊界是否已明確
- [x] Non-Scope 是否足以阻止第一版滑向視覺化編輯器與全域反查 API

### B. 資料與 Schema
- [x] `tbl_duty` 的 JSON 欄位型態需求是否已明確
- [x] relation table 需求是否已明確，例如 `tbl_member_heraldry`
- [x] task / account log 的最小審計欄位是否已明確
- [x] current state 與 history / log 的資料承接是否已區分清楚

### C. 執行與防禦
- [x] 短路求值策略是否已明確
- [x] payload validator 與最大深度限制是否已明確
- [x] rule evaluator 的擴充方式是否符合開閉原則
- [x] PlayerContext 的預載與無狀態假設是否已明確
- [x] evaluator registry contract 是否已明確
- [x] validator / parser / engine 的責任切分是否已明確
- [x] fail-closed 的錯誤分類與回傳策略是否已明確

### D. 查詢與效能
- [x] 待審 / 條件驗證相關查詢需求是否已被盤點
- [x] history / reviewer 稽核查詢需求是否已被盤點
- [x] 大量 history 成長下的索引或歸檔風險是否已被記錄
- [x] JSON 全域反查為非範圍的限制是否已被明確記錄

### E. 模組整合邊界
- [x] module / reaction 與 RuleEngine 的責任分界是否已明確
- [x] task / account 寫回與 log insert 不進入 engine 的原則是否已明確
- [x] 是否已明確規定 table-backed 業務流程不可長期放在 `libs`
- [x] 是否已明確規定 shared EventRuleEngine 可留在 `libs`，但不得取代 owning module 的 payload / context / writeback 邊界

### F. 驗收與 Fallback 基線
- [x] fail-closed 情境是否已被整理成明確驗收點
- [x] context preload 缺值時的 default / error 原則是否已明確
- [x] registry 缺漏與 validator 白名單不一致時的處置是否已明確
- [x] module integration 不可退化的邊界是否已明確
- [x] 第一版實作前的最小落地順序是否已明確

### G. 第一版骨架實作驗收
- [x] payload validator、parser、registry 與 RuleEngine traversal 是否已有第一版實作
- [x] `WATCHED_VIDEO`、`EXAM_SCORE`、`HAS_BADGE` evaluator 是否已有第一版實作
- [x] Docker smoke 是否已覆蓋 `matched`、`not_matched`、`invalid_payload`、`missing_evaluator`、`context_error`
- [x] shared EventRuleEngine runtime 是否已回到 `www/f3cms/libs/EventRuleEngine.php`，且不再假設存在單一 `EventRuleEngine` owning module
- [x] 最小 schema / module baseline 是否已在此 repo 可用
- [x] live DB 是否已套用 EventRuleEngine baseline schema
- [x] `member_id` 對應主表 / module 邊界是否已明確
- [x] 各 owning module 的 integration adapter 是否已實作最小可驗證版本
- [x] 是否已建立第一條由 `Duty` module 載入 `claim` / `factor` 的真實 payload source path
- [x] 是否已建立第一條 player context 的 module-facing preload contract
- [x] 是否已建立第一個把 payload source 與 context preload 串起來的上層 evaluation helper
- [x] 是否已補獨立 fixture 或更多 edge-case smoke

### H. Concrete Scenario Reset 驗收
- [x] Step 1: duty definition 的 contract 是否已定義，且 claim payload 能表達 `Member::Register` trigger 與 `task_template`
- [x] Step 1: duty 內的 `task_template` 是否已能承接 `factor` 與 `reward` contract，且 task 透過 `duty_id` 即可回讀
- [x] Step 1: Docker DB-backed smoke 是否已驗證 duty definition 可寫入 `tbl_duty.claim` 並正確回讀
- [x] Step 2: 觸發 `Member::Register` 後，是否會依 duty definition 建立 task
- [x] Step 3 前置: repo baseline 是否已定義 `tbl_member_seen` 與 `member_id + target + row_id + insert_ts` 之類的結構
- [x] Step 3 前置: `member_seen` 是否已明確採「第一次達標就永遠成立」語意
- [x] Step 3 前置: `rPress` seen endpoint 是否已明確規定只做驗證與協調，而實際寫入落在 `fMember`
- [x] Step 3 前置: `MEMBER_SEEN_TARGET` 是否已成為第一個 concrete evaluator contract
- [x] Step 3: `member_seen` 成立後，task 是否會異動為 `Done`
- [x] Step 3: task done 與 100 點 reward 是否已規定走同一個 transaction

## Current Next Step

本輪已完成 Step 3 的 Docker DB-backed 驗證，包含 `member_seen` 首次寫入、task 狀態轉為 `Done`、100 點 reward 入帳、task/account log 單筆寫入，以及第二次 seen 不重複發放。下一步應回到 review，判斷是否還有必要把 helper 路徑再接上真實 `rPress -> fMember` reaction，或可直接進入 `(Optimization)` 前的整理。