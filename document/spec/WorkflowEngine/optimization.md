# WorkflowEngine Optimization

## 穩定規則摘要

- WorkflowEngine 的第一版主契約已收斂為 runtime-only 路徑：module 先提供 workflow JSON 與 runtime context，再由 engine 進行 definition 驗證、projection、transition guard 與 transition 判定。
- WorkflowEngine 位於 `libs/`，是共用 workflow 規則引擎，不擁有 module 的業務 persistence，也不反向要求建立共用 runtime 資料表。
- workflow audit trail 由各 module 的 module-owned workflow log 承接；討論概念時可用 `press_log`、`order_log`，落到實際資料表時仍遵守 F3CMS 命名慣例，例如 `tbl_press_log`。
- `Reaction` 是 workflow action 的主要協調點，`Outfit` 可用於 display-facing projection，`Kit` 可包 module-local helper，`Feed` 則只負責業務資料與 log 的持久化，不負責 workflow 規則判定。
- 舊的 workflow instance persistence 入口已屬 retired API；新的 module 整合不應再依賴 `getOrCreateInstance()`、`getOrCreateInstanceFromDefinition()`、`transitByInstanceId()`、`syncInstanceState()`、`projectInstance()` 這組路徑。

## 本輪已完成的共享文件同步

- `glossary.md`：補入 WorkflowEngine、Runtime Context、Module-owned Workflow Log、Retired API 術語。
- `guides/module_design.md`：補入 WorkflowEngine integration pattern 與 Reaction / Outfit 對 engine 的穩定責任邊界。
- `guides/data_modeling.md`：補入 module-owned workflow log pattern 與最小欄位建議。
- `reference/reaction_reference.md`：補入 Reaction 端 workflow action integration pattern。

## 封存前剩餘整理

- `history.md` 仍需在封存前做一次壓縮整理，讓下一位承接者可快速看到主線決策、回退點與最終契約。
- 目前不需要額外新增 dedicated WorkflowEngine shared reference，也不需要為本輪同步另外調整 sidebar；現有 glossary、guides、reference 已足以承接第一版穩定規則。

## 後續優化方向

- 若未來有第二個以上 module 採用同一套 workflow 整合，可再評估是否補一份更專門的 workflow integration reference。
- 若 repo 外部仍存在舊 workflow instance persistence 呼叫點，再決定 retired API 是保留拒用訊息，還是正式移除 public method。
