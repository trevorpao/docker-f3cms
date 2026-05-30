# SmokeTestOptimization Optimization

## 穩定規則摘要

- SmokeTestOptimization 第一版的正式入口已固定為 `www/tests/index.php {path}`，且 `path` grammar 穩定收斂為 `<module>/<surface>/<contract>`。
- smoke autodiscovery 的穩定責任鏈為：`www/tests/index.php` 負責 path parsing / module discovery / result emission，`www/f3cms/modules/{Entity}/smoke.php` 承接 entity-owned case 語意，`www/f3cms/libs/Smoke.php` 只承接 shared runtime / base dispatch behavior。
- 第一版 SBE 驗證母體已固定為 `SMSSystem` 的三個 module：`mobile/request/create_or_ensure`、`phonebook/owner/create_with_mobiles`、`campaign/request/create_from_phonebook`。
- 第一版的 stable ownership 分布已固定為：`Mobile` 與 `Campaign` 以 request-side smoke 落地，`Phonebook` 以 owner-side smoke 落地；這不構成 drift，而是目前 entity contract 的真實分布。
- smoke runtime 的 safety baseline 已固定為 `APP_ENV=develop`、`ALLOW_SMOKE_WRITE=1` 與 `SMOKE_DB_NAME` 三重 guard；其中 `SMOKE_DB_NAME` 必須指向獨立 smoke DB，且不得重用 primary `db_name`。
- Docker 是第一版 smoke 驗證的 source of truth；host PHP 因本機環境依賴問題不可作為主要驗證基準。

## 本輪已完成的共享文件同步

- `document/glossary.md`：補入 `Smoke S Layer`、`Module-owned Smoke`、`Smoke Path Grammar`、`Smoke Runtime Guard`、`Smoke DB Isolation`，讓後續討論 smoke 正式入口、module-owned 邊界與 DB safety guard 時有一致用字。
- `document/guides/smoke_s_layer_guide.md`：建立 Smoke S Layer 的正式開發指南，固定 `www/tests/index.php`、module-owned `smoke.php`、`libs/Smoke.php` 與 runtime guard 的責任邊界。
- `document/reference/smoke_s_layer_reference.md`：建立 Smoke S Layer 的查表型 technical reference，固定 path grammar、module discovery、error vocabulary 與 canonical validation commands。
- `document/guides/smoke_result_tier_rerun_guide.md`：建立 smoke result / tier / rerun 的共享指南，固定 tier honesty、最小結果上下文與 DB-backed cleanup 契約。
- `document/guides/index.md`、`document/reference/intro.md`、`document/_sidebar.md`：補上 Smoke S Layer guide / reference 導覽入口，避免共享規則只存在 spec 內。
- `SmokeTestOptimization` spec：`history.md` 已正式切到 `(Optimization)`，不再把「是否可進入 optimization」留在待判定狀態。

## 封存前剩餘整理

- `history.md` 若要封存，仍可再做一次壓縮整理，把「S 層入口定義」、「三個 SMSSystem SBE 落地」與「runtime guard / DB isolation」三條主線濃縮成更短摘要。
- `check.md` 目前已足夠作為 acceptance closeout 基準，不需要因進入 `(Optimization)` 而重寫內容；除非後續要正式封存時再補一句 archive-ready 結論。

## 後續優化方向

- 若未來有第二批 module 採用相同的 `www/tests/index.php {path}` contract，可再評估是否把 Smoke S 層規則升格為 dedicated testing guide。
- 若未來要繼續擴張 smoke，下一步合理方向是把新的 suite 實作逐步對齊已建立的 result/tier/rerun shared guide，而不是回退到第一版入口與 owner boundary 設計。