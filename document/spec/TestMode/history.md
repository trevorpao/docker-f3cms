### 第 29 輪討論結果
1. 本輪承接第 28 輪留下的 `(Optimization)` 承接點，已將 TestMode 第一版的穩定規則正式提升為共享 guide：新增 `document/guides/testmode_development_guide.md`，讓未來的 LLM 與工程師都有單一操作入口，而不是只從 spec 內部文件反推規則。
2. 本輪沒有發現新的程式 drift；既有 canonical smoke、Docker 驗證口徑與 wrapper retirement 狀態都維持穩定，因此本輪只做文件沉澱與導覽同步，不重開任何功能或驗證範圍。
3. 新 guide 固定了 TestMode 的 source of truth、責任鏈、命名規則、Docker 驗證契約、drift prevention 規則，以及未來 LLM / 工程師的閱讀順序，目的就是避免測試系統再次退回扁平命名、legacy scripts 或混合 bootstrap 的狀態。
4. 本輪也同步更新了 guides index、sidebar 與 TestMode optimization 記錄，使新 guide 成為正式可發現文件，而不是孤立檔案。
5. 因此本輪完成後，TestMode 在 `(Optimization)` 階段的共享規則回寫已更完整；目前剩餘 closeout gap 已進一步縮小為 `history.md` 壓縮整理與封存前摘要，而不再是缺少給後續開發者遵循的規則文件。

### 第 28 輪討論結果
1. 本輪承接第 27 輪留下的唯一下一步，已正式進入 TestMode 的 `(Optimization)`：沒有重開任何功能或驗證範圍，而是把第一版已穩定的測試入口、命名規則與收尾口徑沉澱成可重用文件。
2. 本輪沒有發現新的程式 drift；上一輪確認的 `(Optimization)` entry criteria 仍成立，因此本輪只專注於規則回寫與封存前整理，不再回退到 `check` 或 `(done)`。
3. 本輪建立了 `optimization.md`，將 `www/tests/smoke/<domain>/*.php` 作為 smoke source of truth、`bootstrap -> adapters/f3cms` 的責任鏈、canonical naming 規則與 Docker 驗證口徑正式固定下來。
4. 本輪也把可跨 feature 共用的術語同步回 `glossary.md`，補上 `Smoke Suite`、`Canonical Smoke Path`、`Thin Wrapper` 與 `Wrapper Retirement`，避免這些用字只停留在 TestMode spec 內。
5. 因此本輪完成後，TestMode 的剩餘 closeout gap 已進一步收斂為歷史壓縮與封存前整理，而不是任何功能或路徑層級的未完成項。

### 第 27 輪討論結果
1. 本輪承接第 26 輪留下的唯一下一步，已完成 TestMode 第一版是否可進入 `(Optimization)` 的 `check`：不再做新的程式改動，而是對照 `flow.llm.md` 的四個 entry criteria，確認目前是否已從功能/路徑切換階段正式收斂到收尾整理階段。
2. 本輪沒有發現新的程式 drift；代表性 canonical smoke 在上一輪 wrapper retirement 後已用 Docker 驗證成功，且目前也沒有新的未驗證主路徑、未移除 wrapper 或外部引用殘留需要回退。
3. 本輪有一個小型文件 drift：`plan.md` 與 `check.md` 仍保留部分早期 `check` 軌跡中的現在式敘述，例如「目前仍待處理的高風險批次」；但這些內容實際上已是歷史脈絡，而非當前阻塞，因此本輪已補上明確的 `(Optimization)` readiness 結論，將 current source of truth 收斂回最新段落。
4. 依照 `flow.llm.md` 的條件逐項比對後，目前四項進場條件都已成立：主要實作已完成、主要驗收已完成、沒有阻擋主流程承接的關鍵缺口，且下一步已收斂為規則沉澱、詞彙整理、文件同步與封存前收尾。
5. 因此本輪完成後，TestMode 的合理承接點已不再是 `check`，而是可正式進入 `(Optimization)`；下一步應建立 `optimization.md`，整理 smoke source of truth、Docker 驗證口徑與 F3CMS adapter / wrapper 收尾規則。

### 第 26 輪討論結果
1. 本輪承接第 25 輪留下的唯一下一步，已完成 wrapper retirement `(done)`：批次移除 `www/f3cms/scripts/*smoke*.php` 過渡期 wrapper，讓 `www/tests/smoke/<domain>/*.php` 成為唯一保留的 smoke 執行入口。
2. 本輪沒有發現新的文件 / 程式 drift；第 25 輪已先確認工作區內沒有 CLI、Lab、shell script、README 或其他現行程式入口仍依賴舊 wrapper，因此本輪可以直接收掉 wrapper，而不需要再保留雙入口相容期。
3. 本輪移除的 wrapper 涵蓋 `event_rule_engine_smoke.php` 與全部 `workflow_engine_*_smoke.php` legacy scripts；程式側不再保留任何只做 `require` 轉發的 smoke 相容層。
4. `TestMode`、`EventRuleEngine` 與 `WorkflowEngine` 文件中仍描述「wrapper 保留中」的現況已一併回寫為退休後狀態，避免形成「程式已刪除 wrapper，但 owner spec 仍聲稱 wrapper 存在」的文件 drift。
5. 本輪已使用 Docker 在無 wrapper 狀態下重新驗證代表性 canonical smoke 主命令；因此 TestMode 第一版的主路徑切換、canonical naming 與 wrapper retirement 三個收尾階段都已落地完成，合理承接點應回到 `check`，判定是否可進入 `(Optimization)`。

### 第 25 輪討論結果
1. 本輪承接第 24 輪留下的唯一下一步，已完成 wrapper retirement 前的 `check`：重新搜尋 spec、命令範例與工作區程式入口，確認 `www/f3cms/scripts/*smoke*.php` 是否仍被視為 source of truth。
2. 本輪沒有發現新的文件 / 程式 drift；搜尋結果顯示 `bin/`、`README.md`、`www/` 其他程式與現行自動化入口都沒有直接依賴舊 wrapper，剩餘提到 wrapper 的位置僅存在於 TestMode 歷史敘述，以及 `EventRuleEngine` / `WorkflowEngine` 文件中對過渡期現況的描述。
3. 本輪也重新比對了 wrapper 退場條件：所有 suite 都已有 canonical path、Docker 驗證已切到 canonical 主命令、現行文件主路徑已不再以 wrapper 為主，且 wrapper 本身都已退化成單行 `require` 轉發。
4. 因此目前阻塞點不再是技術依賴，而只是文件仍保留「wrapper 尚在」的狀態描述；這類引用可與 wrapper retirement `(done)` 同輪同步回寫，不構成延後理由。
5. 綜合以上，本輪結論是 wrapper retirement 已具備進場條件；下一步可直接進入專門的 `(done)` 批次，刪除 `www/f3cms/scripts/*smoke*.php` 並以 Docker 驗證 canonical path 仍為唯一有效入口。

### 第 24 輪討論結果
1. 本輪承接第 23 輪留下的唯一下一步，已完成最後一批 canonical naming `(done)`：將 `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 切到各自的 canonical path，並同步更新 owner spec 文件與 wrapper 指向。
2. 本輪沒有發現新的文件 / 程式 drift；第 23 輪先盤好的跨 spec 引用面，在本輪已一併同步到 `EventRuleEngine`、`WorkflowEngine` 與 `TestMode`，因此沒有留下「程式已切換但外部 spec 仍停在 flat path」的殘留狀態。
3. 本輪完成的 canonical rename 為：`www/tests/smoke/event_rule_engine_smoke.php` -> `www/tests/smoke/event_rule_engine/basic_or_rule.php`，以及 `www/tests/smoke/workflow_engine_instance_api_smoke.php` -> `www/tests/smoke/workflow_engine/instance_api.php`。
4. 兩支對應舊 `www/f3cms/scripts/` wrapper 已同步改指向新的 canonical path；至此所有 smoke 主路徑都已完成 folderized canonical naming，舊 flat `www/tests/smoke/*.php` 已不再作為 source of truth。
5. 本輪已使用 Docker 驗證兩支新的 canonical path 與舊 wrapper 相容路徑；驗證完成後，合理承接點已回到 `check`，可正式判定 wrapper retirement `(done)` 是否具備進場條件。

### 第 23 輪討論結果
1. 本輪承接第 22 輪留下的唯一下一步，已完成最後一批跨 spec path 更新的 `check`：只盤點 `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 這兩條仍被其他 feature spec 直接引用的主路徑，不提前進入 wrapper retirement `(done)`。
2. 本輪沒有發現新的文件 / 程式 drift；目前 `EventRuleEngine` 與 `WorkflowEngine` 的文件都仍明確指向 flat 路徑，且程式側 wrapper 也都只是單行轉發，因此目前阻塞點是文件引用面尚未一起切換，而不是程式結構不穩。
3. 本輪確認最後一批 canonical naming 不應拆成 wrapper retirement；正確順序應是先做跨 spec rename 與文件同步，再由 `check` 驗證是否已沒有 spec / code 把舊 flat path 當 source of truth，最後才進 wrapper retirement `(done)`。
4. 最後兩條 canonical target 已收斂為：`www/tests/smoke/event_rule_engine_smoke.php` -> `www/tests/smoke/event_rule_engine/basic_or_rule.php`，以及 `www/tests/smoke/workflow_engine_instance_api_smoke.php` -> `www/tests/smoke/workflow_engine/instance_api.php`。前者對應既有 fixture `event_rule_engine/basic_or_rule.json` 與 TestMode 先前已寫入的命名示例；後者則直接反映 WorkflowEngine feature 文件中已固定的 instance API 契約角色。
5. 因此本輪完成後，合理承接點已可由 `check` 轉入最後一批 canonical naming `(done)`：同輪需同步更新 TestMode、EventRuleEngine、WorkflowEngine 文件與 wrapper 指向；完成 Docker 驗證後，再回到 `check` 判定 wrapper retirement 是否具備進場條件。

### 第 22 輪討論結果
1. 本輪承接第 21 輪留下的唯一下一步，已完成 canonical naming 第二批 `(done)`：將第二批七支 `workflow_engine` flat smoke 全數改到 `www/tests/smoke/workflow_engine/` canonical path，並移除舊 flat 檔名。
2. 本輪沒有發現新的文件 / 程式 drift；第 21 輪剛修正的 `workflow_engine_instance_smoke.php` 引用面判斷，在本輪執行時也沒有遇到與外部 spec 衝突的證據，因此第二批範圍維持在先前 check 收斂的七支 suite 內。
3. 本輪完成的 canonical rename 為：`workflow_engine_smoke.php` -> `workflow_engine/definition.php`、`workflow_engine_definition_validation_smoke.php` -> `workflow_engine/definition_validation.php`、`workflow_engine_projection_smoke.php` -> `workflow_engine/projection.php`、`workflow_engine_instance_smoke.php` -> `workflow_engine/instance.php`、`workflow_engine_psc_smoke.php` -> `workflow_engine/psc.php`、`workflow_engine_sjse_edge_smoke.php` -> `workflow_engine/sjse_edge.php`、`workflow_engine_role_guard_smoke.php` -> `workflow_engine/role_guard.php`。
4. 七支對應舊 `www/f3cms/scripts/` wrapper 已同步改指向新的 canonical path；第二批也沒有保留 flat `www/tests/smoke/*.php` alias，因此目前大多數 `workflow_engine` smoke 已切到 folderized canonical path，僅剩 `workflow_engine_instance_api_smoke.php` 與跨 domain 的 `event_rule_engine_smoke.php` 尚待後續跨 spec 同步批次處理。
5. 本輪已使用 Docker 驗證七支新的 canonical path 與至少一條舊 wrapper 相容路徑；驗證完成後，合理承接點已回到 `check`，應收斂最後一批跨 spec path 更新與 wrapper retirement 的順序。

### 第 21 輪討論結果
1. 本輪承接第 20 輪留下的唯一下一步，已完成 canonical naming 第二批的 `check`：重新盤點第一批完成後仍維持 flat 命名的 smoke，並比對它們在其他 feature spec 與程式中的直接引用面，避免下一輪 `(done)` 又把 TestMode 內部 rename 與跨 spec 同步綁在一起。
2. 本輪發現一個小型文件 drift：先前 `plan.md` 將 `workflow_engine_instance_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 一起歸類為「已被其他 feature spec 直接引用」，但目前搜尋結果只確認 `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 有明確外部 spec 引用；`workflow_engine_instance_smoke.php` 目前沒有看到 TestMode 以外的直接 spec 綁定，因此這個判斷已在本輪修正。
3. 本輪將 canonical naming 第二批收斂為仍屬 `workflow_engine` domain、且目前沒有外部 spec 直接引用的七支 suite：`workflow_engine_smoke.php`、`workflow_engine_definition_validation_smoke.php`、`workflow_engine_projection_smoke.php`、`workflow_engine_instance_smoke.php`、`workflow_engine_psc_smoke.php`、`workflow_engine_sjse_edge_smoke.php`、`workflow_engine_role_guard_smoke.php`。
4. 第二批 canonical path 也已收斂為：`workflow_engine_smoke.php` -> `workflow_engine/definition.php`、`workflow_engine_definition_validation_smoke.php` -> `workflow_engine/definition_validation.php`、`workflow_engine_projection_smoke.php` -> `workflow_engine/projection.php`、`workflow_engine_instance_smoke.php` -> `workflow_engine/instance.php`、`workflow_engine_psc_smoke.php` -> `workflow_engine/psc.php`、`workflow_engine_sjse_edge_smoke.php` -> `workflow_engine/sjse_edge.php`、`workflow_engine_role_guard_smoke.php` -> `workflow_engine/role_guard.php`。
5. `event_rule_engine_smoke.php` 與 `workflow_engine_instance_api_smoke.php` 則應留待後續跨 spec 同步批次再處理，因為它們目前仍被 `EventRuleEngine` / `WorkflowEngine` feature 文件直接引用；因此本輪完成後，合理承接點已可由 `check` 轉入 canonical naming 第二批 `(done)`。

### 第 20 輪討論結果
1. 本輪承接第 19 輪留下的唯一下一步，已完成 canonical naming 第一批 `(done)`：先只處理 `workflow_engine` domain 下的五支代表性 suite，將 flat `www/tests/smoke/*.php` 路徑改為 folderized canonical path，並移除檔名中的 `_smoke`。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與目前程式狀態一致，且本輪沒有把 rename 範圍擴張到 `event_rule_engine` 或其他已被外部 spec 直接引用的 suite。
3. 本輪完成的 canonical rename 為：`workflow_engine_press_smoke.php` -> `workflow_engine/press.php`、`workflow_engine_press_rollback_smoke.php` -> `workflow_engine/press_rollback.php`、`workflow_engine_parallel_join_smoke.php` -> `workflow_engine/parallel_join.php`、`workflow_engine_core_judgment_smoke.php` -> `workflow_engine/core_judgment.php`、`workflow_engine_sjse_edge_execution_smoke.php` -> `workflow_engine/sjse_edge_execution.php`。
4. 五支對應舊 `www/f3cms/scripts/` wrapper 已同步改指向新的 canonical path；本輪沒有保留 flat `www/tests/smoke/*.php` alias，因此 `www/tests/smoke/workflow_engine/*.php` 現在已是這一批 suite 的唯一 source of truth。
5. 本輪已使用 Docker 驗證五支新的 canonical path 與至少一條舊 wrapper 相容路徑；驗證完成後，合理承接點已回到 `check`，應收斂 canonical naming 第二批名單與跨 spec 引用更新範圍。

### 第 19 輪討論結果
1. 本輪承接第 18 輪留下的唯一下一步，已完成 canonical naming 的 `check`：不直接進行 rename，而是先收斂第一批 rename 名單、Docker 命令切換方式與文件更新順序，避免下一輪 `(done)` 同時重做路徑盤點。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與目前程式狀態一致，皆反映 legacy smoke 已全數搬移完成，而當前 flat `www/tests/smoke/*.php` 仍只是過渡命名。
3. 本輪確認 canonical naming 的第一批不應一次整包改完所有 suite，而應先集中在 `workflow_engine` domain 下、且近期仍由 TestMode 直接承接的檔案：`workflow_engine_press_smoke.php`、`workflow_engine_press_rollback_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_core_judgment_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php`。這批既能覆蓋單一路徑、multi-path、rollback 三種代表情境，也能避免一開始就同時碰觸 `EventRuleEngine` 與 `WorkflowEngine` 其他 spec 的引用面。
4. 本輪也正式固定 canonical naming `(done)` 的切換口徑：先建立 folderized canonical path，例如 `www/tests/smoke/workflow_engine/press.php`；再把對應 wrapper 與文件主命令改指向新 path；flat 檔名只能在極短相容期內作為過渡 alias，且不得成為新的 source of truth。
5. 文件更新順序也已收斂：先更新 TestMode `history.md`、`plan.md`、`check.md` 與 Docker 驗證記錄，再更新直接引用該 smoke path 的 feature spec，例如 `WorkflowEngine` / `EventRuleEngine`；待 canonical naming 穩定後，才進到 wrapper retirement `(done)`。
6. 因此目前 feature 的合理承接點仍是 `check`，但下一步已可明確轉入 canonical naming 的第一輪 `(done)`：先只處理 `workflow_engine` 第一批 rename，而不是一次重命名所有 smoke。

### 第 18 輪討論結果
1. 本輪承接第 17 輪留下的唯一下一步，已完成 Transaction Rollback 類的最小 `(done)`：沿用既有 `www/tests/adapters/f3cms/press.php` 的 seed 基礎，將 `workflow_engine_press_rollback_smoke.php` 搬移到 `www/tests/smoke/`。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與目前程式狀態一致，且本輪沒有再引入新的 rollback adapter helper，符合第 17 輪的明確結論。
3. 本輪保留 rollback-specific 驗證在 suite 本體內：包含 transaction begin / commit / rollback、trace 寫入後故障模擬，以及 rollback 後 `tbl_press` 與 `tbl_press_log` 的狀態驗證；沒有把只有單一 suite 使用的邏輯提前抽象化。
4. `www/f3cms/scripts/workflow_engine_press_rollback_smoke.php` 已縮成 thin wrapper，因此所有 legacy smoke 主路徑都已切到 `www/tests/smoke/`；目前後續工作已不再是 smoke 搬移，而是 canonical naming 與 wrapper retirement 收尾 stage。
5. 本輪已使用 Docker 驗證新的 rollback smoke 主命令與一條舊 wrapper 相容路徑，結果皆符合預期；因此合理承接點已回到 `check`，下一步應盤點 canonical naming `(done)` 的第一批 rename 名單與切換順序。

### 第 17 輪討論結果
1. 本輪承接第 16 輪留下的唯一下一步，已完成 `workflow_engine_press_rollback_smoke.php` 的 `check`：正式判定是否需要先在 `www/tests/adapters/f3cms/` 補 transaction rollback helper，避免下一輪 `(done)` 再重做同一輪盤點。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與目前程式狀態一致，仍反映只剩 Transaction Rollback 類未搬移。
3. 本輪確認 rollback smoke 可直接重用既有 `www/tests/adapters/f3cms/press.php` 內的 press seed row 與 staff session 基礎；真正屬於 rollback 類特有的部分是 transaction begin / commit / rollback、trace 寫入後故障模擬，以及 rollback 後資料與 trace 回復驗證。
4. 因為目前只剩這唯一一支 rollback smoke，且其交易回復邏輯沒有第二個待搬移 suite 可共享，所以本輪結論是不先新增 transaction rollback helper；下一輪 `(done)` 應直接搬移 `workflow_engine_press_rollback_smoke.php`，只在 suite 本體內保留 rollback-specific 驗證。
5. 因此目前 feature 的合理承接點已可由 `check` 轉到下一輪 `(done)`：沿用既有 Press helper，搬移 rollback smoke，並用 Docker 驗證新主命令與舊 wrapper 相容路徑；之後再進 canonical naming 與 wrapper retirement 收尾 stage。

### 第 16 輪討論結果
1. 本輪承接第 15 輪之後的規劃收尾需求，沒有繼續做新的 smoke 搬移，而是只補 TestMode `plan` 中先前仍不夠明確的兩個結構性規則：wrapper 退場條件 / 移除時機，以及 `www/tests/smoke/` canonical naming 正名要求。
2. 本輪沒有發現新的文件 / 程式 drift；目前程式狀態仍是第五輪 `(done)` 已完成、只剩 `workflow_engine_press_rollback_smoke.php` 未搬移，故這次只需補 plan，不需回退既有 stage。
3. 本輪已在 `plan.md` 明確固定 wrapper 不會無限期保留：必須等最後一支 smoke 搬移完成、canonical naming 與文件 / 呼叫端切換完成後，再另外安排一輪專門的 wrapper retirement `(done)` 批次移除舊 `www/f3cms/scripts/*smoke*.php`。
4. 本輪也已在 `plan.md` 明確固定正名要求：`www/tests/smoke/` 應以 domain 分資料夾、檔名移除 `_smoke` 後綴，並以像 `www/tests/smoke/workflow_engine/press.php` 這樣的 canonical path 作為正式目標，而不是長期保留目前的 flat 檔名。
5. 因此目前 feature 的合理承接點仍是 `check`，下一個實作面下一步沒有改變，仍是先處理 `workflow_engine_press_rollback_smoke.php`；但在它之後，plan 已正式補上 canonical naming `(done)` 與 wrapper retirement `(done)` 兩個收尾 stage。

### 第 15 輪討論結果
1. 本輪承接第 14 輪留下的唯一下一步，已完成 Press Module / DB Seed 類的最小 `(done)`：先把 Press test helper 收進 `www/tests/adapters/f3cms/`，再搬移 `workflow_engine_press_smoke.php` 到 `www/tests/smoke/`。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與目前程式狀態一致，且本輪範圍維持在第 14 輪已固定的單一下一步內。
3. 本輪新增 `www/tests/adapters/f3cms/press.php`，把 press seed row、staff session、CSRF 與 reaction request context 集中成最小 helper，避免 `workflow_engine_press_smoke.php` 繼續把這些 setup 直接綁在 legacy script 內。
4. `www/f3cms/scripts/workflow_engine_press_smoke.php` 已縮成 thin wrapper，因此 Press reaction smoke 的 source of truth 已切到 `www/tests/smoke/workflow_engine_press_smoke.php`；本輪仍未動到 `workflow_engine_press_rollback_smoke.php`。
5. 本輪已使用 Docker 驗證新的 Press smoke 主命令與一條舊 wrapper 相容路徑，兩者都回傳成功 JSON；因此合理承接點已回到 `check`，目前只剩 Transaction Rollback 類待處理。

### 第 14 輪討論結果
1. 本輪承接第 13 輪留下的唯一下一步，已完成 Press Module / DB Seed 類與 Transaction Rollback 類的 finer-grained `check`，不再只停留在「剩兩支高風險 smoke」的粗粒度結論。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與目前程式狀態一致，且第四輪 Engine Runtime Multi-Path 已確實完成搬移與 Docker 驗證。
3. 本輪確認 `workflow_engine_press_smoke.php` 與 `workflow_engine_press_rollback_smoke.php` 雖然都會 seed `tbl_press`，但並不共享同一個最小執行形狀：前者重點在 `rPress::do_published()` 所需的 request / session / CSRF 與 module reaction 路徑，後者重點在 transaction begin / rollback 與 trace 回復驗證。
4. 因此 `workflow_engine_press_smoke.php` 可以獨立成為下一輪 `(done)`，但前提是先在 `www/tests/adapters/f3cms/` 補一層最小 Press test helper，把 press seed row、staff session 與 reaction request context 集中起來；這一層不應與 rollback 驗證綁在同一個 helper 內。
5. 相對地，`workflow_engine_press_rollback_smoke.php` 仍應留在後一輪 `check` / `(done)` 承接，因為它的核心不是 reaction smoke 搬移，而是 transaction 失敗後資料與 trace 是否完整回復。
6. 因此目前 feature 的合理承接點仍是 `check`，但下一步已更明確：先只處理 Press Module / DB Seed 類的最小 helper 與 `workflow_engine_press_smoke.php` 搬移，再用 Docker 驗證新主命令與舊 wrapper 相容路徑。

### 第 13 輪討論結果
1. 本輪承接第 12 輪留下的唯一下一步，已完成 Engine Runtime Multi-Path 類的 `(done)`：先把 shared WorkflowEngine runtime helper 收進 `www/tests/adapters/f3cms/`，再搬移 `workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php` 到 `www/tests/smoke/`。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與實際程式狀態仍一致，且本輪變更正好落在第 12 輪已固定的承接順序之內。
3. 本輪新增 `www/tests/adapters/f3cms/workflow_engine_runtime.php`，把 definition 載入與 validate、`transit()` 後 runtime context 回寫，以及 transition summary 收斂集中成 shared helper，避免三支 suite 繼續各自複製同一組 runtime harness。
4. 三支舊 `www/f3cms/scripts/` 入口已縮成 thin wrapper，因此 `www/tests/smoke/` 現在不只承接 low-risk smoke，也正式成為 Engine Runtime Multi-Path 類的 source of truth。
5. 本輪已使用 Docker 驗證三支新主命令與一條舊 wrapper 相容路徑，結果皆成功；因此下一步合理承接點應回到 `check`，重新確認剩餘未搬移 smoke 已只剩 Press / DB seed 與 transaction rollback 類。

### 第 12 輪討論結果
1. 本輪承接第 11 輪留下的唯一下一步，已完成 `Engine Runtime Multi-Path` 類是否先抽 shared runtime helper 的最終判定，不再停留在「是否要抽」的開放問題。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與實際程式狀態一致，皆反映前三輪 low-risk smoke 搬移已完成，而剩餘高風險工作仍未開始程式搬移。
3. 本輪比對 `workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php` 後，確認三支 script 重複了同一組 WorkflowEngine runtime harness：definition 載入與 validate、`transit()` 後 runtime context 回寫、以及 transition summary 收斂。
4. 因此目前 feature 的合理承接點已可由 `check` 轉到下一輪 `(done)`，而且落地順序已明確：先把 shared WorkflowEngine runtime helper 收進 `www/tests/adapters/f3cms/`，再搬移這三支 Engine Runtime Multi-Path smoke，最後用 Docker 驗證新主命令與舊 wrapper 相容路徑。
5. 本輪沒有做新的程式搬移，也不做新的 Docker 驗證；本輪只完成 helper 判定，避免下一輪 `(done)` 還要再次重做同一批 helper 盤點。

### 第 11 輪討論結果
1. 本輪承接第 10 輪留下的唯一下一步，已完成剩餘五支高風險 smoke 的 finer-grained `check`：不再只標記為「高風險延後批次」，而是拆成 `Press module / DB seed`、`transaction rollback`、`engine runtime multi-path` 三個策略群組。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與實際程式狀態一致，皆反映前三輪 low-risk smoke 搬移與 Docker 驗證已完成。
3. 本輪確認 `workflow_engine_press_smoke.php` 屬於 module reaction 與資料表 seed 類、`workflow_engine_press_rollback_smoke.php` 屬於 transaction rollback 類，而 `workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php` 則形成一個共享 runtime helper 形狀的 multi-path 類。
4. 因此目前 feature 的合理承接點仍是 `check`，但下一步已可以更精準：優先只處理 `Engine Runtime Multi-Path` 類，先決定是否抽共用 runtime helper，再判斷是否值得進下一輪 `(done)`。
5. 本輪沒有做新的程式搬移，也不做新的 Docker 驗證；本輪只完成高風險群組拆分與優先順序收斂，讓下一次承接不必再重做同樣的檔案盤點。

### 第 10 輪討論結果
1. 本輪承接第 9 輪留下的唯一下一步，已完成 TestMode 第三輪 `(done)`：把 `workflow_engine_psc_smoke.php`、`workflow_engine_sjse_edge_smoke.php` 與 `workflow_engine_role_guard_smoke.php` 正式搬到 `www/tests/smoke/`，並把對應舊 `www/f3cms/scripts/` 入口縮成 thin wrapper。
2. 本輪仍未引入新的 bootstrap 或 fixture 結構，三支 suite 全部沿用既有 `www/tests/bootstrap/smoke.php` 與 `www/tests/adapters/f3cms/bootstrap.php`；這表示目前的 smoke 搬移模板已完整覆蓋可歸類為 low-risk 的 WorkflowEngine smoke。
3. 本輪已使用 Docker 驗證三支新的 `www/tests/smoke/` 主命令，並額外驗證至少一條舊 wrapper 相容路徑可正常轉發；因此第三批搬移後的 source of truth 仍穩定維持在 `www/tests/smoke/`。
4. 目前沒有看到新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與實際程式狀態一致，皆反映第三批 low-risk smoke 已完成搬移。
5. 因此目前 feature 的合理承接點已再次回到 `check`；而且以目前盤點結果來看，剩餘未搬移 smoke 已全部落在高風險延後批次，不應再假設存在第四批可直接沿用現有模板的 low-risk 搬移。
6. 本輪沒有處理 `workflow_engine_press_smoke.php`、`workflow_engine_press_rollback_smoke.php`、`workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php`；若後續仍要繼續搬移，應先把它們拆成更細的高風險策略，而不是直接沿用前三輪的 low-risk 節奏。

### 第 9 輪討論結果
1. 本輪承接第 8 輪留下的唯一下一步，已完成第二輪 `(done)` 之後的 `check` 續盤：重新檢視剩餘 smoke，確認是否仍存在第三批 low-risk 候選。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與目前程式狀態一致，皆反映前兩輪 low-risk smoke 搬移與 wrapper 相容路徑驗證已完成。
3. 本輪正式將第三批 low-risk 候選收斂為 `workflow_engine_psc_smoke.php`、`workflow_engine_sjse_edge_smoke.php` 與 `workflow_engine_role_guard_smoke.php`；其中前兩支仍是 definition / metadata 驗證，後一支則是 engine 內 role guard 契約驗證，三者都尚未混入 DB seed、module reaction 或 rollback。
4. 相對地，`workflow_engine_press_smoke.php`、`workflow_engine_press_rollback_smoke.php`、`workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php` 仍保留在高風險延後批次，原因是它們會把 smoke 結構搬移與業務資料 / rollback / 多路徑 helper 回歸綁在一起。
5. 因此目前 feature 的合理承接點已由 `check` 轉到第三輪 `(done)`；下一步不應再補抽象盤點，而是直接搬移第三批 low-risk smoke，再以 Docker 驗證新主命令。
6. 本輪不做新的程式搬移，也不做新的 Docker 驗證；本輪只完成 `check` 結論與第三輪 `(done)` 名單收斂。

### 第 8 輪討論結果
1. 本輪承接第 7 輪留下的唯一下一步，已完成 TestMode 第二輪 `(done)`：把 `workflow_engine_definition_validation_smoke.php`、`workflow_engine_projection_smoke.php` 與 `workflow_engine_instance_smoke.php` 正式搬到 `www/tests/smoke/`，並把對應舊 `www/f3cms/scripts/` 入口縮成 thin wrapper。
2. 本輪沒有引入新的 bootstrap 分層或 fixture 結構，三支 suite 都直接沿用第一輪已建立的 `www/tests/bootstrap/smoke.php` 與 `www/tests/adapters/f3cms/bootstrap.php`；這證明目前的 smoke 基礎骨架已足以支撐第二批 WorkflowEngine 核心契約驗證。
3. 本輪已使用 Docker 驗證三支新的 `www/tests/smoke/` 主命令，並額外驗證至少一條舊 wrapper 相容路徑可正常轉發；因此第二批搬移後的 source of truth 仍穩定維持在 `www/tests/smoke/`。
4. 目前沒有看到新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與實際程式狀態一致，皆反映第二批 low-risk smoke 已完成搬移。
5. 因此目前 feature 的合理承接點已再次回到 `check`，不應直接假設第三批也能照同樣方式搬；下一步要先重新盤點剩餘 smoke 中是否還有 low-risk 候選，或是否已只剩高風險批次。
6. 本輪沒有處理 `workflow_engine_press_smoke.php`、`workflow_engine_press_rollback_smoke.php`、`workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php`；它們仍保留在延後批次，避免本輪把範圍擴張到 DB seed、rollback、module reaction 或多路徑 helper 回歸。

### 第 7 輪討論結果
1. 本輪承接第 6 輪留下的唯一下一步，已完成 TestMode 第一輪 `(done)` 之後的 `check`：盤點第一批搬移的完成邊界、第二批候選與 wrapper 退場前提。
2. 本輪沒有發現新的文件 / 程式 drift；`history.md`、`plan.md`、`check.md` 與實際程式狀態一致，皆反映第一批三支 suite 已搬移、thin wrapper 已生效、Docker 主命令已可執行。
3. 本輪正式將第二批低風險候選收斂為 `workflow_engine_definition_validation_smoke.php`、`workflow_engine_projection_smoke.php` 與 `workflow_engine_instance_smoke.php`，因為它們仍屬 WorkflowEngine 核心契約驗證，且可沿用第一輪已建立的 bootstrap / adapter 模式。
4. 相對地，`workflow_engine_press_smoke.php`、`workflow_engine_press_rollback_smoke.php`、`workflow_engine_core_judgment_smoke.php`、`workflow_engine_parallel_join_smoke.php`、`workflow_engine_sjse_edge_execution_smoke.php` 被歸為延後批次，原因是它們已開始混入 DB seed、module reaction、rollback 或多路徑 helper，若現在一起搬會放大回歸面。
5. 因此目前 feature 的合理承接點已由 `check` 轉到第二輪 `(done)`；下一步不應再補抽象規劃，而是直接搬第二批三支 low-risk smoke，再用 Docker 驗證新主命令。
6. 本輪不做新的程式搬移，也不做新的 Docker 驗證；本輪只完成 `check` 結論與下一輪 `(done)` 的名單收斂。

### 第 6 輪討論結果
1. 本輪承接第 5 輪留下的唯一下一步，已完成 TestMode 第一輪 `(done)`：建立 `www/tests/` 最小骨架、搬移第一批三支 suite、並把對應 `www/f3cms/scripts/` 檔案縮成 thin wrapper。
2. 本輪新增了 `www/tests/bootstrap/smoke.php` 與 `www/tests/adapters/f3cms/bootstrap.php`，把 smoke 執行共用流程與 F3CMS runtime bootstrap 正式拆開；另外也新增了第一個 fixture `www/tests/fixtures/event_rule_engine/basic_or_rule.json`，讓 fixture 路徑不再綁在舊 scripts 內。
3. 第一批已搬移的 suite 為 `www/tests/smoke/event_rule_engine_smoke.php`、`www/tests/smoke/workflow_engine_smoke.php`、`www/tests/smoke/workflow_engine_instance_api_smoke.php`；對應舊 `www/f3cms/scripts/` 檔案已退化成只做 `require` 轉發的 thin wrapper。
4. 本輪已使用 Docker 驗證新的主命令路徑，並額外驗證至少一支舊 wrapper 相容命令可正常轉發；這代表 `www/tests/smoke/` 已成為實際可執行的 source of truth，而不是只存在於規劃文件中。
5. 因此目前 feature 已不再只是 `plan`，而是已完成第一輪 `(done)`；下一步較合理的承接點是 `check`，盤點這輪搬移的完成邊界、第二批 smoke 優先序與 wrapper 退場策略。
6. 本輪沒有處理第二批 smoke，也沒有改動 CLI / Lab；本輪只完成第一批 suite 的實際搬移與驗證，刻意把範圍控制在最小可驗證骨架。

### 第 5 輪討論結果
1. 本輪承接第 4 輪留下的唯一下一步，已正式收斂 TestMode 的 Stage 4：固定第一批搬移標的、thin wrapper 保留名單、Docker 驗證命令切換方式與回退口徑。
2. 本輪把第一批搬移標的收斂為三支最小但有代表性的 suite：`event_rule_engine_smoke.php`、`workflow_engine_smoke.php`、`workflow_engine_instance_api_smoke.php`；它們分別代表純 engine smoke、基本 definition smoke，以及已被既有 spec 明確引用的 instance API 契約。
3. 同時，本輪正式決定相容期內 `www/tests/smoke/<suite>.php` 會是 source of truth，而舊 `www/f3cms/scripts/*.php` 只保留為 thin wrapper；文件與驗證記錄也應優先切到新的 Docker 命令格式。
4. 目前 feature 雖然仍位於 `plan` 階段，但 Stage 1 到 Stage 4 都已有正式輸出，第一版規劃基線已完整；下一步不應再繼續補 plan，而是直接進入第一輪 `(done)`，建立 `www/tests/` 最小骨架並搬移第一批 suite。
5. 本輪不做程式搬移，也不做 Docker 驗證；本輪只完成 Stage 4 的搬移基線收斂與 checklist 同步。

### 第 4 輪討論結果
1. 本輪承接第 3 輪留下的唯一下一步，已正式收斂 TestMode 的 Stage 3：固定 `www/cli/index.php` 與 `www/f3cms/modules/Lab/reaction.php` 對 `www/tests/` 的整合契約。
2. 本輪明確決定第一版不為 CLI 增加通用 smoke runner 命令，也不讓 CLI route 直接 include `www/tests/smoke/*.php` 或 `www/tests/adapters/f3cms/*`；CLI 若未來要提供測試入口，只能是白名單式轉發，而不是 suite 宿主。
3. 本輪也正式決定 `Lab` 第一版只承接結果摘要、suite 清單與有限度診斷資訊，不直接執行 smoke suite 本體，也不承接 test adapter 或 bootstrap 邏輯。
4. 因此目前 `www/tests/`、CLI、Lab 的關係已固定為「測試本體」與「入口消費者」的關係；標準執行路徑仍是直接執行 `www/tests/smoke/<suite>.php`，不依賴 CLI 或 Lab。
5. 目前 feature 仍位於 `plan` 階段，但 Stage 3 已有正式輸出；下一步應承接 Stage 4，盤點第一批搬移標的、wrapper 保留名單、Docker 驗證命令切換與回退口徑。
6. 本輪不做程式搬移，也不做 Docker 驗證；本輪只完成 Stage 3 的整合邊界收斂與 checklist 同步。

### 第 3 輪討論結果
1. 本輪承接第 2 輪留下的唯一下一步，已正式收斂 TestMode 的 Stage 2：固定 `www/tests/bootstrap/`、`www/tests/adapters/f3cms/` 與 `www/f3cms/scripts/` wrapper 的最小責任與呼叫鏈。
2. 本輪根據現有 `www/f3cms/scripts/*.php` 與 `www/cli/index.php` 的重複 bootstrap 模式，明確把 `vendor/autoload.php`、`libs/Autoload.php`、`libs/Utils.php`、`Base::instance()`、`config.php` 這類 F3CMS runtime 接線責任集中到 `www/tests/adapters/f3cms/`，而不是繼續散落在每支 suite 或 wrapper 裡。
3. 同時，本輪也正式固定 `www/tests/bootstrap/` 只承接 suite 執行流程、結果封裝、exit code 與 fixture helper，不直接知道 F3CMS 細節；這讓 smoke suite 與 framework bootstrap 可以真正拆開。
4. 另外，本輪把 `www/f3cms/scripts/` 的 compatibility contract 再縮窄為 thin wrapper：只保留舊命令、參數轉發與 exit code 回傳，不再保存獨立 bootstrap、fixture 或斷言邏輯。
5. 因此目前 feature 仍位於 `plan` 階段，但 Stage 2 已有正式輸出；下一步不應直接搬移檔案，而是承接 Stage 3，收斂 CLI 與 Lab 如何接到新的 `www/tests/` 分層，並明確禁止它們重新吞回 smoke 本體。
6. 本輪不做程式搬移，也不做 Docker 驗證；本輪只完成 Stage 2 的責任收斂與 checklist 同步。

### 第 2 輪討論結果
1. 本輪承接第 1 輪留下的唯一下一步，已正式收斂 TestMode 的 Stage 1：固定 `www/tests/` 的第一版子目錄契約，並明確分出 `smoke/`、`fixtures/`、`bootstrap/`、`adapters/f3cms/` 四層角色。
2. 本輪同時明確決定第一版不先做通用 smoke runner，而是先維持直接執行單支 suite 的模式；這讓範圍維持在目錄契約與責任邊界，不提前擴張到 runner discovery 與 CLI 命令設計。
3. 另外，本輪也正式固定三個入口的第一版邊界：`www/f3cms/scripts/` 進入過渡期 wrapper 角色、`www/cli/index.php` 保持 command gateway 而不承載 smoke 本體、`www/f3cms/modules/Lab/reaction.php` 保持 diagnostic entry 而不成為 smoke 主執行器。
4. 因此目前 feature 仍位於 `plan` 階段，但 Stage 1 已有正式輸出；下一步不應直接整包搬移 smoke，而是承接 Stage 2，把 bootstrap、adapter 與 wrapper 的最小責任收斂成可執行契約。
5. 本輪不做程式搬移，也不做 Docker 驗證；本輪只完成 Stage 1 的規劃收斂與 checklist 同步。
6. 最新討論的下一步選項：先只做一件事，承接 Stage 2，把 `www/tests/bootstrap/`、`www/tests/adapters/f3cms/` 與 `www/f3cms/scripts/` wrapper 的最小責任整理成更明確的 bootstrap / adapter / compatibility contract。

### 第 1 輪討論結果
1. 本輪承接第 0 輪留下的唯一下一步，已為 TestMode 正式建立 `plan.md` 與 `check.md`，補齊 current spec pointer 先前指向不存在檔案的文件 drift。
2. 本輪將測試系統重構拆成四個 stage：目錄契約與責任邊界、bootstrap / adapter 與相容層、CLI / Lab 整合方式、以及搬移順序與驗收口徑。
3. 目前已正式把 feature 由 `idea` 推進到 `plan`；但仍未進入 `(done)`，因為尚未開始實際搬移 `www/tests/`、wrapper 或任何 Docker 驗證路徑調整。
4. 本輪沒有做程式實作，也不需要 Docker 驗證；本輪只完成規劃文件初始化與 Stage 拆分，讓後續重構有正式承接點。
5. 最新討論的下一步選項：先只做一件事，承接 Stage 1，把 `www/tests/` 的子目錄契約、smoke runner 是否屬於第一版，以及 CLI / Lab / scripts 的責任邊界收斂成更具體的 plan 結果。

### 第 0 輪討論結果
1. 目前新需求的核心不是單一功能，而是重新定義 smoke、fixture、CLI 與 F3CMS 內部診斷入口的責任邊界，避免驗證系統長期綁死在 `www/f3cms/scripts/`。
2. 已確認的新方向是：測試系統先搬到 `www/tests/`，而不是 repo root，原因是現有 Docker volume 目前只掛載 `www/` 到容器內，這讓 `www/tests/` 成為第一版最實際且可執行的新位置。
3. 已知角色分層如下：`www/tests/` 承接 smoke / fixture / bootstrap / adapter，`www/cli/index.php` 承接 CLI / cronjob command gateway，`www/f3cms/modules/Lab/reaction.php` 承接 staff 可見的診斷與結果查看入口。
4. 目前 flow stage 應落在 `idea`，因為測試系統的新結構、搬移順序、相容層策略與 `Lab` / CLI 如何整合都還未正式拆成可執行 stage。
5. 已有文件狀態：目前已建立 `idea.md` 與 `history.md`；尚未建立 `plan.md`、`check.md`、`optimization.md`。
6. 目前已知風險或未決問題包括：是否保留 `www/f3cms/scripts/` wrapper 相容層、`Lab` 是否允許受控觸發 smoke、`CLI` 是否要提供 smoke runner 命令，以及未來第二階段是否要把 `www/tests/` 再提升到 repo root。
7. 最新討論的下一步選項：先只做一件事，承接 `idea` 後的 `(discuss)` / `plan` 前收斂，把 `www/tests/` 的子目錄責任、CLI / Lab / scripts 過渡策略與搬移順序整理成可執行的第一版 plan。