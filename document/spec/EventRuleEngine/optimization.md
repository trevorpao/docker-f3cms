# EventRuleEngine Optimization

## 穩定規則摘要

- EventRuleEngine 第一版的主契約已收斂為 shared pure engine 路徑：owning module 先提供 duty claim payload、player context 與 writeback coordination，再由 `www/f3cms/libs/EventRuleEngine.php` 做 payload 驗證、AST traversal 與 evaluator 判定。
- `duty`、`task`、`member`、`press`、`manaccount` 的 table-backed payload source、truth write、task state 與 reward persistence 仍各自由 owning module 承接；EventRuleEngine 不應演化成單一 shared persistence owner。
- concrete scenario 的穩定 runtime chain 已固定為：`Member::Register` 由 `kMember::registerByOauth()` 建立 task，`rPress::do_seen()` 只做事件驗證與協調，`fMember` 承接 `member_seen` first-hit truth，`kDuty::completeTasksForSeenTarget()` 在同一條 transaction 內完成 task done 與 reward writeback。
- `tbl_member_seen` 的穩定語意是 first-hit truth：同一 `(member_id, target, row_id)` 只保留第一次達標紀錄，`insert_ts` 即 truth 成立時間，不再另存 `first_seen_ts` 或 `threshold` 版本資訊。
- `MEMBER_SEEN_TARGET` evaluator 的穩定 contract 是只吃 preload 後的 `member_seen_targets` truth，不直接查 press log 或前台事件流。
- Docker 是 EventRuleEngine 第一版的驗證基準；代表性主命令已固定為 `member_register_oauth_task_create.php` 與 `press_seen_reaction_task_done_reward.php` 這兩條 DB-backed smoke。

## 本輪已完成的共享文件同步

- `glossary.md`：補入 `EventRuleEngine` 與 `First-hit Truth` 術語，讓跨 feature 討論 engine 邊界與 `member_seen` 類型資料時有一致用字。
- `reference/reaction_reference.md`：補入 Event Trigger Coordination Pattern，固定 Reaction 對 event-derived truth 的協調邊界。
- `EventRuleEngine` spec：`history.md`、`plan.md`、`check.md` 已同步到正式進入 `(Optimization)` 的狀態。

## 封存前剩餘整理

- `history.md` 仍可在封存前再做一次壓縮整理，把從 generic baseline 回退、concrete scenario 收斂、以及最終 `rPress -> fMember` runtime 落地這三條主線濃縮成更短的承接摘要。
- 目前不需要再調整 sidebar，也不需要額外新增 dedicated EventRuleEngine shared guide；現階段 glossary、reaction reference 與 spec optimization 已足以承接第一版穩定結論。

## 後續優化方向

- 若未來有第二個以上 module 採用相同的 EventRuleEngine integration pattern，可再評估是否新增 dedicated event rule integration guide。
- 若未來出現第二種以上 first-hit truth 類型，可再評估是否把這個模式升格到 `guides/data_modeling.md`，作為更通用的資料建模規則。