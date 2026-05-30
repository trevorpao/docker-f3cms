
# 系統冒煙測試優化 (Smoke Test Optimization) - idea.md (FORKS S Layer Draft)

### 1. 背景與問題定義 (Problem Statement)
目前專案的 smoke test 已經具備穩定基礎：以 Docker 為驗證基準、以 `www/tests/` 承接獨立測試執行環境、以 `www/tests/bootstrap/` 承接共用 runner contract、並以 `www/tests/adapters/f3cms/` 承接 runtime 接線。不過，現階段 `www/cli/index.php` 仍是主要 CLI 執行入口，而 smoke test 尚未收斂成一個明確獨立的測試入口來 dispatch 不同 Entity 的 S 層驗證。

然而，現況仍有幾個明顯限制：

- smoke 的執行粒度仍偏粗，常需要直接跑完整 runtime 路徑，導致日常回饋速度不夠穩定。
- DB-backed smoke 的資料命名、清理與 rerun 慣例雖已存在，但仍容易散落在各 suite，缺乏更明確的共用契約。
- smoke 的結果輸出與失敗診斷尚未完全標準化，不利於批次執行、每日檢查與後續自動化聚合。
- 現有 smoke 已具備測試本體地位，但在架構語言上，尚未被正式提升為 FORKS 中的獨立驗證分工。
- `www/cli/index.php` 與 smoke test 的執行責任尚未完全拆開，缺少一個專屬於 S 層的標準入口來呼叫不同 `{Entity}/smoke.php`。

本 spec 的主張是：系統應正式升級為 **FORKS (Feed, Outfit, Reaction, Kit, Smoke)** 架構，而且這個 FORKS 定義是 **module / entity-owned** 的。也就是說，每一個 Entity 所屬 module，原則上都可能擁有自己的 `feed.php`、`outfit.php`、`reaction.php`、`kit.php`、`smoke.php` 五個檔案；其中 **S 層 (Smoke Layer)** 就是該 Entity / module 的測試與驗證入口。S 層不是附屬腳本，而是和 F、O、R、K 並列的正式架構分工。

在共享層方面，`www/f3cms/libs/` 應補上一個共用的 `Smoke` 類，作為所有 `{Entity}/smoke` 的父類，承接通用測試流程、共用 assertion / result contract 與可重用的 execution skeleton。但這個 libs-level `Smoke` 只能承接共用 runtime / base behavior，不應反向接管 entity truth、module-local payload 或跨 entity 的業務協調。換句話說，S 層同時包含兩個穩定面向：module-owned 的 `{Entity}/smoke`，以及 libs-owned 的 `Smoke` base class；前者擁有測試語意與案例，後者只提供共用骨架。

### 2. 目標結果 (Target Outcome)
在不改變 Docker baseline、canonical path 與既有 suite ownership 的前提下，建立一套符合 FORKS 架構定位的 S 層驗證體系。第一版完成後，應達成以下結果：

- **確立 FORKS 的 S 層分工**：Smoke 不再只是測試附屬腳本，而是 FORKS 內負責驗證 contract continuity 的正式架構層。
- **確立 module-owned S 檔結構**：每個 Entity / module 都可擁有自己的 `smoke.php`，使 Smoke 成為該 module 正式的一級檔案，而不是外掛腳本。
- **建立 libs 的 `Smoke` 父類**：在 `www/f3cms/libs/` 補上共用 `Smoke` base class，承接所有 `{Entity}/smoke` 的最小共用執行骨架。
- **建立獨立 S 層入口**：`www/cli/index.php` 保持一般 CLI 入口，而 `www/tests/index.php {path}` 成為 smoke test 的正式執行入口，負責依 `<module>/<surface>/<contract>` dispatch 到不同 `{Entity}/smoke.php`。
- **保留既有測試支撐路徑**：`www/tests/bootstrap/`、`www/tests/adapters/f3cms/` 與 Docker command layer 繼續作為 S 層的正式支撐路徑。
- **建立分層驗證模型**：明確區分 pure logic smoke、fixture-driven smoke、DB-backed smoke，讓不同成本的驗證可以對應不同變更風險。
- **統一輸出與 rerun 契約**：所有 S 層 suite 都朝一致結果結構、清理責任與可重跑慣例收斂，支援人工閱讀與批次聚合。
- **固定第一版 Entity-based SBE 基準**：以最近一次完成規劃的 `SMSSystem` spec 為第一版案例母體，選用其三個核心 Entity `Mobile`、`Phonebook`、`Campaign` 作為 smoke SBE 測試基準，並以 `<module>/<surface>/<contract>` 作為正式 path grammar。

### 3. 範圍 (Scope)
- **S 層在 FORKS 中的責任定義**：定義 Smoke 作為 verification architecture layer 的角色、限制與和 F、O、R、K 的互動方式。
- **module-local S 檔責任定義**：定義 `{Entity}/smoke` 作為 entity-owned 驗證入口時，和 `feed.php`、`outfit.php`、`reaction.php`、`kit.php` 的關係與責任邊界。
- **libs `Smoke` base class 定義**：定義 `www/f3cms/libs/Smoke` 的最小責任，避免它長成新的業務協調層。
- **獨立 smoke entrypoint 定義**：定義 `www/tests/index.php {path}` 的 dispatch contract、path resolution 與和 `www/cli/index.php` 的責任切分；正式 path grammar 收斂為 `<module>/<surface>/<contract>`。
- **S 層分類規格**：定義 pure logic smoke、fixture-driven smoke、DB-backed smoke 的適用情境、執行成本與宣告方式。
- **S 層結果契約**：定義 suite 成功/失敗輸出的最小欄位、診斷資訊與可聚合格式。
- **S 層 rerun / cleanup contract**：定義 DB-backed smoke 的資料命名、清理責任與資料隔離規則。
- **S 層批次執行模型**：定義 module-local `{Entity}/smoke`、`www/tests/index.php {path}` 與未來薄型 runner 的共存方式，但不在本階段鎖定除了 `www/tests/index.php` 之外的額外 CLI 細節。
- **第一版 SMSSystem 三 Entity SBE 收斂**：明確指定 `mobile/request/create_or_ensure`、`phonebook/owner/create_with_mobiles`、`campaign/request/create_from_phonebook` 作為第一版 SBE 測試母體，避免 plan 階段再回頭猜測落點。

### 4. 非範圍 (Non-Scope)
- **全面取代單元測試或其他低成本測試**：S 層專注於跨層 contract 驗證，不以覆蓋所有內部邏輯或追求 100% coverage 為目標。
- **以 Host-only 結果取代 Docker baseline**：Host runner 若存在，也只能作為輔助入口，不能推翻 Docker 作為真實結果基準。
- **大規模重寫既有業務 smoke**：第一版先處理 S 層定位、契約與優化方向，不直接要求全面搬動既有 suite。
- **把 libs `Smoke` 做成新的業務協調器**：`www/f3cms/libs/Smoke` 只能作為所有 `{Entity}/smoke` 的共用父類，不承接 entity truth、payload ownership 或跨 entity 協調。
- **讓 `www/cli/index.php` 繼續兼任 smoke 正式入口**：第一版要明確切開 CLI general entry 與 S 層 entry，不再讓 smoke test 依附在一般 CLI 入口語意之下。
- **S 對 O 的正式驗證不納入第一版範圍**：雖然 S 層在 FORKS 定義上應最終覆蓋 F、O、R、K，但第一版正式範圍先聚焦 F、R、K，不把 Outfit 驗證當成已完成前提，避免 idea 假裝完整。
- **S 層直接侵入 F、O、R、K 內部實作**：S 層驗證的是公開 contract 與可觀察行為，不以繞過 owner boundary 直接接管內部細節為目標。

### 5. 核心物件與模組邊界 (Core Objects & Module Boundaries)
在 FORKS 架構下，S 層的穩定邊界定義如下：

- **`www/f3cms/modules/{Entity}/smoke.php` (module-owned S 檔)**：Entity / module 自有的 smoke 檔，和 `feed.php`、`outfit.php`、`reaction.php`、`kit.php` 並列，承接該 Entity 的驗證語意、案例與 owner-side 測試責任。
- **`www/f3cms/libs/Smoke.php` (S 層共用父類)**：所有 `{Entity}/smoke` 的父類，只承接共用執行骨架、結果輸出契約與可重用的 smoke base behavior。
- **`www/tests/index.php` (S 層獨立入口)**：Smoke test 的正式 CLI 入口；透過 `www/tests/index.php {path}` 以 `<module>/<surface>/<contract>` 形式 dispatch 到對應 `{Entity}/smoke.php`，用來承接 S 層的執行與路由責任。
- **`www/tests/bootstrap/` (S 層共用 contract)**：承接結果輸出、fixture 載入、例外封裝與 suite 共用流程，不直接承接 F3CMS 業務細節。
- **`www/tests/adapters/f3cms/` (S 層 runtime adapter)**：承接 S 層與 F3CMS runtime 的接線，讓 suite 透過既有框架環境驗證可觀察行為。
- **`www/cli/index.php` (general CLI entry)**：保留一般 CLI 任務入口，不再兼任 S 層 smoke 的正式 dispatch 入口。
- **Docker command layer**：S 層正式執行基準；當 host 與 Docker 結果不同時，以 Docker 為準。
- **Feed / Outfit / Reaction / Kit**：FORKS 內和 `smoke.php` 並列的 entity-owned 檔案。S 層會驗證它們的對外 contract，但不改變它們各自的 owner boundary。

第一版的驗證重點如下：

- **S -> Mobile**：以 `mobile/request/create_or_ensure` 驗證 `Mobile` entity 的 request surface、門號正規化與 dedupe contract。
- **S -> Phonebook**：以 `phonebook/owner/create_with_mobiles` 驗證 `Phonebook` entity 的 owner-side create surface、門號去重與關聯正規化 contract。
- **S -> Campaign**：以 `campaign/request/create_from_phonebook` 驗證 `Campaign` entity 的 request surface、queue expansion 與 `campaign_log` 建立 contract。
- **S -> Outfit (O)**：列為未來應補齊的正式驗證面向，但不納入第一版完成定義。

### 6. 角色與參與者 (Actors and Roles)
- **功能開發者**：需要根據變更風險選擇對應成本的 S 層驗證，並在失敗時快速定位是 contract 問題、資料殘留問題，還是 runtime wiring 問題。
- **維護者 / Reviewer**：依賴 S 層的穩定輸出契約，判斷 regression 是否真實，並確認 F、R、K 的跨層 contract 是否被破壞。
- **CI / 自動化執行者**：消費 S 層標準化輸出，聚合失敗摘要，但不接管 suite ownership。

### 7. 資料與狀態影響 (Data and State Implications)
- **冪等性要求 (Idempotency)**：DB-backed smoke 若寫入資料，必須具備穩定命名規則，例如固定 prefix、slug 或 case key，避免 rerun 時造成 Unique Key 衝突或污染既有測試資料。
- **清理責任 (Cleanup Contract)**：清理責任應在 suite contract 內被明確宣告與執行，不依賴人工進資料庫清除殘留資料。
- **結果上下文要求**：S 層輸出應至少能表達 case、domain、tier、status 與錯誤摘要，讓批次執行與人工檢查都能讀懂失敗原因。
- **分層誠實性**：pure logic smoke、fixture-driven smoke、DB-backed smoke 必須清楚揭露其覆蓋範圍，避免低成本驗證被誤判為已涵蓋完整 runtime contract。

### 8. 限制與依賴 (Constraints and Dependencies)
- **延續既有 TestMode 結論**：本 spec 必須延續既有 canonical path、bootstrap contract、adapter wiring 與 Docker baseline，不重開第一版入口設計。
- **入口責任必須切開**：`www/tests/index.php` 專責 smoke dispatch，`www/cli/index.php` 保持 general CLI；兩者不得再混用語意。
- **libs 只承接 shared runtime**：`www/f3cms/libs/Smoke.php` 若成立，必須遵守 libs 的 shared runtime 邊界，不能因為 smoke 會重用，就把 module-owned 測試語意或業務協調推進 libs。
- **保持 owner boundary**：S 層雖是正式架構層，但仍必須維持 consumer / verifier 角色，不引入破壞 FORK 維護性的 convenience shortcut。
- **漸進導入**：優化必須允許逐支 suite 漸進收斂，不要求一次重寫所有 smoke。
- **runner 不得反客為主**：若新增薄型 runner，它只能作為 S 層 consumer，不可成為新的 source of truth 或新的 drift 來源。

### 9. 風險與未決問題 (Risks and Open Questions)
- **S 層定位過重的風險**：若 S 層被寫成能直接支配 F、O、R、K 的上位層，會破壞 owner boundary，與 FORK 維護性衝突。
- **libs `Smoke` 過胖的風險**：若 `www/f3cms/libs/Smoke.php` 吞入 module-local 測試語意、資料準備或業務協調，會把本應由 `{Entity}/smoke` 擁有的責任錯推到 libs。
- **入口漂移的風險**：若 `www/tests/index.php` 與 `www/cli/index.php` 的責任沒有切開，後續仍會回到 smoke 依附 general CLI 的狀態，讓 S 層入口語意再次混亂。
- **分層規格過細的風險**：若 pure / fixture / DB 分類定義過細，可能增加命名與維護成本，拖慢日常使用。
- **輸出契約過厚的風險**：若結果欄位要求過多，可能使既有 suite 的遷移成本過高。
- **第一版 scope 漂移**：若沒有明確限制第一版只先正式承接 F、R、K，文件很容易高估 S 層已覆蓋 Outfit 驗證。

---

### 10. 實例化規格與情境 (Specification by Example & Scenarios)

**【情境一】S 層以 `Mobile` Entity 作為第一版 SBE 測試之一**
*說明：以最近完成規劃的 SMSSystem 為案例母體，先用 `Mobile` 驗證 entity-owned smoke 如何承接 request surface 與正規化 contract。*

- **Given (假設)** `Mobile/smoke.php` 繼承 `www/f3cms/libs/Smoke.php`，且 `www/tests/index.php mobile/request/create_or_ensure` 會路由到對應的 `Mobile` smoke。
- **When (當)** 開發者以本機格式 `0912345678` 與 E.164 格式 `+886912345678` 連續觸發同一組 `Mobile` request surface 測試。
- **Then (那麼)** `Mobile` smoke 應驗證 request surface 會把本機格式正規化為 `+886912345678`，並重用同一筆 `tbl_mobile` 資料，而不是重複建立兩筆 row。
- **And (並且)** 此案例應作為 `Mobile` entity 的第一版 SBE 測試基準，而不是泛稱的抽象 smoke。

**【情境二】S 層以 `Phonebook` Entity 作為第一版 SBE 測試之一**
*說明：`Phonebook` 應驗證 owner-side create surface 如何透過 Entity 自有 smoke 承接門號去重與關聯正規化。*

- **Given (假設)** `Phonebook/smoke.php` 透過 `www/tests/index.php phonebook/owner/create_with_mobiles` 被觸發，且輸入電話包含 `0912345678`、`+14155550123`、`0912345678` 三筆，其中一筆為重複號碼。
- **When (當)** 該 smoke 執行 owner-side `Phonebook` create surface。
- **Then (那麼)** `Phonebook` smoke 應驗證 `Phonebook` 建立成功，且對應 `mobileIds` 最終只收斂為兩筆正規化後的 `Mobile` 關聯。
- **And (並且)** 此案例應明確表達 `Phonebook` entity 自己如何擁有與 `Mobile` 關聯的驗證語意。

**【情境三】S 層以 `Campaign` Entity 作為第一版 SBE 測試之一**
*說明：`Campaign` 應驗證 request surface 建立任務後，如何展開 `campaign_log` 與 provider 選擇資訊。*

- **Given (假設)** `Campaign/smoke.php` 透過 `www/tests/index.php campaign/request/create_from_phonebook` 被觸發，且其輸入來自一份已建立完成的 `Phonebook`。
- **When (當)** `Campaign` request surface 建立一筆新的發送任務。
- **Then (那麼)** `Campaign` smoke 應驗證系統建立 `tbl_campaign`，並依 `Phonebook` 目標展開對應數量的 `tbl_campaign_log`，同時帶出 provider alias 與 target count。
- **And (並且)** 此案例應成為第一版 `Campaign` entity 的正式 SBE 測試，而不是只用抽象 Reaction contract 描述帶過。

**【邊界情境】Host runner 可以存在，但不得推翻 Docker baseline**
*說明：S 層優化不能把便利性置於 source of truth 之前。*

- **Given (假設)** 開發者使用一個新的 host runner 或其他輔助命令，在本機快速觸發 `www/tests/index.php {path}` 並顯示成功。
- **When (當)** 相同 suite 在 Docker 容器內執行時失敗。
- **Then (那麼)** 團隊應以 Docker 結果作為真實驗證基準。
- **And (並且)** host runner 只能被視為輔助開發入口，不可成為新的 source of truth。

**【邊界情境】Outfit 驗證尚未納入第一版完成定義】
*說明：避免文件以 FORKS 全覆蓋語氣，誤導第一版已承接 Outfit contract。*

- **Given (假設)** 團隊已建立 F、R、K 的 S 層驗證規格與部分 suite。
- **When (當)** 有人檢查第一版 Smoke Test Optimization 是否已完整覆蓋 FORKS 全部五層。
- **Then (那麼)** 文件應明確指出 Outfit 驗證仍是後續待補面向，不屬於第一版完成條件。
- **And (並且)** 第一版不得因為 S 層已正式命名，就假設 O 層驗證已經存在。

