# SeenTargetTaskCompletion - check.md

## Current Check Baseline
- 目前尚未進入正式 `check` 驗收，但 implementation 已開始。
- 本文件除了保留驗收骨架，也開始記錄 `(done)` 階段已完成的 runtime slice 與尚存 drift。

## 已完成項
- current spec 已切換到 `SeenTargetTaskCompletion`。
- `history.md` 已完成 Round 1 至 Round 4 的承接，並收斂以下結論：
	- seen truth 以 `{entity}_seen` 為第一版 baseline。
	- reusable completion contract 的 owner 是 `Duty`。
	- 不建立通用 target resolver，但允許 task 提供 target existence / availability 掛載點。
	- 單一 feed transaction 由 feed 持有；跨多個 module feed 時由 kit 協調 transaction。
	- task 只顯示目前可達成且未過期項目。
	- task 已完成後，重複 seen API 不再走 truth / reward path。
	- 第二個 `Post` 任務以多筆 `post_seen` 逐步累積，條件全部成立後才完成 task。
- Stage 1 contract 已完成文件定稿：
	- seen-target completion 最小輸入已定義
	- 第一版 output shape 已定義
	- `{entity}_seen` truth reference envelope 已定義
	- target existence hook 的 dispatch 與 fail-closed fallback 已定義
- Stage 2 boundary 已完成文件定稿：
	- content module helper、Duty kit、feed 的責任矩陣已定義
	- cross-feed transaction coordination 與 owner write 邊界已定義
	- hook 呼叫時機與 `target_unavailable` skip path 已定義
- Stage 3 query / evaluate 規則已完成文件定稿：
	- achievable / expired task query contract 已定義
	- `Post` 多篇累積採 truth 累積 + 每次重算模式
	- evaluate 順序與前台一致性原則已定義
- Stage 4 validation contract 已完成文件定稿：
	- Docker / `php-fpm` 已指定為正式驗證基線
	- canonical smoke command shape 已定義
	- implementation 前 baseline smoke 與 implementation 後 canonical smoke suite 已列出
	- host lint `exit 134` 已明確標記為不可單獨作為 regression 判定
- 第一個 implementation slice 已完成並通過 Docker smoke：
	- `kDuty::completeTasksForSeenTarget(...)` 已顯式回傳 `short_circuited_tasks` 與 `skipped_tasks`
	- 重複 seen 第二次呼叫會回傳 `already_completed` short-circuit，而不再只默默回傳空 `completed_tasks`
	- baseline smoke 已更新斷言並通過
- 第一條 canonical smoke suite 已建立並通過 Docker 驗證：
	- `www/tests/smoke/seen_target_task_completion/press_seen_reward.php` 已建立
	- 新 suite 目前仍驗證 legacy `member_seen` baseline，但正式落在 `seen_target_task_completion/` domain，而不再只依賴 `event_rule_engine/*`
- 第二條 canonical smoke suite 已建立並通過 Docker 驗證：
	- `www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php` 已建立
	- repeat seen 的 `already_completed` short-circuit 契約已從 press reward smoke 中拆成獨立 suite
- 第一個 target hook runtime slice 已完成並通過 Docker smoke：
	- `kDuty::completeTasksForSeenTarget(...)` 已在 truth write 前做 fail-closed target hook dispatch
	- `kPress::isAvailable(...)` 已作為第一個 owner-side availability hook 落地
	- target unavailable 時會回傳 `skipped_tasks.target_unavailable`，且不寫 `member_seen`、不改 task 狀態、不發 reward
- 第三條 canonical smoke suite 已建立並通過 Docker 驗證：
	- `www/tests/smoke/seen_target_task_completion/target_hook_unavailable.php` 已建立
	- target unavailable 契約已由獨立 suite 驗證為 fail-closed，且目前落在 legacy `member_seen` path 之前
- `Post` owner hook 的第一個 legacy slice 已完成並通過 Docker smoke：
	- `kPost::isAvailable(...)` 已作為第一個 `Post` owner-side availability hook 落地
	- `Post` target 現在可沿既有 `kDuty::completeTasksForSeenTarget(...)` fail-closed path 進行 target availability 檢查
- 第四條 canonical smoke suite 已建立並通過 Docker 驗證：
	- `www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php` 已建立
	- `Post` 三篇累積後才完成 task 並發 reward 的契約，已由獨立 suite 驗證通過
- 第五條 canonical smoke suite 已建立並通過 Docker 驗證：
	- `www/tests/smoke/seen_target_task_completion/cross_feed_transaction_rollback.php` 已建立
	- strict mode session 下的超長 `reward.action_code` 已被驗證會觸發 log write failure，且 `kDuty::completeTasksForSeenTarget(...)` 會回滾 seen truth、task 狀態與 account side effect

## 未完成項
- Round 3 / Round 4 的穩定結論已開始沿 `(done)` 階段逐步落成 runtime slice，但仍遠未完成完整實作對齊。
- 尚未定義 `{entity}_seen` schema / preload contract 的實作欄位與查詢細節。
- Stage 4 列出的第一版 canonical smoke suites 已全部落地：`press_seen_reward.php`、`repeat_seen_short_circuit.php`、`target_hook_unavailable.php`、`post_seen_accumulate_reward.php`、`cross_feed_transaction_rollback.php`。
- 尚未把第一個 implementation slice 從 legacy `member_seen` path 推進到 `{entity}_seen` / target hook / achievable filtering。
- 尚未把 runtime 從 legacy owner hooks 進一步推進到 `{entity}_seen`、更完整的多 target owner hook，或 cross-feed rollback 驗證。

## 程式對齊狀態
- 目前程式已開始局部 resync，但仍未符合本 spec 的第一版完整結論。
- 已知差異：
	- 目前 `kDuty::completeTasksForSeenTarget(...)` 仍經由 `fMember` abstraction 建立 seen truth；對 `Press` 而言，當 `tbl_press_seen` 存在時已可寫入 entity table，但 shared Docker DB 尚未持久重建，所以常駐環境仍預設回退 `tbl_member_seen`。
	- `{entity}_seen` runtime abstraction 已落地在 `fMember`，其中 `Press` 已有第一個 schema artifact 與 smoke-level live table 驗證；`Post` 等其他 target 仍未提供 target-specific table。
	- target existence hook dispatch 已有 `Press` 與 `Post` path，但尚未與 `{entity}_seen` 對齊，也尚未覆蓋更多 target owner。
	- completed-task short-circuit 已有 legacy path 證據，expired filtering 也已有 completion path + pending query slice；但 achievable / target-unavailable / unreachable 的 query contract 尚未完全與 completion path 共用。
	- cross-feed rollback canonical suite 已有 legacy path 證據，但目前仍是靠 smoke 內 session strict mode 人工固定 failure hook，而不是 runtime 內建 fault injection 機制。

## 新完成項
- `{entity}_seen` 的第一個 runtime abstraction slice 已完成：
	- `fMember::oneSeenTarget(...)` / `createSeenTarget(...)` / `seenTargetMapByMemberId(...)` 會優先查找 `tbl_{target}_seen`
	- 若 target-specific table 不存在，會自動回退到 `tbl_member_seen`
	- `seenTargetMapByMemberId(...)` 已能彙整 generic 與 entity seen tables，為 future preload contract 做對齊
- `Press` 的第一個 live entity table slice 已可驗證：
	- `tbl_press_seen` 的 schema artifact 已改放 `document/sql/260425.sql`
	- `Press` canonical suites 會在 smoke 期間暫建 `tbl_press_seen`，直接驗證 entity table write 與 reuse / rollback / unavailable 行為
	- 在 table 存在時，`Press` truth 已驗證會寫入 `tbl_press_seen`，而不是回退 `tbl_member_seen`
- `Post` 的第二個 live entity table slice 已可驗證：
	- `tbl_post_seen` 的 schema artifact 已加入 `document/sql/260425.sql`
	- `Post` canonical suite 會在 smoke 期間暫建 `tbl_post_seen`，直接驗證 entity table write 與累積完成行為
	- 在 table 存在時，`Post` truth 已驗證會寫入 `tbl_post_seen`，而不是回退 `tbl_member_seen`
- smoke schema source-of-truth drift 已修正：
	- `Press` / `Post` smoke helper 現在直接從 `document/sql/260425.sql` 擷取對應 `CREATE TABLE` 語句
	- `php-fpm` 已掛入 `/var/www/document`，因此 Docker smoke 與 SQL artifact 現在共用同一份 schema source of truth
- expired task completion gate 的第一個 runtime slice 已完成：
	- `kDuty::completeTasksForSeenTarget(...)` 現在會在 factor evaluate 前檢查 `claim.task_template.expire_at`
	- 當 task 尚未完成且 `expire_at` 已過期時，會回傳 `skipped_tasks.reason = task_expired`
	- expired task 目前仍保留本次合法 seen truth，但不會進入 task done / reward path
	- `www/tests/smoke/seen_target_task_completion/task_expired_skip.php` 已建立並通過 Docker 驗證
- expired task query contract 的第一個 runtime slice 已完成：
	- `fDuty` 現在提供共用的 `loadTaskTemplate(...)` 與 `isTaskTemplateExpired(...)`
	- `fTask::pendingByMemberId(...)` 現在會排除已過期 task，與 completion path 的 `task_expired` gate 對齊
	- `fTask::byMemberId(...)` 仍維持 raw query 行為，供較寬的 orchestration path 使用
	- `www/tests/smoke/seen_target_task_completion/pending_tasks_exclude_expired.php` 已建立並通過 Docker 驗證
- target_unavailable query contract 的第一個 runtime slice 已完成：
	- `fDuty` 現在提供共用的 `isSeenTargetAvailable(...)`、`listSeenTargets(...)` 與 `hasUnavailableSeenTarget(...)`
	- `fTask::pendingByMemberId(...)` 現在也會排除已經 `target_unavailable` 的 seen-target task
	- `fTask::byMemberId(...)` 仍維持 raw query 行為，供較寬的 orchestration path 使用
	- `www/tests/smoke/seen_target_task_completion/pending_tasks_exclude_unavailable.php` 已建立並通過 Docker 驗證

## 驗證狀態
- 已有第一個可採信的 current-spec runtime slice 驗證結果，但仍屬 legacy baseline 路徑。
- 目前只知道 host 端 PHP lint 曾出現 `exit 134`；依 FDD 規則，這不能直接作為 regression 判定。
- implementation 前可參照既有 baseline smoke：
	- `www/tests/smoke/event_rule_engine/press_seen_reaction_task_done_reward.php`
	- `www/tests/smoke/event_rule_engine/member_seen_task_done_reward.php`
- implementation 後正式驗收應優先使用 `www/tests/smoke/seen_target_task_completion/` 下的 canonical suites。
- 本輪已完成的 Docker 驗證：
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/event_rule_engine/member_seen_task_done_reward.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/event_rule_engine/press_seen_reaction_task_done_reward.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/press_seen_reward.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/target_hook_unavailable.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/cross_feed_transaction_rollback.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php`（`{entity}_seen` abstraction slice 後重驗）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php`（`{entity}_seen` abstraction slice 後重驗）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/press_seen_reward.php`（`tbl_press_seen` live slice 驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php`（`tbl_press_seen` live slice 驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/target_hook_unavailable.php`（`tbl_press_seen` live slice 驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/cross_feed_transaction_rollback.php`（`tbl_press_seen` live slice 驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php`（`tbl_post_seen` live slice 驗證）
	- `docker compose exec -T php-fpm ls -l /var/www/document/sql/260425.sql`（確認 `php-fpm` 可直接讀取 SQL artifact）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/press_seen_reward.php`（helper 改讀 `document/sql/260425.sql` 後重驗）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php`（helper 改讀 `document/sql/260425.sql` 後重驗）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/task_expired_skip.php`（expired task completion gate 驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/press_seen_reward.php`（expired task gate 後回歸驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/pending_tasks_exclude_expired.php`（pending query 排除 expired task 驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/task_expired_skip.php`（query-side 對齊後回歸驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/press_seen_reward.php`（query-side 對齊後回歸驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/pending_tasks_exclude_unavailable.php`（pending query 排除 target_unavailable task 驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/target_hook_unavailable.php`（共用 availability helper 後回歸驗證）
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/press_seen_reward.php`（共用 availability helper 後回歸驗證）

## 待驗收情境
- `Press` seen 成功建立對應 `{entity}_seen`，並完成單篇任務。
- `Post` seen 逐步累積三篇內容，最後完成任務並發放 reward。
- target existence hook 回傳 unavailable 時，拒絕 truth write 或後續 completion。
- 已完成 task 的重複 seen API 會 short-circuit，不重複發點。
- 單一 feed transaction 與跨 feed transaction 在資料一致性上的行為符合 spec。
- expired task 不出現在前台可達成任務清單中，且前台 query 要與 completion path 的 `task_expired` gate 共用同一套判準。
- `target_unavailable` 的前台 query 已開始與 completion path 對齊；`task_unreachable` 與更完整 achievable 的 query contract 仍需共用判準。

## 目前判定
- 目前 feature 已進入 `(done)`，但距離 `check` 完成或 `(Optimization)` 仍有明顯差距。
- 下一步應沿既有 implementation momentum，優先把 pending query / 前台 task list 向 `task_unreachable` 或 prerequisite-based achievable filtering 收斂，再視結果決定是否需要更大的 query abstraction。
