### 第 7 輪討論結果
1. 本輪承接目前 `check` 階段的架構邊界盤點，正式確認一條 F3CMS 規則：只要需求牽涉實體資料表，就必須以 module / reaction / service 承接主體實作，而不是把業務主流程長期放在 `libs`。
2. 依這條規則，EventRuleEngine 目前的第一版骨架雖已能通過最小 smoke，但現有 `www/f3cms/libs` 內的 `EventRuleEngine`、registry、evaluator、`PlayerContext` 等主體責任已超出 `libs` 應承接的範圍，因此這裡已形成新的 architectural drift。
3. 本輪同步收斂 `libs` 的正確邊界：`libs` 只保留最小的 EventRuleEngine JSON parser；凡是依賴 `tbl_duty`、`tbl_task`、`tbl_task_log`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_manaccount_log` 的 payload 載入、context preload、evaluator 組裝、module integration 與狀態寫回，都應回到 module 側。
4. 因此目前 feature 仍位於 `check`，但下一輪 `(done)` 的最小優先事項已改為先修正這個架構漂移，而不是直接沿著 `libs` 版骨架繼續補更多 integration 或 fixture。
5. 本輪尚未動程式，只先把這個新架構規則回寫到 `idea.md`、`plan.md`、`check.md` 與 `history.md`，避免後續承接時繼續把錯的責任邊界當成既定方向。
6. 最新討論的下一步選項：先只做一件事，把 parser 以外的 EventRuleEngine 主體從 `www/f3cms/libs` 收斂回 module 邊界，再決定是否同輪補 module integration adapter、payload source 承接與更多 edge-case smoke。

### 第 6 輪討論結果
1. 本輪承接第 5 輪留下的唯一下一步，已完成 EventRuleEngine 第一版最小程式骨架實作，新增了 `EventRuleEngine`、payload validator、parser、registry、`PlayerContext`、evaluation result 結構與三個第一版 evaluator，讓 Stage 3 / Stage 4 的規劃首次落成可執行程式。
2. 本輪同時新增 Docker 可跑的 `event_rule_engine_smoke.php`，並已使用專案既有容器環境完成驗證；目前 canonical 主路徑已切到 `www/tests/smoke/event_rule_engine/basic_or_rule.php`，且後續已在 TestMode 收尾輪完成 legacy wrapper retirement；smoke 已覆蓋 `matched`、`not_matched`、`invalid_payload`、`missing_evaluator` 與 `context_error` 五條最小驗證路徑。
3. 驗證結果顯示目前第一版骨架與 spec 收斂方向一致：validator 可阻擋非法 payload、registry 缺漏會 fail-closed、context 缺值不會默默補查資料，且三個第一版 rule types 已可被 traversal 正常 dispatch。
4. 因此目前 feature 已不再只是純 `plan` 狀態，而是已完成一輪 `(done)` 的最小骨架落地，接下來較合理的承接點是 `check`：盤點這輪骨架實作已完成與仍未完成的 integration / fixture 缺口。
5. 本輪當時尚未辨識到 `libs` 與 module 邊界的架構漂移；這個缺口已在第 7 輪補充修正。
6. 最新討論的下一步選項：先只做一件事，承接 `check`，確認第一版骨架的完成邊界與缺口，再決定下一輪 `(done)` 是補 module integration adapter、payload source 承接，還是補更多 fixture / edge-case smoke。

### 第 5 輪討論結果
1. 本輪承接第 4 輪留下的唯一下一步，已將 EventRuleEngine 的 Stage 4 正式收斂為第一版驗收與 fallback 基線，不再只停留在「需要 check」的抽象描述。
2. 本輪把第一版 acceptance 切成 payload 結構、engine 判斷、context 缺值 / preload、registry / integration 失配四個驗收切面，並明確規定非法 payload、缺 evaluator 與 context error 都必須 fail-closed，而不能偽裝成一般 business false。
3. 本輪也正式收斂了 context 缺值時的 default 原則、registry 與 validator 白名單不一致時的處置規則，以及 module integration 不可退化的邊界，避免後續實作把補查 DB、寫 log 或直接發獎勵塞回 engine。
4. 到這一輪為止，Stage 1 到 Stage 4 都已具備正式輸出：DSL contract、schema / query baseline、engine 骨架、驗收與 fallback 基線均已收斂，因此目前 feature 仍位於 `plan` 階段，但已接近可進入 `(done)` 的切入點。
5. 目前仍沒有程式 / 文件 drift，因為這個 feature 尚未有正式實作；本輪也不需要 Docker 驗證，因為尚未進入 smoke 或 runtime verification。
6. 最新討論的下一步選項：先只做一件事，承接 `(done)` 的最小實作切入點，先實作 payload validator、parser、registry 與 RuleEngine traversal 的第一版骨架，再決定同輪是否補三個 rule types 的最小 smoke / fixture。

### 第 4 輪討論結果
1. 本輪先辨識並處理文件 drift：`idea.md` 仍保留 PostgreSQL `JSONB` / GIN 與舊版 `tbl_manaccount_log` 欄位描述，但 `plan.md` / `history.md` 已收斂為 MariaDB 10.4.6、`parent_id` 型 module-owned log 與非全域 JSON 反查基線，因此本輪先把 `idea.md` 同步到目前穩定結論。
2. 在 drift 修正後，本輪承接第 3 輪留下的唯一下一步，已將 Stage 3 正式收斂成第一版 engine 執行骨架，包含 `DutyRuleLoader -> PayloadValidator -> RuleParser -> RuleEngine -> EvaluationResult` 的最小路徑。
3. 本輪同時明確化 evaluator registry contract、`PlayerContext` preload contract、validator / parser / engine 邊界，以及 fail-closed 的錯誤分類與回傳策略，避免後續實作時再把 DB query、context preload 或 task / account 寫回混回 RuleEngine 內部。
4. 另外，本輪也比照既有 WorkflowEngine 與 Press reaction 的責任切分，正式記錄 EventRuleEngine 應維持「module / reaction 準備 context，engine 純判斷，狀態寫回與 log 仍由 module 層負責」的整合邊界。
5. 目前沒有看到程式 / 文件 drift，因為此 feature 仍未進入實作；本輪也不需要 Docker 驗證，現階段仍屬 `plan` 階段下的 spec-only 收斂工作。
6. 最新討論的下一步選項：先只做一件事，承接 Stage 4，把 fail-closed 驗收點、context preload 缺值情境、registry 缺漏情境與 fallback 原則整理成更明確的 check / risk baseline，作為後續進入 `(done)` 前的最後規劃整理。

### 第 3 輪討論結果
1. 本輪承接第 2 輪留下的唯一下一步，已將 EventRuleEngine 的 Stage 2 正式收斂成 MariaDB 10.4.6 相容的 schema / index / query baseline，而不再停留於 `idea.md` 的抽象 schema 敘述。
2. 本輪明確確認第一版 DB 基線應以 MariaDB 行為為準，因此 `tbl_duty.claim`、`factor`、`next` 雖維持 JSON contract 語意，但規劃上不依賴 PostgreSQL `JSONB`、GIN 或通用 generated column 索引能力。
3. 本輪已將 `tbl_task`、`tbl_task_log`、`tbl_member_heraldry`、`tbl_manaccount`、`tbl_manaccount_log` 的最小欄位與最小索引正式寫入 `plan.md`，並同步將 reviewer 稽核查詢與 log 成長下的索引 / 歸檔風險一併明文化。
4. 其中 `tbl_task_log` 與 `tbl_manaccount_log` 均採 module-owned log 模式，使用 `parent_id` 指回主表，這樣可與 F3CMS 既有 `tbl_press_log` 慣例保持一致，避免重新引入共享 workflow runtime table 或混亂的 log 邊界。
5. 因此目前 feature 仍位於 `plan` 階段，但已從「只有 DSL 與資料邊界」推進到「已有第一版可實作的 DB baseline」；目前尚未進入 `(done)`，也尚未需要 Docker 驗證。
6. 最新討論的下一步選項：先只做一件事，承接 Stage 3，將 RuleEngine、Evaluator registry、PlayerContext preload contract、validator / parser 邊界與錯誤回傳策略收斂成最小可實作骨架。

### 第 2 輪討論結果
1. 本輪直接承接第 1 輪留下的唯一下一步，已將 `idea.md` 內既有的 JSON DSL、rule types、RuleEngine / PlayerContext / Evaluator 邊界、payload validator 與資料承接原則正式回寫到 `plan.md` 的 Stage 1 收斂結果。
2. 本輪同時把 `check.md` 中 Stage 1 對應的驗收項更新為已完成，包含 DSL contract、第一版 rule types、責任邊界、JSON 欄位型態、relation table、task / account log 最小欄位、短路求值、防禦策略與 JSON 全域反查為非範圍等內容。
3. 目前沒有看到文件與程式碼的 drift，因為此 feature 仍未進入實作階段；當前缺口集中在 schema / index / query 規劃，而不是程式回寫或 Docker 驗證。
4. 因此當前 flow stage 已可由單純 `idea` 承接點推進到 `plan` 階段：核心模型與邊界已有第一版正式輸出，但尚未進入 `(done)`。
5. 本輪不處理程式碼，也不做 Docker 驗證；本輪只完成 Stage 1 的 spec 收斂與 checklist 同步。
6. 最新討論的下一步選項：先只做一件事，承接 Stage 2，把 JSON 欄位、relation table、task / account log、history / reviewer 查詢與索引風險整理成更明確的 schema / query 規劃。

### 第 1 輪討論結果
1. 目前 `EventRuleEngine` spec 資料夾僅有 `idea.md`，尚未建立 `history.md`、`plan.md`、`check.md`，因此還不存在正式的 FDD 承接鏈。
2. 依 `idea.md` 現況判斷，這個 feature 已有相對完整的問題定義、目標、範圍、資料影響、限制與風險，但尚未正式拆成可執行 stage，也尚未形成驗收清單。
3. 因此當前 flow stage 應先落在 `idea` 後、準備進入 `(discuss)` / `plan` 的承接點，而不是直接進入實作或驗收。
4. 本輪不處理程式碼，也不做 Docker 驗證；本輪只完成 FDD 基本文件初始化，讓後續討論、規劃與 review 有正式落點。
5. 最新討論的下一步選項：先只做一件事，根據目前 `idea.md` 已收斂的內容，把 `plan.md` 補成第一版可執行 stage，並在 `check.md` 建立對應的驗收與風險清單。