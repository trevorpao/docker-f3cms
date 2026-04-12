# TestMode Check

## Purpose
- 作為 TestMode 第一版規劃與重構驗收清單。
- 確保測試系統重構不只停留在目錄想像，而能逐步對齊入口、相容層與驗證路徑。
- 讓後續 `check` 與 `(Optimization)` 有正式回看基準。

## Current Status

目前狀態：
- [x] 已有 `idea.md`
- [x] 已建立 `history.md`
- [x] 已建立 `plan.md`
- [x] 已建立 `check.md`
- [x] 已完成第十輪 wrapper retirement 與程式實作
- [x] 已完成第十輪 Docker 驗證切換
- [x] 已完成第十一輪 `(Optimization)` 進場判定 `check`
- [x] 已完成第十二輪 `(Optimization)` 規則沉澱與共享術語同步

## 第一版驗收清單

### A. 路徑與責任邊界
- [x] `www/tests/` 作為新主路徑是否已明確
- [x] `www/cli/index.php` 作為 command gateway 的角色是否已明確
- [x] `www/f3cms/modules/Lab/reaction.php` 作為 diagnostic entry 的角色是否已明確
- [x] `www/f3cms/scripts/` 不再作為新 smoke 正式落點的方向是否已明確

### B. 結構與相容性
- [x] 現有 Docker volume 對 `www/tests/` 的限制是否已被納入
- [x] `www/tests/smoke/`、`fixtures/`、`bootstrap/`、`adapters/` 的第一版契約是否已完全收斂
- [x] smoke runner 是否屬於第一版是否已明確
- [x] scripts wrapper 相容層是否已決定保留策略
- [x] `www/tests/bootstrap/` 的最小責任是否已明確
- [x] `www/tests/adapters/f3cms/` 的最小責任是否已明確
- [x] smoke -> bootstrap -> adapter 的第一版呼叫鏈是否已明確
- [x] wrapper 禁止保留獨立 bootstrap 與斷言邏輯是否已明確

### C. CLI 與 Lab 整合
- [x] CLI 是否提供 smoke runner 命令是否已明確
- [x] Lab 是只顯示結果還是允許受控觸發是否已明確
- [x] CLI / Lab 不承載 smoke 本體的邊界是否已正式固定
- [x] CLI / Lab 與 `www/tests/` 的消費者關係是否已明確
- [x] 標準執行路徑不依賴 CLI / Lab 是否已明確

### D. 搬移與驗收
- [x] 第一版搬移順序是否已明確
- [x] 第一批要搬移的 smoke / fixture 是否已盤點
- [x] Docker 驗證命令的切換方式是否已明確
- [x] 回退與相容期策略是否已明確
- [x] thin wrapper 保留名單是否已明確
- [x] 第一輪 `(done)` 的最小驗收口徑是否已明確

### E. 第一輪 `(done)` 驗收
- [x] `www/tests/bootstrap/` 最小骨架是否已建立
- [x] `www/tests/adapters/f3cms/` 最小骨架是否已建立
- [x] 第一個 fixture 是否已落在 `www/tests/fixtures/`
- [x] 第一批三支 suite 是否已搬到 `www/tests/smoke/`
- [x] 對應舊 `www/f3cms/scripts/` 是否已縮成 thin wrapper
- [x] 新 `www/tests/smoke/` Docker 主命令是否已驗證成功
- [x] 至少一條舊 wrapper 相容命令是否已驗證成功

### G. 第二輪 `(done)` 驗收
- [x] `workflow_engine_definition_validation_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] `workflow_engine_projection_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] `workflow_engine_instance_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] 對應三支舊 `www/f3cms/scripts/` 是否已縮成 thin wrapper
- [x] 第二輪新 `www/tests/smoke/` Docker 主命令是否已驗證成功
- [x] 第二輪至少一條舊 wrapper 相容命令是否已驗證成功

### H. 第三輪 `(done)` 驗收
- [x] `workflow_engine_psc_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] `workflow_engine_sjse_edge_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] `workflow_engine_role_guard_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] 對應三支舊 `www/f3cms/scripts/` 是否已縮成 thin wrapper
- [x] 第三輪新 `www/tests/smoke/` Docker 主命令是否已驗證成功
- [x] 第三輪至少一條舊 wrapper 相容命令是否已驗證成功

### I. 第四輪 Engine Runtime Multi-Path `(done)` 驗收
- [x] `www/tests/adapters/f3cms/workflow_engine_runtime.php` shared helper 是否已建立
- [x] `workflow_engine_core_judgment_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] `workflow_engine_parallel_join_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] `workflow_engine_sjse_edge_execution_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] 對應三支舊 `www/f3cms/scripts/` 是否已縮成 thin wrapper
- [x] 第四輪新 `www/tests/smoke/` Docker 主命令是否已驗證成功
- [x] 第四輪至少一條舊 wrapper 相容命令是否已驗證成功

### J. 第五輪 Press Module / DB Seed `(done)` 驗收
- [x] `www/tests/adapters/f3cms/press.php` 最小 Press helper 是否已建立
- [x] `workflow_engine_press_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] 對應舊 `www/f3cms/scripts/` 是否已縮成 thin wrapper
- [x] 第五輪新 `www/tests/smoke/` Docker 主命令是否已驗證成功
- [x] 第五輪至少一條舊 wrapper 相容命令是否已驗證成功

### K. 第六輪 Transaction Rollback `(done)` 驗收
- [x] `workflow_engine_press_rollback_smoke.php` 是否已搬到 `www/tests/smoke/`
- [x] 對應舊 `www/f3cms/scripts/` 是否已縮成 thin wrapper
- [x] 第六輪新 `www/tests/smoke/` Docker 主命令是否已驗證成功
- [x] 第六輪至少一條舊 wrapper 相容命令是否已驗證成功

### L. 第七輪 canonical naming 第一批 `(done)` 驗收
- [x] `workflow_engine/press.php` 是否已建立為 canonical path
- [x] `workflow_engine/press_rollback.php` 是否已建立為 canonical path
- [x] `workflow_engine/parallel_join.php` 是否已建立為 canonical path
- [x] `workflow_engine/core_judgment.php` 是否已建立為 canonical path
- [x] `workflow_engine/sjse_edge_execution.php` 是否已建立為 canonical path
- [x] 對應五支舊 `www/f3cms/scripts/` wrapper 是否已改指向新的 canonical path
- [x] 第七輪 canonical path Docker 主命令是否已驗證成功
- [x] 第七輪至少一條舊 wrapper 相容命令是否已驗證成功

### M. 第八輪 canonical naming 第二批 `check` 結論
- [x] 第二批 rename 是否已限縮在沒有外部 spec 直接引用的 `workflow_engine` flat smoke
- [x] `workflow_engine_instance_smoke.php` 是否已重新確認為目前無外部 spec 直接引用
- [x] 第二批 canonical path 名單是否已固定
- [x] `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 是否已明確延後到跨 spec 同步批次

### N. 第八輪 canonical naming 第二批 `(done)` 驗收
- [x] `workflow_engine/definition.php` 是否已建立為 canonical path
- [x] `workflow_engine/definition_validation.php` 是否已建立為 canonical path
- [x] `workflow_engine/projection.php` 是否已建立為 canonical path
- [x] `workflow_engine/instance.php` 是否已建立為 canonical path
- [x] `workflow_engine/psc.php` 是否已建立為 canonical path
- [x] `workflow_engine/sjse_edge.php` 是否已建立為 canonical path
- [x] `workflow_engine/role_guard.php` 是否已建立為 canonical path
- [x] 對應七支舊 `www/f3cms/scripts/` wrapper 是否已改指向新的 canonical path
- [x] 第八輪 canonical path Docker 主命令是否已驗證成功
- [x] 第八輪至少一條舊 wrapper 相容命令是否已驗證成功

### O. 最後一批跨 Spec `check` 結論
- [x] 最後一批是否已收斂為 `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php`
- [x] `event_rule_engine_smoke.php` 的 canonical target 是否已固定為 `event_rule_engine/basic_or_rule.php`
- [x] `workflow_engine_instance_api_smoke.php` 的 canonical target 是否已固定為 `workflow_engine/instance_api.php`
- [x] 是否已明確要求同輪同步更新 `EventRuleEngine` / `WorkflowEngine` 文件
- [x] 是否已明確要求 wrapper retirement 必須晚於這一批 canonical naming 完成後的再次 `check`

### P. 第九輪最後一批 canonical naming `(done)` 驗收
- [x] `event_rule_engine/basic_or_rule.php` 是否已建立為 canonical path
- [x] `workflow_engine/instance_api.php` 是否已建立為 canonical path
- [x] `EventRuleEngine` / `WorkflowEngine` 文件是否已同步切到新的 canonical path
- [x] 對應兩支舊 `www/f3cms/scripts/` wrapper 是否已改指向新的 canonical path
- [x] 第九輪 canonical path Docker 主命令是否已驗證成功
- [x] 第九輪至少一條舊 wrapper 相容命令是否已驗證成功

### Q. wrapper retirement 前 `check` 結論
- [x] 是否已重新確認工作區內沒有 CLI、Lab、shell script、README 或其他現行入口依賴 `www/f3cms/scripts/*smoke*.php`
- [x] 是否已確認剩餘 wrapper 引用只存在於歷史敘述或可同輪修正的現況描述
- [x] `EventRuleEngine` / `WorkflowEngine` owner spec 是否已納入同輪同步更新範圍
- [x] 是否已確認所有 wrapper 都只剩單行 `require` 轉發
- [x] 是否已正式判定 wrapper retirement 具備進場條件

### R. 第十輪 wrapper retirement `(done)` 驗收
- [x] `www/f3cms/scripts/*smoke*.php` 是否已全部移除
- [x] `TestMode`、`EventRuleEngine`、`WorkflowEngine` 文件是否已同步移除「wrapper 保留中」的現況描述
- [x] Docker 是否已在無 wrapper 狀態下重新驗證代表性 canonical smoke 主命令
- [x] `www/tests/smoke/<domain>/*.php` 是否已成為唯一保留的 smoke 執行入口

### S. `(Optimization)` 進場判定 `check` 結論
- [x] `check.md` 是否已確認主要實作完成
- [x] `check.md` 是否已確認主要驗收完成
- [x] 是否已確認目前沒有阻擋主流程承接的關鍵缺口
- [x] 下一步是否已收斂為文件同步、規則沉澱、詞彙整理與封存前收尾
- [x] 是否已正式判定 TestMode 第一版可進入 `(Optimization)`

### 第四輪 `(done)` 完成後的新狀態
- `workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php` 已正式由 `www/tests/smoke/` 承接
- `www/tests/adapters/f3cms/workflow_engine_runtime.php` 已成為 Engine Runtime Multi-Path 類共用 runtime harness 的 source of truth
- 重新盤點後，剩餘未搬移 smoke 已只剩 `workflow_engine_press_smoke.php` 與 `workflow_engine_press_rollback_smoke.php`
- 因此目前待處理的高風險批次已收斂為 Press Module / DB Seed 類與 Transaction Rollback 類

### 第五輪 `(done)` 完成後的新狀態
- `workflow_engine_press_smoke.php` 已正式由 `www/tests/smoke/` 承接
- `www/tests/adapters/f3cms/press.php` 已成為 Press reaction smoke 的最小 setup source of truth
- 目前剩餘未搬移 smoke 已只剩 `workflow_engine_press_rollback_smoke.php`
- 因此目前待處理的高風險批次只剩 Transaction Rollback 類

### 第六輪 `(done)` 完成後的新狀態
- `workflow_engine_press_rollback_smoke.php` 已正式由 `www/tests/smoke/` 承接
- 目前已沒有剩餘未搬移 smoke，legacy smoke 主路徑已全部切到 `www/tests/smoke/`
- 因此後續承接點已不再是 smoke 搬移，而是 canonical naming 與 wrapper retirement 收尾 stage

### 第七輪 `(done)` 完成後的新狀態
- `workflow_engine` domain 的第一批五支 suite 已切到 `www/tests/smoke/workflow_engine/*.php` canonical path
- 對應 flat `www/tests/smoke/*_smoke.php` 檔名已自第一批中移除，不再作為 source of truth
- 因此後續承接點已進一步收斂為 canonical naming 第二批與最終 wrapper retirement

### 第八輪 `(done)` 完成後的新狀態
- 第二批七支 `workflow_engine` flat smoke 已切到 `www/tests/smoke/workflow_engine/*.php` canonical path
- 目前仍維持 flat 命名且待後續處理者已收斂為 `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php`
- 因此後續承接點已不再是 `workflow_engine` 內部 rename，而是最後一批跨 spec path 更新與 wrapper retirement

### 第九輪 `(done)` 完成後的新狀態
- `event_rule_engine` 與 `workflow_engine` 的 smoke 主路徑已全部切到 folderized canonical path
- 目前剩餘承接點已不再是 canonical naming，而是 wrapper retirement 是否具備進場條件
- 因此下一步應由 `check` 重新確認所有 spec、命令範例與程式入口是否都已不再把舊 wrapper 視為 source of truth

### 第十輪 `(done)` 完成後的新狀態
- `www/f3cms/scripts/*smoke*.php` 過渡期 wrapper 已全部移除
- `www/tests/smoke/<domain>/*.php` 已成為唯一保留的 smoke 執行入口
- 目前剩餘承接點已不再是路徑切換，而是回到 `check` 判定 TestMode 第一版是否已可進入 `(Optimization)`

### 第十一輪 `check` 完成後的新狀態
- TestMode 第一版已確認可正式進入 `(Optimization)`
- 後續承接點已不再是功能或路徑調整，而是建立 `optimization.md` 並整理穩定規則

### 第十二輪 `(Optimization)` 完成後的新狀態
- `optimization.md` 已建立並承接 TestMode 第一版的穩定規則摘要
- 共用術語已同步回 `glossary.md`，TestMode 的主要收穫不再只存在於 feature spec 內
- 目前剩餘 closeout gap 已收斂為歷史壓縮與封存前整理

### F. `check` 收斂結果
- [x] 第一輪 `(done)` 的完成邊界是否已盤點
- [x] 第二批低風險 smoke 候選是否已明確
- [x] 高風險、需延後批次的 smoke 是否已明確
- [x] wrapper 退場前提是否已明確

## Check Findings

以下各段批次分析主要保留歷史 `check` 軌跡；當前是否完成、是否可進入下一階段，應以前段 `Current Status`、`Current Next Step` 與最新 readiness 結論為準。

### 已完成邊界
- 第一批主路徑已切換成功：`event_rule_engine_smoke`、`workflow_engine_smoke`、`workflow_engine_instance_api_smoke`
- `www/tests/bootstrap/` 與 `www/tests/adapters/f3cms/` 已能支撐純 engine / definition / instance-api 類 smoke
- 舊 `scripts/` thin wrapper 已可保護既有命令，不必在第一輪同時切掉所有舊入口

### 第二批建議搬移名單
- `workflow_engine_definition_validation_smoke.php`
- `workflow_engine_projection_smoke.php`
- `workflow_engine_instance_smoke.php`

第二批選這三支的理由：
- 都仍屬 WorkflowEngine 核心契約驗證，與第一輪已建立的 bootstrap / adapter 模式高度一致
- 不像 `workflow_engine_press_smoke.php` 與 `workflow_engine_press_rollback_smoke.php` 那樣直接進入 DB seed / module reaction 路徑
- 不像 `workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php` 那樣帶較多 helper 與多路徑情境，適合先放後批

### 第二輪時的延後批次名單
- `workflow_engine_press_smoke.php`
- `workflow_engine_press_rollback_smoke.php`
- `workflow_engine_core_judgment_smoke.php`
- `workflow_engine_parallel_join_smoke.php`
- `workflow_engine_sjse_edge_execution_smoke.php`

延後原因：
- 直接依賴 DB seed、rollback、module reaction 或較複雜的多路徑 helper
- 若在第二輪就一起搬，會把 `check` 的焦點從 smoke 架構搬移擴張到業務資料與流程行為回歸

### Wrapper 退場前提
- 對應 suite 已有 `www/tests/smoke/` 主路徑
- Docker 驗證記錄已改以新命令為主
- 文件與 spec 不再以舊 `www/f3cms/scripts/` 路徑作為 source of truth
- 已確認沒有 CLI / Lab / 其他腳本仍以舊路徑作為唯一入口

### 第二輪 `(done)` 完成後的新狀態
- 目前低風險、可直接沿用共用 bootstrap / adapter 的 WorkflowEngine smoke 已累積完成六支
- 剩餘未搬移者主要集中在 press / rollback / parallel / core judgment / sjse execution 這類較高風險批次
- 下一輪若要繼續 `(done)`，應先重新確認是否還存在第三批 low-risk，而不是直接把高風險批次一起搬入

### 第三批 low-risk 候選
- `workflow_engine_psc_smoke.php`
- `workflow_engine_sjse_edge_smoke.php`
- `workflow_engine_role_guard_smoke.php`

第三批選這三支的理由：
- `workflow_engine_psc_smoke.php` 與 `workflow_engine_sjse_edge_smoke.php` 仍屬 definition / metadata / transition-kind 驗證，沒有 DB seed、module reaction 或 rollback 依賴
- `workflow_engine_role_guard_smoke.php` 雖然會跑 transition，但仍停留在 engine 內的 role guard 契約驗證，未混入資料表 seed 或模組反應層
- 三支都可沿用現有 `www/tests/bootstrap/` 與 `www/tests/adapters/f3cms/`，不需要新增第三種 bootstrap 結構

### 第三輪前重新確認後的高風險延後批次
- `workflow_engine_press_smoke.php`
- `workflow_engine_press_rollback_smoke.php`
- `workflow_engine_core_judgment_smoke.php`
- `workflow_engine_parallel_join_smoke.php`
- `workflow_engine_sjse_edge_execution_smoke.php`

這批仍延後的理由：
- 已混入 DB seed、rollback、module reaction 或較重的多路徑 helper
- 若直接納入第三輪，會把 smoke 結構搬移與 workflow 行為回歸耦合在一起

### 第三輪 `(done)` 完成後的新狀態
- 目前低風險、可直接沿用共用 bootstrap / adapter 的 WorkflowEngine smoke 已累積完成九支
- 目前剩餘未搬移者已全部落在高風險延後批次，尚未看到第四批仍可直接套用既有搬移模板的 low-risk 候選
- 若後續仍要繼續搬移，應先把剩餘 smoke 再拆成更細的策略，例如 DB seed 類、rollback 類、parallel / core-judgment 類，而不是再沿用目前的 low-risk 節奏

### 第四輪前高風險群組拆分結果

#### A. Press Module / DB Seed 類
- `workflow_engine_press_smoke.php`

特徵：
- 直接操作 `tbl_press`、`tbl_press_log`
- 需要建 seed row 與 session / CSRF / request context
- 直接呼叫 `\F3CMS\rPress()->do_published()`，已進入 module reaction 層

#### B. Transaction Rollback 類
- `workflow_engine_press_rollback_smoke.php`

特徵：
- 直接操作 transaction begin / commit / rollback
- 驗證 workflow trace 與 downstream write failure 的 rollback 行為
- 不只是 smoke 主路徑搬移，還牽涉 transaction 與資料一致性驗證

#### C. Engine Runtime Multi-Path 類
- `workflow_engine_core_judgment_smoke.php`
- `workflow_engine_parallel_join_smoke.php`
- `workflow_engine_sjse_edge_execution_smoke.php`

特徵：
- 共享 `loadWorkflowEngine`、`transitionSummary`、`transitRuntime` 這類 helper 形狀
- 驗證 rollback / branch / join / duplicate reviewer / terminate 等多路徑 runtime 行為
- 比 definition-only smoke 更接近小型 runtime suite，需要決定是否先抽共用 runtime helper

### 第四輪前分組後的優先順序建議
- 第一優先：Engine Runtime Multi-Path 類
- 第二優先：Press Module / DB Seed 類
- 第三優先：Transaction Rollback 類

排序理由：
- Engine Runtime Multi-Path 類仍主要停留在 WorkflowEngine 本體，不必先引入 module reaction 與資料庫寫入回復策略
- Press Module / DB Seed 類已跨進 module reaction 與資料表 seed，但仍比 rollback 類少一層 transaction failure 模擬
- Transaction Rollback 類最容易把 smoke 搬移、資料一致性與回復口徑綁在一起，應最後處理

### 第四輪前 Engine Runtime Multi-Path 類的 helper 檢查結論

本輪已補完這一類進入 `(done)` 前最後一個必要檢查：是否應先抽 shared runtime helper。

檢查結果：應先抽，理由如下：
- 三支 script 都自行宣告 `loadWorkflowEngine()`、`transitionSummary()`、`transitRuntime()` 形狀，重複不只是命名相近，而是責任完全重疊
- 三支差異主要只剩 summary 欄位多寡與路徑 assertion，代表共通部分已足以形成穩定 adapter helper
- 若不先抽 helper，下一輪 `(done)` 只是把重複程式搬到 `www/tests/smoke/`，不會形成可持續的 `www/tests` source of truth

因此第四輪前該次 `check` 結論是：
- Engine Runtime Multi-Path 類已具備進下一輪 `(done)` 的條件
- 但下一輪 `(done)` 的第一步應先建立 shared WorkflowEngine runtime helper，而不是直接逐支平移 legacy script
- 本輪仍未動到 Press Module / DB Seed 與 Transaction Rollback 類，避免把範圍再次擴大

### 第四輪時仍待處理的高風險批次

目前狀態說明：
- Engine Runtime Multi-Path 類已於第四輪 `(done)` 完成搬移與 Docker 驗證，不再屬於待搬移名單
- Press Module / DB Seed 類已於第五輪 `(done)` 完成最小搬移
- Transaction Rollback 類已於第六輪 `(done)` 完成搬移，僅剩驗證收尾與後續 canonical naming / wrapper retirement

### Press / Rollback 類的最新承接結論
- `workflow_engine_press_smoke.php` 已依此結論完成搬移，且 helper 已限制在 press seed row、staff session、CSRF 與 reaction request context
- `workflow_engine_press_rollback_smoke.php` 不需要先補 transaction rollback helper；它可直接沿用既有 Press helper 的 seed / session 基礎，並在 suite 本體內保留 rollback-specific 驗證

## Current Next Step

下一步應繼續 `(Optimization)`：壓縮 `history.md` 並確認是否還有需要回寫到共享文件的封存前缺口。

### Canonical Naming 第一批 `check` 結論
- 第一批 rename 名單固定為：`workflow_engine_press_smoke.php`、`workflow_engine_press_rollback_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_core_judgment_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php`
- 第一批 canonical path 應改為 `www/tests/smoke/workflow_engine/*.php`，並移除檔名中的 `_smoke`
- 第一批 Docker 驗證應先跑新的 canonical path，再跑舊 `www/f3cms/scripts/*.php` wrapper 相容命令
- 第一批文件更新應先從 TestMode 開始，再向外同步其他直接引用該路徑的 feature spec

### Canonical Naming 第二批 `check` 結論
- 第二批 rename 名單固定為：`workflow_engine_smoke.php`、`workflow_engine_definition_validation_smoke.php`、`workflow_engine_projection_smoke.php`、`workflow_engine_instance_smoke.php`、`workflow_engine_psc_smoke.php`、`workflow_engine_sjse_edge_smoke.php`、`workflow_engine_role_guard_smoke.php`
- 第二批 canonical path 應改為 `www/tests/smoke/workflow_engine/definition.php`、`definition_validation.php`、`projection.php`、`instance.php`、`psc.php`、`sjse_edge.php`、`role_guard.php`
- 本輪重新確認 `workflow_engine_instance_smoke.php` 目前沒有外部 spec 直接引用，因此可納入第二批；相對地，`event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 仍應延後到跨 spec 同步批次
- 第二批 Docker 驗證仍應先跑新的 canonical path，再跑對應舊 `www/f3cms/scripts/*.php` wrapper 相容命令

### 最後一批跨 Spec `check` 結論
- 最後一批 rename 名單固定為：`event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php`
- canonical path 應改為 `www/tests/smoke/event_rule_engine/basic_or_rule.php` 與 `www/tests/smoke/workflow_engine/instance_api.php`
- 這一批不能只更新 TestMode；需同輪同步更新 `EventRuleEngine` 與 `WorkflowEngine` 文件，否則 rename 完成後仍會留下外部 spec drift
- wrapper retirement 不應與這一批同輪進行；應等這一批 canonical naming 與 Docker 驗證完成後，再回到 `check` 確認所有引用面都已切換

### Wrapper Retirement `check` 結論
- 本輪重新搜尋工作區後，未發現 `bin/`、`README.md`、`www/` 其他程式或現行自動化入口仍直接依賴 `www/f3cms/scripts/*smoke*.php`
- 剩餘 wrapper 引用僅存在於歷史敘述，以及 `EventRuleEngine` / `WorkflowEngine` 文件中對過渡期現況的描述；前者可保留，後者需與 retirement 同輪回寫
- 所有 wrapper 都已退化為單行 `require` 轉發，符合批次移除前提
- 因此 wrapper retirement 已具備進場條件，可進入專門的 `(done)` 批次

### `(Optimization)` 進場判定結論
- 依照 `flow.llm.md` 的四項 entry criteria 逐項比對後，目前 TestMode 第一版已確認主要實作與主要驗收完成
- 目前沒有阻擋 release 或主流程承接的關鍵缺口；既有 smoke 主路徑、canonical naming 與 wrapper retirement 都已落地
- 剩餘工作已收斂為建立 `optimization.md`、整理穩定詞彙、回寫共用規則與封存前收尾
- 因此本輪 `check` 完成後，下一步應正式進入 `(Optimization)`

### `(Optimization)` 第一輪沉澱結果
- `optimization.md` 已固定 TestMode 第一版的 smoke source of truth、責任鏈、canonical naming 與 Docker 驗證口徑
- `glossary.md` 已補入可跨 feature 共用的測試與收尾術語
- 目前尚未發現需要再回寫到 `setup.md`、`overall.md` 或其他 guide 的新通用流程