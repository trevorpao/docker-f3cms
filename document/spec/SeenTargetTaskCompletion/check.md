# SeenTargetTaskCompletion - check.md

## Current Check Baseline
- 目前尚未進入正式 implementation check。
- 本文件先建立驗收骨架，承接 `plan.md` 的四個 stage，避免 `history.md` 已收斂但 `check.md` 空白的 drift。

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

## 未完成項
- 尚未把 Round 3 / Round 4 的穩定結論回寫成實作導向的 plan stage 以前的狀態已解除，但實作本身尚未開始。
- 尚未定義 `{entity}_seen` schema / preload contract 的實作欄位與查詢細節。
- 尚未真正建立 `www/tests/smoke/seen_target_task_completion/` 下的 canonical smoke suites。
- 尚未取得 Docker runtime 驗證結果。

## 程式對齊狀態
- 目前程式仍屬 pre-resync 狀態，尚未符合本 spec 的第一版結論。
- 已知差異：
	- 目前 `kDuty::completeTasksForSeenTarget(...)` 仍直接建立 `member_seen` 並在 kit 內持有 transaction。
	- 目前尚無 `{entity}_seen` truth path。
	- 目前尚無 target existence hook dispatch。
	- 目前尚無明確的 completed-task short-circuit 與 achievable / expired filtering 證據。

## 驗證狀態
- 尚無可採信的 current-spec runtime 驗證結果，但正式驗證入口已定義。
- 目前只知道 host 端 PHP lint 曾出現 `exit 134`；依 FDD 規則，這不能直接作為 regression 判定。
- implementation 前可參照既有 baseline smoke：
	- `www/tests/smoke/event_rule_engine/press_seen_reaction_task_done_reward.php`
	- `www/tests/smoke/event_rule_engine/member_seen_task_done_reward.php`
- implementation 後正式驗收應優先使用 `www/tests/smoke/seen_target_task_completion/` 下的 canonical suites。

## 待驗收情境
- `Press` seen 成功建立對應 `{entity}_seen`，並完成單篇任務。
- `Post` seen 逐步累積三篇內容，最後完成任務並發放 reward。
- target existence hook 回傳 unavailable 時，拒絕 truth write 或後續 completion。
- 已完成 task 的重複 seen API 會 short-circuit，不重複發點。
- 單一 feed transaction 與跨 feed transaction 在資料一致性上的行為符合 spec。
- expired task 不出現在前台可達成任務清單中。

## 目前判定
- 目前文件規劃階段已完整覆蓋 Stage 1 至 Stage 4，但 feature 仍未進入 `check` 完成或 `(Optimization)`。
- 下一步應進入 `(done)`，開始 implementation 與 canonical smoke suite 建立。
