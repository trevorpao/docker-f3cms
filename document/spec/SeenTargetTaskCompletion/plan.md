# SeenTargetTaskCompletion - plan.md

## Current Stage
- `plan`
- 依 `history.md` Round 3 / Round 4，owner、資料模型、transaction 原則與 target existence hook 已收斂，本文件負責把這些結論拆成可執行階段。

## Stage 1: Contract 與資料模型定稿

狀態：
- 已完成文件定稿，待後續 `(done)` 依此實作

目標：
- 把 `{entity}_seen` baseline、Duty owner、target existence hook 與 result shape 定義成第一版實作 contract。

工作項目：
- 定義 seen-target completion 最小輸入：`member_id`、`target`、`row_id`、`source`、`insert_user`。
- 定義第一版輸出 shape：`seen`、`completed_tasks`、必要的 reward / short-circuit metadata。
- 定義 `{entity}_seen` 的第一版承接口徑，至少涵蓋 `press_seen` 與 `post_seen`。
- 定義 task target existence hook 的 contract，包括 target type 到 owner method 的 dispatch 方式。
- 定義 target owner method 的 fallback 行為，例如 method 不存在、row 不存在、row 不可達成時的統一回傳口徑。

本階段驗收點：
- 文件可清楚回答 `{entity}_seen` 與既有 `member_seen` 的關係：第一版以 `{entity}_seen` 為準，不再把 `member_seen` 當本 spec 的 runtime baseline。
- 文件可清楚回答 `Duty` 與內容 module 的分工：`Duty` 持有 completion orchestration，內容 module 持有 availability rule 與 truth write。
- 文件可清楚回答 target existence hook 的 dispatch 與 fallback 規則。
- 文件可清楚回答第一版 completion result 的 `completed_tasks`、`short_circuited_tasks`、`skipped_tasks` shape。

Fallback：
- 若 `{entity}_seen` schema 無法在第一版同時覆蓋 `Press` 與 `Post`，先以 contract 層對齊，實作分兩步落地，不回退 owner 結論。

## Stage 2: Completion Flow 邊界拆分

狀態：
- 已完成文件定稿，待後續 `(done)` 依此拆分現有 code path

目標：
- 把目前集中在 `kDuty::completeTasksForSeenTarget(...)` 的責任拆成符合 spec 的 flow 邊界。

工作項目：
- 釐清內容 module entry 需要保留的責任：target row 解析、availability 驗證、`{entity}_seen` truth write。
- 釐清 Duty kit 需要承接的責任：pending task evaluate、EventRuleEngine context preload、task done、reward writeback、completed-task short-circuit。
- 定義單一 feed 與跨 feed 兩種 transaction 路徑的責任分配。
- 定義 `Duty` 在 evaluate 前呼叫 target existence hook 的時機點。

本階段驗收點：
- 文件可清楚回答哪些 write 留在 feed，哪些 orchestration 留在 kit。
- 文件可清楚回答跨 feed transaction 為何允許在 kit 協調，且不與 owner write 混淆。
- 文件可清楚回答 task 已完成時，重複 seen API 如何 short-circuit。
- 文件可清楚回答 hook 呼叫時機與 `skipped_tasks.target_unavailable` 的處理方式。

Fallback：
- 若 cross-feed transaction 的實作切法暫時無法一次收斂，先保留 transaction routing 規則與最小單一 feed path，不回退 hook 與 owner 結論。

## Stage 3: Task 可見性與可達成條件

狀態：
- 已完成文件定稿，待後續 `(done)` 依此建立 achievable / expired query 與 evaluate path

目標：
- 把「前台只顯示目前可達成且未過期任務」與「Post 多篇逐步累積完成」寫成明確 query / evaluate 規則。

工作項目：
- 定義 pending task query 是否需要額外承接 expire / achievable 條件。
- 定義 target availability 與 task achievable 的先後順序。
- 定義第二個 `Post` 任務的中間狀態如何由多筆 `post_seen` 累積。
- 定義 completed / expired / unreachable task 在前台與 evaluate path 的處理方式。

本階段驗收點：
- 文件可清楚回答前台 task list 的可見性條件。
- 文件可清楚回答第二個 `Post` 任務的中間狀態如何影響 evaluate。
- 文件可清楚回答 expired task 是否仍保留 truth，但不再參與前台顯示與 reward path。
- 文件可清楚回答 evaluate 順序：completed -> expired -> achievable/hook -> factor evaluate -> done/reward。
- 文件可清楚回答前台 query 與 completion path 必須使用同一套 achievable / expired 判準。

Fallback：
- 若 achievable 與 availability 暫時無法完全合併，先以「先過 target hook，再 evaluate task factor」作為第一版順序。

## Stage 4: 驗證與落地前檢查

狀態：
- 已完成文件定稿，待後續 `(done)` 依此建立或更新 smoke suite

目標：
- 為後續 `(done)` 與 `check` 建立一致的驗收入口，避免 host-only 判斷漂移。

工作項目：
- 指定 Docker 為優先驗證環境。
- 指定 `php-fpm` 為正式 PHP 驗證 service。
- 指定 canonical smoke command shape：`docker compose exec -T php-fpm php /var/www/tests/smoke/<path>`。
- 記錄 implementation 前的 baseline smoke 與 implementation 後的目標 smoke suite。
- 列出第一版至少要覆蓋的驗證情境：
	- `Press` seen 成功完成單篇任務
	- `Post` seen 逐步累積三篇後完成任務
	- target hook 回傳不可達成時拒絕 truth / completion
	- 已完成 task 的重複 seen API short-circuit
	- truth write 後 task / reward 失敗時的 transaction 行為
- 明確標示 host PHP lint `exit 134` 不能直接作為 regression 判定。

本階段驗收點：
- `check.md` 已有對應的完成項 / 未完成項 / 驗證欄位。
- 驗證路徑已明確指定 Docker 或既有 smoke 為優先。
- 文件可清楚區分 implementation 前 baseline smoke 與 implementation 後 canonical smoke suite。
- 文件可清楚回答 host lint `exit 134` 只屬環境噪音，不是 release 判準。

Fallback：
- 若本輪尚未找到可直接執行的 Docker smoke，至少先在 `check.md` 明列待補驗證入口，不以 host-only failure 代替驗收。

## 下一步
- 文件規劃階段已完成，下一個最小步驟是進入 `(done)`，依 Stage 1 至 Stage 4 的定稿內容開始拆分現有 code path。
- implementation 開始後，應同步建立 `www/tests/smoke/seen_target_task_completion/` 下的 canonical suites，並以 Docker 命令驗證。
