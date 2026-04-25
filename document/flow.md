# Flow Driven Development

串連 `idea -> (discuss) -> plan -> (done) -> check -> (Optimization)`，並標註與 AI 協作時的重點。

這條 flow 本身不分前端或後端，也不綁定特定技術框架。對 F3CMS 而言，差別不在於要不要改 flow，而在於每一步應該補哪些 F3CMS 特有的判斷。

除了 `idea.md`、`plan.md`、`check.md`、`optimization.md` 之外，建議每個 feature 另外維護一份 `history.md`，用來記錄多輪開發過程中的決議、進度、風險與當下狀態。

`history.md` 應視為 flow 開發模式中的強制承接檔，而不是可有可無的補充筆記。只要有發生討論，就應把可承接的摘要寫進 `history.md`；其中凡是與目前進度、stage 推進、阻塞、回退點、下一步有關的討論，都必須在當輪同步更新，不應只留在對話或 PR 討論中。

## Flow 本身

### `idea`
定義需求、目標、範圍、限制、風險與未決事項。

補充原則：`idea` 階段的規格應優先以 example / scenario 驅動收斂，而不是只靠抽象敘述；若需求還無法用幾個穩定情境說清楚，通常代表它仍未準備好進入 `plan`。

### `(discuss)`
針對 `idea` 中未定的部分做討論、收斂與取捨，直到可以進入規劃。

### `plan`
把已確認方向拆成可執行的階段、子任務、驗收點與風險處理方式。

### `(done)`
依照 `plan` 執行實作，並記錄關鍵變更、結果與偏差。

### `check`
對照 `idea` 與 `plan` 驗證是否完成，確認沒有遺漏功能、風險與規格。

### `(Optimization)`
將本次開發沉澱為可複用的規則、詞彙、文件與後續優化方向。

補充規則：
- `(Optimization)` 是整個 flow 的最後一步，不是中途承接文件。
- 在 feature 仍處於 `idea`、`(discuss)`、`plan`、`(done)`、`check` 任一階段時，`optimization.md` 應維持空白，或最多只保留檔名 / 標題，不應預先寫入正式內容。
- 開發中的進度、卡點、下一步、暫時性收斂與中途判斷，都應寫進 `history.md`，不是先寫進 `optimization.md`。
- 只有當 `check.md` 已確認主要開發與驗收已完成，正式進入 `(Optimization)` 後，才開始撰寫 `optimization.md`。

## Command 與 Stage 對應

FDD 指令是操作入口，不等於 stage 本身。stage 仍以 `idea -> (discuss) -> plan -> (done) -> check -> (Optimization)` 為準；指令的責任是幫工程師或 LLM 在正確時機，用正確方式承接這些 stage。

### `FDD Focus`
- 用途：切換目前工作的 target spec，建立本輪承接焦點。
- 對應關係：不是某一個 stage，而是所有 stage 的前置入口。
- 使用時機：開始承接某個 feature、切換到另一個 spec、或 current spec pointer 失效時。

### `FDD Sprint`
- 用途：依 current spec 的 `history.md`、`plan.md`、`check.md` 推進本輪最小可交付工作。
- 對應關係：主要服務 `plan`、`(done)`、`check` 之間的實際推進，也可用於承接仍需收斂的 `(discuss)` 後續工作。
- 使用時機：要落實本輪工作、補驗證、修正 drift、更新 spec 承接內容時。

### `FDD Review`
- 用途：檢查 stage 判定、spec 文件一致性、驗收狀態與程式 / 文件是否漂移。
- 對應關係：不是單一 stage，而是用來校準 `plan`、`(done)`、`check`、`(Optimization)` 前後是否仍一致。
- 使用時機：懷疑文件漂移、承接點不清楚、想先 review 再決定是否繼續 sprint 時。

### `FDD Retrospective`
- 用途：在主要功能與驗收完成後，將穩定規則、詞彙與共用知識沉澱到 `optimization.md`、glossary、guides、references 等文件。
- 對應關係：直接對應 `(Optimization)`。
- 使用時機：`check.md` 已確認主要實作完成，接下來工作以收尾、整理、升格共用規則與封存前處理為主時。

### 總結
- `FDD Focus`：先決定現在在做哪個 spec。
- `FDD Sprint`：推進目前 spec 的最小下一步。
- `FDD Review`：確認目前 spec 的 stage、文件與現況是否一致。
- `FDD Retrospective`：在 feature 完成後做 `(Optimization)` 與知識沉澱。

## Stage Entry / Exit Criteria

FDD 不只定義階段順序，也要定義每一個 stage 何時可以開始、何時才算結束。若 entry / exit criteria 不清楚，實務上很容易出現跳階段、文件先後錯置，或把未收斂的問題帶進下一步。

### `idea`
- Entry：feature 已被提出，但需求、範圍、限制、依賴或風險仍未成形。
- Exit：需求目標、範圍、限制、依賴與主要風險已可描述，且沒有阻止進入 `plan` 的關鍵未決問題；同時主要需求已能用代表性 example / scenario 說明，而不是只剩抽象摘要。

### `(discuss)`
- Entry：`idea.md` 已有內容，但仍存在影響 stage、scope、責任邊界、資料落點或驗收方式的未決議題。
- Exit：足以進入規劃的核心決策已收斂，且相關結論已同步寫入 `history.md`，必要時回寫到 `idea.md`。

### `plan`
- Entry：需求方向與主要決策已確認，不需要再回到開放式探索。
- Exit：已拆出可執行 stage、子任務、驗收點、風險與 fallback，工程師可直接據此進入 `(done)`。

### `(done)`
- Entry：`plan.md` 與 `check.md` 已能明確指出本輪要做哪一個 stage。
- Exit：本輪實作、驗證與承接已完成，且 `history.md` 已更新目前進度、已完成項與下一步。

### `check`
- Entry：已有實作結果可供驗收，而不是只有設計或口頭方案。
- Exit：已能清楚區分已完成、未完成、高風險與可延後項，且下一步優先順序已重新確認。

### `(Optimization)`
- Entry：`check.md` 已確認主要實作與驗收完成，剩餘工作主要是規則沉澱、詞彙整理、文件同步與封存前收尾。
- Exit：穩定規則已回寫到 `optimization.md`，且需要升格到 glossary / guides / references / sidebar 的內容已由工程師明確判定並處理或列單、`history.md` 已壓縮，且 feature 已具備封存或長期維護所需的最小文件集。

## Minimum Deliverable Per Round

FDD 要求每一輪都產出可承接的最小交付物，不能只有討論、只有程式，或只有一段沒有落檔的結論。

### `idea` 輪
- 至少更新一次 `idea.md`，或明確指出尚缺哪些資訊才足以更新；若需求仍抽象，優先補 example / scenario，而不是先堆更多抽象敘述。

### `(discuss)` 輪
- 至少新增一輪 `history.md`，記錄本輪結論、阻塞、回退點與下一步選項。

### `plan` 輪
- 至少新增或修正一段可執行 stage、驗收點或 fallback，而不是只停留在抽象方向。

### `(done)` 輪
- 至少留下可驗證的程式變更、驗證結果，以及對 `history.md` 的承接更新。

### `check` 輪
- 至少標示已完成項、未完成項與下一步，避免驗收結果只停留在對話中。

### `(Optimization)` 輪
- 至少完成一項穩定規則回寫、詞彙整理或封存前整理，而不是只宣告「準備收尾」。

## Drift Handling

FDD 預設多輪對話後一定會出現文件漂移，因此需要一套正式的 drift handling 規則，而不是臨時憑印象判斷。

### 判斷優先序
- 先看 `history.md`：用來判斷目前卡在哪一個 stage、上一輪真正完成了什麼、下一步是什麼。
- 再看 `plan.md` 與 `check.md`：確認正式拆分與驗收是否已跟上 `history.md`。
- 最後才回看 `idea.md`：確認是否是需求本身變了，而不是後續文件尚未同步。

### 漂移處理原則
- 若 `history.md` 與 `plan.md` / `check.md` 不一致，先以最新 `history.md` 判斷當前承接點。
- 若穩定結論只存在 `history.md`，必須回寫到 `plan.md` 或 `check.md`，不能長期只留在歷史摘要中。
- 若 `idea.md` 與後續文件不一致，要先判斷是需求變更、前提被推翻，還是文件落後；不要直接重做設計。
- 若發現前置假設失效，必須明確指出要回退的是哪一段文件或哪一個 stage，不可籠統寫成「重新整理」。
- 在重要漂移尚未處理前，不進入 `(Optimization)`。

## Document And Source-Of-Truth Rules

FDD 不只要求文件有寫，還要求在判斷架構、術語、流程與 feature 現況時，先讀對的文件。若 source of truth 優先序不明確，實務上很容易把 generic framework 假設當成專案事實，或在多輪承接時讀錯入口。

### F3CMS 文件優先
- 理解 F3CMS 架構、術語、責任邊界、流程時，應先以 `document/` 下的文件為主要真實來源，而不是直接套用 generic framework assumptions。
- 若任務是架構、分層、名詞或流程判斷，優先讀 glossary / guides / reference / flow 等共用文件，再開始下結論。

### Feature 文件承接優先序
- 若任務位於 `document/spec/<feature>/`，先讀 `history.md`，用來判斷目前 stage、上一輪真正完成了什麼、下一步是什麼。
- 再讀 `plan.md` 與 `check.md`，確認正式拆分、驗收與文件同步是否已跟上。
- 只有在文件顯示前面尚未完成，或需求 / 前提已被推翻時，才回讀 `idea.md`；不要每次承接都重跑需求設計。

### 使用規則
- 回答或動手前，應先說明目前讀了哪些文件，以及它們如何支撐當前判斷。
- 若 `document/` 內共用文件與 feature 文件不一致，先指出是共用規則未同步，還是 feature 文件漂移，不可直接跳到重做設計。

## Validation And Environment Rules

FDD 不只管文件流，也要管驗證方式。若驗證規則不明確，不同人與不同 LLM 很容易各跑各的路徑，導致結果不可比較。

### 驗證優先順序
- 優先沿用專案既有的 smoke script、task、container command、既有測試入口。
- 若任務涉及 PHP script execution、smoke、workflow verification 或 post-change runtime checks，優先使用專案既有 Docker 環境，不優先用本機 PHP。
- 若 Docker 與 host 結果不同，以 Docker 為準；host-only failure 不直接視為 code regression。

### 資料庫驗證
- 若任務涉及資料庫驗證、查詢或 schema 檢查，連線資訊應以 `.env` 為預設真實來源。
- 不猜測、不硬編碼、不自行替換 `.env` 已提供的驗證帳密。

### 驗證紀錄要求
- 驗證結果應回寫到 `history.md` 或 `check.md`，不要只留在 terminal output。
- 若使用替代驗證路徑，需說明為何未採用既有 smoke / task / Docker 基準。

## Artifact Ownership Matrix

FDD 要求不同類型的資訊放到正確文件，不可長期混寫。

### `idea.md`
- 放：需求目標、範圍、限制、依賴、風險、未決事項、已確認的高層決策。
- 不放：多輪進度、暫時 next step、驗收結果逐輪更新、收尾結論。

### `plan.md`
- 放：stage、子任務、PR 切分、驗收點、fallback、風險處理與實作順序。
- 不放：純歷史討論摘要、尚未收斂的需求雜訊、正式收尾內容。

### `check.md`
- 放：完成項、未完成項、風險、回歸驗收、文件同步清單。
- 不放：尚未驗證的設計幻想、與驗收無關的背景敘事。

### `history.md`
- 放：多輪承接摘要、目前 stage、阻塞、回退點、最新下一步選項。
- 不放：長期穩定而已確定的正式規格終稿；這些應回寫到 `idea.md`、`plan.md`、`check.md`。

### `optimization.md`
- 放：已正式進入 `(Optimization)` 後的穩定規則、詞彙沉澱、後續優化方向、封存前整理。
- 不放：開發中的卡點、中途判斷、暫時性 next step。

### Workspace Instructions / 常駐規則
- 放：跨 feature、跨任務都成立的高頻規則，例如驗證環境、文件優先順序、資料庫驗證來源。
- 不放：單一 feature 的臨時策略、一次性 workaround 或只對某一條 flow 成立的細節。

## Feature Completion And Archive Criteria

FDD 需要明確定義 feature 何時算完成，否則很容易長期停在 `check` 後半完成狀態。

### Feature-level Done
- 主要實作已完成。
- 主要驗收已完成，且高風險項已有明確處置或接受理由。
- `idea.md`、`plan.md`、`check.md`、`history.md` 已彼此對齊，沒有明顯漂移。

### 進入 `(Optimization)` 前最低條件
- 不再有阻擋 release 或主流程承接的功能缺口。
- 下一步已從「補功能」收斂為「補文件、整理規則、沉澱詞彙、封存前處理」。

### 封存前最低條件
- `optimization.md` 已補上穩定規則與後續優化方向。
- `history.md` 已完成壓縮整理，能讓下一位讀者快速承接。
- 需要升格到 glossary / guides / references / sidebar 的內容，已由工程師明確判定為「處理」、「列清單」或「暫不升格」。

### 未達條件時
- 若仍有關鍵 drift、未完成驗收或未收斂 next step，不應宣告 feature 已完成，也不應直接封存。

## History 的角色

`history.md` 不是用來取代 `idea`、`plan`、`check`、`optimization`，而是用來承接多輪對話與開發過程中的狀態。

建議用途：
- 摘要每一輪討論內容，而不是只記最後結論
- 記錄每一輪 discuss 的結論
- 記錄與進度有關的討論，例如目前做到哪裡、下一步先做什麼、哪個假設失效、哪一段需要回退
- 記錄目前正在執行哪一個 stage
- 記錄已完成、未完成與阻塞事項
- 記錄需要回退確認的假設
- 在多輪對話時，作為 AI 與工程師的共同上下文入口

最低要求：
- 只要有討論，就要有對應摘要進 `history.md`
- 只要討論影響進度，就必須當輪更新 `history.md`
- 不可把「目前做到哪裡」「接下來做什麼」只留在聊天室、PR comment 或口頭討論中

換句話說：
- `idea.md` 記錄需求與決策基礎
- `plan.md` 記錄實作拆分
- `check.md` 記錄驗收標準與結果
- `optimization.md` 記錄收尾與後續優化
- `history.md` 記錄過程與當下進度

## SOP

未來新增功能時，應依下列流程實作，落實 Spec-Driven Development。

1. 建立新資料夾：於 `document/spec/<feature>/` 下新增 `idea.md`、`plan.md`、`check.md`、`optimization.md`、`history.md`。
2. 撰寫 `idea.md`：整理需求目標、範圍、風險、依賴、未決事項與必要範例。進入 `plan` 前，不應保留關鍵未決問題。
	- 補充：必要範例不應只作為附錄；應優先用 mainline scenario 與 boundary / counter-example 來收斂規格，避免 `idea.md` 與後續 `plan` 對需求有不同解讀。
3. 初始化 `history.md`：先記錄目前 feature 狀態、已有文件、主要未決事項與下一步。
4. 進行 `(discuss)`：若需求複雜，可先用 AI 或人工 review 摘要現況、拆解風險與討論取捨，直到 `idea` 可落地。每輪有結論或已辨識出進度影響時，同步更新 `history.md`。
5. 撰寫 `plan.md`：列出階段、子任務、PR 切分、驗收點、fallback 與高風險區塊，並在 `history.md` 記錄目前進入哪一個 stage。若規劃過程中有任何影響 stage 順序、範圍或承接方式的討論，也要摘要回寫。
6. 進入 `(done)`：依 `plan` 執行，並持續記錄關鍵變更與 smoke test 結果。每輪完成後，更新 `history.md` 的進度、阻塞與下一步；若中途出現「先做哪一步」「哪段要回退」「哪個前置假設失效」這類進度討論，也必須同步摘要到 `history.md`。
7. 撰寫 `check.md`：用 checklist 驗證完成項、未完成項、風險與回歸檢查結果，並將重要驗收結論回寫到 `history.md`。若驗收討論改變了當前優先順序或下一步，也要一併記錄。
8. 執行 `(Optimization)`：整理規則、詞彙、優化方向與需要同步的文件，再視需要歸檔 `document/spec/<feature>/`。在這一步開始前，`optimization.md` 應保持空白；完成前，先做一次 `history.md` 壓縮整理。

## History 維護原則

當討論有新的"下一步選項"，要主動更新 `history.md`。

### History 格式規範

`history.md` 必須使用固定格式，避免每個 feature 各寫各的，導致後續承接成本過高。

格式要求如下：
- 內容由新到舊排序，最新一輪永遠放最上面。
- 每一輪都只能新增，不可回頭修改舊輪內容。
- 每一輪都必須明確寫出「最新討論的下一步選項」。
- 每一輪的小標題固定使用：`### 第 xx 輪討論結果`。
- 每一輪內容建議用條列式，至少包含本輪結論與下一步選項。

標準格式：

```md
### 第 12 輪討論結果
1. 本輪結論 A
2. 本輪結論 B
3. 最新討論的下一步選項：
	- 選項一
	- 選項二

### 第 11 輪討論結果
1. 本輪結論 A
2. 本輪結論 B
3. 最新討論的下一步選項：
	- 選項一
	- 選項二
```

補充說明：
- 若本輪只有一個明確下一步，也仍應寫成「最新討論的下一步選項」，不要省略這一段。
- 若本輪沒有產生有效結論，也應記錄為「本輪未定案」與當下保留的下一步選項。
- 「只增不改」的意思是：舊輪內容一旦寫入，就不應直接改寫、覆蓋或重排；新資訊只能以新一輪方式加在最上方。

### 什麼資訊應該寫進 history
- 本輪日期或輪次
- 當前 flow 階段
- 本輪討論摘要
- 已確定的結論
- 與進度相關的討論摘要
- 本輪實際變更
- 當前阻塞或風險
- 回退點與假設修正
- 下一步建議

### 哪些討論一定要寫進 history
- 會影響目前 stage 判斷的討論
- 會改變下一步順序的討論
- 會改變 scope、驗收口徑或 integration 路徑的討論
- 發現前置假設有誤、需要回退或改道的討論
- 已完成什麼、尚未完成什麼、現在卡在哪裡的進度討論

### 什麼資訊不應該只留在 history
- 穩定需求定義，應回寫到 `idea.md`
- 穩定實作拆分，應回寫到 `plan.md`
- 穩定驗收標準，應回寫到 `check.md`
- 穩定優化結論，應回寫到 `optimization.md`

也就是說，`history.md` 用來記錄「過程中的狀態」，而不是讓正式文件永遠停留在過時版本。

實務上可把它理解成：
- 正式規格寫進 `idea.md`、`plan.md`、`check.md`、`optimization.md`
- 所有討論摘要先寫進 `history.md`
- 一旦討論內容已穩定，再把正式結論回寫到對應 spec 文件

## History 壓縮 SOP

當 feature 經過多輪對話或 `history.md` 已累積過長時，應做一次壓縮整理，避免後續每輪都重新判讀整份歷史。

### 何時需要壓縮
- `history.md` 已累積多輪紀錄，導致閱讀成本過高
- 已有多個已完成 stage，早期細節不再需要逐段重讀
- 同一批結論在對話中被重複引用
- AI 或工程師已開始依賴重讀全檔才能知道現況

### 壓縮目標
- 保留已確認的重要決議
- 保留目前進行中的 stage 與阻塞點
- 保留尚未完成的關鍵風險
- 移除不再需要逐字保留的中間推論與重複紀錄

### 壓縮做法
1. 先新增一個新的壓縮輪次，放在最上方。
2. 在這個新輪次中整理 `已完成事項`。
3. 再整理 `目前狀態`。
4. 再整理 `未解問題 / 風險`。
5. 最後整理 `最新討論的下一步選項`。
6. 不直接改寫舊輪內容；若需要壓縮，只能新增一輪「摘要性結果」來承接舊資訊。

補充原則：
- 壓縮的是重複描述，不是刪除進度脈絡
- 若某段討論曾改變 stage 推進或回退決策，壓縮後仍應保留該結論
- 若某段討論只影響暫時性推論、且後來已被明確覆蓋，應在新的壓縮輪次中說明已被覆蓋，而不是直接修改舊文

### 壓縮後建議結構
- 仍以 `### 第 xx 輪討論結果` 作為該次壓縮輪次標題
- 先寫壓縮後摘要
- 再寫已完成事項
- 再寫目前進度
- 再寫未解問題 / 風險
- 最後寫最新討論的下一步選項
- 必要時附上「本輪為摘要性壓縮整理」說明

## F3CMS 在各步驟應補的內容

以下內容不是 flow 本身，而是 F3CMS 專案在每一步建議額外補充的判斷。

### 1. `idea` 階段

在 F3CMS 中，`idea` 不應只描述頁面或 API 行為，還要先回答：
- 這個需求涉及哪個業務實體
- 是新模組，還是既有模組延伸
- 是否牽涉多語系、權限、上稿流程、分類、relation 或 SEO
- 這次變更主要影響 Feed、Reaction、Outfit、Kit 哪一層

若這些問題還說不清楚，先不要急著進 `plan`。

可參考：
- [guides/sa_requirement_breakdown.md](guides/sa_requirement_breakdown.md)
- [guides/module_design.md](guides/module_design.md)
- [guides/data_modeling.md](guides/data_modeling.md)

### 2. `(discuss)` 階段

F3CMS 的 `discuss` 重點通常不是 UI 細節，而是責任邊界與資料落點。

至少應討論：
- 這是不是新的 Entity
- 欄位應放主表、`_lang`、`_meta` 還是 relation table
- 哪些行為是 Feed 責任，哪些是 Reaction / Outfit / Kit 責任
- 是否有既有模組可以延伸，而不是新增模組

若這一步討論不完整，後面很容易出現跨層亂放邏輯。

另外，`(discuss)` 階段不只要得出結論，也要把討論摘要寫進 `history.md`。即使本輪尚未完全定案，只要已辨識出目前阻塞、可行方向、回退點或下一步優先順序，也應先記錄，避免下一輪重問同一件事。

### 3. `plan` 階段

在 F3CMS 中，`plan` 建議至少拆出以下層次：
- schema / SQL 變更
- Feed 變更
- Reaction 變更
- Outfit 變更
- Kit 或共用工具變更
- 文件同步項

同時標出：
- 哪些既有模組會被影響
- 哪些 query、join、分頁、排序可能有風險
- 驗收時要看哪些 guide / reference 才能確認方向正確

可參考：
- [guides/create_new_module.md](guides/create_new_module.md)
- [guides/query_and_performance.md](guides/query_and_performance.md)

### 4. `(done)` 階段

F3CMS 的實作順序通常建議是：
1. schema 或 table 調整
2. Feed
3. Reaction / Outfit
4. Kit 或共用工具
5. 文件補齊

這不是硬性規定，但通常比從畫面或 API 先行更穩。

執行時應記錄：
- 實際修改了哪些模組與層級
- 哪些原假設被推翻
- 哪些 smoke test 已完成
- 並同步將當前 stage 進度與下一步更新到 `history.md`

若本輪主要工作其實是「確認下一步」「修正承接點」「決定是否回退某段 plan」，這也屬於應寫入 `history.md` 的進度討論，不可省略。

### 5. `check` 階段

F3CMS 的 `check` 不只看功能有沒有動，還要檢查：
- 模組邊界有沒有跑掉
- 欄位有沒有放錯資料層
- Feed / Reaction / Outfit / Kit 是否各自只做自己的事
- 是否破壞既有查詢、權限、排序或列表行為
- 文件是否同步更新

### Convention-refactor 評估點

`check` 階段中，應固定做一次「是否需要因框架慣例而重構」的評估。這不是可有可無的 brainstorming，而是正式 review checkpoint。

目的：
- 判斷目前 implementation 是否已出現 convention drift
- 判斷這個 drift 是否已嚴重到必須先做局部重構，才能安全承接下一步或完成驗收
- 判斷該重構是本輪正式工作，還是應列入後續 `(Optimization)` / next step

典型徵兆包含：
- 框架邊界失效，例如邏輯放錯層、cross-layer coupling、或其他模組開始跨調不該當成共用 API 的 hook
- 僅被呼叫一次、且語意只是為了包住單一 caller 而存在的函式，不含 smoke test 這類驗證碼中的輔助函式
- 過度窄化的 Feed 函式，只服務單一路徑、單一畫面或單一 workaround，導致查詢能力無法自然重用

### FORK 分工優先級

評估 convention-refactor 時，應先按 FORK 分工優先級判斷，而不是把所有徵兆視為同等級。

- 第一級：必須符合。例如 `mh()` 只能出現在 Feed，transaction begin / commit / rollback 只能由 Feed 持有
- 第二級：應優先收斂。例如只服務單一 caller、沒有穩定語意邊界的函式
- 第三級：結構偏好。例如跨 module 優先只呼叫 Kit / Feed，而不是把別人的 Reaction / Outfit 當共用 API

判斷規則：
- 只要第一級違反，該輪就應優先修正第一級，不應先討論第二級或第三級美化
- 第二級、第三級只能在高優先級規則已成立後，再判斷是否本輪處理

評估結果只分兩類：
- 必須立即重構：若 drift 已影響目前 implementation、review、驗收或下一步承接，且不處理會持續擴散
- 可延後重構：若徵兆已存在，但目前仍可安全完成 feature，且不會立刻污染下一步邊界

判斷規則：
- 若錯的邊界已開始被第二個 caller、第二條流程或下一步設計重複依賴，通常應判定為必須立即重構
- 若只是局部命名不佳、單一函式尚未形成擴散依賴，則可先記錄於 `history.md` 或 `check.md`，待後續收斂
- 若主要 runtime gap、驗收缺口或功能前提尚未補齊，除非 drift 已阻塞當前工作，否則先完成 feature 主線

文件落點：
- 本輪若判定「必須立即重構」，應把原因、範圍與驗證方式寫進 `history.md`，必要時同步更新 `plan.md`
- 本輪若判定「可延後重構」，也應把徵兆與延後理由記錄在 `history.md` 或 `check.md`，不能只留在對話中

可參考：
- [guides/pr_review_checklist.md](guides/pr_review_checklist.md)
- [guides/data_architecture_checklist.md](guides/data_architecture_checklist.md)

### 6. `(Optimization)` 階段

在 F3CMS 中，優化不只代表重構程式，也代表沉澱規則。

進入條件：
- `check.md` 已確認主要開發與驗收完成。
- 目前剩餘工作已收斂為文件、詞彙、規則沉澱與後續優化方向。

未進入本階段前的要求：
- `optimization.md` 應為空白，或最多只保留標題。
- 不可把開發中的中間結論、暫時性 next step、卡點或 API 收斂過程提早寫進 `optimization.md`。
- 這些內容應寫在 `history.md`，待真正進入 `(Optimization)` 後，再將穩定內容沉澱進 `optimization.md`。

常見項目：
- 將 feature 特有詞彙補進 [glossary.md](glossary.md)
- 若形成穩定規則，且工程師判斷具跨 feature 或長期重用價值，再回寫到對應 guides 或 references
- 若新增或升格了共享文件，且導航需要更新，再調整 [_sidebar.md](_sidebar.md)
- 在 `optimization.md` 補上未來可能的改善方向
- 視需要將 `document/spec/<feature>/` 移動到封存區
- 在封存前，先將 `history.md` 壓縮為可承接與可追蹤的最終摘要版本

### 因框架慣例而重構的發生條件

F3CMS 是 convention-driven 架構，因此有些重構不是為了「漂亮」，而是為了把實作重新對齊框架責任邊界。

只有在下列條件成立時，才應把「框架慣例重構」當成當輪正式工作：
- 已出現明確的 convention drift，例如邏輯放錯層、命名已不再表達實際責任、或多個 caller 正在跨調不該作為共用 API 的 hook
- 這個 drift 已開始影響下一步 implementation、review、驗收或長期維護，而不是單純審美問題
- 重構範圍可收斂為局部 resync，不需要藉機重開 feature 設計或擴大 scope
- 可以用既有驗證方式證明行為不變，或至少可用 focused review 清楚界定風險

不應發生的情況：
- feature 的主要前提、驗收或 runtime gap 尚未補齊時，只因「框架看起來比較漂亮」就擴張重構
- 把新慣例當成理由，重做一整層命名或結構，但無法指出具體 drift 與承接收益
- 在沒有穩定 caller、穩定語意、或明確痛點前，過早抽象 presenter / helper / adapter

實務判準：
- 若重構是為了恢復 F3CMS 分層語意、消除 cross-layer coupling、或收斂穩定共用 pattern，屬於有效的 convention-driven refactor
- 若重構只是讓名稱更順眼、但不影響責任邊界、承接成本或驗收品質，則應延後或不做

## AI 協作要點

- 在 `idea` 與 `(discuss)` 階段，要求 AI 先幫忙拆需求、標風險、辨識 Entity 與模組邊界。
- 要求 AI 在每輪討論後把摘要回寫到 `history.md`，尤其是和目前進度、阻塞、回退點、下一步有關的內容。
- 在 `plan` 階段，要求 AI 產出可執行的分階段工作與 checklist，而不是直接寫最終程式。
- 在 `(done)` 階段，要求 AI 明確說明改的是哪一層，避免跨層混寫。
- 在 `check` 階段，要求 AI 以 review 心態檢查建模、分層、命名與驗收遺漏。
- 在 `(Optimization)` 階段，要求 AI 一併指出哪些內容值得升格到 glossary、reference、guide 或 sidebar，並由工程師決定是否真的要建立或更新共享文件。
- 在多輪對話中，優先要求 AI 先讀 `history.md`，再決定是否需要回讀其他 spec 文件。

最低執行要求：
- AI 不應只在完成程式後才更新 `history.md`
- AI 不應只記錄最終結論，而漏掉會影響承接的過程摘要
- 若本輪對話涉及進度判斷，應先更新 `history.md`，再進入下一步

## 專用指令

根據情境選擇指令。

### Done 情境

落實 `document/spec/<feature>` 之 stage `<step-number>`。

1. 載入 `document/spec/<feature>/plan.md` 與 `document/spec/<feature>/check.md`
2. 根據 `plan.md` 執行對應工作
3. 根據 `check.md` 檢查目前結果
4. 若有落差，依檢查結果進一步修正
5. 再次對照 `check.md` 驗證
6. 更新 `history.md` 的目前進度、已完成項與下一步
7. 產生 git commit 所需說明

### Optimization 情境

執行 `document/spec/<feature>` 的文件化與優化。

前提：
- 只有在 feature 已正式進入 `(Optimization)` 時，才能執行這個情境。
- 若目前仍在開發、驗收或承接下一步，應回到 `history.md`、`plan.md`、`check.md` 處理，不應提前填寫 `optimization.md`。

1. 將 feature 的穩定商業規則整理到適合的 spec 文件；若工程師判斷已有跨 feature 或長期重用價值，再升格到 guide 或其他共用文件
2. 將 feature 的特殊詞彙整理進 [glossary.md](glossary.md)
3. 在 `document/spec/<feature>/optimization.md` 中說明未來可能的改進方向
4. 視需要將 `document/spec/<feature>/` 移動到封存區
5. 將 feature 中確定需要跨 feature 共用的規格回寫到適合的共用文件，方便後續協作
6. 若有新增或升格共享文件，再更新 [_sidebar.md](_sidebar.md)
7. 壓縮並整理 `history.md`
8. 產生 git commit 所需說明
