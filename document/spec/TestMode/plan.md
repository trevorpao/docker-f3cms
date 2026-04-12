# TestMode Plan

## Purpose
- 將 TestMode 的測試系統重構方向拆成可執行階段。
- 讓 `www/tests/`、CLI、Lab 與既有 scripts 的責任邊界有正式承接基準。
- 控制第一版範圍，避免一開始就整包重寫所有 smoke、CLI 與診斷入口。

## Plan Basis

本計畫目前直接承接 `idea.md` 中已明確存在的核心方向：
- `www/tests/` 是第一版 smoke / fixture / future test system 的新主路徑
- `www/cli/index.php` 是 CLI / cronjob command gateway
- `www/f3cms/modules/Lab/reaction.php` 是 diagnostic entry
- `www/f3cms/scripts/` 只應作為過渡期 wrapper 或相容層，不再是新 smoke 的正式落點
- 第一版必須相容現有 Docker volume 只掛載 `www/` 的限制

## Current Stage Assessment

目前 feature 已完成完整 `plan` 收斂，且已正式進入 `(Optimization)`：
- `www/tests/` 最小骨架已建立
- 目前所有 legacy smoke 都已搬移到 `www/tests/smoke/`，且全部 smoke 主路徑都已切到 canonical folder path
- `www/f3cms/scripts/*smoke*.php` 過渡期 wrapper 已批次移除
- Docker 已成功驗證 canonical 主命令在無 wrapper 狀態下仍可執行
- 目前後續工作已進一步收斂為規則沉澱、共享術語同步與封存前整理

因此目前承接點為：
- feature 已不再只是純 `plan`，而是已進入 `(Optimization)`
- `optimization.md` 已開始承接穩定規則與收尾整理
- 本文件除了保留規劃，也記錄多輪 `(done)` 與收尾 stage 的承接基線

## Stage Plan

### Stage 1: 收斂目錄契約與責任邊界

目標：
- 將 `www/tests/` 的第一版目錄與角色正式固定
- 明確定義 smoke、fixture、bootstrap、adapter 的責任邊界
- 將 CLI、Lab、scripts 的定位收斂為可執行契約

主要工作：
- 固定 `www/tests/` 的子目錄層次
- 固定 smoke runner 是否屬於第一版
- 固定 CLI / Lab / scripts 的角色說明

輸出：
- 第一版目錄契約
- 第一版入口責任邊界

#### Stage 1 收斂結果

##### A. `www/tests/` 第一版子目錄契約

第一版先固定以下目錄層次：

```text
www/tests/
	smoke/
	fixtures/
	bootstrap/
	adapters/
		f3cms/
```

各子目錄責任如下：

`www/tests/smoke/`：
- 放可直接執行的 smoke suite
- 一支檔案代表一個最小可驗證契約或一組高度相關情境
- 不直接保存 framework bootstrap 細節，避免每支 smoke 重複貼上初始化程式

`www/tests/fixtures/`：
- 放 smoke / test 共用 fixture
- 以 JSON、payload、definition、sample input 為主
- 應盡量保持唯讀、可重用，不混入執行流程控制

`www/tests/bootstrap/`：
- 放測試執行共用 bootstrap
- 責任是提供 runner 可重用的初始化邏輯，不放測試斷言本體
- 不直接承接 framework-specific business logic

`www/tests/adapters/f3cms/`：
- 放 F3CMS-specific 接線邏輯
- 例如載入 `vendor/autoload.php`、`libs/Autoload.php`、`libs/Utils.php`、`config.php` 的包裝
- 目的是把 smoke contract 與 F3CMS runtime 細節拆開

##### B. Smoke Runner 是否納入第一版

第一版決策：
- 不先做通用 smoke runner
- 先維持「直接執行單支 suite 檔」的模式
- 等 Stage 3 再評估 CLI 是否提供 smoke runner 命令

這個決策的理由：
- 目前最急迫的是先把 smoke 與 fixture 從 `www/f3cms/scripts/` 抽出正式主路徑
- 若現在先設計 generic runner，會把議題擴大到參數規格、suite discovery、結果格式與 CLI route，超出第一版最小範圍

因此第一版標準執行形式先固定為：
- `docker compose exec -T php-fpm php /var/www/tests/smoke/<suite>.php`

##### C. `www/f3cms/scripts/` 的第一版邊界

第一版決策：
- `www/f3cms/scripts/` 進入過渡期相容層角色
- 現有 smoke scripts 可暫時保留為 wrapper
- 新增 smoke 不再直接落在 `www/f3cms/scripts/`

wrapper 原則：
- wrapper 只做轉發，不再承載新 fixture、新 bootstrap 或新斷言
- wrapper 存在的目的只是保護現有命令與既有引用者
- 一旦 `www/tests/` 路徑穩定並完成呼叫端切換，wrapper 應可被移除

##### D. `www/cli/index.php` 的第一版邊界

第一版決策：
- `www/cli/index.php` 保持 command gateway 身分
- 不把 smoke 腳本本體搬進 CLI route
- 本階段不強制 CLI 提供 smoke runner 命令

CLI 第一版應避免：
- 直接內嵌 smoke 測試細節
- 讓 route handler 自行組裝 fixture 或 bootstrap
- 把 CLI 變成 `scripts/` 的新收容層

##### E. `www/f3cms/modules/Lab/reaction.php` 的第一版邊界

第一版決策：
- `Lab` 先定義為 diagnostic entry，不是 smoke 主執行器
- 優先定位成資訊查看、結果摘要與有限度診斷入口
- 第一版不把完整 smoke 執行主流程放進 `Lab`

Lab 第一版可接受的方向：
- 顯示最近一次 smoke 結果摘要
- 顯示哪些 suite 已存在
- 提供受控、白名單式的單點診斷行為

Lab 第一版不可接受的方向：
- 任意觸發整包 smoke suite
- 直接複製 smoke 斷言邏輯
- 成為 wrapper 與測試本體的混合入口

##### F. 第一版命名與邊界原則

第一版再補一條命名原則：
- `www/tests/smoke/` 下的檔名應表達測試契約或 suite，不再以歷史 scripts 目錄為依附理由
- `www/tests/fixtures/` 應依 domain 或 suite 分資料夾，而不是全部平鋪
- `bootstrap/` 與 `adapters/` 分離，避免 framework-specific 邏輯再次污染 smoke 本體

##### G. Smoke Path 正名要求

第一版規劃再正式補上 `www/tests/` 的正名要求，避免新主路徑只是把舊 `scripts/*.php` 平移過來：

1. `www/tests/smoke/` 應以 domain / 子系統分資料夾，而不是長期維持所有 suite 平鋪
2. smoke 主檔名應移除 `_smoke` 後綴，改用該情境本身作為檔名
3. canonical path 應優先表達 domain，再表達情境，例如：
	- `www/tests/smoke/workflow_engine/press.php`
	- `www/tests/smoke/workflow_engine/parallel_join.php`
	- `www/tests/smoke/event_rule_engine/basic_or_rule.php`
4. 若 suite 本質上屬於 module reaction 驗證，仍可放在同一 domain 下，但應以情境命名，而不是沿用 legacy script 名稱
5. 相容期內可暫時保留目前已搬移的 flat 檔名，但 plan 上的正式目標路徑應以 folderized canonical path 為準

這個正名要求的目的不是美觀，而是避免之後 `www/tests/smoke/` 再次長成另一個扁平化的 `scripts/` 歷史包袱。

### Stage 2: 規劃 bootstrap、adapter 與相容層

目標：
- 定義測試如何在不複製 bootstrap 程式的情況下進入 F3CMS runtime
- 定義 framework-specific adapter 的落點
- 定義 `www/f3cms/scripts/` 的過渡期 wrapper 策略

主要工作：
- 收斂 `www/tests/bootstrap/` 的最小責任
- 收斂 `www/tests/adapters/f3cms/` 的最小責任
- 決定既有 smoke scripts 是搬移、包 wrapper，還是分期替換

輸出：
- bootstrap / adapter 契約
- scripts 過渡策略

#### Stage 2 收斂結果

##### A. `www/tests/bootstrap/` 的最小責任

`www/tests/bootstrap/` 第一版只承接「測試執行共用流程」，不直接知道 F3CMS 細節。

最小責任固定如下：

1. 提供 smoke suite 可共用的執行入口包裝
2. 統一成功 / 失敗 exit code 與輸出格式
3. 統一例外捕捉與結果封裝，避免每支 suite 自行重複 try/catch
4. 提供 suite 讀取 fixture、組裝輸出摘要等 framework-agnostic helper

`www/tests/bootstrap/` 第一版不應承接：

- `vendor/autoload.php`
- `libs/Autoload.php`
- `libs/Utils.php`
- `config.php`
- `Base::instance()`
- CLI route 或 Lab reaction 的參數協定

也就是說，`bootstrap/` 的責任是「如何跑一支 suite」，不是「如何啟動 F3CMS」。

##### B. `www/tests/adapters/f3cms/` 的最小責任

`www/tests/adapters/f3cms/` 第一版只承接 F3CMS runtime 接線。

根據現有 `www/f3cms/scripts/*.php` 與 `www/cli/index.php` 的重複模式，adapter 應集中封裝以下動作：

1. 載入 `vendor/autoload.php`
2. 載入 `libs/Autoload.php`
3. 載入 `libs/Utils.php`
4. 取得 `Base::instance()`
5. 載入 `config.php`
6. 視需要補上測試執行所需的最小 environment 校正

第一版 adapter 的邊界：

- adapter 可以回傳已完成 bootstrap 的 F3 / runtime context
- adapter 可以提供少量 F3CMS-specific helper，例如載入 options 或建立測試用 context
- adapter 不直接保存 smoke 斷言
- adapter 不負責 suite 發現、CLI 路由或 Lab UI 呈現

因此第一版的分層應是：

- smoke suite 定義情境與斷言
- bootstrap 負責 suite 執行流程
- adapter 負責接上 F3CMS runtime

##### C. `www/tests/bootstrap/` 與 `www/tests/adapters/f3cms/` 的呼叫順序

第一版呼叫鏈固定為：

1. smoke suite 宣告情境
2. suite 透過 `bootstrap/` 進入共用執行流程
3. 若 suite 需要 F3CMS runtime，再由 `bootstrap/` 呼叫 `adapters/f3cms/`
4. adapter 完成 F3CMS bootstrap 後把 context 回交給 suite

這個順序的意義是：

- suite 不直接 include F3CMS bootstrap 檔
- 將來若不是 F3CMS，而是別的 framework，只替換 adapter，不改 smoke contract
- 不讓每支 smoke 再次複製目前 `scripts/` 中相同的 require 區塊

##### D. `www/f3cms/scripts/` wrapper 的第一版 compatibility contract

第一版 wrapper 契約正式固定如下：

1. wrapper 只保留舊路徑與舊命令相容性
2. wrapper 只允許做「參數轉發、載入對應 suite、傳回 exit code」
3. wrapper 不再擁有獨立 bootstrap
4. wrapper 不再保存 fixture、測試資料或斷言邏輯
5. wrapper 不新增新的 smoke 專屬 helper

換句話說，像目前 `www/f3cms/scripts/workflow_engine_smoke.php` 與 `www/f3cms/scripts/event_rule_engine_smoke.php` 這類檔案，在重構後應收斂成：

- 舊檔名仍可存在一段時間
- 內容只剩 thin wrapper
- 真正 suite 本體移到 `www/tests/smoke/`
- 真正 runtime bootstrap 走 `www/tests/adapters/f3cms/`

##### E. 第一版相容層的禁止事項

為避免把舊結構原封不動搬到新目錄，第一版再明確禁止以下做法：

- 在 `www/tests/smoke/` 檔頭直接複製 `require_once dirname(__DIR__) . '/vendor/autoload.php'` 這類初始化片段
- 在 wrapper 裡重新長出獨立 bootstrap 差異
- 讓 CLI 或 Lab 繞過 `www/tests/` 直接執行舊 `scripts/` 本體
- 把 fixture 讀取、結果輸出、runtime 接線全部混在單一 smoke 檔案中

##### F. Stage 2 對後續搬移的直接影響

完成 Stage 2 後，第一批搬移的標準會變成：

- 先找現有最典型、bootstrap 重複最明顯的 smoke script
- 把 suite 本體搬到 `www/tests/smoke/`
- 把 F3CMS 初始化搬到 `www/tests/adapters/f3cms/`
- 舊 `scripts/` 保留 thin wrapper

這表示下一步已不再是抽象討論「要不要分層」，而是可以進入 Stage 3，決定 CLI / Lab 要如何安全接到這套新分層。

### Stage 3: 規劃 CLI 與 Lab 的整合方式

目標：
- 讓 CLI 與 Lab 能與新的 `www/tests/` 結構整合，但不重新吞回 smoke 本體

主要工作：
- 收斂 CLI 是否要提供 smoke runner 命令
- 收斂 Lab 是只顯示結果，還是允許受控觸發
- 明確禁止把 smoke 細節塞回 CLI route 或 Lab reaction

輸出：
- CLI integration contract
- Lab integration contract

#### Stage 3 收斂結果

##### A. `www/cli/index.php` 的第一版 integration contract

根據目前 `www/cli/index.php` 的責任，它本質上是 cronjob / command gateway，而不是測試系統容器。

因此第一版正式決策如下：

1. 不在第一版為 CLI 增加通用 smoke runner 命令
2. 不讓 CLI route 直接 include `www/tests/smoke/*.php`
3. 不讓 CLI route 直接 include `www/tests/adapters/f3cms/*`
4. CLI 若未來需要對 smoke 提供入口，必須走明確白名單命令，且只做轉發，不承載 suite 本體

這個決策的理由：

- 目前 CLI 已有自己的環境設定與 cronjob 角色
- 若現在把 smoke runner 直接塞進 CLI，等於把測試 orchestration 與正式 command gateway 混在一起
- 這會讓 `www/tests/` 雖然名義上抽出，實際上仍被 CLI route 結構綁死

所以第一版 CLI 的整合契約固定為：

- CLI 可以在未來提供「呼叫某個測試入口」的 command
- 但 CLI 不擁有 smoke suite 定義
- CLI 不複製 bootstrap
- CLI 不組裝 fixture
- CLI 不自己決定 suite discovery 規則

##### B. `www/f3cms/modules/Lab/reaction.php` 的第一版 integration contract

根據目前 `Lab` 既有用途，它偏向 staff 可見的診斷入口，而不是完整測試執行器。

因此第一版正式決策如下：

1. `Lab` 不直接執行 smoke suite 本體
2. `Lab` 不直接 include `www/tests/smoke/*.php`
3. `Lab` 不直接 include `www/tests/adapters/f3cms/*`
4. `Lab` 第一版只允許承接結果摘要、suite 清單或有限度診斷資訊

第一版 `Lab` 可接受的整合形式：

- 顯示最近一次 smoke 結果摘要
- 顯示目前已登記或已存在的 suite 名稱
- 顯示某些 suite 的最後執行時間、成功失敗狀態或輸出摘要

第一版 `Lab` 不可接受的整合形式：

- 任意從 web 入口觸發整包 smoke
- 把 suite 執行流程直接寫進 reaction
- 把 F3CMS test adapter 邏輯塞回 `Lab`

##### C. CLI / Lab 與 `www/tests/` 的第一版關係

第一版把兩者與 `www/tests/` 的關係固定如下：

- `www/tests/` 是測試本體所在
- CLI 是可選的 command gateway
- Lab 是可選的結果查看 / 診斷入口

也就是說：

- `www/tests/` 可以完全獨立存在，不依賴 CLI 或 Lab 才能執行
- CLI / Lab 是消費者，不是測試本體的宿主
- 任何 future integration 都只能指向 `www/tests/`，不能反向把 suite 長回 CLI / Lab

##### D. 第一版觸發策略

第一版正式決定：

1. 標準執行方式仍是直接跑 `www/tests/smoke/<suite>.php`
2. CLI 不負責成為標準執行路徑
3. Lab 不負責成為標準執行路徑
4. 若未來需要自動化或管理入口，再由 Stage 4 或後續輪次決定額外 command / report 機制

這代表第一版只先完成結構重整，不同時擴張為測試平台產品化。

##### E. Stage 3 對 Stage 4 的直接影響

完成 Stage 3 後，搬移順序可更單純地依照 smoke 本體與 wrapper 相容性安排，而不必被 CLI / Lab 改造綁住。

因此 Stage 4 的焦點就能明確收斂為：

- 先搬哪些 smoke / fixture
- 哪些舊 `scripts/` 保留 thin wrapper
- Docker 驗證命令怎麼切換到 `www/tests/`
- 相容期何時結束

### Stage 4: 規劃搬移順序與驗收口徑

目標：
- 把搬移順序、相容期與驗收方式拆成可執行步驟

主要工作：
- 定義先搬哪些 smoke / fixture
- 定義哪些舊路徑可暫時保留 wrapper
- 定義 Docker 驗證命令如何切到 `www/tests/`
- 定義這次重構的驗收條件與風險記錄方式

輸出：
- 第一版搬移順序
- 驗收與回退基線

#### Stage 4 收斂結果

##### A. 第一批搬移標的

第一批不追求把全部 `www/f3cms/scripts/*smoke*.php` 一次搬完，而是先選三支最具代表性的 suite：

1. `event_rule_engine_smoke.php`
2. `workflow_engine_smoke.php`
3. `workflow_engine_instance_api_smoke.php`

選這三支的理由：

- `event_rule_engine_smoke.php`：代表新近建立、純 engine 導向的 smoke，最能驗證新 `tests/bootstrap + adapters/f3cms` 分層是否成立
- `workflow_engine_smoke.php`：代表讀 definition / 輸出摘要的基本 smoke，可作為最簡單的 wrapper 遷移樣板
- `workflow_engine_instance_api_smoke.php`：已被 WorkflowEngine spec 明確引用，代表 module-local flow JSON 與 instance API 的主契約，不宜晚搬

第一批刻意不先搬的類型：

- 直接寫 DB seed / rollback 的 press 類 smoke
- 含較多 helper function 與多路徑判定的 workflow 執行 smoke
- 尚未被新測試分層驗證過的複雜 parallel / branch / rollback 場景

##### B. `www/f3cms/scripts/` 的第一版 thin wrapper 保留名單

第一版策略不是立刻刪除全部舊路徑，而是區分「第一批搬移」與「過渡期保留」。

第一版 thin wrapper 保留名單先固定為目前所有 `*smoke.php`：

- `event_rule_engine_smoke.php`
- `workflow_engine_smoke.php`
- `workflow_engine_instance_api_smoke.php`
- `workflow_engine_definition_validation_smoke.php`
- `workflow_engine_instance_smoke.php`
- `workflow_engine_projection_smoke.php`
- `workflow_engine_psc_smoke.php`
- `workflow_engine_sjse_edge_smoke.php`
- `workflow_engine_sjse_edge_execution_smoke.php`
- `workflow_engine_parallel_join_smoke.php`
- `workflow_engine_core_judgment_smoke.php`
- `workflow_engine_role_guard_smoke.php`
- `workflow_engine_press_smoke.php`
- `workflow_engine_press_rollback_smoke.php`

其中第一批搬移完成後：

- 上述三支第一批標的改為 thin wrapper
- 其餘尚未搬移者暫時維持原狀，直到下一批次再處理

這樣可以避免一次觸碰太多 smoke，降低回歸面積。

##### C. Docker 驗證命令切換基線

第一版正式切換規則如下：

舊命令形態：

- `docker compose exec -T php-fpm php /var/www/f3cms/scripts/<script>.php`

新主命令形態：

- `docker compose exec -T php-fpm php /var/www/tests/smoke/<suite>.php`

相容期規則：

1. 第一批 suite 搬移後，`www/tests/smoke/` 路徑成為 source of truth
2. 舊 `www/f3cms/scripts/*.php` 僅作為相容 wrapper
3. 文件、spec 與後續驗證記錄應優先改寫為新命令
4. 若仍需保留舊命令，只能視為 alias，而不是主驗證口徑

##### D. 第一版回退口徑

第一版回退不使用大範圍還原，而是維持小範圍回退原則：

1. 若新 `www/tests/smoke/<suite>.php` 執行異常，先允許保留同名舊 wrapper 命令作為暫時 fallback
2. 若 adapter 分層造成 bootstrap 差異，優先修正 `www/tests/adapters/f3cms/`，而不是把 bootstrap 邏輯搬回 wrapper
3. 若文件引用尚未切完，允許短期保留雙命令記錄，但 source of truth 仍是 `www/tests/`
4. 不因單一 suite 搬移失敗就回退整個 `www/tests/` 結構方向

##### E. wrapper 退場條件與實際移除時機

wrapper 不應無限期保留；第一版規劃在這裡補上明確退場條件與實際移除時機。

wrapper 退場條件固定如下：

1. 對應 suite 已有 `www/tests/` canonical path，且該 canonical path 已完成 Docker 驗證
2. `history.md`、`plan.md`、`check.md`、相關 feature spec 與驗證記錄都已改以 `www/tests/` canonical path 為主
3. CLI、Lab、既有 shell script、文件範例與其他自動化入口不再以 `www/f3cms/scripts/*.php` 作為唯一入口
4. wrapper 本身已退化為單純轉發，不再承載任何獨立 bootstrap、fixture、helper 或 assertion
5. 已完成一次「無 wrapper 仍可執行」的整體驗證，至少涵蓋 Docker 主命令與主要引用路徑

實際移除時機固定如下：

1. 不在單支 suite 剛搬移完成的同一輪立即刪除 wrapper
2. 不在仍有未搬移 smoke 的中途批次刪除整包 wrapper
3. 應在最後一支 smoke 完成搬移、canonical naming 完成、文件與呼叫端切換完成之後，另外安排一輪專門的 wrapper retirement `(done)`
4. 該輪 `(done)` 的目標應是批次移除 `www/f3cms/scripts/*smoke*.php` wrapper、改寫殘餘引用，並以 Docker 驗證沒有舊路徑依賴

換句話說：wrapper 的移除是已規劃的正式工作，但它不是每輪搬移順手做掉的附帶清理，而是要在 TestMode 結構穩定後，用一個獨立 stage 收尾。

##### F. 第一版驗收基線

第一版真正進入 `(done)` 時，最小驗收口徑固定如下：

1. 已建立 `www/tests/` 所需最小目錄與 bootstrap / adapter 骨架
2. 第一批三支 suite 已移入 `www/tests/smoke/`
3. 對應舊 `www/f3cms/scripts/*.php` 已縮成 thin wrapper
4. Docker 驗證至少能用新命令成功執行第一批 suite
5. 相關 spec / 文檔中的主命令已切到 `www/tests/smoke/`

##### G. Stage 4 完成後的承接點

完成 Stage 4 後，TestMode 的第一版 `plan` 已具備完整搬移基線。

所以下一步不再是補文件，而是進入第一輪 `(done)`：

- 建立 `www/tests/` 最小骨架
- 先搬第一批三支 suite
- 讓對應 `scripts/` 退化成 thin wrapper
- 用 Docker 驗證新主命令

### 第一輪 `(done)` 完成結果

本輪已完成以下實作：

1. 建立 `www/tests/bootstrap/smoke.php`，統一 smoke suite 的輸出、fixture 載入與例外封裝
2. 建立 `www/tests/adapters/f3cms/bootstrap.php`，集中 F3CMS runtime bootstrap
3. 建立第一個 fixture：`www/tests/fixtures/event_rule_engine/basic_or_rule.json`
4. 搬移以下三支 suite 到 `www/tests/smoke/`
	- `event_rule_engine_smoke.php`
	- `workflow_engine_smoke.php`
	- `workflow_engine_instance_api_smoke.php`
5. 將對應 `www/f3cms/scripts/` 三支檔案縮成 thin wrapper
6. 已用 Docker 驗證：
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/event_rule_engine_smoke.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/workflow_engine_smoke.php`
	- `docker compose exec -T php-fpm php /var/www/tests/smoke/workflow_engine_instance_api_smoke.php`
	- `docker compose exec -T php-fpm php /var/www/f3cms/scripts/event_rule_engine_smoke.php`

這代表第一版主路徑已從 `www/f3cms/scripts/` 正式切到 `www/tests/smoke/`，且 wrapper 相容層已開始生效。

## Current Next Step

下一步應繼續 `(Optimization)`：壓縮 `history.md` 並確認是否還有需要回寫到共享文件的封存前缺口。

## Naming And Retirement Follow-up

既有兩個明確收尾項目前都已完成：

1. canonical naming 已完成，所有 smoke 主路徑都已收斂到 `www/tests/smoke/<domain>/*.php`
2. wrapper retirement 已完成，`www/f3cms/scripts/*smoke*.php` 過渡期檔案已批次移除

也就是說，TestMode 先前規劃的正式收尾順序已全部落地；目前承接點不再是路徑切換，而是回到 `check` 確認第一版完成邊界與是否進入 `(Optimization)`。

### Canonical Naming 第一批 `(done)` 基線

為避免一次改動所有 suite 導致引用面過大，第一批 canonical naming `(done)` 先固定如下：

1. 只先處理 `workflow_engine` domain
2. 只先處理近期仍由 TestMode 直接承接、且代表不同驗證形狀的五支 suite：
	- `www/tests/smoke/workflow_engine_press_smoke.php` -> `www/tests/smoke/workflow_engine/press.php`
	- `www/tests/smoke/workflow_engine_press_rollback_smoke.php` -> `www/tests/smoke/workflow_engine/press_rollback.php`
	- `www/tests/smoke/workflow_engine_parallel_join_smoke.php` -> `www/tests/smoke/workflow_engine/parallel_join.php`
	- `www/tests/smoke/workflow_engine_core_judgment_smoke.php` -> `www/tests/smoke/workflow_engine/core_judgment.php`
	- `www/tests/smoke/workflow_engine_sjse_edge_execution_smoke.php` -> `www/tests/smoke/workflow_engine/sjse_edge_execution.php`
3. 第一批不先碰 `event_rule_engine_smoke.php`，也不先碰 `workflow_engine_instance_api_smoke.php` 這類已被其他 feature spec 直接引用的路徑，避免同一輪把 TestMode 與其他 spec 的引用更新綁在一起

### Canonical Naming 第二批 `check` 基線

第一批完成後，第二批 canonical naming 已進一步收斂如下：

1. 第二批仍只處理 `workflow_engine` domain，不先碰 `event_rule_engine`
2. 第二批只處理目前沒有外部 feature spec 直接引用的七支 flat smoke：
	- `www/tests/smoke/workflow_engine_smoke.php` -> `www/tests/smoke/workflow_engine/definition.php`
	- `www/tests/smoke/workflow_engine_definition_validation_smoke.php` -> `www/tests/smoke/workflow_engine/definition_validation.php`
	- `www/tests/smoke/workflow_engine_projection_smoke.php` -> `www/tests/smoke/workflow_engine/projection.php`
	- `www/tests/smoke/workflow_engine_instance_smoke.php` -> `www/tests/smoke/workflow_engine/instance.php`
	- `www/tests/smoke/workflow_engine_psc_smoke.php` -> `www/tests/smoke/workflow_engine/psc.php`
	- `www/tests/smoke/workflow_engine_sjse_edge_smoke.php` -> `www/tests/smoke/workflow_engine/sjse_edge.php`
	- `www/tests/smoke/workflow_engine_role_guard_smoke.php` -> `www/tests/smoke/workflow_engine/role_guard.php`
3. `workflow_engine_instance_smoke.php` 不再視為外部 spec 直接引用路徑；本輪搜尋只確認 `workflow_engine_instance_api_smoke.php` 與 `event_rule_engine_smoke.php` 有明確外部 spec 綁定
4. 因此 `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 應保留到後續跨 spec 同步批次，避免第二批 `(done)` 一次擴張到 `EventRuleEngine` / `WorkflowEngine` 文件更新
5. 第二批 `(done)` 的驗證策略仍沿用第一批：先驗證新的 canonical path，再驗證對應舊 wrapper 相容路徑

### 最後一批跨 Spec `check` 基線

第二批完成後，剩餘 canonical naming 已收斂為只剩兩條帶外部 spec 引用的主路徑：

1. `www/tests/smoke/event_rule_engine_smoke.php` 應改為 `www/tests/smoke/event_rule_engine/basic_or_rule.php`
2. `www/tests/smoke/workflow_engine_instance_api_smoke.php` 應改為 `www/tests/smoke/workflow_engine/instance_api.php`
3. 這一批不能只改 TestMode；必須同輪同步更新 `EventRuleEngine` / `WorkflowEngine` 文件中的主路徑敘述，否則會形成新的跨 spec 文件 drift
4. 這一批完成後，才應回到 `check`，重新確認是否還有任何 spec、命令範例或程式入口把舊 flat path 當 source of truth
5. 只有在上述引用面都已切到 canonical path 後，wrapper retirement `(done)` 才具備進場條件

### Wrapper Retirement `check` 基線

最後一批 canonical naming 完成後，wrapper retirement 的進場條件已收斂為以下五點：

1. 工作區內不再有 CLI、Lab、shell script、README 或其他現行程式入口直接依賴 `www/f3cms/scripts/*smoke*.php`
2. 剩餘提到 wrapper 的文件若只是歷史敘述，可保留；若描述現況，需與 retirement 同輪同步回寫
3. `EventRuleEngine` 與 `WorkflowEngine` owner spec 不得再把 wrapper 表述為現行主路徑的一部分
4. wrapper 本身都必須已退化為單行 `require` 轉發，不承載任何獨立邏輯
5. 只有在上述條件同時成立時，才應進入專門的 wrapper retirement `(done)` 批次

### Wrapper Retirement `(done)` 驗證口徑

wrapper retirement `(done)` 完成時，最小驗證口徑固定如下：

1. `www/f3cms/scripts/*smoke*.php` 已全部移除
2. `history.md`、`plan.md`、`check.md` 與 owner spec 中對 wrapper 的現況描述已同步更新
3. Docker 已在無 wrapper 狀態下重新驗證代表性 canonical smoke 主命令
4. `www/tests/smoke/<domain>/*.php` 已成為唯一保留的 smoke 執行入口

### `(Optimization)` 進場結論

在 wrapper retirement 完成後，TestMode 第一版的 `(Optimization)` 進場條件已確認成立：

1. `check.md` 已確認主要實作完成，包含 smoke 搬移、canonical naming 與 wrapper retirement
2. `check.md` 已確認主要驗收完成，且 Docker 已驗證 canonical smoke 主命令
3. 目前沒有阻擋 release 或主流程承接的關鍵缺口；剩餘只是不再影響功能正確性的文件整理與規則沉澱
4. 下一步已收斂為建立 `optimization.md`、整理穩定名詞與回寫共用規則，而不是再補功能或重做結構

### Canonical Naming 的 Docker 命令切換方式

第一批 canonical naming `(done)` 的 Docker 驗證應遵循以下順序：

1. 先驗證新的 canonical path，例如 `docker compose exec -T php-fpm php /var/www/tests/smoke/workflow_engine/press.php`
2. 再驗證舊 `www/f3cms/scripts/*.php` wrapper 仍能正常轉發到新 canonical path
3. flat `www/tests/smoke/*.php` 若需要短期保留，僅能作為過渡 alias 驗證，不得再作為文件中的主命令

### Canonical Naming 的文件更新順序

第一批 canonical naming `(done)` 應使用以下文件更新順序：

1. 先更新 TestMode 的 `history.md`、`plan.md`、`check.md`
2. 再更新直接引用該 smoke path 的 Docker 驗證記錄與命令範例
3. 最後才更新其他 feature spec 中對應的 path 引用，例如 `WorkflowEngine` 或 `EventRuleEngine` 文件

這樣可以先確保 TestMode 作為搬移主 spec 的 source of truth 穩定，再向外同步其他文件。