# TestMode Optimization

## 穩定規則摘要

- TestMode 第一版的 smoke source of truth 已固定在 `www/tests/smoke/<domain>/*.php`；legacy `www/f3cms/scripts/*smoke*.php` wrapper 已在收尾輪全部退休，不再保留雙入口。
- smoke suite 的穩定責任鏈為：suite 本體放在 `www/tests/smoke/`、共用 runner contract 放在 `www/tests/bootstrap/`、F3CMS runtime 接線放在 `www/tests/adapters/f3cms/`。
- canonical naming 的穩定規則是使用 domain / 子系統資料夾與契約導向檔名，例如 `workflow_engine/definition.php`、`workflow_engine/instance_api.php`、`event_rule_engine/basic_or_rule.php`；不得再回到扁平化 `*_smoke.php` 命名作為主路徑。
- Docker 是 TestMode 的驗證基準；主命令口徑固定為 `docker compose exec -T php-fpm php /var/www/tests/smoke/<path>`，不再以 host PHP 或 retired wrapper 路徑作為真實結果來源。
- CLI 與 Lab 在第一版只作為 consumer / gateway，不承載 smoke 本體；未來若要擴充整合，也只能指向 `www/tests/`，不能把 suite 或 bootstrap 邏輯長回既有入口。

## 本輪已完成的共享文件同步

- `glossary.md`：補入 `Smoke Suite`、`Canonical Smoke Path`、`Thin Wrapper`、`Wrapper Retirement` 術語，讓跨 feature 討論測試入口與收尾方式時有一致用字。
- `guides/testmode_development_guide.md`：建立給未來工程師與 LLM 直接遵循的通用開發指南，固定 TestMode 的 source of truth、命名、驗證與 drift prevention 規則。
- `TestMode` spec：`history.md`、`plan.md`、`check.md` 已同步到可進入 `(Optimization)` 的狀態，不再把 wrapper retirement 視為待辦。

## 封存前剩餘整理

- `history.md` 仍可在封存前做一次壓縮整理，把前段低風險搬移批次與後段 canonical naming / wrapper retirement 主線再濃縮成更短的承接摘要。
- 目前不需要再回寫 `setup.md` 或 `overall.md`；TestMode 的穩定收穫已提升為 dedicated guide，現階段共享 closeout 主要只剩封存前摘要整理。
- 若之後有第二個 feature 也採用同樣的 `www/tests/` contract，可再評估是否新增一份 dedicated testing guide；目前 glossary 加上 TestMode optimization 已足以承接第一版結論。

## 後續優化方向

- 若未來新增 smoke runner command，可補一份針對 `www/tests/` 的 execution guide，但前提是仍維持 Docker 與 canonical path 為 source of truth。
- 若未來有更多 domain 採用相同 folderized pattern，可再把 canonical naming 規則抽成更通用的 testing naming guide。