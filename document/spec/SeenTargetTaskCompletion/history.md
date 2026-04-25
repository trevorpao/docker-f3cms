# SeenTargetTaskCompletion - history.md

## Round 1
- 使用者明確更正了前一條 spec 的誤會起點：真正想抽象的是 `rPress::completeSeenForMember(...)` 與 `kDuty::completeTasksForSeenTarget(...)` 這類 EventRuleEngine-style seen target completion 函式，而不是 Press publish / offline 的 workflow action coordination。
- 因此本輪正式建立新 spec `SeenTargetTaskCompletion`，以「會員完成內容閱讀 / 觀看後，如何建立 truth 並觸發 duty task completion」作為主題。
- 第一個 concrete scenario 仍是既有的 seen-driven 會員任務；第二個 concrete scenario 則是新增 `Post` 單頁文章三篇都讀完後給 100 點的任務。這兩個例子都支持「seen-target completion 應有 reusable contract」這個方向。
- 目前 stage 應停在 `idea` / `(discuss)`：新 spec 已建立，但 owner 邊界、transaction 邊界與 reusable public surface 仍未收斂。
- 下一步：補做 owner review，回答 `Duty`、`Member`、內容 module helper 三者各自應承接哪些 responsibility，並判斷 transaction 應回到哪個 Feed。

## Round 2
- 使用者進一步把完整端到端流程補齊：後台管理者建立兩個 `duty`，兩者都在 `Member::Register` 後建立 `task`；會員前台看到任務後，前往指定 `Press` 與 `Post`；觸發 seen API 後，在對應 seen truth 留下紀錄，並完成 task、取得點數。
- 本輪已把這條敘述重寫成更精確的 mainline scenario，避免流程只停留在單一 `Press` 或單一 duty 的描述。
- 同時也辨識出一個新的核心問題：使用者現在提到的是對應 `{entity}_seen` 留下記錄，但既有 EventRuleEngine 文件基線仍是 `member_seen` generic truth；這代表 seen 資料模型本身已成為本 spec 的正式 open question，而不只是 implementation detail。
- 除了 owner 與 transaction 邊界之外，本輪也新增了 task 可見性、重複觸發去重、跨內容中間狀態與獎勵冪等這幾個必須先回答的設計問題。
- 下一步：先做 owner / data-model review，回答 seen truth 應維持 generic `member_seen` 還是改為 `{entity}_seen`，再繼續判斷 reusable public surface。

## Round 3
- 使用者已直接回答核心 open questions，第一版收斂如下：seen truth 採 `{entity}_seen`；reusable completion contract 的 owner 是 `Duty`；不建立通用 target resolver；task 只顯示目前可達成且未過期項目；同一 task 完成後重複 seen API 不再進入 truth / reward path。
- transaction 邊界也被重新定義：若 use case 只涉及單一 module feed，仍由 feed 持有 transaction；若同一 completion flow 涉及多個 module feed，則 transaction coordination 在 kit 運作，再分派各 feed 執行 owner write。
- 第二個 `Post` 任務的中間狀態也已收斂：不是先做 generic member_seen 再推導，而是由各篇 `post_seen` 通過後逐步累積，全部條件成立後才把 task 設為完成。
- 這代表本 spec 已從單純列 open questions 的 `idea/(discuss)`，前進到 owner、data model 與 transaction 原則都已收斂的可規劃狀態。
- 下一步：把 Round 3 的結論拆成 plan，特別是 `{entity}_seen` schema / preload contract、Duty kit surface、跨 feed transaction 協調方式，以及 completed-task short-circuit。

## Round 4
- 使用者補充了一個更精確的 target existence 邊界：不做通用 target resolver 仍成立，但 task 可以建立一個 target existence 掛載點。
- 這代表 `Duty` 可以在 evaluate 前依 target type 做 dispatch，但它只負責叫用對應 owner method，不內建跨 entity 的 availability 規則。
- 這個掛載點模式可參考既有 `isAvailable($row)` 範例：task 端只決定 hook 入口與 dispatch 方式，真正的 entity-specific 規則，例如 status、時間窗、inventory 與額外 module 條件，仍留在 `kPost`、`kPress` 或其他內容 module owner。
- 因此本 spec 的 target 驗證結論被細化為：「沒有通用 resolver，但允許 task contract 提供 hook point。」
- 下一步：在 plan 中把 target existence hook 的介面、dispatch 規則與 fallback 行為寫清楚。

## Round 5
- 本輪依 `plan.md` Stage 1 完成第一版 contract 定稿，沒有擴張到 implementation。
- 已明確寫定 seen-target completion 的最小輸入：`member_id`、`target_type`、`target_row_id`、`source`、`insert_user`、`seen_truth`。
- 已明確寫定第一版輸出 shape：`seen`、`completed_tasks`、`short_circuited_tasks`、`skipped_tasks`，讓後續 `Duty` kit 不只回傳 done task，也能表達已完成 short-circuit 與 skipped reason。
- 已明確寫定 `{entity}_seen` truth reference envelope，規定 `Duty` 與 EventRuleEngine context preload 應依 envelope 取用資料，而不是再退回 generic `member_seen`。
- 已明確寫定 target existence hook 的 dispatch 與 fallback：第一版預設 dispatch 到 `\F3CMS\k{TargetType}::isAvailable($row)`，並採 fail-closed；row 不存在、class 不存在、method 不存在、method 回傳 false 都視為 `target_unavailable`。
- 這代表 Stage 1 的文件任務已完成，下一步可進入 Stage 2，繼續釐清 kit / feed / content-module 的實作邊界與 transaction routing。

## Round 6
- 本輪依 `plan.md` Stage 2 完成 completion flow 邊界定稿，仍未擴張到 implementation。
- 已正式定義 content module helper 的責任：target resolve、owner-side availability、`{entity}_seen` truth write，之後才把標準化輸入交給 `Duty`。
- 已正式定義 `Duty` kit 的責任：pending task iterate、context preload、factor evaluate、completed-task short-circuit、cross-feed transaction coordination，以及 normalized result 組裝。
- 已正式定義 feed 的責任：owner table write 與 rollback 參與；kit 不取代 feed 成為 persistence owner。
- 已正式定義 transaction routing：單一 feed path 由 feed 持有 transaction；跨內容 truth、task、reward 的多 owner flow 由 kit 協調 transaction，但各 owner write 仍留在各 feed。
- 已正式定義 hook 呼叫時機：content module 先做 owner-side availability；`Duty` 在 evaluate 某 task 前可依 task contract 再確認 hook，失敗時進 `skipped_tasks.target_unavailable`，不進入 done / reward。
- 這代表 Stage 2 的文件任務已完成，下一步可進入 Stage 3，釐清 achievable / expired task query contract 與 `Post` 多篇累積的 evaluate 順序。

## Round 7
- 本輪依 `plan.md` Stage 3 完成 task 可見性與可達成條件定稿，仍未擴張到 implementation。
- 已正式定義 pending task 不只是 status pending，而是必須同時滿足未完成、未過期、prerequisite 仍可成立、target hook 仍可達成。
- 已正式定義 evaluate 順序：先 short-circuit completed，再排除 expired，再做 achievable / hook 檢查，最後才進 EventRuleEngine factor evaluate 與 done / reward。
- 已正式定義 expired task 的第一版規則：保留既有 truth 與歷史資料，但不再出現在前台可達成任務清單，也不再參與 pending evaluate。
- 已正式定義 `unreachable` 與 `target_unavailable` 的第一版處理：不出現在前台可達成任務清單，不進 done / reward，僅在 `skipped_tasks` 留 reason。
- 已正式定義第二個 `Post` 任務採 truth 累積 + 每次重算模式，不額外新增 `task_progress_count` 類型中介欄位；每次 `post_seen` 成功後，都以完整 truth 集合重新 evaluate 多 leaf `AND`。
- 已正式定義前台 task list 與 completion path 必須共用同一套 achievable / expired 判準；若暫時無法完全共用，第一版以 completion path 判準為準，前台 query 必須向它收斂。
- 這代表 Stage 3 的文件任務已完成，下一步可進入 Stage 4，補齊 Docker 優先的驗證入口與待驗收情境對照。

## Round 8
- 本輪依 `plan.md` Stage 4 完成驗證與落地前檢查定稿，仍未擴張到 implementation。
- 已正式定義 Docker 為唯一正式驗證 baseline，`php-fpm` 為正式 PHP 驗證 service，canonical smoke command shape 為 `docker compose exec -T php-fpm php /var/www/tests/smoke/<path>`。
- 已正式辨識 implementation 前的 baseline smoke：`event_rule_engine/press_seen_reaction_task_done_reward.php` 與 `event_rule_engine/member_seen_task_done_reward.php`。這兩條 smoke 可作為現況對照，但不能直接取代本 spec implementation 後的正式驗收。
- 已正式列出 implementation 後應建立的 canonical smoke suites：`seen_target_task_completion/press_seen_reward.php`、`post_seen_accumulate_reward.php`、`target_hook_unavailable.php`、`repeat_seen_short_circuit.php`、`cross_feed_transaction_rollback.php`。
- 已正式定義驗證結果判讀規則：host lint `exit 134` 只記錄為環境噪音；只有 Docker smoke 與 canonical suite 可作為 runtime 驗收基準。
- 這代表 Stage 4 的文件任務已完成；Stage 1 到 Stage 4 的規劃文件已齊備，下一步可正式進入 `(done)`，開始 implementation 與 smoke suite 落地。

## Round 9
- 本輪正式進入 `(done)`，但只做最小 implementation slice，不重開 schema 或 owner 邊界。
- 目前先沿既有 legacy `member_seen` path 落地第一步：`kDuty::completeTasksForSeenTarget(...)` 已顯式回傳 `short_circuited_tasks` 與 `skipped_tasks`，讓第二次 seen 呼叫不再只有空 `completed_tasks`，而能回報 `already_completed` short-circuit。
- 為了支撐這個 result shape，`fTask` 新增了較窄的 `byMemberId(...)` 讀取面，讓 `Duty` 能同時看 pending 與 done task，而不必立刻重開更大的 query abstraction。
- 這個切片仍明確屬於 legacy baseline：truth 仍是 `member_seen`，transaction 仍在 kit，還沒有 `{entity}_seen`、target hook dispatch、achievable / expired filtering。
- 已完成兩條 Docker baseline smoke 驗證：`event_rule_engine/member_seen_task_done_reward.php` 與 `event_rule_engine/press_seen_reaction_task_done_reward.php` 都通過，且第二次 seen 現在會顯式回傳一筆 `already_completed` short-circuit，同時維持 seen/task/account log 的單筆冪等。
- 下一步：繼續沿最小 implementation slice 前進，優先在 target hook dispatch、achievable filtering、或 canonical smoke suite 之間選一個局部切入點，不要一次重開全部 `{entity}_seen` schema。

## Round 10
- 本輪延續 `(done)` 階段，但仍維持最小切片：沒有重開 schema，也沒有擴大到 target hook 或 achievable filtering。
- 本輪把第一條 canonical smoke suite 從 baseline `event_rule_engine/*` domain 正式搬進 `www/tests/smoke/seen_target_task_completion/press_seen_reward.php`，讓 SeenTargetTaskCompletion 首次擁有自己的正式 smoke 入口。
- 新 canonical suite 目前仍驗證 legacy `member_seen` 路徑，內容對齊既有 `rPress` seen completion 與 repeat short-circuit 行為，因此它是「canonical path 已建立，但 runtime contract 仍是 legacy baseline」的過渡狀態。
- 已用 Docker 完成新 suite 驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/press_seen_reward.php` 通過，且輸出仍符合第一個 implementation slice 的 short-circuit contract。
- 這代表本 spec 的驗證入口已不再完全依賴 `event_rule_engine/*` baseline；不過 canonical suite 目前只有一條，尚未覆蓋 `Post` 累積、target hook unavailable、cross-feed rollback 等情境。
- 下一步：優先再補一條 canonical suite 或落一個 target hook / achievable filtering 切片，持續把 `seen_target_task_completion/` domain 從 smoke 入口一路推進到 runtime contract。

## Round 11
- 本輪延續 `(done)` 階段，仍維持「先補 canonical smoke，再決定下一個 runtime slice」的策略，沒有重開 schema 或 owner 邊界。
- 本輪新增第二條 canonical smoke suite：`www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php`，把 repeat seen 的 `already_completed` short-circuit 從 `press_seen_reward.php` 的附帶斷言，提升成獨立契約。
- 新 suite 仍驗證 legacy `member_seen` baseline，但它明確收斂了「第二次 seen 不應新增 completed_tasks、只應回傳一筆 `already_completed` short-circuit，且 seen/task/account log 維持單筆冪等」這條 runtime 行為。
- 已用 Docker 完成驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php` 通過。
- 到目前為止，`seen_target_task_completion/` canonical domain 已至少有兩條可執行 suite：`press_seen_reward.php` 與 `repeat_seen_short_circuit.php`；但 `Post` 累積、target hook unavailable、cross-feed rollback 仍未落地。
- 下一步：優先在 `Post` 累積 canonical suite 與 target hook runtime slice 之間選一個局部切入點，繼續把 legacy baseline 驗證往 spec 正式契約推進。

## Round 12
- 本輪延續 `(done)` 階段，但沒有直接打開 `Post` runtime surface；先選了更小的 target hook slice，因為目前 `Post` 模組尚無 seen completion 入口，直接補 `post_seen_accumulate_reward.php` 會把 scope 擴大成新 public surface + smoke。
- 本輪在 `kDuty::completeTasksForSeenTarget(...)` 補上最小 fail-closed target hook dispatch：若 `\F3CMS\k{Target}::isAvailable(...)` 不存在或回傳 false，會在 truth write 前直接回傳 `skipped_tasks.target_unavailable`，避免寫入 seen truth 與 reward path。
- 本輪也在 `kPress` 補上第一個 owner-side hook：`kPress::isAvailable(...)` 以 `fPress::onePublished(...)` 作為 published availability 判準。
- 本輪新增第三條 canonical smoke suite：`www/tests/smoke/seen_target_task_completion/target_hook_unavailable.php`，驗證 target 先被建立 task、後續變成 unavailable 時，completion path 只回 skipped，不寫 seen、不改 task 狀態、不發 reward。
- 已用 Docker 完成驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/target_hook_unavailable.php` 通過。
- 到目前為止，`seen_target_task_completion/` canonical domain 已有三條可執行 suite：`press_seen_reward.php`、`repeat_seen_short_circuit.php`、`target_hook_unavailable.php`；但 `Post` 累積與 cross-feed rollback 仍未落地。
- 下一步：優先補 `post_seen_accumulate_reward.php`，或把 target hook coverage 從 `Press` 擴到其他 target owner。

## Round 13
- 本輪延續 `(done)` 階段，仍維持最小 canonical-suite 優先策略；沒有新增 `rPost` public API，而是先驗證 `Post` 累積契約能否直接掛在現有 legacy `kDuty` path。
- 本輪在 `kPost` 補上第一個 owner-side availability hook：`kPost::isAvailable(...)` 以 `fPost::one(..., ['status' => fPost::ST_ON])` 作為 enabled 判準，讓 `Post` target 也能走既有 fail-closed target hook dispatch。
- 本輪新增第四條 canonical smoke suite：`www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php`，用三個 `Post` target 驗證「前兩次 seen 只累積 truth 並回 `factor_not_matched`，第三次 seen 才完成 task 並發 100 點 reward」這條契約。
- 新 suite 仍刻意落在 legacy `member_seen` baseline 與 `kDuty::completeTasksForSeenTarget(...)` path，而不是先引入新的 `Post` seen reaction surface；這讓 canonical 驗證可以先前進，而不需要在同一輪重開新 public API。
- 已用 Docker 完成驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php` 通過。
- 到目前為止，`seen_target_task_completion/` canonical domain 已有四條可執行 suite：`press_seen_reward.php`、`repeat_seen_short_circuit.php`、`target_hook_unavailable.php`、`post_seen_accumulate_reward.php`；尚未落地的主要情境只剩 cross-feed rollback 與更完整的 `{entity}_seen` runtime resync。
- 下一步：優先補 `cross_feed_transaction_rollback.php`，或開始把 canonical suite 已覆蓋的 legacy `member_seen` path 逐步推向 `{entity}_seen`。

## Round 14
- 本輪延續 `(done)` 階段，仍維持 canonical smoke 先行；沒有先重開 `{entity}_seen`，而是先補齊 Stage 4 已列出的最後一條 major rollback suite。
- 本輪新增第五條 canonical smoke suite：`www/tests/smoke/seen_target_task_completion/cross_feed_transaction_rollback.php`，利用 strict mode session 下超長 `reward.action_code` 觸發 `tbl_task_log.action_code` 寫入失敗，驗證 `kDuty::completeTasksForSeenTarget(...)` 的 transaction 會把 seen truth、task 狀態與 account side effect 一併回滾。
- 本輪中途做過一次局部修正：最初 failure 打在 `member_seen.source` 長度限制，而不是預期的 log write；其後已把 smoke 的 `source` 縮短，並加入 failure-point 斷言，確保 suite 真正驗證的是 `action_code` log write rollback。
- 已用 Docker 完成驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/cross_feed_transaction_rollback.php` 通過，且 caught error 已明確為 `Data too long for column 'action_code'`。
- 到目前為止，`seen_target_task_completion/` canonical domain 已有五條可執行 suite：`press_seen_reward.php`、`repeat_seen_short_circuit.php`、`target_hook_unavailable.php`、`post_seen_accumulate_reward.php`、`cross_feed_transaction_rollback.php`。
- 下一步：優先把 canonical suite 已覆蓋的 legacy `member_seen` path 逐步推向 `{entity}_seen`，或開始縮小 `{entity}_seen` runtime resync 的第一個 implementation slice。

## Round 15
- 本輪延續 `(done)` 階段，正式開始 `{entity}_seen` runtime resync 的第一個 implementation slice，但不重開 live schema。
- 本輪把 `fMember` 的 seen storage 抽成 target-aware abstraction：`oneSeenTarget(...)`、`createSeenTarget(...)` 與 `seenTargetMapByMemberId(...)` 現在都會優先查找 `tbl_{target}_seen`，若 target-specific table 不存在，則自動回退到既有 `tbl_member_seen`。
- 這個 slice 的目的不是立刻改變 live runtime 的資料落點，而是先把讀寫路徑從「硬編碼 member_seen」改成「entity table 優先、缺表回退」，讓後續 schema 落地時不必再重開整段 orchestration contract。
- 本輪也讓 `seenTargetMapByMemberId(...)` 可彙整 generic 與 entity seen tables；即使目前 live DB 仍只有 `tbl_member_seen`，這個 preload contract 已先對齊 future `{entity}_seen` 方向。
- 已確認目前 live DB 仍只有 `tbl_member_seen`，沒有 `tbl_press_seen` / `tbl_post_seen`；因此本輪驗證重點是「abstraction 落地後不破壞現有 canonical behavior」，而不是立即切換 live truth table。
- 已用 Docker 完成最小驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php` 與 `docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php` 都通過。
- 下一步：若要繼續 `{entity}_seen` resync，應優先決定第一個 live table 落地 target，例如 `tbl_press_seen`，再把 canonical suite 從「fallback abstraction」推進到「真實 entity table write」。

## Round 16
- 本輪延續 `(done)` 階段，將第一個 live entity table target 收斂到 `Press`，但仍避免直接重建 shared Docker DB。
- 本輪新增 `Press` 專用 smoke helper，讓 `Press` canonical suites 在執行時暫建 `tbl_press_seen`，因此可以在不污染當前 shared DB 的前提下，直接驗證 runtime abstraction 會優先寫入 entity table，而不是回退 `tbl_member_seen`。
- 本輪最初曾把 `tbl_press_seen` 定義放進 active Docker schema 檔，但這與目前 SQL artifact 規則不符；其後已修正為改放 `document/sql/260425.sql`，由文件 SQL artifact 承接這個 schema 變更。
- 已用 Docker 完成四條 `Press` canonical suite 重驗：`press_seen_reward.php`、`repeat_seen_short_circuit.php`、`target_hook_unavailable.php`、`cross_feed_transaction_rollback.php` 都通過，且在 table 存在時已明確驗證 `tbl_press_seen` 才是 truth owner，`tbl_member_seen` 保持 0 筆。
- 這代表 `Press` 已成為第一個可驗證的 live entity table target；不過目前 shared Docker DB 仍未持久重建，因此 `tbl_press_seen` 仍是由 smoke 在 runtime 暫建，而非環境常駐 table。
- 下一步：若要把這個 slice 從 smoke-level live table 推進到環境常駐 table，應重建 Docker DB 或提供 migration path，之後再把 `Press` baseline / canonical 驗證全面切到常駐 `tbl_press_seen`。

## Round 17
- 本輪沒有變更 runtime 行為，只修正 SQL artifact 放置位置的 drift。
- `tbl_press_seen` 不再放在 `conf/mysql/docker-entrypoint-initdb.d/target_db.sql`，而是改放到 `document/sql/260425.sql`，以符合目前「新增 SQL 放 document/sql/{YYMMDD}.sql」的交付規則，也避免直接耦合到 docker-entrypoint baseline，降低 rollback 成本。
- 這代表 `Press` live entity table slice 的 schema 依據，現在以 `document/sql/260425.sql` 為準；smoke 仍維持 runtime 暫建 `tbl_press_seen` 的做法，因此不需要重跑 runtime 驗證。
- 下一步：若要真正讓環境常駐 `tbl_press_seen`，應由 DBA / deployment flow 依 `document/sql/260425.sql` 執行，而不是再把 schema 直接塞回 docker-entrypoint init 檔。

## Round 18
- 本輪延續 `(done)` 階段，沿用與 `Press` 相同的最小策略，把 `Post` 推進成第二個 live entity table target，但仍不要求 shared Docker DB 先持久重建。
- 本輪在 `document/sql/260425.sql` 補上 `tbl_post_seen` schema artifact，並新增 `Post` 專用 smoke helper，讓 `post_seen_accumulate_reward.php` 在執行時暫建 `tbl_post_seen`，直接驗證 runtime abstraction 會優先寫入 entity table，而不是回退 `tbl_member_seen`。
- 已用 Docker 完成驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php` 通過，且輸出已明確驗證 `post_seen_count = 3`、`member_seen_count = 0`。
- 這代表 `Post` 已成為第二個可驗證的 live entity table target；目前 `Press` 與 `Post` 都能在 table 存在時走 entity truth path，但 shared Docker DB 仍未持久重建，所以兩者目前仍由 smoke 在 runtime 暫建 seen tables。
- 下一步：若要把這兩個 target 從 smoke-level live table 推進到環境常駐 table，應由 DBA / deployment flow 依 `document/sql/260425.sql` 執行，再把 canonical 驗證全面收斂到常駐 entity tables。

## Round 19
- 本輪延續 `(done)` 階段，但沒有再擴張 runtime contract；只修正 smoke schema source-of-truth 的 drift，讓測試 helper 與正式 SQL artifact 共用同一份 table 定義。
- 本輪新增共用 helper，讓 `Press` / `Post` 的 smoke 建表邏輯直接從 `document/sql/260425.sql` 擷取對應的 `CREATE TABLE` 語句，不再各自持有第二份 schema 字串。
- 為了讓 Docker smoke 在 `php-fpm` 容器內也能讀到這份 artifact，本輪同步把 `${DOCU_PATH}` 掛到 `php-fpm:/var/www/document`；這讓 `document/sql` 不再只對 web server 可見，而是成為 smoke runtime 也可直接讀取的 source of truth。
- 已用 Docker 完成最小重驗：先確認 `php-fpm` 內可讀到 `/var/www/document/sql/260425.sql`，再重跑 `press_seen_reward.php` 與 `post_seen_accumulate_reward.php`；兩者都通過，且仍維持 `press_seen_count = 1 / member_seen_count = 0` 與 `post_seen_count = 3 / member_seen_count = 0`。
- 這代表目前 `Press` / `Post` 的 smoke-level live table，不只在行為上對齊 entity truth path，也已在 schema 來源上與 `document/sql/260425.sql` 收斂為單一 source of truth。
- 下一步：若要繼續往前，應回到下一個 runtime slice，而不是再擴大 smoke infra；最自然的方向仍是常駐 entity table rollout，或 achievable / expired filtering 的第一個最小切片。

## Round 20
- 本輪延續 `(done)` 階段，正式把下一個 runtime drift 收斂到 expired task filtering，但仍只做 completion path 的最小切片，不重開前台 query、schema 或更多 target owner。
- 本輪在 `kDuty::completeTasksForSeenTarget(...)` 補上第一版 expired gate：當 `claim.task_template.expire_at` 存在且已過期時，未完成 task 會在 factor evaluate 前直接進 `skipped_tasks.reason = task_expired`，不進 done / reward path。
- 本輪刻意維持既有 seen flow 不變：內容 module 仍先建立或載入本次 seen truth，之後 `Duty` 才依 expired gate 決定 task 是否可繼續 evaluate；因此 expired task 不會回滾本次合法 seen truth，但會保留 `task` 為 `New` 並避免 reward side effect。
- 本輪新增 canonical smoke suite `www/tests/smoke/seen_target_task_completion/task_expired_skip.php`，驗證過期 task 會回傳單筆 `task_expired` skip、維持 `task.status = New`、`account.balance = 0`，同時 `tbl_press_seen = 1`、`tbl_member_seen = 0`。
- 已用 Docker 完成驗證：`docker compose exec -T php-fpm php /var/www/tests/smoke/seen_target_task_completion/task_expired_skip.php` 通過；並重跑 `press_seen_reward.php` 確認新增 gate 不影響既有完成與 reward path。
- 這代表 Stage 3 的第一個 runtime slice 已開始落地，但目前仍只有 completion path 承接 expired contract；前台 task list 與 `pending query` 尚未共用同一套 expired / achievable 判準。
- 下一步：優先把前台 task list / pending query 向這個 completion-path expired contract 收斂，再決定是否同輪一併承接 achievable filtering。

## Round 21
- 本輪延續 `(done)` 階段，直接承接 Round 20 的下一步，把 query-side contract 補到與 completion-path expired gate 對齊，但仍不重開 achievable filtering 或新增前台 controller surface。
- 本輪把 `expire_at` 判準收斂回 `Duty` owner：`fDuty` 現在提供共用的 `loadTaskTemplate(...)` 與 `isTaskTemplateExpired(...)`，讓 claim 解析與過期判斷不再散落在 `Duty` kit 與 `Task` feed 各自維護。
- 本輪讓 `fTask::pendingByMemberId(...)` 排除已過期 task，但保留 `fTask::byMemberId(...)` 的 raw query 行為不變；這樣 pending query 會與 completion path 使用同一套 expired 判準，同時不影響 `Duty` 目前用來遍歷 `New/Claimed/Done` task 的較寬查詢面。
- 本輪新增 smoke `www/tests/smoke/seen_target_task_completion/pending_tasks_exclude_expired.php`，驗證 raw pending query 仍可看到 active + expired 兩筆 task，但可見 pending query 只留下 active task。
- 已用 Docker 完成驗證：`pending_tasks_exclude_expired.php` 通過；並重跑 `task_expired_skip.php` 與 `press_seen_reward.php`，確認 query-side 對齊沒有破壞既有 expired completion path 與正常 reward path。
- 這代表 Stage 3 的 expired contract 已從 completion path 擴到 pending query；目前剩下的主要 query/runtime drift 已收斂到 achievable filtering，而不是 expired filtering。
- 下一步：若要繼續最小前進，應把 pending query 與 completion path 再向 achievable / target_unavailable / task_unreachable 的共用判準收斂。

## Round 22
- 本輪延續 `(done)` 階段，承接 Round 21 的 achievable filtering 方向，但仍只做最小 query/runtime 對齊，不重開更大的 prerequisite 或 unreachable 設計。
- 本輪把 seen-target availability hook 收斂回 `Duty` owner：`fDuty` 現在提供 `isSeenTargetAvailable(...)`、`listSeenTargets(...)` 與 `hasUnavailableSeenTarget(...)`，讓 query-side 與 completion path 共用同一套 `target_unavailable` fail-closed 判準。
- 本輪讓 `fTask::pendingByMemberId(...)` 在既有 expired filter 之外，再排除 `task_template.factor` 內已經 `target_unavailable` 的 seen-target task；同時保留 `fTask::byMemberId(...)` 的 raw query 行為不變。
- 本輪新增 smoke `www/tests/smoke/seen_target_task_completion/pending_tasks_exclude_unavailable.php`，驗證 raw pending query 仍保留 unavailable task row，但可見 pending query 會把它排除。
- 本輪第一次驗證時，helper 沒有遞迴進入 `task_template.factor`，導致 smoke 失敗；其後已以最小修正補上 factor 遞迴入口，重跑同一條 smoke 後通過。
- 已用 Docker 完成驗證：`pending_tasks_exclude_unavailable.php` 通過；並重跑 `target_hook_unavailable.php` 與 `press_seen_reward.php`，確認共用 helper 不影響既有 fail-closed completion path 與正常 reward path。
- 這代表 pending query 現在已與 completion path 對齊兩個最小 contract：`task_expired` 與 `target_unavailable`；目前剩下的 query/runtime drift 更聚焦在 `task_unreachable` 或更完整 achievable 規則。
- 下一步：若要繼續最小前進，應把 pending query 與 completion path 再向 `task_unreachable` 或 prerequisite-based achievable contract 收斂。
