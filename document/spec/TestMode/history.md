### 第 0 輪討論結果
1. 目前新需求的核心不是單一功能，而是重新定義 smoke、fixture、CLI 與 F3CMS 內部診斷入口的責任邊界，避免驗證系統長期綁死在 `www/f3cms/scripts/`。
2. 已確認的新方向是：測試系統先搬到 `www/tests/`，而不是 repo root，原因是現有 Docker volume 目前只掛載 `www/` 到容器內，這讓 `www/tests/` 成為第一版最實際且可執行的新位置。
3. 已知角色分層如下：`www/tests/` 承接 smoke / fixture / bootstrap / adapter，`www/cli/index.php` 承接 CLI / cronjob command gateway，`www/f3cms/modules/Lab/reaction.php` 承接 staff 可見的診斷與結果查看入口。
4. 目前 flow stage 應落在 `idea`，因為測試系統的新結構、搬移順序、相容層策略與 `Lab` / CLI 如何整合都還未正式拆成可執行 stage。
5. 已有文件狀態：目前已建立 `idea.md` 與 `history.md`；尚未建立 `plan.md`、`check.md`、`optimization.md`。
6. 目前已知風險或未決問題包括：是否保留 `www/f3cms/scripts/` wrapper 相容層、`Lab` 是否允許受控觸發 smoke、`CLI` 是否要提供 smoke runner 命令，以及未來第二階段是否要把 `www/tests/` 再提升到 repo root。
7. 最新討論的下一步選項：先只做一件事，承接 `idea` 後的 `(discuss)` / `plan` 前收斂，把 `www/tests/` 的子目錄責任、CLI / Lab / scripts 過渡策略與搬移順序整理成可執行的第一版 plan。