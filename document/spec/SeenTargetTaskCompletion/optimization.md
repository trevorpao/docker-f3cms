# SeenTargetTaskCompletion - optimization.md

## Optimization Entry Check
- 本 spec 已具備進入 `(Optimization)` 的最低條件。
- 理由如下：
	- `Press` 與 `Post` 的 canonical seen-target completion flow 都已有 Docker 驗證
	- target availability、expired task、already completed、factor not matched 與 prerequisite-specific reason 都已有 direct evidence
	- prerequisite contract 已完成 carrier、operator、identifier、fail-open / fail-closed 與 reason split 的收斂
	- generic `task_unreachable` 已完成名詞決策，且目前不構成 runtime gap

## Stable Rules To Preserve
- Seen-target completion 的 owner 邊界：內容 module owner 負責 target resolve / availability / `{entity}_seen` truth；`Duty` kit 負責 completion orchestration、evaluate 與 writeback 協調。
- canonical validation path：`www/tests/smoke/seen_target_task_completion/` 是本 spec 的正式驗證入口，Docker `php-fpm` 結果為 source of truth。
- prerequisite contract：
	- carrier 固定在 `claim.task_template.prerequisite`
	- operator 正式支援 `AND` / `OR`
	- dependency identifier 正式支援 `duty_slug` / `task_template_slug`
	- unknown operator / invalid dependency payload 採 fail-open
	- resolve 不到 dependency 或缺少 dependency task row 採 `prerequisite_unresolvable`
	- dependency 已 resolve 但 `expected_status` 未成立時採 `prerequisite_unmet`
- terminology contract：
	- `task_unreachable` 保留為 generic unreachable vocabulary
	- prerequisite domain 不再輸出 `task_unreachable`

## Shared Doc Backfill
- glossary 應保留以下條目，避免未來 spec 再次重定義：
	- Seen-target completion
	- prerequisite_unmet
	- prerequisite_unresolvable
	- task_unreachable

## Remaining Closeout Gaps
- 若未來沒有新的 runtime evidence 打破目前前提，本 spec 不需要再開新的 implementation slice。
- 剩餘 closeout 工作主要是 archive / retrospective 維護，而不是功能擴充。
