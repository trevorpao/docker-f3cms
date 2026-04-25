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
