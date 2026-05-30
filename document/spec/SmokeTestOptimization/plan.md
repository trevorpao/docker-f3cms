# SmokeTestOptimization Plan

## Purpose
- 將 SmokeTestOptimization 的 idea 收斂為可執行的 stage、子任務、驗收點、依賴、風險與 fallback。
- 以目前唯一正確的 `idea.md` 為承接基準，重建第一版 FORKS S 層的落地路線。
- 控制第一版範圍，只先承接 S 層正式化、入口切分、libs `Smoke` 父類、module-owned `smoke.php` 與第一個代表案例，不擴張到完整 Outfit 驗證或全面重寫所有 smoke。

## Plan Basis

本計畫目前直接承接 `idea.md` 中已明確成立的核心方向：
- FORKS 在本 spec 中是 module / entity-owned 定義；每個 Entity 理論上都可擁有 `feed.php`、`outfit.php`、`reaction.php`、`kit.php`、`smoke.php` 五個檔案。
- `www/f3cms/modules/{Entity}/smoke.php` 是 entity-owned 的 S 層檔案，承接該 Entity 的測試語意與案例。
- `www/f3cms/libs/Smoke.php` 應作為所有 `{Entity}/smoke` 的共用父類，但只承接 shared runtime / base behavior，不承接 entity truth 或業務協調。
- `www/cli/index.php` 保持 general CLI 入口；`www/tests/index.php {path}` 應成為 smoke test 的正式入口，且 `path` grammar 收斂為 `<module>/<surface>/<contract>`。
- `www/tests/bootstrap/` 與 `www/tests/adapters/f3cms/` 繼續作為 S 層的共用支撐路徑。
- 第一版 SBE 測試母體固定為 `SMSSystem` 的三個 Entity：`Mobile`、`Phonebook`、`Campaign`，其對應 path 分別為 `mobile/request/create_or_ensure`、`phonebook/owner/create_with_mobiles`、`campaign/request/create_from_phonebook`。
- 第一版以 `SMSSystem` 三個 Entity 的 module-owned smoke 承接可觀察 contract，其中 `Mobile` / `Campaign` 目前以 request-side smoke 落地，`Phonebook` 以 owner-side smoke 落地；Outfit 驗證不列為完成條件。
- smoke runtime 需明確要求 `APP_ENV=develop`、`ALLOW_SMOKE_WRITE=1`，且必須以 `SMOKE_DB_NAME` 指向獨立 smoke DB，不可重用 primary `db_name`。

## Current Stage Assessment

目前 feature 已進入 `check` 階段，理由如下：
- `idea.md`、`history.md`、`plan.md`、`check.md` 已形成可承接的文件骨架。
- `www/tests/index.php`、`www/f3cms/libs/Smoke.php` 與三個 SMSSystem module-owned smoke 已落地。
- 三條正式 path 與 guard path 已有 Docker 驗證證據，現階段主要工作是整理已完成項、非範圍項與後續收斂項，而不是繼續補第一版 blocking gap。

因此本文件的承接目標是：
- 把既有 `idea.md` 轉成可執行的 stage plan
- 固定第一版先做哪些最小落地項目
- 為後續 `history.md` 與 `check.md` 建立穩定參照

## Stage Plan

### Stage 1: 固定 S 層責任與入口契約

目標：
- 將 FORKS 中 S 層的 module-owned 與 libs-owned 分工寫成可執行契約。
- 切開 `www/cli/index.php` 與 `www/tests/index.php` 的責任。
- 固定第一版入口規則、path resolution 原則與 autodiscovery 規則，避免後續實作時重新猜入口語意。

主要工作：
- 定義 `www/f3cms/modules/{Entity}/smoke.php` 的最小責任。
- 定義 `www/f3cms/libs/Smoke.php` 的最小責任與禁止事項。
- 定義 `www/tests/index.php {path}` 的 dispatch contract。
- 定義 `www/tests/index.php {path}` 的 autodiscovery contract。
- 定義 `www/cli/index.php` 不再兼任 smoke 正式入口的邊界。

輸出：
- S 層責任矩陣
- tests / cli 入口切分契約
- path resolution、autodiscovery 與 dispatch 最小規格

驗收點：
- 可以清楚回答 `{Entity}/smoke.php` 與 `libs/Smoke.php` 各自擁有什麼責任。
- 可以清楚回答 `www/tests/index.php {path}` 與 `www/cli/index.php` 的分工差異。
- 第一版 path 至少能描述 `mobile/request/create_or_ensure`、`phonebook/owner/create_with_mobiles`、`campaign/request/create_from_phonebook` 如何被路由到對應 smoke。
- 可以清楚回答 `<module>/<surface>/<contract>` 如何自動發現對應 module 的 `smoke.php`，以及 module 不存在、`smoke.php` 缺失、surface/contract 無法解析時應回報什麼錯誤。

依賴：
- `idea.md` 目前對 FORKS/S 層、entrypoint 與代表案例的定義保持穩定。

風險：
- 若這一階段沒有先定清入口責任，後續實作容易回到 smoke 依附 CLI 的舊模型。
- 若這一階段沒有先定清 libs 邊界，`libs/Smoke.php` 容易變成新的業務協調器。

fallback：
- 若 autodiscovery 的 method naming 一時無法完全定案，先固定最小規則為：path 第一段對應 module 名稱、第二段對應 smoke surface、第三段對應 contract method；不要提前擴張 alias 或 fuzzy match。

#### Stage 1 收斂結果

##### A. `www/tests/index.php {path}` 的 autodiscovery contract

第一版既然已固定 path grammar 為 `<module>/<surface>/<contract>`，則 `www/tests/index.php` 應直接提供 autodiscovery，而不是要求人工維護一份靜態 route table。

第一版 autodiscovery 規則固定如下：

1. path 第一段 `module` 對應 `www/f3cms/modules/<Module>/` 目錄。
2. `module` 名稱以 F3CMS 既有 module 命名慣例正規化後比對，例如 `mobile` -> `Mobile`、`phonebook` -> `Phonebook`、`campaign` -> `Campaign`。
3. 找到 module 後，固定尋找 `www/f3cms/modules/<Module>/smoke.php` 作為該 module 的 smoke owner 檔案。
4. path 第二段 `surface` 與第三段 `contract` 由該 module 的 `smoke.php` 解析；第一版不做跨 module fallback。
5. autodiscovery 失敗時必須明確回報失敗原因，不得靜默 fallback 到 `www/cli/index.php` 或其它 legacy 入口。
6. 入口執行前必須先通過 runtime guard：`APP_ENV=develop`、`ALLOW_SMOKE_WRITE=1`、`SMOKE_DB_NAME != primary db_name`。

##### B. 第一版 method resolution 原則

第一版先固定最小 method resolution 契約，避免一開始引入過度複雜的 action router：

1. `smoke.php` 應暴露一個可被 `surface + contract` 組合決定的公開執行入口。
2. 第一版可接受的做法包括：
	- `run($surface, $contract)` 類型的統一入口
	- `runRequestCreateOrEnsure()` 這類由 `surface + contract` 映射出的明確 method
3. 但第一版不接受：
	- 任意字串動態呼叫所有 public method
	- 缺少白名單/顯式規則的反射式暴露
	- surface/contract 解析失敗時模糊 fallback 到相近名稱

##### C. autodiscovery failure vocabulary

第一版至少應區分以下錯誤：

1. module 不存在
2. module 存在但 `smoke.php` 不存在
3. `smoke.php` 存在但不符合 `libs/Smoke.php` 基底契約
4. surface 不存在
5. contract 不存在

這些錯誤應由 `www/tests/index.php` 直接回傳，作為 S 層入口的一部分，而不是留給 PHP fatal error 自然爆出。

##### D. Stage 1 pseudo-spec

以下 pseudo-spec 用來約束第一版 `www/tests/index.php` 的最小實作，不代表最終程式碼樣貌，但後續實作不得偏離這組 contract。

```text
Input:
	php /var/www/tests/index.php <path>

Path Grammar:
	<path> := <module>/<surface>/<contract>

	<module>   := [a-z][a-z0-9_]*
	<surface>  := [a-z][a-z0-9_]*
	<contract> := [a-z][a-z0-9_]*

	Example:
		mobile/request/create_or_ensure
		phonebook/owner/create_with_mobiles
		campaign/request/create_from_phonebook
```

```text
Path Parsing Contract:
	1. `www/tests/index.php` 只接受一個主要 path argument。
	2. path 必須正好可拆成 3 個 segment：module / surface / contract。
	3. segment 數量不足、過多、或含非法字元時，直接回傳 invalid_path。
	4. 第一版不接受 query-style 參數、alias path、模糊補全、或多層 contract path。
```

```text
Module Resolution Contract:
	1. 取 path[0] 作為 module key，例如 `mobile`。
	2. 將 module key 正規化為 F3CMS module 名稱，例如：
		mobile    -> Mobile
		phonebook -> Phonebook
		campaign  -> Campaign
	3. 目標 module 目錄固定為:
		/var/www/f3cms/modules/<Module>/
	4. 若目錄不存在，回傳 module_not_found。
	5. 目標 smoke owner 檔固定為:
		/var/www/f3cms/modules/<Module>/smoke.php
	6. 若 smoke.php 不存在，回傳 smoke_file_not_found。
```

```text
Smoke Bootstrap Contract:
	1. `www/tests/index.php` 載入對應 module 的 smoke.php。
	2. smoke instance 必須符合 `www/f3cms/libs/Smoke.php` 基底契約。
	3. 若 smoke class 不存在、無法建構、或未遵守 base contract，回傳 invalid_smoke_contract。
	4. `www/tests/index.php` 只負責 path parsing、module discovery、smoke bootstrap、result emission。
	5. `www/tests/index.php` 不持有 entity business logic，不直接實作 request/create_or_ensure 等 domain 行為。
	6. `www/tests/adapters/f3cms/bootstrap.php` 在第一次 DB 初始化前，必須建立並切換到 `SMOKE_DB_NAME` 所指定的獨立 smoke DB。
```

```text
Method Resolution Contract:
	第一版允許兩種實作策略，二選一即可，但 spec 需固定為顯式規則：

	Strategy A: Unified Entrypoint
		smoke->run(<surface>, <contract>, <context>)

	Strategy B: Explicit Method Mapping
		<surface>, <contract> 轉為固定 method name，例如：
			request + create_or_ensure -> runRequestCreateOrEnsure
			owner   + create_with_mobiles -> runOwnerCreateWithMobiles
			request + create_from_phonebook -> runRequestCreateFromPhonebook

	限制：
		1. 不允許任意 public method 自動暴露。
		2. 不允許模糊匹配相近 method 名稱。
		3. surface 不存在時回傳 surface_not_found。
		4. contract 不存在時回傳 contract_not_found。
```

```text
Execution Contract:
	1. `www/tests/index.php` 呼叫 smoke 後，結果必須經過統一 result emitter 輸出。
	2. success / failure 都應維持一致結構。
	3. host execution 只視為輔助入口；Docker execution 才是驗證基準。
	4. 不允許 discovery 失敗時 fallback 到 `www/cli/index.php` 或 legacy scripts。
	5. 未明確設定 `APP_ENV=develop` 或 `ALLOW_SMOKE_WRITE=1` 時，入口必須直接拒絕執行。
	6. `SMOKE_DB_NAME` 若缺失、非法、或等於 primary `db_name`，bootstrap 必須直接拒絕執行。
```

```text
Minimal Error Response Schema:
	{
		"code": 0,
		"status": "error",
		"error": "module_not_found",
		"message": "Smoke module 'mobile' not found.",
		"path": "mobile/request/create_or_ensure"
	}

	error 可用值至少包含：
		smoke_env_blocked
		smoke_write_not_allowed
		invalid_path
		module_not_found
		smoke_file_not_found
		invalid_smoke_contract
		surface_not_found
		contract_not_found
		execution_failed
```

```text
Minimal Success Response Schema:
	{
		"code": 1,
		"status": "ok",
		"path": "mobile/request/create_or_ensure",
		"module": "Mobile",
		"surface": "request",
		"contract": "create_or_ensure",
		"result": { ... }
	}
```

```text
Stage 1 Done Condition:
	1. 工程師可依這份 pseudo-spec 直接實作 `www/tests/index.php`。
	2. `mobile/request/create_or_ensure`、`phonebook/owner/create_with_mobiles`、`campaign/request/create_from_phonebook` 三條 path 的解析規則已無歧義。
	3. autodiscovery 與 error vocabulary 已固定，不需再回頭重談入口 contract。
```

### Stage 2: 建立 libs `Smoke` 父類與 module-owned smoke skeleton

目標：
- 在程式結構上建立 S 層最小骨架。
- 讓 `{Entity}/smoke.php` 與 `www/f3cms/libs/Smoke.php` 的關係正式落地。

主要工作：
- 建立 `www/f3cms/libs/Smoke.php` 的最小 base class。
- 定義 base class 應承接的共用能力，例如 execution skeleton、result contract、共用 helper hook。
- 定義 module `{Entity}/smoke.php` 的最小介面或覆寫點。
- 確保 module-owned smoke 保有案例語意與 owner-side responsibility。

輸出：
- `libs/Smoke.php` 最小骨架
- 一份 module-owned `smoke.php` skeleton contract

驗收點：
- 至少能描述一個 `{Entity}/smoke.php` 如何繼承 `libs/Smoke.php`。
- `libs/Smoke.php` 不需要知道 entity truth、module payload 或跨 entity 協調。
- module-owned smoke 不需要直接複製共用 execution skeleton。

依賴：
- Stage 1 已定清 `Smoke` 父類與 module smoke 的責任切分。

風險：
- base class 若一開始塞入太多 helper，後續很難維持 shared runtime 邊界。
- 若 module smoke 的 hook 設計過窄，之後不同 domain 容易回頭複製貼上。

fallback：
- 若共用父類的抽象介面還無法一次定準，先保留最小 abstract skeleton，只承接 run / result / fail handling，不先抽象 DB cleanup 或 domain-specific helper。

### Stage 3: 建立 `www/tests/index.php` dispatch 並串接 SMSSystem 三個 Entity 案例

目標：
- 讓 S 層有獨立可執行入口。
- 以 `SMSSystem` 的 `Mobile`、`Phonebook`、`Campaign` 三個 Entity 案例驗證 dispatch 模型不是空規格。

主要工作：
- 建立 `www/tests/index.php` 的最小 dispatch 流程。
- 依 Stage 1 契約實作 `{path}` 的 autodiscovery 與 dispatch。
- 串接 `mobile/request/create_or_ensure`、`phonebook/owner/create_with_mobiles`、`campaign/request/create_from_phonebook` 作為第一版 entity-based SBE 案例。
- 讓入口能透過既有 `www/tests/bootstrap/`、`www/tests/adapters/f3cms/` 取得 runtime support。

輸出：
- `www/tests/index.php` 最小可執行入口
- `SMSSystem` 三個 Entity 案例的第一版正式承接路徑

驗收點：
- `php /var/www/tests/index.php mobile/request/create_or_ensure` 能成為清楚的正式命令口徑。
- `php /var/www/tests/index.php phonebook/owner/create_with_mobiles` 能成為清楚的正式命令口徑。
- `php /var/www/tests/index.php campaign/request/create_from_phonebook` 能成為清楚的正式命令口徑。
- `APP_ENV=develop ALLOW_SMOKE_WRITE=1 SMOKE_DB_NAME=<isolated_db>` 是正式執行前提。
- 該入口不需要再經過 `www/cli/index.php` 才能 dispatch 到 smoke。
- autodiscovery 不需要手寫三條固定 route，也能根據 module 目錄與 `smoke.php` 自動解析這三個 path。
- 第一版三個 Entity 案例可以透過此入口分別檢查 `Mobile` 正規化 / dedupe、`Phonebook` owner-side 關聯正規化、`Campaign` queue expansion contract。

依賴：
- Stage 1 已固定 path resolution 原則。
- Stage 2 已提供最小 `Smoke` base class 與 module smoke skeleton。

風險：
- 若 dispatch 規則與 module 實際位置耦合過深，之後其他 Entity 會難以沿用。
- 若這一階段直接擴張成批次 runner，容易超出第一版最小範圍。

fallback：
- 若 module-local smoke 與現有 SMSSystem 測試 path 一時無法完全對齊，先允許 `www/tests/index.php` dispatch 到既有三個案例的薄包裝層，但要明確把最終目標維持在 `{Entity}/smoke.php`、`<module>/<surface>/<contract>` grammar 與 autodiscovery 模型，而不是回退成靜態 route map。

### Stage 4: 收斂結果契約、分層驗證與 rerun 規則

目標：
- 把 S 層從「能跑」提升到「可穩定承接 review、rerun 與聚合」。
- 固定第一版結果契約與 smoke tier 語意。

主要工作：
- 定義 pure logic / fixture-driven / DB-backed 三層 smoke 的最小宣告方式。
- 定義成功 / 失敗輸出的固定欄位，例如 `case`、`domain`、`tier`、`status`、`message`。
- 定義 DB-backed smoke 的固定 prefix / slug / cleanup contract。
- 固定 host runner 只能是輔助入口、Docker 才是驗證基準。

輸出：
- S 層結果契約
- tier 分類規格
- rerun / cleanup 規則

驗收點：
- 第一版代表案例與至少一個 DB-backed 案例都能產生一致格式結果。
- DB-backed smoke 可在同一 Docker 環境下穩定重跑。
- 文件能清楚區分低成本 smoke 與完整 runtime contract 的覆蓋差異。

依賴：
- Stage 3 已有正式入口與至少一個代表案例可供驗證。

風險：
- 若結果欄位設計過厚，會提高遷移成本。
- 若把 rerun helper 過度抽進 base class，會再次侵蝕 module-owned 邊界。

fallback：
- 若第一版無法一次統一所有 smoke 類型，先以代表案例與一支 DB-backed case 建立結果契約，再逐步推廣到其他 Entity。

### Stage 5: 文件同步與第一版驗收收斂

目標：
- 讓第一版 S 層落地後可被 history / check / shared docs 穩定承接。
- 補上後續 review 所需的文件化基線。

主要工作：
- 建立並更新 `history.md`，記錄每輪 stage、阻塞與下一步。
- 建立 `check.md`，對齊第一版完成條件與未完成項。
- 視需要同步 glossary、reference 或 guide，沉澱 S 層術語與入口規則。

輸出：
- `history.md`
- `check.md`
- 必要的共享文件同步項

驗收點：
- 第一版的完成條件、未完成項與後續待補面向能在 `check.md` 中被清楚辨識。
- `history.md` 能承接「目前做到哪裡、下一步是什麼」。
- 文件明確保留 Outfit 驗證尚未納入第一版完成定義。

依賴：
- 前述 stage 已完成至少一條實際落地路徑。

風險：
- 若只做程式不補文件，下一輪很容易重新討論 S 層定義。

fallback：
- 若共享文件同步範圍一時無法收斂，先確保 `history.md` 與 `check.md` 完整，再延後 glossary / guide 細化。

## Cross-Cutting Acceptance

第一版整體完成時，至少應同時滿足以下條件：
- `www/tests/index.php {path}` 已成為 S 層正式入口，`www/cli/index.php` 不再兼任 smoke dispatch。
- `www/tests/index.php {path}` 已能依 `<module>/<surface>/<contract>` 自動發現對應 module 的 `smoke.php`，不依賴手寫 route table。
- `www/f3cms/libs/Smoke.php` 已存在，且其責任仍維持在 shared runtime / base behavior。
- 至少一個 module-owned `{Entity}/smoke.php` 已能被正式入口承接。
- `SMSSystem` 的 `Mobile`、`Phonebook`、`Campaign` 三個 Entity 已作為第一版 SBE 測試母體被固定下來。
- 第一版文件仍明確保留 Outfit 驗證為後續待補，而非已完成項。

### SBE-Derived Acceptance Anchors

以下 5 條是直接從 `idea.md` 的 SBE 壓縮出的第一版驗收錨點，後續 `check.md` 應優先沿用這組語句：

1. **Mobile entity contract**：當透過 `www/tests/index.php mobile/request/create_or_ensure` 觸發 `Mobile` smoke 時，系統應把本機門號正規化為 E.164，並確保等價號碼只對應同一筆 `tbl_mobile`。
2. **Phonebook entity contract**：當透過 `www/tests/index.php phonebook/owner/create_with_mobiles` 觸發 `Phonebook` smoke 時，系統應把重複電話去重後收斂成正規化的 `Mobile` 關聯，並保留 `Phonebook` owner-side create 語意。
3. **Campaign entity contract**：當透過 `www/tests/index.php campaign/request/create_from_phonebook` 觸發 `Campaign` smoke 時，系統應建立 `tbl_campaign` 並依目標展開對應的 `tbl_campaign_log` 與 provider alias。
4. **Docker source of truth**：host runner 或其他輔助入口可以存在，但若 host 與 Docker 結果不同，仍以 Docker 結果作為真實驗證基準。
5. **Outfit deferred scope**：第一版完成定義不得宣稱已覆蓋 Outfit 驗證；O 層仍是後續待補面向。

## First-Step Recommendation

若下一輪要開始進入 `(done)`，最小起手順序建議如下：
1. 先建立 `www/tests/index.php` 的 path resolution 與 dispatch contract。
2. 再建立 `www/f3cms/libs/Smoke.php` 的最小骨架。
3. 接著以 `SMSSystem` 的 `Mobile`、`Phonebook`、`Campaign` 三個 Entity 作為第一批 module-owned `smoke.php` 承接案例。
4. 最後再把結果契約與 rerun cleanup 規則往外收斂。