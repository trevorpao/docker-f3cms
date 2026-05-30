# SmokeTestOptimization Check

## Current Stage
- `check`

## Check Basis
- 承接 [document/spec/SmokeTestOptimization/history.md](document/spec/SmokeTestOptimization/history.md) 所記錄的目前 stage 與下一步。
- 承接 [document/spec/SmokeTestOptimization/plan.md](document/spec/SmokeTestOptimization/plan.md) 已固定的 Stage 1 pseudo-spec、SBE-derived acceptance anchors 與第一版範圍。
- 第一版驗收母體固定為 `SMSSystem` 的三個 module：`mobile`、`phonebook`、`campaign`。

## Drift Status
- [x] 已有 `idea.md` 作為需求與邊界基準。
- [x] 已有 `plan.md` 作為 stage 與 pseudo-spec 基準。
- [x] 已有 `history.md` 作為承接紀錄基準。
- [x] `check.md` 已補建，並與 `history.md` / `plan.md` 同步到目前 stage。
- [x] `www/tests/index.php`、`www/f3cms/libs/Smoke.php`、`Mobile/smoke.php`、`Phonebook/smoke.php`、`Campaign/smoke.php` 已落地，三個 SMSSystem SBE 已齊備。

## Stage 1 Acceptance

### A. Path Grammar And Parsing
- [x] `www/tests/index.php` 只接受一個主要 path argument
- [x] path 固定為 `<module>/<surface>/<contract>` 三段結構
- [x] path segment 含非法字元、段數不足或過多時，回傳 `invalid_path`
- [x] 第一版未引入 alias path、模糊補全、query-style path 或多層 contract path

### B. Module Discovery
- [x] path 第一段能正規化到 F3CMS module 名稱，例如 `mobile -> Mobile`
- [x] autodiscovery 固定尋找 `www/f3cms/modules/<Module>/smoke.php`
- [x] module 不存在時回傳 `module_not_found`
- [x] module 存在但 `smoke.php` 缺失時回傳 `smoke_file_not_found`
- [x] autodiscovery 未 fallback 到 `www/cli/index.php` 或其它 legacy 入口

### C. Smoke Contract
- [x] `Mobile/smoke.php` 已遵守 `www/f3cms/libs/Smoke.php` 的基底契約
- [x] smoke bootstrap 失敗時由 `www/tests/index.php` 回傳 `invalid_smoke_contract`
- [x] `www/tests/index.php` 只持有 path parsing、module discovery、smoke bootstrap、result emission，不直接承接 entity business logic

### D. Method Resolution
- [x] method resolution 已固定為顯式規則，而非任意 public method 暴露
- [x] 第一版目前採 unified entrypoint `run(surface, contract, context)`
- [x] `Mobile/smoke.php` 以白名單 method map 固定 `request + create_or_ensure -> runRequestCreateOrEnsure`
- [x] surface 不存在時回傳 `surface_not_found`
- [x] contract 不存在時回傳 `contract_not_found`
- [x] 第一版未使用 fuzzy match 或相近名稱 fallback

### E. Result Schema
- [x] failure response 至少包含 `code`、`status`、`error`、`message`、`path`
- [x] success response 至少包含 `code`、`status`、`path`、`module`、`surface`、`contract`、`result`
- [x] success / failure 結果結構已能一致聚合

## SBE-Derived Acceptance Anchors

### Mobile
- [x] `www/tests/index.php mobile/request/create_or_ensure` 已能作為正式命令口徑，且目前可成功執行
- [x] `Mobile` smoke 會把本機門號正規化為 E.164
- [x] 等價號碼只對應同一筆 `tbl_mobile`

### Phonebook
- [x] `www/tests/index.php phonebook/owner/create_with_mobiles` 已能作為正式命令口徑
- [x] `Phonebook` smoke 會把重複電話去重後收斂成正規化 `Mobile` 關聯
- [x] `Phonebook` owner-side create 語意已被保留

### Campaign
- [x] `www/tests/index.php campaign/request/create_from_phonebook` 已能作為正式命令口徑
- [x] `Campaign` smoke 會建立 `tbl_campaign`
- [x] `Campaign` smoke 會依目標展開對應 `tbl_campaign_log`
- [x] `Campaign` smoke 會帶出 provider alias 或等價的 provider 選擇結果

## Cross-Cutting Constraints
- [x] `www/cli/index.php` 未再兼任 smoke dispatch 正式入口
- [x] `www/f3cms/libs/Smoke.php` 目前仍維持 shared runtime / base behavior 邊界
- [x] host runner 若存在，仍明確以 Docker 作為 source of truth
- [x] 第一版文件仍保留 Outfit 驗證為後續待補，而非已完成項
- [x] smoke execution 需明確要求 `APP_ENV=develop`
- [x] smoke execution 需明確要求 `ALLOW_SMOKE_WRITE=1`
- [x] smoke execution 不可重用 primary `db_name`，必須使用 `SMOKE_DB_NAME`

## Validation Plan
- [x] 文件階段：已以 `plan.md` 與 `history.md` 對齊 check wording
- [x] 程式階段：已以 Docker 執行 `php /var/www/tests/index.php mobile/request/create_or_ensure`、`php /var/www/tests/index.php mobile/request/unknown_contract`、`php /var/www/tests/index.php mobile/unknown_surface/create_or_ensure`、`php /var/www/tests/index.php phonebook/owner/create_with_mobiles`、`php /var/www/tests/index.php campaign/request/create_from_phonebook` 驗證目前行為
- [x] 已驗證未設 `APP_ENV=develop` 時，smoke 會被阻擋
- [x] 已驗證未設 `ALLOW_SMOKE_WRITE=1` 時，smoke 會被阻擋
- [x] 已驗證 `SMOKE_DB_NAME` 等於 primary `db_name` 時，smoke 會被阻擋
- [x] DB-backed 驗證已不再直接使用 primary `db_name`，而是要求獨立 `SMOKE_DB_NAME`

## Not In Scope For First Pass
- [x] 不以本輪 check 宣告 Outfit 驗證已完成
- [x] 不以本輪 check 宣告所有 module 都已有 `smoke.php`
- [x] 不以本輪 check 宣告所有 smoke tier 已完全統一

## Blocking Gap Assessment
- [x] 第一版正式入口、三條 SMSSystem 主路徑與 guard path 已有 Docker 驗證證據
- [x] 目前沒有阻擋第一版承接的 runtime gap
- [x] 目前沒有阻擋第一版承接的 acceptance gap
- [x] 目前沒有阻擋第一版承接的 code-to-spec mismatch
- [x] `SMOKE_DB_NAME=target_db` 會被拒絕，且此行為已確認為預期 safety guard，而非 regression

## Residual Non-Blocking Items
- [x] Outfit 驗證仍為後續待補，不影響第一版 check 結論
- [x] 更多 module-owned `smoke.php` 覆蓋仍為後續擴張項
- [x] smoke tier / result contract / rerun 規則尚待下一階段統一，但不阻擋第一版完成判斷

## Next Check Update Trigger
- [x] 當 `www/tests/index.php` 初版落地後，更新 Stage 1 與三個 SMSSystem path 的實作驗收結果
- [x] 當 `www/f3cms/libs/Smoke.php` 與第一批 module-owned `smoke.php` 落地後，更新 smoke contract 驗收結果
- [x] 當 Docker validation 跑完後，補入 executable validation 結果與未通過項

## Check Conclusion
- [x] 第一版 smoke 正式入口已可由 `check` 階段判定為無 blocking gap
- [x] 下一步可評估是否進入 `(Optimization)`，而不是回退到實作補洞