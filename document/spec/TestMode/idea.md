# TestMode - idea.md

這份文件用來定義 F3CMS 專案的測試模式重構方向，目標不是只搬移 smoke 檔案，而是把目前混雜在 `www/f3cms/scripts/`、`www/cli/index.php` 與 `www/f3cms/modules/Lab/reaction.php` 的不同驗證角色正式拆開，建立一套可長期維護、可跨框架承接、又不脫離現有 Docker 執行環境的測試與驗證結構。

### 1. 背景與問題定義 (Problem Statement)

目前專案中至少存在三種不同性質的執行入口：
- `www/f3cms/scripts/`：承接 smoke scripts、fixture 與一些直接用 PHP 執行的驗證腳本
- `www/cli/index.php`：承接排程與 CLI 型命令入口
- `www/f3cms/modules/Lab/reaction.php`：承接 F3CMS 內部 staff 可見的診斷與手動測試入口

這三者目前在語意上並未正式分層，導致以下問題：
- smoke test 與營運 CLI 混在一起，不容易區分哪些是驗證用途、哪些是正式營運命令
- fixture 與 app runtime code 邊界不清楚，使測試資產無法被視為 repo-level 可攜資產
- 當未來要以其他框架重構時，現有 smoke / fixture / 驗證路徑過度依附 F3CMS 目錄與 bootstrap 方式，不利快速遷移
- `Lab` 目前更像手動診斷頁面，而不是正式測試系統的一部分，卻與 smoke 類需求存在重疊感

若不先把這些角色正式拆開，未來每次新增 smoke 或測試入口，都會再次把「驗證系統」塞回 F3CMS app 內部，而不是逐步抽離成 framework-light 的 repo 資產。

### 2. 目標結果 (Target Outcome)

建立一套新的測試模式，使專案可以同時滿足以下目標：
- 將 smoke、fixture 與未來測試系統集中到 `www/tests/` 下
- 保留 Docker 容器在現有 volume 設定下可直接執行測試的能力
- 讓 `www/cli/index.php` 專注於 CLI / 排程型 command gateway，而不是測試腳本本體的收容器
- 讓 `www/f3cms/modules/Lab/reaction.php` 專注於受控診斷、結果查看或人工觸發入口，而不是作為 smoke 主體
- 讓測試資產逐步朝「可跨框架承接」的 repo-level 邏輯靠近，即使目前實體位置先落在 `www/tests/`

最終希望做到：當未來要重構 F3CMS 為其他框架時，測試資產可以快速被重新接線，而不需要先從 F3CMS 內部 scripts 目錄抽絲剝繭。

### 3. 範圍 (Scope)

本輪規格包含：
- 定義 `www/tests/` 的角色與子目錄分層
- 定義 smoke、fixture、bootstrap、adapter 的責任邊界
- 定義 `www/cli/index.php` 與 `www/f3cms/modules/Lab/reaction.php` 在新測試模式下的定位
- 定義既有 `www/f3cms/scripts/` 如何逐步退出 smoke 主路徑
- 定義與 Docker 現有掛載方式相容的第一版測試目錄方案

### 4. 非範圍 (Non-Scope)

本輪規格不直接處理：
- 一次性重寫所有現有 smoke scripts
- 引入完整 PHPUnit / Pest / Codeception 測試框架
- 重寫 `Lab` 成完整測試管理後台
- 重構全部 CLI routing 與 cronjob 系統
- 將整個 repo 掛載策略一次改成 workspace-root 模式

本輪的重點是先把結構、責任與遷移方向定義清楚，而不是一次完成所有搬遷。

### 5. 核心物件與流程 (Core Objects or Processes)

此功能不是單一 Entity，而是針對驗證系統的分層重整。

第一版核心構成如下：
- `www/tests/smoke/`：承接 smoke scripts，本身是最小可執行驗證案例集合
- `www/tests/fixtures/`：承接 smoke / test 所需的 JSON、payload、definition fixture
- `www/tests/bootstrap/`：承接測試執行時共用的 bootstrap 邏輯，避免每支 smoke script 各自複製 bootstrap 程式
- `www/tests/adapters/`：承接與 F3CMS 或未來其他框架的接線層，目的是隔離 framework-specific bootstrap 與 repo-level smoke 定義
- `www/cli/index.php`：作為正式 CLI 入口，必要時呼叫 `www/tests/` 內的測試 runner 或 suite
- `www/f3cms/modules/Lab/reaction.php`：作為 staff 可用的內部診斷與結果查看入口

### 6. 角色與參與者 (Actors and Roles)

- 開發者：撰寫與執行 smoke / fixture / adapter，作為重構與回歸的主要使用者
- Tech Lead / SA / SD：定義哪些驗證屬於 repo-level contract，哪些仍是 F3CMS app-level 診斷
- 維運或排程系統：透過 CLI 入口執行受控 command
- Staff / 內部管理者：透過 `Lab` 查看資訊、執行有限度診斷或讀取 smoke 結果
- 未來的新框架 adapter：承接 `www/tests/` 中的 smoke 契約，而不需要完全依附 F3CMS 目錄

### 7. 資料與狀態影響 (Data and State Implications)

這次重構的主要影響不在業務資料表，而在驗證資產與執行路徑：

1. `www/f3cms/scripts/` 中現有 smoke scripts 將逐步搬移到 `www/tests/smoke/`
2. 既有 `workflow_fixtures/` 應改由 `www/tests/fixtures/` 承接
3. 測試 bootstrap 不應散落在每一支 smoke 檔內，而應由 `www/tests/bootstrap/` 統一處理
4. framework-specific 接線應往 `www/tests/adapters/f3cms/` 類型的位置集中，避免 smoke 腳本直接依附 app 內部路徑細節
5. `www/cli/index.php` 若需執行 smoke，不應把 smoke script 本體塞回 CLI 路由內，而應由 CLI 呼叫對應 runner
6. `Lab` 若需顯示 smoke 結果，應優先顯示結果或觸發既定 command，而不是自己承載完整 smoke 實作

### 8. 限制與依賴 (Constraints and Dependencies)

- 現有 Docker volume 只將 `./www` 掛到容器內的 `/var/www`，因此第一版測試系統若要維持現有 Docker 驗證路徑，實體位置必須先落在 `www/tests/`
- 第一版仍需相容目前的 F3CMS bootstrap 方式，包括 `vendor/autoload.php`、`libs/Autoload.php`、`libs/Utils.php` 與 `config.php`
- 第一版應優先沿用既有 Docker 驗證方式，不另開 host-only 驗證新基準
- CLI 與 Lab 都已在現有系統中有運作中的角色，因此重構不能假設這兩個入口可以直接移除

### 9. 風險與未決問題 (Risks and Open Questions)

- 若只搬檔案位置、不整理 bootstrap 與 adapter，測試系統仍會強耦合在 F3CMS 內部，無法達成跨框架重用目標
- 若讓 `Lab` 直接成為 smoke 主執行器，可能混淆 staff 診斷與正式測試的邊界，也增加 web-triggered 執行風險
- 若讓 `CLI` 直接塞滿 smoke 細節，CLI 將再次成為 scripts 的替代收容器，而不是 command gateway
- `www/tests/` 雖然比 `www/f3cms/scripts/` 更好，但它仍位於 `www` 之下；未來若 Docker 掛載策略改成 repo-root，可能還需再做第二階段搬移
- 現有 WorkflowEngine 與 EventRuleEngine smoke 已開始形成慣例，搬移時需要維持相容期與 wrapper 策略

目前仍未決的問題包括：
- 第一版是否要在 `www/tests/` 下同時建立 smoke runner
- 是否要保留 `www/f3cms/scripts/` 作為相容層 wrapper 一段時間
- `Lab` 是只顯示結果，還是允許受控觸發 smoke suite

### 10. 早期範例或情境 (Early Examples or Scenarios)

#### Scenario A: 開發者在 Docker 中執行 smoke

開發者希望驗證某個 engine 或 module 的最小 contract。

第一版期望路徑：
- 執行 `docker compose exec -T php-fpm php /var/www/tests/smoke/<suite>.php`
- 測試透過 `www/tests/bootstrap/` 或 `www/tests/adapters/f3cms/` 進入 F3CMS runtime
- fixture 從 `www/tests/fixtures/` 載入

#### Scenario B: CLI 觸發既定 smoke suite

維運或工程師希望用 CLI 執行既定測試集合。

第一版期望路徑：
- `www/cli/index.php` 作為入口
- CLI 將命令分派到 `www/tests/` 的 runner 或指定 smoke 檔案
- CLI 本身不保存 smoke 邏輯細節

#### Scenario C: Lab 顯示最近一次 smoke 結果

Staff 想查看系統內部診斷資訊與最近一次 smoke 結果。

第一版期望路徑：
- `www/f3cms/modules/Lab/reaction.php` 顯示結果摘要或允許有限度觸發受控檢查
- `Lab` 不是 smoke 主體，也不應直接複製 smoke 腳本

### 11. 初步目錄方向 (Early Directory Direction)

第一版建議目錄：

```text
www/
  cli/
    index.php
  tests/
    smoke/
    fixtures/
    bootstrap/
    adapters/
      f3cms/
  f3cms/
    modules/
      Lab/
        reaction.php
```

第一版遷移原則：
- `www/tests/` 是 smoke / fixture / future test system 的新主路徑
- `www/cli/index.php` 是 command gateway
- `www/f3cms/modules/Lab/reaction.php` 是 diagnostic entry
- `www/f3cms/scripts/` 可在過渡期保留 wrapper，但不再是新 smoke 的正式落點