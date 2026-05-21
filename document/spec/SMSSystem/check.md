# SMSSystem Check

## Current Status
- Current stage: (Optimization)
- Main implementation: first ten slices completed
- Validation: expansion slice, worker routing slice, worker opt-out slice, worker rate-limit slice, worker provider-failure slice, owner-side phonebook slice, campaign request-surface slice, mobile request-surface slice, mainline request-flow slice, and formal web-route request-flow slice passed in Docker
- Document chain: initialized on 2026-05-20

## Completed
- `idea.md` 已定義問題、範圍、非範圍、核心模組、資料表草案、SBE scenario 與時序圖。
- `history.md` / `plan.md` / `check.md` 已補齊最小承接骨架。
- 已確認 provider helper 基礎可沿用既有 `SmsProviderInterface`、`AmazonSnsProvider`、`MitakeProvider`、`smSender`。
- 已確認 SMSSystem 的主 queue 不應直接沿用 `smSender` 內建 queue，仍以 `tbl_campaign_log` + worker 為準。
- 已補出 Stage 1 的 owner / schema 初稿：table owner、核心欄位、索引草案、`tbl_campaign_log` status vocabulary 與初始 error_message 候選。
- 已補入 provider routing rule：正規化後 `+886` 門號走三竹，其它國碼門號走 AWS，並將 `tbl_campaign.provider_policy` / `tbl_campaign_log.provider_alias` 的責任切開。
- 已補出 Stage 3 task expansion contract：campaign 建立時同步展開 `tbl_campaign_log`、以 `mobile_id` 去重、展開時寫入 `provider_alias`，並明確切開 campaign 計數欄位與 worker 更新責任。
- 已補出 Stage 4 worker guardrails：`Opt-out` / `Invalid` / rate limit 的判斷順序、provider failure 回寫規則、`failed_count` 更新責任，以及 `sent_ts` / `last_sent_ts` 只在成功發送時更新。
- 已解掉 `Skipped` / `Failed` 漂移：本期主流程以 `idea.md` 的 SBE 為準，`Opt-out` 與 rate limit 都收斂為 `Failed`。
- 已補出 Stage 5 validation route：正式 Docker baseline 為 `php-fpm`，direct SQL 驗證只在必要時走 `mariadb`，且帳密來源固定回到 `.env`。
- 已定義 SMSSystem 最小 smoke matrix：task expansion、opt-out、rate limit、provider routing、provider failure。
- 已解掉 owner naming drift：SMSSystem 不再與既有 `Task` domain 共用 task owner 名稱，後續 implementation 以 `Mobile` / `Phonebook` / `Campaign` 與 `tbl_mobile` / `tbl_phonebook*` / `tbl_campaign*` 為準。
- 已完成第一個 implementation slice：
  - `document/sql/260520.sql` 已補 `tbl_mobile`、`tbl_phonebook`、`tbl_phonebook_mobile`、`tbl_campaign`、`tbl_campaign_log`
  - `www/f3cms/modules/Campaign/feed.php` 已提供最小 `createForPhonebook(...)` expansion surface
  - `www/tests/smoke/sms_system/task_expansion.php` 已在 Docker `php-fpm` 通過
- 已完成第二個 implementation slice：
  - `www/f3cms/modules/Campaign/feed.php` 已補 pending log query、`Sent` / `Failed` 回寫、`tbl_mobile.last_sent_ts` 更新與 campaign summary sync helper
  - `www/f3cms/modules/Campaign/kit.php` 已提供最小 `Pending -> Sent/Failed` worker orchestration
  - `www/f3cms/modules/Crontab/reaction.php` 與 `www/cli/index.php` 已補 `run-campaign-worker` CLI route
  - `www/tests/smoke/sms_system/worker_provider_routing.php` 已在 Docker `php-fpm` 通過
- 已完成第三個 implementation slice：
  - `www/tests/smoke/sms_system/worker_opt_out.php` 已在 Docker `php-fpm` 通過
  - 已驗證 `Opt-out` 由 worker 在 provider dispatch 前收斂為 `Failed`
  - 已驗證 `error_message = Opt-out Number`、`provider_message_id` 保持空值、`tbl_mobile.last_sent_ts` 不被誤更新
- 已完成第四個 implementation slice：
  - `www/tests/smoke/sms_system/worker_rate_limit.php` 已在 Docker `php-fpm` 通過
  - 已驗證 5 分鐘內命中防刷的門號會在 provider dispatch 前收斂為 `Failed`
  - 已驗證 `error_message = Rate Limited (5 mins)`、`provider_message_id` 保持空值、`tbl_mobile.last_sent_ts` 維持原值
- 已完成第五個 implementation slice：
  - `www/tests/smoke/sms_system/worker_provider_failure.php` 已在 Docker `php-fpm` 通過
  - 已驗證 provider adapter 回傳失敗時會收斂為 `Failed`
  - 已驗證 `error_message = Provider Error: upstream timeout`、`provider_message_id` 保持空值、`failed_count` 正確回寫、`tbl_mobile.last_sent_ts` 不被誤更新
- 已完成第六個 implementation slice：
  - `www/f3cms/modules/Mobile/feed.php` 已提供門號正規化與 ensure surface
  - `www/f3cms/modules/Phonebook/feed.php` 與 `www/f3cms/modules/Phonebook/reaction.php` 已提供最小 phonebook owner-side 建立入口
  - `www/tests/smoke/sms_system/owner_surface_phonebook_campaign.php` 已在 Docker `php-fpm` 通過
  - 已驗證 owner-side phonebook data 可直接進入 `Campaign` expansion，不再依賴 smoke-only seed path
- 已完成第七個 implementation slice：
  - `www/f3cms/modules/Campaign/reaction.php` 已提供最小 `create_from_phonebook` request surface
  - `www/tests/smoke/sms_system/request_surface_campaign_create.php` 已在 Docker `php-fpm` 通過
  - 已驗證 request-surface helper 會建立 `Queued` campaign、初始化 `Pending` logs，並保留既有 provider routing
- 已完成第八個 implementation slice：
  - `www/f3cms/modules/Mobile/reaction.php` 已提供最小 `create_or_ensure` request surface
  - `www/tests/smoke/sms_system/request_surface_mobile_create_or_ensure.php` 已在 Docker `php-fpm` 通過
  - 已驗證本地格式與 E.164 會收斂到同一筆 normalized mobile row，不需直接呼叫 `fMobile`
- 已完成第九個 implementation slice：
  - `www/f3cms/modules/Phonebook/reaction.php` 已補 `createWithPhonesRequest(...)` request helper
  - `www/tests/smoke/sms_system/mainline_phonebook_campaign_request_flow.php` 已在 Docker `php-fpm` 通過
  - 已驗證 `Phonebook request surface -> Campaign request surface` 的 mainline flow 會建立 deduped phonebook、`Queued` campaign 與 `Pending` logs
- 已完成第十個 implementation slice：
  - `www/tests/smoke/sms_system/web_route_phonebook_campaign_flow.php` 已在 Docker `php-fpm` 通過
  - 已驗證正式 HTTPS `/api/phonebook/create_with_phones` 與 `/api/campaign/create_from_phonebook` 會經過 `web-server` / F3 對外路由，並透過 `HTTP_MOBILE_TOKEN` 測試通道解析 member session、建立 deduped phonebook、`Queued` campaign 與 `Pending` logs
- 已完成 optimization 承接：
  - 已補 `document/guides/api_testing_guide.md`，收斂本機 `https://loc.f3cms.com:4433/api/` 與 Docker route 驗證規則
  - 已補 `document/guides/sms_system_api_sample.md`，用 `API-sample.md` 風格整理 SBE mainline 的 frontend-facing API request / response 文件
  - 已將 member-facing route 改為 session-first：frontend 走 `do_create_with_phones()` / `do_create_from_phonebook()` 時，`member_id` 由 `fMember::_current('id')` 取得，不再要求前端傳入
  - 已將 member-facing route 的 audit 欄位收斂為 staff-only：會員 route 一律寫 `insert_user = 0`，不再把 member id 寫入 staff audit 欄位
  - 已補最小 `kMember::_isLogin()` / `_chkLogin()`，支援 develop-only `HTTP_MOBILE_TOKEN` + `DEV_TOKEN` / `DEV_MEMBER_ID` 測試通道
  - 已補 `rPhonebook::do_mine()` / `rCampaign::do_mine()`，提供 member-facing mine list route，並對齊 repo 既有 paginated `subset` / `total` / `limit` / `page` list contract
  - 已將 `idea.md` 補齊「取得清單」SBE scenario，讓 `Phonebook` / `Campaign` 的 member list retrieval example 成為正式需求例子
  - 已補 `document/reference/api_route_reference.md`，收斂 `/api/@module/@method` 的 naming / request / error-code contract
  - 已將 `sms_system/web_route_phonebook_campaign_flow.php` 收斂到新的 API testing / route contract 文件，且 formal smoke 現在同時驗證 `create_with_phones`、`create_from_phonebook`、`phonebook/mine`、`campaign/mine`
  - 已更新 `document/_sidebar.md`，讓 API testing guide 與 API route reference 成為正式可發現文件
  - 已更新 `document/guides/index.md` 與 `document/_sidebar.md`，讓 SMSSystem API sample 成為正式可發現文件
  - 已更新 `document/glossary.md`，補入 API route contract、response envelope、web-route smoke 的共用術語

## Pending
- 目前沒有未完成的 SMSSystem mainline 驗證 slice。
- 目前沒有阻擋 `(Optimization)` 的關鍵缺口。
- 目前沒有剩餘的 closeout gap；`history.md` 壓縮整理與 archive-ready 判定已完成。

## Risks
- 若先做供應商串接再補資料結構，容易讓 queue / blacklist / rate limit 邏輯失去 owner boundary。
- 若忽略 repo 內已存在的 SMS provider helper，容易在 SMSSystem 再疊一套重複 abstraction，增加 FORK 維護成本。
- 若誤把 `smSender` 內建 queue 當成 SMSSystem 主 queue，會讓 `tbl_campaign_log` 與 queue worker 的責任重疊。
- 若 `tbl_campaign_log` vocabulary 未先收斂，後續 smoke 與 retry/failure handling 會出現 drift。
- 若 `tbl_mobile.last_sent_ts` 與 `tbl_campaign_log.sent_ts` 的責任未切清，後續 rate limit 判斷可能出現雙來源 drift。
- 若門號未先正規化就做 provider routing，`886...`、`09...`、`+886...` 可能落到不同 provider，造成實際送信漂移。
- 若空 phonebook 或異常 phonebook 仍允許建立 `Queued` campaign，worker 會承接不必要的空任務與計數漂移。
- 若未來要啟用 `Skipped`，必須先補正式情境與驗收規格；否則會再次和現行 `Failed` vocabulary 衝突。
- 若 implementation 先開始、但 `sms_system` smoke suite 尚未建立，容易退回 ad hoc 驗證，削弱後續承接穩定性。
- 若實作仍沿用 generic `Task` / `tbl_task*` 命名，會直接與既有 duty/reward task domain 衝突。
- 對外 web route 驗證已補齊；後續若要繼續擴 scope，主要風險改為新需求是否會越過目前已收斂的 owner / request / route boundary。

## Next Check Focus
- 若後續繼續處理 SMSSystem，優先確認是否出現新的需求、acceptance gap、或 archive 決策變更；否則不再重開 implementation slice
