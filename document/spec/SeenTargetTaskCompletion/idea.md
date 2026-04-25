# SeenTargetTaskCompletion - idea.md

## 1. 背景與問題定義 (Problem Statement)
目前真正需要抽象化的，不是 Press publish / offline 這條 workflow path，而是會員在看完特定內容後，如何將「seen truth 建立 + EventRuleEngine task 判斷 + task done / reward writeback」這條路徑做成可被不同任務重用的函式。

目前第一條 concrete path 已存在於：
- `rPress::completeSeenForMember(...)`
- `kDuty::completeTasksForSeenTarget(...)`

這條路徑目前可用，但已有幾個問題：
- seen target 的 entry point 目前被綁在 `Press` module，若之後 `Post`、`Book` 或其他內容模組也有同類任務，容易複製同一套 target 解析與 completion 協調
- `kDuty::completeTasksForSeenTarget(...)` 目前把 `{entity}_seen` truth 建立、pending task 迭代、EventRuleEngine evaluate、task done、reward writeback 與 transaction 都包在一起，語意雖集中，但 reusable 邊界仍未明確
- 第一個會員任務與第二個會員任務都證明了這條 path 的可重用性，但目前 reusable contract 仍停留在 `Press` 命名與單一路徑上
- 若不先把「seen target completion」收斂成穩定抽象，未來每增加一個內容實體或 seen-driven 任務，就可能重複實作 reaction entry、truth write 與 task completion coordination

本 spec 的核心問題因此是：如何把 seen-target completion 這條 EventRuleEngine 判斷與任務完成流程抽成可重用函式，同時仍維持 F3CMS 的 entity ownership 與 FORK 分工。

## 2. 目標結果 (Target Outcome)
建立一個可被不同 seen-driven 任務重用的 completion contract，讓系統不必為每個內容模組重寫：
- target 解析
- `{entity}_seen` truth 建立
- EventRuleEngine context preload
- pending task 評估
- task done / reward writeback 協調

第一版目標：
- 明確定義 seen-target completion 的穩定抽象邊界
- 支援第一個會員任務與第二個會員任務這兩個 concrete scenario
- 讓 `Press` 不再是這條 path 唯一的 entry surface 命名來源
- 保持 EventRuleEngine 為 shared pure engine，owner module 繼續持有 payload source、truth write 與 writeback

## 3. 範圍 (Scope)
- 盤點 `rPress::completeSeenForMember(...)` 中哪些責任應抽成 reusable entry contract
- 盤點 `kDuty::completeTasksForSeenTarget(...)` 中哪些責任屬於可重用的 seen-target completion flow
- 定義 seen target completion 所需的最小輸入 contract，例如 `member_id`、`target`、`row_id`、`source`
- 以兩個會員任務範例校準 reusable contract 是否足夠
- 重新評估 owner 應落在 `Member`、`Duty`、對應內容 module helper，或其他既有 module-owned surface

## 4. 非範圍 (Non-Scope)
- 不處理 Press publish / offline workflow coordination
- 不重寫 `EventRuleEngine` 核心算法、parser、validator、registry 或 evaluator contract
- 不把 table-backed truth write、task state write、reward writeback 搬進 `libs`
- 不在本 spec 第一版同時處理所有內容模組的 entry path 改造
- 不在本 spec 第一版重開 `WorkflowEngine` / `EventRuleEngine` 的 engine-family 統一問題

## 5. 核心物件與流程 (Core Objects or Processes)
- **Seen Target Entry**：由內容模組或 member-facing surface 接收「會員完成閱讀 / 觀看」事件，解析 target 與 row_id
- **Seen Truth Write**：本 spec 第一版採 `{entity}_seen` 作為內容實體的 truth 基線，例如 `press_seen`、`post_seen`。truth write 仍應由對應 entity owner 建立，不應被 shared engine 直接持有
- **Duty-owned Task Completion Coordination**：`Duty` 是這條 reusable completion contract 的 owner，依 duty payload 與 pending task 狀態，判斷哪些 task 應標記完成並觸發 reward writeback
- **EventRuleEngine**：只負責 task factor 的 payload 驗證與 evaluate
- **Kit-level Cross-feed Transaction Coordination**：若 transaction 只涉及單一 module feed，仍由該 feed 持有；若同一 completion flow 涉及多個 module feed，則 transaction coordination 在 kit 運作，再分派各 feed 執行 owner write

主要流程：
1. entry surface 接收 member seen 事件
2. 解析 target / row_id / source，並確認 target row 合法存在
3. 由對應內容 owner 建立或載入 `{entity}_seen` truth
4. preload EventRuleEngine 所需 context
5. 只載入目前可達成且未過期的 pending task，依 duty payload evaluate
6. 對 matched task 做 done / reward writeback；若 task 已完成，則重複 seen API 不再進入 truth / reward path
7. 回傳 normalized completion result

### 優化後的端到端流程草案

1. 後台管理者建立兩個 `duty`，兩者的 `trigger` 都是 `Member::Register`。
2. 會員完成註冊後，系統依 trigger 掃描對應 `duty`，建立兩個 `task`。
3. 會員在前台看到自己的兩個任務後，分別前往指定 `Press` 文章與指定 `Post` 單頁文章。
4. 當會員完成閱讀條件時，前台呼叫對應內容的 seen API。
5. seen entry surface 先驗證 `member_id`、target、row_id 與 target row 的合法性，再建立或載入對應 `{entity}_seen` truth。
6. 系統以 `{entity}_seen` truth 為基礎 preload EventRuleEngine context，只重新評估該會員目前可達成且未過期的 pending task。
7. 若某個 duty factor 因本次 seen truth 而成立，系統標記對應 task 為 `Done`，並在同一條 completion flow 內完成點數 writeback；若 task 先前已完成，則重複 seen API 不再重走 truth / reward。
8. API 回傳 normalized result，至少能讓前端知道：truth 是否建立成功、哪些 task 完成、是否獲得點數。

這條優化後流程的重點不是新增新 engine，而是把「內容 seen 事件 -> truth 建立 -> task completion evaluate -> reward writeback」收斂成穩定 contract，讓 `Press` 與 `Post` 都走同一套 completion 主線。

### 第一版 Contract 定稿

#### 1. Seen-target completion 最小輸入

第一版 completion contract 以 `Duty` 為 owner，最小輸入欄位如下：

- `member_id`：會員 id，必填，且必須對應 enabled member
- `target_type`：內容實體名稱，第一版至少支援 `Press`、`Post`
- `target_row_id`：內容 row id，必填
- `source`：呼叫來源，例如 `rPress`、`rPost`
- `insert_user`：寫入者 id；member-facing seen flow 允許等於 `member_id`
- `seen_truth`：由內容 module owner 建立或載入後回傳的 `{entity}_seen` 結果，作為後續 evaluate 的 truth 入口

補充：
- 第一版不要求 `Duty` 自己建立 `{entity}_seen`；`seen_truth` 必須先由內容 module owner 建立或載入後，再交給 `Duty` 繼續 completion。
- 第一版不要求統一所有 `{entity}_seen` table schema，但要求能提供一致的 truth reference，至少可辨識 target type、target row、member 與 truth row id。

#### 2. 第一版輸出 shape

第一版 completion result 至少包含：

- `seen`：本次使用的 `{entity}_seen` truth reference
- `completed_tasks`：本次因 evaluate 而完成的 task 清單
- `short_circuited_tasks`：因 task 已完成而被略過的 task 清單
- `skipped_tasks`：因 target unavailable、task expired、task unreachable 或 factor not matched 而未完成的 task 清單

其中：
- `completed_tasks` 至少包含 `task_id`、`duty_id`、`status`、`reward_action_code`、`reward_amount`
- `short_circuited_tasks` 至少包含 `task_id` 與 `reason = already_completed`
- `skipped_tasks` 至少包含 `task_id`、`reason`；第一版 reason 可接受 `target_unavailable`、`task_expired`、`task_unreachable`、`factor_not_matched`

#### 3. `{entity}_seen` truth reference contract

第一版不強迫 `press_seen` 與 `post_seen` 共用完全相同欄位，但要求對 `Duty` 暴露一致的 reference envelope：

- `entity`：例如 `press_seen`、`post_seen`
- `id`：truth row id
- `member_id`
- `target_type`
- `target_row_id`
- `source`

`Duty` 與 EventRuleEngine context preload 應依這個 envelope 取用資料，而不是再回退到 generic `member_seen` 當 runtime baseline。

#### 4. Target existence hook contract

task contract 允許帶一個 target existence / availability hook，第一版採下列 dispatch 規則：

- `Duty` 只依 `target_type` 組出 owner class 與 method 名稱
- 第一版預設 method 名稱為 `isAvailable`
- 第一版預設 owner class 形狀為 `\F3CMS\k{TargetType}`，例如 `\F3CMS\kPost::isAvailable($row)`
- hook input 的 `$row` 由對應內容 module owner 自行載入並傳入對應 method

換句話說，`Duty` 負責 dispatch contract，不負責替 module 猜 query 或內建 availability 規則。

#### 5. Hook fallback 規則

第一版 fallback 順序如下：

1. 若 target row 不存在，視為 `target_unavailable`
2. 若 owner class 不存在，視為 `target_unavailable`
3. 若 `isAvailable` method 不存在，視為 `target_unavailable`
4. 若 `isAvailable($row)` 回傳 false，視為 `target_unavailable`
5. 只有在 target row 存在且 hook 明確回傳 true 時，才允許繼續 evaluate / truth-completion path

第一版採 fail-closed，而不是在 hook 缺失時自動放行。

### Stage 2 邊界定稿

#### 1. Content module entry / helper 責任

`Press`、`Post` 或其他內容 module entry / helper 第一版保留以下責任：

- 解析 request 內的 target identifier，例如 `id`、`slug`
- 載入 target row
- 呼叫 owner-side availability rule，例如 `kPress::isAvailable($row)`、`kPost::isAvailable($row)`
- 建立或載入對應 `{entity}_seen` truth
- 將 `seen_truth` 與標準化輸入轉交給 `Duty`

內容 module 不應承接：

- pending task 迭代
- duty payload 載入
- EventRuleEngine evaluate
- task done / reward writeback 決策

#### 2. Duty kit 責任

`Duty` kit 第一版保留以下責任：

- 接收已標準化的 seen-target completion 輸入
- 在 evaluate 前依 task contract 需要決定是否呼叫 target existence hook
- 載入目前可達成且未過期的 pending task
- 建立 EventRuleEngine context preload
- 執行 factor evaluate
- 對 matched task 產生 done / reward writeback 決策
- 對 already completed task 產生 short-circuit 結果
- 回傳 normalized completion result

`Duty` kit 不應直接承接：

- 自行 query 猜測 target row
- 自行建立 `{entity}_seen` truth
- 直接持有單一 owner write 的 feed 細節

#### 3. Feed 責任

各 owner feed 第一版保留以下責任：

- 單一 owner table 的 transaction write
- 對應 truth row 的 insert / update
- task row / task log 的 done write
- reward / account log 的 writeback

feed 不應承接：

- 跨多個 module owner 的 orchestration 決策
- duty factor evaluate
- target type dispatch 規則

#### 4. Transaction routing 規則

第一版 transaction routing 固定如下：

1. 若 completion flow 只涉及單一 owner feed，例如只做 `press_seen` truth write，則 transaction 由該 feed 持有。
2. 若 completion flow 同時涉及內容 truth write、task done、reward writeback 等多個 owner feed，則由 `Duty` kit 作為 orchestration surface 啟動跨 feed transaction coordination。
3. kit 協調 transaction 時，只負責流程順序、rollback 邏輯與 owner write dispatch；實際 SQL write 仍由各自 feed 執行。
4. 任一 owner write 失敗時，整條 completion flow rollback，不允許 partial success 留下半套 truth / task / reward 狀態。

這裡的「kit 可協調 transaction」不等於 kit 取代 feed 成為 persistence owner；它只是在跨 feed use case 下持有 orchestration 邊界。

#### 5. Hook 呼叫時機

第一版 hook 呼叫時機固定如下：

1. 內容 module entry 先完成 target row 載入與 owner-side availability 檢查。
2. `{entity}_seen` truth 建立或載入完成後，`Duty` 在 evaluate 某個 task 前，可依 task contract 再次確認該 task 所需的 target hook 是否成立。
3. 若 hook 在 evaluate 前回傳 `target_unavailable`，該 task 進入 `skipped_tasks`，reason 為 `target_unavailable`，不進入 done / reward path。
4. hook 不應晚於 reward writeback 執行；它必須發生在 task 完成決策之前。

#### 6. Stage 2 責任矩陣

- `Reaction`：request / permission / response
- `Content module helper`：target resolve、availability、`{entity}_seen` truth write
- `Duty kit`：completion orchestration、task iterate、evaluate、short-circuit、transaction coordination
- `Feed`：owner write 與 rollback 參與
- `EventRuleEngine`：pure factor evaluate

### Stage 3 規則定稿

#### 1. Task 可見性 query contract

前台 task list 與 Duty evaluate path 第一版共用同一組 task visibility / achievability 語意，至少區分下列狀態：

- `visible_and_achievable`：可顯示、可繼續完成、可參與 evaluate
- `visible_but_blocked`：可顯示，但目前不可達成；第一版不採此模式，避免前台與 completion path 分裂
- `expired`：不可顯示、不可參與 evaluate
- `completed`：可在歷史紀錄顯示，但不再參與 pending evaluate
- `unreachable`：因 target unavailable 或 prerequisite 失效而不可達成；第一版不顯示在前台可達成任務清單

第一版前台只顯示 `visible_and_achievable` 的 task，不額外顯示 blocked / unreachable task。

#### 2. Pending task query 條件

第一版 `pending task` 不是單純的 status pending，而是同時滿足：

- task 尚未完成
- task 尚未過期
- task 所需 prerequisite 仍可成立
- task 對應的 target hook 在當前 evaluate 時點仍可達成

若任一條件不成立，該 task 不進入本輪 pending evaluate 清單。

#### 3. Evaluate 順序

第一版 evaluate 順序固定如下：

1. 先篩掉已完成 task，直接進 `short_circuited_tasks`
2. 再篩掉 expired task，進 `skipped_tasks.reason = task_expired`
3. 再做 target hook / achievable 檢查，不成立則進 `skipped_tasks.reason = target_unavailable` 或 `task_unreachable`
4. 最後才進 EventRuleEngine factor evaluate；未命中則進 `skipped_tasks.reason = factor_not_matched`
5. 只有通過以上所有條件的 task，才可進入 done / reward path

這代表第一版不接受「先 evaluate factor，再回頭判斷 task 是否其實已過期或不可達成」的順序。

#### 4. Expired task 規則

第一版 expired task 規則如下：

- expired task 保留既有 truth 與歷史資料，不主動刪除 `{entity}_seen`
- expired task 不出現在前台可達成任務清單
- expired task 不參與 pending evaluate，也不進 reward path
- 若 task 過期前已完成，仍視為 completed，不回滾既有 reward

#### 5. Unreachable task 規則

第一版 `unreachable` 指的是：

- task 依賴的 target 已不存在或 owner-side availability 失效
- task 依賴的 prerequisite 在當前版本下已不可滿足

第一版處理方式：

- 不出現在前台可達成任務清單
- 不進 done / reward path
- 可進 `skipped_tasks` 留下 `task_unreachable` 或 `target_unavailable` reason
- 是否需要後續額外標記資料表狀態，不在第一版範圍內

#### 6. Post 多篇累積規則

第二個 `Post` 任務的完成條件採「truth 累積 + 每次重算」模式，而不是額外維護一份 task-local counter：

- 每次 `post_seen` 成功，只新增或載入該篇 `Post` 對應的 `{entity}_seen`
- `Duty` 在每次 seen 後重新 preload 該會員目前所有相關 `post_seen` truth
- EventRuleEngine factor 以多個 `MEMBER_SEEN_TARGET` leaf 的 `AND` 群組重新計算
- 只有當三篇指定 `Post` 都已存在有效 `post_seen` truth 時，task 才從 not matched 轉為 matched

第一版不另外引入 `task_progress_count` 或類似中介欄位，避免同時維護第二套 truth。

#### 7. Post 多篇 evaluate 順序

對第二個 `Post` 任務而言，單次 seen 事件的 evaluate 順序如下：

1. `Post` module 驗證本次 target row 可用
2. 建立或載入本次 `post_seen`
3. `Duty` 重新 preload 該會員所有與此 duty 相關的 `post_seen` truth
4. `Duty` 先做 expired / achievable 檢查
5. EventRuleEngine 以完整 truth 集合 evaluate 多 leaf `AND`
6. 若仍缺任一篇 `Post`，本次結果為 `factor_not_matched`
7. 若三篇都成立，才進 task done / reward

#### 8. Stage 3 前台與 completion 一致性原則

第一版要求前台 task list 與 completion evaluate 共用同一套 achievable / expired 判準：

- 前台不可把某 task 顯示為可完成，但 completion path 判斷它其實 expired
- completion path 不可把某 task 視為可完成，但前台完全不顯示它
- 若兩者暫時做不到完全共用 query，第一版以 completion path 的判準為準，前台 query 必須向它收斂，而不是反過來

### Stage 4 驗證定稿

#### 1. 驗證環境基線

第一版 SeenTargetTaskCompletion 的驗證基線固定如下：

- Docker 是唯一正式 baseline
- `php-fpm` 是正式 PHP 驗證 service
- smoke source of truth 必須放在 `www/tests/smoke/<domain>/*.php`
- host PHP lint 或 host-only execution 不得覆蓋 Docker 結果

若需要啟動環境，第一版沿用 repo 既有腳本：

- `./bin/build.sh`
- `./bin/up.sh`

#### 2. Canonical 驗證命令口徑

第一版 canonical smoke command shape 固定如下：

```sh
docker compose exec -T php-fpm php /var/www/tests/smoke/<path>
```

文件、shell wrapper、Lab、CLI 若需要間接觸發 smoke，也應指向這條 canonical path，而不是再建立新的 script source of truth。

#### 3. 現有 baseline smoke

在尚未完成實作重構前，本 spec 第一版的最接近 baseline smoke 為：

- `www/tests/smoke/event_rule_engine/press_seen_reaction_task_done_reward.php`
- `www/tests/smoke/event_rule_engine/member_seen_task_done_reward.php`

它們的用途是：

- 驗證現有 `Press seen -> task done -> reward` 路徑確實存在
- 提供 pre-resync 行為基線，讓後續 implementation 可以對照新舊路徑差異

它們的限制也必須明確記錄：

- 目前仍以 `member_seen` 為真實資料路徑
- 尚未覆蓋 `{entity}_seen` 新 contract
- 尚未覆蓋 target existence hook dispatch
- 尚未覆蓋 achievable / expired query contract

因此它們只能作為 implementation 前的 baseline，不可直接當成本 spec 最終驗收完成的唯一依據。

#### 4. 第一版目標 smoke suite

本 spec 進入 `(done)` 後，應新增或收斂出下列 canonical smoke suites：

- `www/tests/smoke/seen_target_task_completion/press_seen_reward.php`
- `www/tests/smoke/seen_target_task_completion/post_seen_accumulate_reward.php`
- `www/tests/smoke/seen_target_task_completion/target_hook_unavailable.php`
- `www/tests/smoke/seen_target_task_completion/repeat_seen_short_circuit.php`
- `www/tests/smoke/seen_target_task_completion/cross_feed_transaction_rollback.php`

第一版不要求這些檔案在 Stage 4 當下就存在，但要求 Stage 4 明確把它們列為 implementation 後的正式驗收入口。

#### 5. 驗收情境對照

第一版待驗收情境與 smoke suite 對照如下：

- `Press` seen 完成單篇任務：`seen_target_task_completion/press_seen_reward.php`
- `Post` seen 三篇累積後完成任務：`seen_target_task_completion/post_seen_accumulate_reward.php`
- target hook unavailable：`seen_target_task_completion/target_hook_unavailable.php`
- repeat seen short-circuit：`seen_target_task_completion/repeat_seen_short_circuit.php`
- cross-feed rollback：`seen_target_task_completion/cross_feed_transaction_rollback.php`

#### 6. 驗證結果判讀規則

第一版驗證結果判讀固定如下：

- Docker smoke 成功，才可視為 runtime 驗證通過
- host PHP lint `exit 134` 只記錄為 host environment noise，不直接視為 regression
- 若現有 baseline smoke 與新 suite 結果不一致，以新 suite 搭配 Docker 結果作為 SeenTargetTaskCompletion 的 source of truth
- 若尚未建立新 suite，則 feature 仍停留在 `plan` 或 `(done)` 中途，不可提前宣告進入 `check` 完成

## 6. 候選落點 (Placement Options)

### Option A: 放在 kDuty
理由：
- `kDuty` 已持有 duty payload 載入、EventRuleEngine evaluate、task completion 與 reward writeback 語意
- `completeTasksForSeenTarget(...)` 現況本來就在 `kDuty`，說明這裡已是第一個 concrete owner
- 使用者已明確指定 `Duty` 作為這條 reusable completion contract 的 owner

風險：
- current implementation 已混合 transaction 與 truth write，需重新拆成「kit 協調 transaction + feed 執行 owner write」
- 若 target existence 解析其實屬於內容 module，自然 owner 不一定全在 `Duty`

### Option B: 放在 Member module
理由：
- 若 reusable contract 被定義為「member 完成某 target 的 seen 事件後，觸發後續 task 檢查」，`Member` 一度看似可能成為 entry owner

風險：
- 容易把本來屬於 duty/task 的 completion 判斷全部吸進 `Member`
- 與本輪已收斂的 `{entity}_seen` + `Duty` owner 結論不一致

### Option C: 內容 module helper + Duty completion contract
理由：
- 內容 module 保留 target row 解析與 existence 驗證
- `Duty` 保留 task completion evaluate / reward 協調
- 兩者之間只用穩定 contract 連接，例如 task 提供 target existence 掛載點，再由對應 module 回答 availability

風險：
- 若 contract 切得不好，可能只是把既有耦合拆成兩段薄 wrapper
- 需要明確定義 reusable surface 究竟在哪一層才算真的抽象成功

### Option D: 新的 shared helper
理由：
- 若 seen-target completion 被證明不屬於單一 module，理論上可考慮 shared helper

風險：
- 若 helper 內含 truth write、task done、reward writeback 或 target-specific query，就會直接違反 F3CMS owner 規則
- 現階段沒有足夠證據顯示這條 path 已脫離 `Member` / `Duty` / 內容 module 三者的既有 ownership

目前判定：
- `libs` 不是可行 owner，因為這條 path 已明確涉及 truth write、task done、reward writeback 這類特定實體操作
- `Duty` 已收斂為這條 reusable completion contract 的 owner
- 對應內容 module 仍持有 target existence 驗證與 `{entity}_seen` truth write；`Duty` 不直接猜測 target row
- task 可提供 target existence / availability 掛載點，但只作為 dispatch contract；實際規則仍由各內容 module 自己實作，例如 `kPost::isAvailable($row)` 這類 owner method
- transaction 若只涉及單一 module feed，仍由 feed 持有；若涉及多個 module feed，則在 kit 進行協調
- 第一版已可從純 open question 前進到 owner / data-model 已收斂的 idea 狀態，下一步可進 plan

## 7. 資料與狀態影響 (Data and State Implications)
- seen truth 以 `{entity}_seen` 為基線，例如 `tbl_press_seen`、`tbl_post_seen`；context preload 與查詢路徑應以 entity truth 為主，而不是 generic `member_seen`
- `tbl_task`、`tbl_task_log`、`tbl_manaccount`、`tbl_manaccount_log` 的 writeback 邏輯仍需保留既有 owner
- 第二個會員任務顯示，`factor.rules` 應允許多個 `MEMBER_SEEN_TARGET` leaf 以 `AND` 方式組合
- reusable contract 必須能支援不同 target，例如 `Press` 與 `Post`
- `tbl_task` 所屬 contract 可額外保留 target existence / availability 掛載點，供 `Duty` 在 evaluate 前 dispatch 到對應 module owner method
- transaction 若跨多個 module feed，應由 kit 協調；若只在單一 module feed 內完成，仍由該 feed 持有

## 8. 限制與依賴 (Constraints and Dependencies)
- 必須符合 FORK owner 規則：單一 owner write 仍由 feed 執行；但同一 use case 若跨多個 module feed，transaction coordination 可在 kit 運作
- EventRuleEngine 仍維持 shared pure engine，不直接持有 persistence
- target existence 驗證應留在對應 entity owner；允許在 task 建立 target existence 掛載點，但不建立通用 target resolver 或共用 availability 規則
- reusable contract 應以既有兩個會員任務 scenario 能共同承接為最低標準
- 若抽象只剩名稱搬家、沒有形成穩定 reusable boundary，則不算成功

## 9. 風險與未決問題 (Risks and Open Questions)
- **Transaction 拆層風險**：既有 `kDuty::completeTasksForSeenTarget(...)` 已把多種 write 與 transaction 疊在一起，落地時需先拆出 kit 協調與 feed write 邊界
- **資料模型擴張風險**：改用 `{entity}_seen` 後，EventRuleEngine context preload、索引策略與 migration 成本會上升
- **Task 過期過濾風險**：前台只顯示目前可達成且未過期任務，代表任務查詢 contract 需要顯式承接 expire 與 achievable 條件
- **重複觸發短路風險**：規則要求 task 已完成時不再走 truth / reward path，實作上需先有穩定的 completed-task short circuit
- **Post 中間狀態表示風險**：第二個任務的中間狀態由 `post_seen` 通過後逐步累積，必須確認 factor evaluate 與 seen truth 查詢口徑一致

目前收斂結論：
- 這條 spec 真正要處理的是 EventRuleEngine-style seen target completion，而不是 workflow action coordination
- 第一個會員任務與第二個會員任務都支持這條新 spec 的存在，因為兩者都屬於 `{entity}_seen` truth + duty factor + reward writeback 路徑
- `WorkflowActionCoordinator` 已屬誤題；後續 focus 應改到本 spec
- `Duty` 是 reusable completion contract 的 owner，內容 module 保留 target 驗證與 `{entity}_seen` truth write
- 不建立通用 target resolver；但 task 可提供 target existence 掛載點，由 `Press`、`Post` 等內容 module 各自回覆 entity availability
- task 有過期可能，前台只顯示目前可達成且未過期的任務
- 同一 task 已完成後，重複 seen API 不再進入 truth / reward path
- 第二個任務的跨內容中間狀態，由各篇 `post_seen` 通過後逐步累積，全部條件成立時才完成 task

## 10. 早期範例或情境 (Early Examples or Scenarios)

### Mainline Scenario: 第一個會員任務
情境：
- `Member::Register` 後建立 duty-driven task
- 會員之後看完指定 `Press` 內容
- 若符合 `MEMBER_SEEN_TARGET` factor，則 task 可標記 `Done` 並給點

預期：
- entry surface 不需要為每個 seen-driven task 重寫整套 completion coordination
- reusable contract 能承接 `Press` target 的 existence 驗證與 completion flow

### Mainline Scenario: 第二個會員任務
情境：
- 後台管理者新增第二個 `duty`，設定 `trigger = Member::Register`
- 會員註冊後，系統自動建立對應 `task`
- 完成條件為讀完三則 `Post` 單頁文章：服務條款 `id = 4`、隱私條款 `id = 7`、退貨條款 `id = 11`
- 三則都成立後，才給 100 點

預期：
- reusable contract 不應被 `Press` 命名綁死
- EventRuleEngine factor 可用多個 `MEMBER_SEEN_TARGET` leaf + `AND` 組合承接
- target 為 `Post` 時，不需要再複製一份 `completeSeenForMember`-style 函式
- 每次 `post_seen` 通過後，只累積該篇內容的 seen truth；當三篇都成立時才標記 task 完成

### Mainline Scenario: 後台建立兩個 duty，前台完成兩條任務
情境：
- 後台管理者建立兩個 `duty`，兩者都在 `Member::Register` 後建立 `task`
- 第一個任務要求完成指定 `Press` 閱讀
- 第二個任務要求讀完三則指定 `Post`
- 會員註冊後，在前台看到這兩個 task，並分別前往對應內容
- 會員在 `Press` 與 `Post` 頁面觸發 seen API

預期：
- 不同內容模組可共用同一條 seen-target completion 主線，而不是各自重寫 completion 協調
- 對應 target 的 seen truth 會被建立或載入
- 只有目前可達成且未過期的 pending task 會因本次 seen 事件被重新 evaluate
- 成立的 task 會被標記完成並得到點數
- 若 task 尚未滿足全部條件，系統仍保留中間完成狀態所需的 truth，而不提前發獎

### Boundary Scenario: target row 不存在
情境：
- seen event 提供了 target / row_id
- 但對應內容 row 不存在、未上線或不可用

預期：
- reusable contract 應在 truth write 前就拒絕不合法 target
- task 可透過 target existence 掛載點 dispatch 到對應 entity owner method
- target existence 的責任仍由對應 entity owner 承接，而不是由 `Duty` 內建共用規則判斷

### Boundary Scenario: task 透過掛載點檢查 target availability
情境：
- task payload 指向某個 target type，例如 `Post`
- `Duty` 在 evaluate 前需要確認 target row 目前是否仍可達成

預期：
- task contract 可提供像 `target existence hook` 這樣的掛載點
- `Duty` 只負責依 target type dispatch 到對應 module，例如 `\F3CMS\kPost::isAvailable($row)`
- 真正的 availability 規則仍由各內容 module owner 自己維護，例如 status、start/end time、inventory 或其他 entity-specific 條件

### Boundary Scenario: truth 建立後 task writeback 失敗
情境：
- `{entity}_seen` truth 已建立
- 但 task done 或 reward writeback 失敗

預期：
- transaction 邊界必須明確，避免 partial success 破壞資料一致性
- 若這條 completion flow 涉及多個 module feed，transaction coordination 應在 kit 運作

### Boundary Scenario: 重複打 seen API，但 task 已完成
情境：
- 會員已完成對應 task，並已領取 reward
- 前台再次對同一 target 呼叫 seen API

預期：
- 系統先 short-circuit 已完成 task，不再重走 truth / reward path
- 不會重複發點，也不會再做多餘的 completion writeback

## 11. 成功條件 (Success Criteria)
- 可清楚回答 seen-target completion 的 reusable contract 應包含哪些最小輸入與輸出
- 可清楚回答 `Duty`、內容 module helper 與對應 feed / kit 各自的 owner 邊界
- 第一個與第二個會員任務都可用同一組抽象路徑承接，而不再各自複製 completion 主流程
- 新抽象不把 persistence 重新塞進 `libs`，且能清楚區分「kit 協調跨 feed transaction」與「feed 執行 owner write」
- 可清楚回答 target existence hook 的 dispatch 規則、fallback 行為與 fail-closed 原則
- 可清楚回答 Stage 2 的 transaction routing、hook 呼叫時機與責任矩陣
- 可清楚回答 Stage 3 的 achievable / expired query 規則、`Post` 多篇累積 evaluate 順序與前台一致性原則
- 可清楚回答 Stage 4 的 Docker 驗證入口、baseline smoke 與 implementation 後 canonical smoke suite 對照
