### 第 14 輪討論結果
1. 本輪直接承接第 13 輪留下的唯一下一步，已完成 WorkflowEngine 第一版的共享文件同步：將 runtime-only 主契約、module-owned workflow log、Reaction 整合方式與 retired API 邊界回寫到 `document/` 下的 glossary、guides 與 reference。
2. 本輪同時修正 `plan.md` 內少量仍偏向舊 instance-persistence 語氣的段落，現在已明確區分「邏輯上的 instance / runtime context」與「WorkflowEngine 不再擁有專屬 runtime persistence」這兩個層次。
3. `check.md` 也已同步收尾：文件同步項目已勾選完成，Current Next Step 已不再停留在「先補 glossary / guides / references」，而是可以正式承接 `(Optimization)`。
4. 這一步沒有新增需要回退的前置假設，也沒有重開 feature implementation scope；本輪只做規則沉澱、文件同步與封存前準備，符合 FDD 的最後階段邊界。
5. 依目前文件與既有 Docker 驗證結果，WorkflowEngine 已可由 `check` 後承接點正式推進到 `(Optimization)`；後續剩餘工作已收斂為 `optimization.md` 完整化與 `history.md` 壓縮整理。
6. 最新討論的下一步選項：先只做一件事，沿用本輪已完成的共享文件同步，補齊 `optimization.md` 的穩定規則摘要與封存前注意事項，然後進行 `history.md` 壓縮整理。

### 第 13 輪討論結果
1. 本輪不是回頭重做 WorkflowEngine 設計，而是針對 `idea.md` 的呈現方式做文件校正：保留前段早期 XML 解析報告式寫法，同時補強後段 JSON 版本的定位，避免讀者把兩者誤認為互斥方案。
2. 本輪已把「為何由 XML 轉換到 JSON」明文化：XML 保留作 legacy 規格與歷史語意分析材料，JSON 則作為第一版 WorkflowEngine 的直接承接格式，方便 module-first 整合、runtime 載入與現代化 payload 表達。
3. 本輪同時修正 `idea.md` 內一處早期過渡性假設：不再把「是否轉成 JSON」描述為尚未定案的候選題，而是明確回寫目前 discuss 已定案的來源格式策略，避免 `idea.md` 內部自相矛盾。
4. 這一步沒有新增需要回退的前置假設，也沒有改變目前所處 stage；WorkflowEngine 仍停在 `check` 後、尚未進入 `(Optimization)` 的承接點。
5. 最新討論的下一步選項：先只做一件事，沿用 `check.md` 的 current next step，補齊 runtime-only 主契約、module-owned log、retired API 邊界等穩定規則的文件同步清單，再判定是否進入 `(Optimization)`。

### 第 12 輪討論結果
1. 本輪直接承接第 11 輪留下的唯一下一步，已盤點並處理 `WorkflowEngine.php` 內剩餘的 legacy instance helper：`getOrCreateInstance()`、`getOrCreateInstanceFromDefinition()`、`projectInstance()` 現在都明確降級為 retired API，不再保留任何看似可用的 `tbl_workflow_instance` 入口。
2. 本輪同時移除了 `WorkflowEngine.php` 內對 `tbl_workflow_instance` / `tbl_workflow_instance_trace` 的最後常數與私有 DB helper 殘留，因此 engine 目前只保留 definition-side 的相容載入能力；runtime contract 已完全收斂為「module 提供 workflow JSON + runtime context」。
3. 盤點結果顯示這些 legacy helper 在 repo 內已無外部呼叫點，因此本輪不需要回退前面 module-facing 整合結論；這一步只是把前一輪已完成的 runtime-only 主契約正式收尾。
4. 驗證基準仍沿用 Docker 容器；本輪至少應再確認 `WorkflowEngine.php` 無語法錯誤，且不再殘留 `tbl_workflow_instance` / `tbl_workflow_instance_trace` 相關程式入口。
5. 目前沒有新增需要回退的前置假設；若未來真的發現 repo 外部仍有隱性相依，需回退的是本輪「把這些 public methods 改成 retired API」這一段，而不是回退到 definition source 或 Press transaction 的前面結論。
6. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；阻塞內容已從 engine runtime persistence 殘影，縮小為文件同步收尾，例如 glossary / guides / references 是否需要承接這組穩定規則。
7. 最新討論的下一步選項：先只做一件事，依 `check.md` 補齊「穩定規則與術語的文件同步清單」，決定哪些內容應回寫到 glossary、guides、references，完成後再判定是否可進入 `(Optimization)`。

### 第 11 輪討論結果
1. 本輪直接承接第 10 輪留下的唯一下一步，已把 `WorkflowEngine::transit()` 的實際執行路徑改成只吃 runtime context，不再要求 `instance_id`，也不再寫入 `tbl_workflow_instance` / `tbl_workflow_instance_trace`。
2. 本輪同時把 `transitByInstanceId()` 與 `syncInstanceState()` 明確降級為 retired API，避免 module-facing 路徑再誤用舊 instance table persistence；parallel join 也已改為從 runtime `trace_rows` 判定，不再讀 `tbl_workflow_instance_trace`。
3. 由於 `loadDefinition()` 原本仍會落回 `tbl_workflow_definition`，本輪也一併補上 file-based definition source：`Press` 走 module-local `flow.json`，`PSC_CHAIN` / `SJSE_EDGE` 走 smoke fixture JSON，因此 definition 驗證與 edge-case smoke 已能在沒有 `tbl_workflow_*` 資料表的情況下通過。
4. 驗證結果如下：definition smoke、projection、role guard、publish/offline lifecycle、parallel join、rollback、branch、terminate 等腳本皆已在 Docker 內通過；本機 PHP 因 Homebrew ICU 動態庫缺失無法直接執行，因此本輪驗證基準已改以專案容器為準。
5. 目前沒有新增需要回退的前置假設；需要保留的限制是 `getOrCreateInstance()`、`getOrCreateInstanceFromDefinition()`、`projectInstance()` 等舊 helper 仍留在 `WorkflowEngine.php`，雖然已無現行呼叫點，但還沒有正式移除或改寫。
6. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；阻塞內容已從 transit 執行路徑縮小為「是否要把剩餘的 legacy instance helper 完整退場，讓 `libs/WorkflowEngine` 不再保留 `tbl_workflow_*` 依賴殘影」。
7. 最新討論的下一步選項：先只做一件事，盤點並處理 `getOrCreateInstance()`、`getOrCreateInstanceFromDefinition()`、`projectInstance()` 與相關常數/說明，決定是正式移除、改寫成 runtime-only helper，或明確標示為 legacy compatibility API。

### 第 10 輪討論結果
1. 本輪直接承接第 9 輪留下的唯一下一步，實際驗證 `Press` 的 publish / offline 兩條路徑；結果兩者都已能透過目前的 module-facing 整合路徑正確寫入 `tbl_press_log`，並同步更新 `tbl_press.status`。
2. 驗證結果如下：publish 路徑寫入 `action_code = PUBLISH`、`old_state_code = Draft`、`new_state_code = Published`；offline 路徑寫入 `action_code = OFFLINE`、`old_state_code = Published`、`new_state_code = Offlined`，而 `tbl_press.status` 也與 log 完全一致。
3. 因此目前沒有新增需要回退的前置假設；`fPress::published($req)` 仍可安全納入同一個 transaction，故不需要回退 `plan.md` 中「`Press/reaction.php` 最小 transaction 整合方案」這一段。
4. 目前已完成的只是 `Press` 這條 module-facing 路徑驗證，不代表 `WorkflowEngine::transit()` 的舊 runtime persistence 依賴已解決；`tbl_workflow_instance` / `tbl_workflow_instance_trace` 仍只是不再被 `Press` 這條路徑使用。
5. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；阻塞內容已從 `Press` 整合驗證縮小為「是否要把 `WorkflowEngine::transit()` 本身也回收成不依賴 instance table 的版本」。
6. 最新討論的下一步選項：先只做一件事，收斂 `WorkflowEngine::transit()` 與相關 helper，讓 engine 的 transition 執行路徑也不再依賴 `tbl_workflow_instance` / `tbl_workflow_instance_trace`。

### 第 9 輪討論結果
1. 本輪承接第 8 輪指定的唯一下一步，先驗證 `Press` 端實際可用的 module-owned log 落點；目前已確認 `Press` 這個概念上的 `press_log` 在 F3CMS 的實際落地表名為 `tbl_press_log`，並把外鍵欄位從 `press_id` 改為 `parent_id`，讓不同 module 的 log schema 能維持一致。
2. 已回退的範圍是 `Press` 專屬 log 命名與欄位策略；仍沿用的結論不變：WorkflowEngine 位於 `libs/`、module 先取得 workflow JSON、workflow audit trail 由 module-owned table 承接，而且 `Press/reaction.php` 仍是第一個 module-facing 整合點。
3. 本輪也確認 `WorkflowEngine::transit()` 目前仍硬依賴 `tbl_workflow_instance` / `tbl_workflow_instance_trace`，不適合直接用在現在的 `Press` 路徑；因此 `Press/reaction.php` 已改為使用 definition projection 與 `canTransit()` 做 transition 判定，避免再碰已刪除的 WorkflowEngine 專屬資料表。
4. `Press/reaction.php` 目前的最小落地順序已變成：transaction 外先讀 `tbl_press` 與 `flow.json`、用 engine 判定合法 transition；transaction 內先寫 `tbl_press_log`，再寫回 `tbl_press.status`，兩者成功才 commit，否則 rollback。
5. `tbl_press_log` 也已補上 `action_code`、`old_state_code`、`new_state_code` 等 workflow audit 欄位，讓 module-owned log 不只記人與時間，也能記錄新舊狀態。
6. 最新討論的下一步選項：先只做一件事，實際驗證 `Press` 的 publish / offline 路徑是否能正確寫入 `tbl_press_log` 並更新 `tbl_press.status`，再決定是否要把 `WorkflowEngine::transit()` 本身也回收成不依賴 instance table 的版本。

### 第 8 輪討論結果
1. 本輪承接第 7 輪指定的唯一下一步，已把 `Press/reaction.php` 的最小 transaction 整合方案補進 `plan.md`：現在不只要求 `press_log` 與 `tbl_press.status` 共用 transaction，也明確定義了 transaction 外先讀取現況與做 engine 判定、transaction 內先寫 `press_log` 再寫回業務表的最小順序。
2. 本輪沒有回到 `idea.md` 重新討論，也沒有重談 `Press/reaction.php` 是否為第一個 module-facing 替換點；這些結論全部沿用前輪，這一步只補 transaction 細節。
3. 本輪同時把 `check.md` 的 `Current Next Step` 與 Stage 5 驗收點對齊目前進度，避免下一輪還停在較舊的「先具體化 `press_log` 欄位與時機」敘述。
4. 目前若還有前置假設需要回退，會是 `fPress::published($req)` 是否能安全納入同一個 transaction；若後續驗證發現不行，需回退的是 `plan.md` 中「`Press/reaction.php` 最小 transaction 整合方案」這一段，而不是回退到 WorkflowEngine 的分層或 module-owned log table 結論。
5. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；阻塞內容已縮小為「把本輪 transaction 方案正式落成程式與驗證」。
6. 最新討論的下一步選項：先只做一件事，依 `plan.md` 的 transaction 方案修改 `Press/reaction.php`，把 workflow transition、`press_log` 寫入與 `tbl_press.status` 寫回放進同一個 transaction，並以 `check.md` 驗證。

### 第 7 輪討論結果
1. 本輪在承接第 6 輪內容時，額外發現 `plan.md` 的「Press 最小整合落地狀態」還殘留一個過早成立的假設：它把 `WorkflowEngine` runtime 寫入與 `tbl_press.status` 寫回已包在同一個 transaction 視為已完成，但這段敘述尚未承接到 `press_log` 的新規格，因此需要回退。
2. 已回退的不是 WorkflowEngine 的主契約，也不是 module-owned log table 的方向；需要修正的是 `plan.md` 中該段進度敘述，避免下一輪誤以為 `press_log` 的 transaction 邊界已落地。
3. 目前仍沿用的結論不變：WorkflowEngine 位於 `libs/`、module 先取得 workflow JSON、workflow audit trail 由 module-owned log table 承接、`Press` 的下一步是把 `press_log` 與 `tbl_press` 的 transaction 方案具體化。
4. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；阻塞內容沒有改變，仍是 `Press/reaction.php` 的 transition、`press_log` 寫入與 `tbl_press.status` 寫回如何共用同一個 transaction。
5. 最新討論的下一步選項：先只做一件事，根據目前已收斂的 `press_log` 欄位與寫入時機，補出 `Press/reaction.php` 的最小 transaction 整合方案。

### 第 6 輪討論結果
1. 本輪承接第 5 輪指定的唯一下一步，已把 `Press` 的 `press_log` 最小策略補到 `plan.md` 與 `check.md`：不再只停留在「module 應有自己的 log table」這個抽象結論，而是明確寫出 `press_id`、`staff_id`、`action_code`、`old_state_code`、`new_state_code`、`created_at` 等最小欄位。
2. 本輪同時把 `press_log` 的寫入時機收斂為：WorkflowEngine 已完成 transition 判定且回傳成功結果後、但 `tbl_press.status` 尚未正式寫回前，先在同一個 transaction 內寫入 `press_log`，再寫回業務表。
3. 這一步沒有推翻任何既有前提；目前仍沿用的結論包括：WorkflowEngine 位於 `libs/`、module 先取得 workflow JSON、WorkflowEngine 不擁有專屬資料表、workflow audit trail 由 module-owned log table 承接。
4. 目前若還有前置假設需要回退，會是 `Press` 端是否已存在可沿用的既有 log 結構；若後續發現 `press_log` 並非正確落點，需回退的是 `plan.md` 中「Press 的 `press_log` 最小欄位策略」這一段，而不是回退到 `idea.md` 或重新討論 WorkflowEngine 分層。
5. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；阻塞內容已進一步縮小為「如何把 `Press/reaction.php` 的 transition、`press_log` 寫入與 `tbl_press.status` 寫回放進同一個 transaction」。
6. 最新討論的下一步選項：先只做一件事，根據本輪新增的 `press_log` 策略，補出 `Press/reaction.php` 的最小整合方案，明確定義 transaction 起點、log 寫入點與業務表寫回點。

### 第 5 輪討論結果
1. 本輪新增需求明確化了第 4 輪尚未補完的 module-owned trace 策略：當各 module 透過 WorkflowEngine 控制 flow 時，不是由 `libs/WorkflowEngine` 擁有共用 log table，而是由各 module 自己擁有對應的 log table，例如 `Press` 對應 `press_log`、`Order` 對應 `order_log`。
2. 這個新需求沒有推翻「WorkflowEngine 屬於 `libs/`、不應擁有專屬資料表」的前提；相反地，它把先前抽象的「trace 由 module 或既有業務資料承接」進一步收斂成更明確的 module-owned log table 方向。
3. 本輪同時把最小 log 欄位要求補進 spec：至少應能記錄 `staff_id`、操作時間、新舊狀態，以及必要的 `action_code`；其他如 remark、branch / parallel context 則可視 module 需求再補。
4. 因此需要同步承接修正的不是只有 `idea.md`，也包含 `plan.md` 與 `check.md`：Stage 3 的 runtime ownership 現在不只要回答「誰承接 trace」，還要回答「module 的 log table 最小欄位與寫入責任是什麼」。
5. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；阻塞內容已由一般性的 runtime / trace ownership，進一步收斂為「先補出 `Press` 的 `press_log` 最小策略，作為 module-owned workflow log 的第一個實例」。
6. 最新討論的下一步選項：先只做一件事，根據新版 `idea.md` / `plan.md` / `check.md`，補出 `Press` 的 `press_log` 最小欄位、寫入時機與 transaction 邊界，作為 WorkflowEngine 第一個 module-owned log 承接方案。

### 第 4 輪討論結果
1. 本輪已完成第 3 輪指定的單一步驟：回退並重寫 `plan.md` 與 `check.md` 中依賴 WorkflowEngine 專屬 schema / SQL / `tbl_workflow_*` 資料表的段落，改成「definition 由 module 提供、runtime context 由 module 或既有業務資料承接」的版本。
2. 目前已明確保留不變的主契約是：module 先取得 workflow JSON，再初始化 `libs/WorkflowEngine`；因此被回退的是持久化落點，不是 instance-based public API，也不是 `Press/flow.json` 這條 module-first 路徑。
3. `plan.md` 現在已把 Stage 3 改寫為 definition source 與 non-lib-owned runtime ownership 策略，不再把 `tbl_workflow_definition`、`tbl_workflow_instance`、`tbl_workflow_instance_trace` 之類的專屬表視為正式方案。
4. `check.md` 也已同步改寫：凡是原本以 WorkflowEngine 專屬資料表為前提的驗收項，現在都回退為未完成或待重新收斂；因此目前仍不能判定功能已可進入 `(Optimization)`。
5. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；但阻塞內容已從「回退 spec 中的專屬 schema 假設」前進到「補齊 module / 既有業務資料如何承接 runtime state 與 trace 的最小策略」。
6. 最新討論的下一步選項：先只做一件事，根據新版 `plan.md` / `check.md`，補出 `Press` 或既有業務資料如何承接目前 state、trace、operator 與 branch / parallel context 的最小策略，讓下一輪驗收不再依賴 WorkflowEngine 專屬資料表。

### 第 3 輪討論結果
1. 本輪新增了一個更上位的前提：約束改為「WorkflowEngine 屬於 `libs/` 層級，因此不應擁有專屬資料表」。依這個前提，已移除 `conf/mysql/docker-entrypoint-initdb.d/` 下的三個 WorkflowEngine 專屬 SQL 檔，並刪除既有六張 `tbl_workflow_*` 測試表。
2. 目前已確認資料庫中不再存在 `tbl_workflow_definition`、`tbl_workflow_definition_stage`、`tbl_workflow_definition_transition`、`tbl_workflow_definition_role_map`、`tbl_workflow_instance`、`tbl_workflow_instance_trace`；這一步代表先前的「lib 可帶專屬持久化表」假設已被正式推翻。
3. 因此前置假設需要回退的不是 `idea.md` 的主幹用例，而是 `plan.md` 中所有以 WorkflowEngine 專屬 schema / SQL / runtime table 為前提的段落，包含 Stage 3 的 schema 與持久化策略、`tbl_workflow_*` 資料表設計、definition-side / runtime-side SQL 落地、以及依賴這些表的 seed / smoke 驗證敘述。
4. 這個回退不影響「module 先取得 workflow JSON，再初始化 engine」這條主契約；被推翻的是持久化落點，不是 engine 位於 `libs/`、也不是 instance-based public API 的方向。
5. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；但阻塞內容已由「是否進入 `(Optimization)`」改成「先回退並重整 `plan.md` / `check.md` 中依賴專屬資料表的段落」。
6. 最新討論的下一步選項：先只做一件事，回退並重寫 `plan.md` 中 WorkflowEngine 專屬 schema / SQL 落地相關段落，改成不依賴 lib 專屬資料表的版本；在這一步完成前，不應進入 `(Optimization)`。

### 第 2 輪討論結果
1. 前一輪已明確定下的下一步是補一支 instance-based public API 的最小 smoke；這一步現在已完成，canonical 主路徑為 `www/tests/smoke/workflow_engine/instance_api.php`，且後續已在 TestMode 收尾輪完成 legacy wrapper retirement。
2. 這支 smoke 直接載入 `www/f3cms/modules/Press/flow.json`，並驗證 `new WorkflowEngine($workflowJson, $options)`、`validateDefinition()`、`project()`、`canTransit()` 與最小 `transit()` 路徑，目的就是把新契約固定下來，而不是再回頭討論 API 方向。
3. 目前沒有發現需要回退的前置假設：`Press` 以 module-local workflow JSON 驅動 engine、以及 instance API 可作為對 module 的主路徑，這兩個前提在這一步沒有被推翻。
4. 目前卡住的 flow 階段仍是 `check` 後、尚未進入 `(Optimization)` 的承接點；但與前一輪相比，原本缺的「最小契約驗證點」已補上，剩下的問題已收斂為是否足以正式進入最後一步。
5. 最新討論的下一步選項：不要擴到下一個 module，也不要現在開始寫 `optimization.md`；先依 `check.md` 與 `history.md` 重新做一次小型 gate review，判定 WorkflowEngine 是否已具備正式進入 `(Optimization)` 的條件。

### 第 1 輪討論結果
1. 目前 `idea.md`、`plan.md`、`check.md` 仍維持一致：WorkflowEngine 的主幹用例是由 module 先取得 workflow JSON，再初始化 `libs/WorkflowEngine`，並透過 instance API 執行驗證、判定與 transition。
2. 以 `check.md` 現況來看，Stage 1 到 Stage 6 的主要非文件性驗收都已完成；尚未完成的是文件同步項，以及進入 `(Optimization)` 前最後一個最小契約驗證點。
3. `plan.md` 已把第一個 module-facing 替換點定為 `www/f3cms/modules/Press/reaction.php`，而目前程式也已進一步把 Press 的流程來源固定為 module-local 的 `flow.json`；這代表真正待固定的不是方向，而是 instance-based public API 的獨立驗證。
4. 目前卡住的 flow 階段是 `check` 後、尚未進入 `(Optimization)` 的承接點；原因不是 schema、runtime 或 module integration 仍有大缺口，而是還缺一支最小 smoke 來把 `new WorkflowEngine($workflowJson, $options)` 這條新契約固定下來。
5. 現在不應整包重做 engine，也不應先擴到下一個 module-facing 路徑；這兩者都會超過目前最小承接步驟的範圍。
6. 最新討論的下一步選項：先新增一支直接載入 workflow JSON 並呼叫 `new WorkflowEngine($workflowJson, $options)` 的 smoke script，驗證 `validateDefinition()`、`project()`、`canTransit()` 或最小 transit 路徑是否可獨立成立；完成這一步後，再判定是否正式進入 `(Optimization)`。

### 第 0 輪討論結果
1. 目前 `idea.md`、`plan.md`、`check.md` 已對齊同一條主幹用例：`WorkflowEngine` 位於 `libs/`，可由 `Reaction` / `Outfit` / `Kit` 呼叫，module 需先取得 workflow JSON，再初始化 engine。
2. 第一版核心能力已在 spec 上完成收斂，包含 definition / instance 分離、role constant、rollback / branch / parallel / any-of、歷史 edge cases，以及至少一條實際 module integration。
3. 以 `check.md` 目前狀態來看，Stage 1 到 Stage 6 的主要非文件性驗收都已關閉；剩下未完成的是文件同步項，例如 `glossary.md` 與 guides / references 回寫。
4. 依 `check.md` 目前狀態，剩餘工作已收斂為文件同步與契約固定；這代表本功能已接近 `(Optimization)` 入口，但尚未進入最後一步，因此 `optimization.md` 目前應保持空白。
5. `plan.md` 已把第一個 module-facing 替換點定為 `www/f3cms/modules/Press/reaction.php`，並把唯一 public API 收斂成 `__construct($workflowJson, $options = [])`、`validateDefinition()`、`project()`、`canTransit()`、`transit()`。
6. 目前卡住的 flow 階段是 `check` 後、尚未進入 `(Optimization)` 的承接點；卡點不是設計未定，也不是 engine 能力不足，而是新契約雖已形成，但還缺一個最小驗證點把它固定下來。
7. 依目前 spec 文件，下一步不應整包重做，也不應再擴到下一個 module；最合理的小步驟是先補一支直接走 instance API 的 smoke，確認 `new WorkflowEngine($workflowJson, $options)` 這條路徑可以獨立驗證。
8. 最新討論的下一步選項：先新增一支直接載入 workflow JSON 並呼叫 `new WorkflowEngine($workflowJson, $options)` 的 smoke script，用來固定 instance-based public API 契約；這比先擴到下一個 module 更小，也能讓功能在進入 `(Optimization)` 前先補齊最後一個最小驗證點。