# SMSSystem Optimization

## Purpose
- 記錄 SMSSystem 在主要實作與驗收完成後，已沉澱為穩定規則的共用知識。
- 作為 feature 進入 `(Optimization)` 後的最小封存前承接檔。

## Stable Rules Confirmed
- 正式對外 API 測試的本機 canonical base URL 為 `https://loc.f3cms.com:4433/api/`。
- Docker 內部 web-route smoke 驗證應走 `https://web-server/api/<module>/<method>`，並帶 `Host: loc.f3cms.com`，同時關閉 peer verify 以配合 container-internal hostname。
- `/api/@module/@method` 的 route contract 已固定：`module` 對應 `rModule`，`method` 對應 `do_method`，公開 path 使用小寫 module 與 snake_case method。
- Reaction route response envelope 以 `code / data / csrf` 為準；route failure 判讀應以 body 的 `code` 為主，不應只看 HTTP status。
- SMSSystem 的 frontend-facing mainline route 採 session-first member context：`/api/phonebook/create_with_phones` 與 `/api/campaign/create_from_phonebook` 在有登入 session 時，`member_id` 應由 `fMember::_current('id')` 取得，而不是由前端提交。
- `insert_user` / `last_user` 是 staff audit 欄位；SMSSystem 的 member-facing route 不應把 member id 寫入 staff audit，會員自行操作時固定寫 `0`。
- 開發驗證可使用最小 member 測試通道：在 `APP_ENV=develop` 且後端已設定 `DEV_TOKEN` / `DEV_MEMBER_ID` 時，可透過 `HTTP_MOBILE_TOKEN` 建立測試 member session；正式前端串接仍以正常登入 session 為準。
- `web_route_phonebook_campaign_flow.php` 已升級為真實的 token-path smoke：它會用 `Mobile-Token` header 驗證 develop 測試登入，而不是繼續依賴 payload `member_id` fallback。

## Stable Artifacts Elevated
- `document/guides/api_testing_guide.md`
- `document/guides/sms_system_api_sample.md`
- `document/reference/api_route_reference.md`
- `www/tests/smoke/sms_system/web_route_phonebook_campaign_flow.php`

## 本輪已完成的共享文件同步
- `document/_sidebar.md`：補上 `api_testing_guide.md` 與 `api_route_reference.md` 的導航入口，避免穩定文件只存在於目錄中但不易發現。
- `document/guides/index.md` / `document/_sidebar.md`：補上 `sms_system_api_sample.md` 的入口，讓前端與 LLM 可以直接找到 SMSSystem SBE mainline API sample。
- `document/glossary.md`：補上 `API Route Contract`、`Response Envelope`、`Web-route Smoke` 術語，讓 route-level 驗證與回應判讀不只停留在 SMSSystem spec 內。

## Feature State
- SMSSystem 的最小 owner / request / web-route 驗證鏈已完成。
- 目前沒有未完成的 mainline implementation slice。

## 封存前剩餘整理
- `history.md` 壓縮整理已完成，下一位承接者可以直接從 owner naming resync、formal web-route 驗證與 shared-doc 回寫三條主線理解這份 spec 的收尾狀態。
- 目前不需要再回寫新的 guides 或 reference；API testing 與 route contract 的共享規則已升格完成，feature 已進入 archive-ready 狀態。

## If Work Continues
- 只有在出現新的產品需求、acceptance scope、或 archive / closeout 決策時，才需要再開新一輪工作。