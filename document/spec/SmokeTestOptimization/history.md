# SmokeTestOptimization History

## 2026-05-23

### Stage
- `(Optimization)`

### Summary
- SmokeTestOptimization 已完成第一版 FORKS Smoke S Layer 落地：`www/tests/index.php {path}` 成為正式入口，`www/f3cms/libs/Smoke.php` 與 module-owned `smoke.php` 的 owner boundary 已固定，並由 `Mobile`、`Phonebook`、`Campaign` 三個 SMSSystem SBE 作為主線驗證母體。
- feature 中段的主要工作已完成 path grammar / autodiscovery、runtime guard、smoke DB isolation 與三條正式 smoke path 的 Docker 驗證；第一版已確認沒有阻擋承接的 runtime gap、acceptance gap 或 code-to-spec mismatch。
- feature 後段已完成共享文件沉澱：Smoke S Layer guide、reference、glossary 術語，以及 result / tier / rerun guide 都已回寫；目前 spec 已進入 archive-ready 狀態。

### Completed This Round
- 建立 `www/tests/index.php` 正式入口，固定 `<module>/<surface>/<contract>` path grammar、module autodiscovery、success envelope 與 error vocabulary。
- 建立 `www/f3cms/libs/Smoke.php` 最小 base contract，並以 `Mobile`、`Phonebook`、`Campaign` 三個 module-owned `smoke.php` 落地第一版 SBE。
- 收斂 runtime safety baseline：`APP_ENV=develop`、`ALLOW_SMOKE_WRITE=1`、`SMOKE_DB_NAME` 與獨立 smoke DB bootstrap 已固定。
- 完成 Docker 驗證：三條主路徑可執行，unknown surface / contract 與 guard-block case 都有明確證據；`SMOKE_DB_NAME=target_db` 的失敗已確認為預期 safety guard。
- 完成共享文件同步：`glossary.md`、`guides/smoke_s_layer_guide.md`、`reference/smoke_s_layer_reference.md`、`guides/smoke_result_tier_rerun_guide.md`、索引與 sidebar 都已承接穩定規則。

### Drift
- 先前的第三個 SBE anchor 缺失、plan/check/history 不一致，以及 smoke guard / DB isolation 未文件化等 drift 已全部收斂。
- 目前沒有阻擋 closeout 或 archive 的關鍵 drift。
- 剩餘項目如 Outfit 驗證、更多 module 覆蓋，屬後續擴張項，不屬於第一版 closeout 阻塞。

### Next Step
- SmokeTestOptimization 已完成 retrospective / closeout 準備。
- 若後續沒有新需求，這份 spec 可直接維持 archive-ready 狀態；若未來再開工，應以新的 smoke coverage 需求、acceptance gap 或共享規則擴張為起點，而不是重開既有第一版入口實作。