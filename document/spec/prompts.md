# Flow 常用提示詞

本文件用來記錄依 [../flow.md](../flow.md) 開發時常用的提示詞模板。

使用方式：
- 將 `<feature>`、`<stage>`、`<module>`、`<path>` 這類 placeholder 換成實際值。
- 優先先跑 `idea -> (discuss) -> plan`，不要跳過前段直接要求產碼。
- 若需求已經很清楚，可直接選用對應階段的 prompt，而不是整套全部重跑。
- 多輪對話時，優先先讀 `history.md`，再視需要回讀 `idea.md`、`plan.md`、`check.md`、`optimization.md`。

## FDD 常駐規則對應模板

這一節不是 feature-specific prompt，而是把目前已穩定的 FDD 常駐規則，改寫成可直接複用的 prompt 模板，降低 LLM 在實作時漏接環境、文件與承接順序的機率。

### 通用前置模板

在開始前，請先套用以下常駐規則：

1. 若任務涉及驗證、smoke、PHP script execution、workflow verification 或 post-change runtime checks，優先使用專案既有 Docker 環境，不優先用本機 PHP。
2. 若任務涉及資料庫驗證、查詢或 schema 檢查，連線資訊以 `.env` 為預設真實來源，不要猜測或硬編碼帳密。
3. 若任務涉及 F3CMS 架構、術語、流程或 feature 承接，先讀 `document/` 下對應文件，不要直接套用 generic framework assumptions。
4. 若任務位於 `document/spec/<feature>/`，先讀 `history.md`，再用 `plan.md` 與 `check.md` 判斷下一步；除非文件顯示前面未完成，否則不要回到 `idea.md` 重跑。

要求：
- 在真正動手前，先說明你準備讀哪些文件、使用哪個驗證環境，以及為什麼。
- 若發現文件漂移，先指出漂移點，不要直接重做設計。

### Docker 優先驗證模板

本輪任務涉及驗證或 smoke，請先套用以下規則：

1. 優先使用專案既有 Docker compose service 與 container path 執行驗證。
2. 除非 Docker 不可用或任務明確要求 host environment，否則不要先用本機 PHP。
3. 若 host 與 Docker 結果不同，以 Docker 為準；不要把 host-only failure 直接判定為 code regression。

請執行順序：
1. 先確認要使用的 Docker service / container path
2. 再執行既有 smoke 或驗證指令
3. 最後回報驗證結果與是否需要更新 `history.md` 或 `check.md`

### 資料庫驗證模板

本輪任務涉及資料庫驗證，請先套用以下規則：

1. 以 `.env` 中的資料庫連線資訊為預設真實來源。
2. 不要猜測、硬編碼、替換或省略 `.env` 已提供的驗證帳密。
3. 若 Docker 與 `.env` 同時存在，先確認實際驗證目標應以哪個執行環境為準，再開始查詢。

要求：
- 先說明你會用哪組 `.env` 連線資訊作為驗證基準。
- 若需要執行 SQL 或檢查 schema，先說明使用的是容器內還是其他既有驗證路徑。
- 不要在回覆中主動展開敏感帳密內容，除非任務本身明確要求。

### F3CMS 文件優先模板

本輪任務涉及 F3CMS 架構、術語、責任邊界或流程判斷。

請先套用以下規則：
1. 把 `document/` 視為主要真實來源，不要直接依 generic MVC / CMS 假設下結論。
2. 若是架構或分層問題，優先讀 guide / glossary / reference。
3. 若是 feature 承接問題，優先讀 `document/spec/<feature>/history.md`，再讀 `plan.md`、`check.md`。

回答順序要求：
1. 先說明你讀了哪些 `document/` 文件
2. 再摘要目前已確認的專案規則或 feature 狀態
3. 最後才給建議、修改或下一步

### History 優先承接模板

請承接 `document/spec/<feature>/` 的現況。

執行順序要求：
1. 先讀 `document/spec/<feature>/history.md`
2. 再讀 `plan.md` 與 `check.md`
3. 只有在文件內容顯示前面未完成或需求已變更時，才回讀 `idea.md`

要求：
- 先明確回答目前在哪一個 flow stage
- 列出上一輪真正完成了什麼
- 指出當前最小下一步
- 若發現文件漂移，先說明要回退或同步的是哪一段，不要直接重做整份設計

### FDD Sprint 模板

請依 Flow Driven Development 執行這一輪工作，並先套用以下常駐規則：

- 先讀 `document/spec/.current-spec.md` 作為當前目標 spec 的單一真實來源
- 若 `document/spec/.current-spec.md` 不存在、不可讀或未指向有效 spec，立即中斷，不可猜測目標 spec
- 驗證優先使用專案既有 Docker 環境
- 資料庫驗證帳密以 `.env` 為準
- F3CMS 架構、術語與流程以 `document/` 為主
- 先從 current spec 指向的 `history.md` 開始，再用 `plan.md`、`check.md` 判斷下一步

回答與執行順序：
1. 先讀 `document/spec/.current-spec.md`
2. 若指標檔無效，立即中斷並要求先執行 `FDD Focus`
3. 再說明你讀了哪些 current spec 文件與目前判定的 flow stage
4. 再說明本輪要做的最小範圍
5. 若涉及驗證，優先採用 Docker / 既有 smoke 路徑
6. 若涉及資料庫驗證，說明以 `.env` 為基準
7. 若發現 drift，先指出 drift handling，不直接重做設計
8. 完成後指出是否需要更新 current spec 的 `history.md`、`plan.md`、`check.md`

### FDD Review 模板

請依 Flow Driven Development 對目前 feature 或文件集合做 review，目標是檢查 drift、文件一致性與 stage 判斷，而不是直接重做設計或跳進實作。

先套用以下常駐規則：

- 先讀 `document/spec/.current-spec.md` 作為當前目標 spec 的單一真實來源
- 若 `document/spec/.current-spec.md` 不存在、不可讀或未指向有效 spec，立即中斷，不可猜測目標 spec
- F3CMS 架構、術語與流程以 `document/` 為主
- feature work 先讀 current spec 的 `history.md`，再用 `plan.md`、`check.md` 判斷下一步
- 若文件與程式碼不一致，先指出差異與影響，再判斷是否需要回退或同步

Review 順序要求：
1. 先讀 `document/spec/.current-spec.md`
2. 若指標檔無效，立即中斷並要求先執行 `FDD Focus`
3. 再列出你讀了哪些 current spec 文件
4. 先判斷目前處於哪一個 flow stage
5. 檢查 `history.md`、`plan.md`、`check.md`、必要時 `idea.md` 是否彼此一致
6. 檢查文件與現況程式碼 / 驗證結果是否有漂移
7. 先列出 findings，再給出最小修正建議

輸出要求：
- 先列出 drift / 一致性 findings，依嚴重度排序
- 每一項要指出是文件漂移、stage 判斷漂移、驗收漂移，或程式與文件不一致
- 若無重大 findings，也要明確說明目前一致、但仍有哪些殘餘風險或文件缺口
- 不直接重寫整套 spec，除非使用者明確要求

### FDD Retrospective 模板

請依 Flow Driven Development 執行 `(Optimization)` 階段工作，專注在 glossary / guides / references / optimization 收尾，不回頭擴張功能。

先套用以下常駐規則：

- 先讀 `document/spec/.current-spec.md` 作為當前目標 spec 的單一真實來源
- 若 `document/spec/.current-spec.md` 不存在、不可讀或未指向有效 spec，立即中斷，不可猜測目標 spec
- 只有在 `check.md` 已確認主要實作與驗收完成後，才進入 `(Optimization)`
- 穩定規則應從 feature 文件回寫到共用文件，而不是長期只留在 `history.md`
- 若任務涉及驗證，仍優先使用專案既有 Docker 路徑

執行順序要求：
1. 先讀 `document/spec/.current-spec.md`
2. 若指標檔無效，立即中斷並要求先執行 `FDD Focus`
3. 先確認 current spec 是否已具備進入 `(Optimization)` 的條件
4. 盤點哪些穩定規則、詞彙、流程或參考資訊應回寫到 glossary / guides / references
5. 盤點 `optimization.md` 應收錄哪些收尾內容
6. 只做文件沉澱、詞彙整理、收尾同步，不回頭新增功能
7. 完成後指出是否還缺哪些封存前條件

輸出要求：
- 先說明是否符合進入 `(Optimization)` 的前提
- 再列出本輪要同步的共用文件與原因
- 完成後指出還剩哪些 archive / 收尾缺口

### FDD Flow reAlign 模板

請以 `document/flow.md` 為基準，校準 `document/flow.llm.md`。

先套用以下規則：

- `document/flow.md` 是完整版本與主規格
- `document/flow.llm.md` 是低 token 執行版，不負責保存完整背景說明
- 校準目標是保持規則一致，不是把兩份文件寫成同樣長度

執行順序要求：
1. 先讀 `document/flow.md`
2. 再讀 `document/flow.llm.md`
3. 先比對規則是否遺漏、錯位、失真或過度簡化
4. 先列出需要同步到 `flow.llm.md` 的內容
5. 只保留 LLM 執行必需的低 token 規則，不回填長段背景說明

輸出要求：
- 先說明 `flow.llm.md` 是否已與 `flow.md` 對齊
- 列出遺漏規則、表述失真或多餘內容
- 若要修改，以最小必要修改維持低 token 與高一致性

### FDD Focus 模板

請將當前目標 spec 切換為使用者指定的 `document/spec/<Spec>/`，並在此目標下承接後續工作。

先套用以下規則：

- 以 `document/flow.llm.md` 為低 token 執行規則
- 以 `document/flow.md` 為完整規則參考
- 先建立或更新 `document/spec/.current-spec.md`
- 使用者提供的是 spec 資料夾名或 `document/spec/<Spec>` 路徑
- 先做精確比對；若精確比對失敗，且只差大小寫，允許用 case-insensitive 方式解析到唯一 spec
- 若目標 spec 不存在，或 case-insensitive 結果不唯一，立即中斷，不可猜測或自動替換成其他 feature

執行順序要求：
1. 先解析使用者提供的 spec 名稱或路徑
2. 先做精確比對；若精確比對失敗，再做 case-insensitive 比對
3. 確認對應的 `document/spec/<Spec>/` 是否唯一存在
4. 若不存在，或 case-insensitive 比對出現多個候選，立即中斷並回報有效錯誤
5. 若存在，建立或更新 `document/spec/.current-spec.md`
6. 再讀該 spec 的 `history.md`、`plan.md`、`check.md`
7. 先回答目前 stage、上一輪完成項、當前最小下一步
8. 將後續任務都視為在該 spec 目標下執行

輸出要求：
- 先明確說明 `document/spec/.current-spec.md` 已切換到哪個 spec
- 若是靠大小寫 fallback 才成功切換，也要一併說明
- 若 spec 無效或大小寫比對結果不唯一，先中斷並指出找不到或無法唯一判定哪個資料夾
- 若 spec 有效，先摘要目前承接點
- 再處理使用者後續要求

## 初始化

### 建立 spec 目錄

```bash
mkdir -p document/spec/<feature> && cd $_ && touch {idea,plan,check,optimization,history}.md && cd -
```

### 初始化 feature spec

請依 [../flow.md](../flow.md) 的 SOP，為 `document/spec/<feature>/` 初始化 `idea.md`、`plan.md`、`check.md`、`optimization.md`、`history.md` 的基本結構。

要求：
- 不要直接進入實作細節
- 先建立每份文件應有的章節骨架
- 若缺少必要上下文，先列出待補資訊

### 初始化 history

請為 `document/spec/<feature>/history.md` 建立初版內容。

要求：
- 使用固定格式，標題必須是 `### 第 0 輪討論結果`
- 內容由新到舊排序；初始化時先只建立第 0 輪
- 本輪內容至少包含：目前 feature 摘要、目前所處 flow 階段、已有文件狀態、目前已知風險或未決問題、最新討論的下一步選項
- 「最新討論的下一步選項」不可省略，即使目前只有一個下一步也要明確寫出
- 不要用自由散文格式，請用條列式寫成可承接的討論摘要

## Idea 階段

### 新增或修改 idea

目前有新的需求、限制、假設修正或 scope 變動。請先完整理解需求，再更新 `document/spec/<feature>/idea.md` 與必要的 spec 文件。

執行順序要求：
1. 先讀 `document/spec/<feature>/history.md`，理解目前進度、最近結論、阻塞、下一步與已被推翻的假設。
2. 再讀 `document/spec/<feature>/idea.md`；若新需求可能影響拆分或驗收，再補讀 `plan.md`、`check.md`，必要時才讀 `optimization.md`。
3. 先整理「這次新需求到底改變了什麼」：
	- 新增了哪些需求目標或限制
	- 推翻了哪些既有假設
	- 哪些既有結論仍可沿用
	- 會影響哪些 Entity、Module、責任邊界、資料落點或驗收口徑
4. 只有在完整理解後，才開始修改 spec 文件；不要一收到新需求就直接重寫 `idea.md`。

文件更新要求：
- 先以最小必要修改更新 `idea.md`，保留仍然有效的既有內容
- 若新需求已影響 `plan.md`、`check.md` 或目前 flow 階段，必須同步指出哪些段落需要承接修正
- 若有新結論、假設修正、回退點、阻塞或下一步改變，必須同步新增一輪到 `history.md`
- 不要直接產生程式碼
- 不要跳過理解階段直接進入實作方案

輸出要求：
- 先摘要本次新需求對現況的影響
- 再說明你準備修改哪些 spec 文件，以及原因
- 最後再實際更新文件

### 補強 idea 內容

請依 [../flow.md](../flow.md) 的 `idea` 定義，完善 `document/spec/<feature>/idea.md`。

要求：
- 補上需求目標、範圍、限制、依賴、風險、未決事項
- 若是 F3CMS 功能，額外指出可能涉及的 Entity、Module 與層級影響
- 不要直接產生程式碼
- 若有新增或確認結論，順手以新一輪格式補到 `history.md`

### 檢查 idea 是否足夠進 plan

請檢查 `document/spec/<feature>/idea.md` 是否已足以進入 `plan`。

請明確回答：
- 哪些資訊已足夠
- 哪些仍然模糊或互相衝突
- 哪些問題若不先解決，後面會導致重工

### 將需求轉成 F3CMS 問題清單

請將 `document/spec/<feature>/idea.md` 的內容，轉成 F3CMS 需求拆解問題清單。

至少包含：
- 是否為新 Entity
- 是否為既有模組延伸
- 欄位可能落在哪一層
- 可能影響 Feed、Reaction、Outfit、Kit 的哪些責任

## Discuss 階段

### 列出 discuss 議題

請根據 `document/spec/<feature>/idea.md`，列出進入 `plan` 前必須先討論完成的議題。

要求：
- 依重要性排序
- 區分「不解決不能進 plan」與「可延後決定」
- 若是 F3CMS 需求，優先指出 Entity、模組邊界、資料建模與權限風險
- 若已形成明確結論，補一段可回寫到 `history.md` 的決議摘要

### AI 協助收斂風險

請根據 `document/spec/<feature>/idea.md`，摘要現況、主要風險與建議的取捨方案。

要求：
- 提供保守方案與積極方案
- 指出哪些風險需要 fallback 或 adapter
- 不直接寫程式，只協助做決策收斂

### 比較兩種方案

目前有兩種做法：
- 方案 A：<option-a>
- 方案 B：<option-b>

請依 [../flow.md](../flow.md) 的 `(discuss)` 階段精神，比較兩者的風險、維護成本、模組邊界影響與後續擴充性，並給出建議。

## Plan 階段

### 由 idea 產生 plan

請依 [../flow.md](../flow.md) 的 `plan` 定義，根據 `document/spec/<feature>/idea.md` 建立 `document/spec/<feature>/plan.md`。

要求：
- 拆出 stage、子任務、依賴、風險、fallback、驗收點
- 若是 F3CMS 功能，至少區分 schema、Feed、Reaction、Outfit、Kit、文件同步項
- 標出哪些項目適合拆成獨立 commit 或 PR
- 建立或更新 `history.md` 中的目前進度與下一步，且必須使用固定輪次格式新增一輪

### 檢查 plan 是否可執行

請檢查 `document/spec/<feature>/plan.md` 是否已足夠讓工程師直接進入 `(done)`。

請明確指出：
- 哪些 stage 定義清楚
- 哪些 stage 仍過大或不具體
- 哪些驗收點不足

### 將 plan 拆成 stage prompt

請將 `document/spec/<feature>/plan.md` 拆成可逐步執行的 stage prompt。

每個 stage 請提供：
- 目標
- 需要讀的文件
- 需要修改的檔案類型
- 完成後應驗證的項目

## Done 階段

### 執行指定 stage

落實 `document/spec/<feature>` 之 stage `<stage-number>`。

要求：
1. 載入 `document/spec/<feature>/plan.md` 與 `document/spec/<feature>/check.md`
2. 先說明本 stage 的目標與預計修改範圍
3. 實作對應變更
4. 根據 `check.md` 驗證本 stage 已完成的項目
5. 說明尚未完成與風險
6. 更新 `document/spec/<feature>/history.md`
7. 產生 git commit 所需說明

### 只做本階段，不擴張範圍

請只執行 `document/spec/<feature>/plan.md` 中的 stage `<stage-number>`。

要求：
- 不要順手擴做其他 stage
- 若發現前置條件不足，先停下並指出缺口
- 若需更動原 plan，先說明原因

### 依 check 反修 stage

請根據 `document/spec/<feature>/check.md` 與目前實作結果，修正 stage `<stage-number>` 的遺漏項與問題。

要求：
- 先列出缺口
- 再做最小必要修正
- 最後重新對照 `check.md`

## Check 階段

### 由 plan 產生 check

請依 [../flow.md](../flow.md) 的 `check` 定義，根據 `document/spec/<feature>/idea.md` 與 `document/spec/<feature>/plan.md` 建立 `document/spec/<feature>/check.md`。

要求：
- 用 checklist 表達
- 區分功能完成、風險驗證、回歸檢查、文件同步
- 若是 F3CMS 功能，加入模組邊界、資料層落點、權限、query/performance 檢查
- 若有重要驗收結論，補一段可用固定輪次格式回寫到 `history.md` 的摘要

### 執行 check review

請依 `document/spec/<feature>/check.md` 對目前實作做驗收檢查。

請輸出：
- 已完成項
- 未完成項
- 有風險但暫可接受項
- 建議回補項

### 以 code review 角度補 check

請用 code review 的角度檢查 `document/spec/<feature>/check.md` 是否缺少重要驗收點。

優先檢查：
- 邊界責任
- 資料建模
- 回歸風險
- 命名一致性
- 文件同步

## Optimization 階段

### 收斂文件與規則

執行 `document/spec/<feature>` 的文件化與優化。

要求：
1. 在 `document/spec/<feature>/optimization.md` 補上未來可能的改進方向
2. 將 feature 的特殊詞彙整理進 [../glossary.md](../glossary.md)
3. 將 feature 中形成穩定規則的部分回寫到適合的共用文件
4. 指出需要更新的 sidebar、guide、reference 或其他入口文件
5. 壓縮並整理 `document/spec/<feature>/history.md`

### 更新 history.md

請根據這一輪最新討論或文件變更，更新 `document/spec/<feature>/history.md`。

格式要求：
- 只能在最上方新增一輪，不可改寫舊輪內容
- 小標題固定使用：`### 第 xx 輪討論結果`
- 內容由新到舊排序
- 每一輪都必須包含「最新討論的下一步選項」
- 若本輪有假設被推翻、需要回退、目前卡住的 stage 改變，也必須明確寫出

內容要求：
- 摘要本輪新增結論
- 說明哪些既有結論仍成立，哪些被推翻
- 指出目前卡在哪一個 flow 階段
- 寫出最小且合理的下一步，不要一次展開整包重做

禁止事項：
- 不要覆蓋舊輪
- 不要把 `history.md` 改寫成一般會議記錄或自由段落
- 不要省略「最新討論的下一步選項」

### 壓縮 history.md

請依 [../flow.md](../flow.md) 的 History 壓縮 SOP，整理 `document/spec/<feature>/history.md`。

要求：
- 只能新增一輪摘要性壓縮結果，不可直接刪改舊輪
- 小標題固定使用：`### 第 xx 輪討論結果`
- 明確標示本輪為摘要性壓縮整理
- 至少整理：已完成事項、目前狀態、未解問題或風險、最新討論的下一步選項
- 壓縮後仍要保留目前 stage、阻塞點、回退點與仍有效的結論

### 封存 feature spec

請執行 `document/spec/<feature>` 的封存作業。

要求：
1. 檢查 `idea.md`、`plan.md`、`check.md`、`optimization.md`、`history.md` 是否已完整
2. 視需要將 `document/spec/<feature>/` 移動到封存區
3. 更新受影響的導覽或路徑
4. 產生 git commit 所需說明

## F3CMS 常用補充提示

### 判斷這是不是新模組

請根據 `document/spec/<feature>/idea.md` 的需求，判斷這是否應該成為新的 F3CMS Module。

請明確分析：
- 是否有獨立 Entity
- 是否有獨立生命週期
- 是否有獨立資料表需求
- 是否更適合延伸既有模組

### 判斷欄位落點

請根據 `document/spec/<feature>/idea.md` 或目前設計稿，判斷各欄位應放在 main table、`_lang`、`_meta` 或 relation table。

要求：
- 一欄一欄說明理由
- 指出有爭議的欄位
- 若發現目前設計不合理，直接指出

### 規劃 F3CMS 層級變更

請根據 `document/spec/<feature>/plan.md`，列出這次功能會修改哪些 F3CMS 層級。

請至少區分：
- Feed
- Reaction
- Outfit
- Kit
- schema / SQL
- 文件

### 產出 F3CMS review 重點

請根據 `document/spec/<feature>/idea.md` 與 `document/spec/<feature>/plan.md`，產出本功能的 review 重點。

至少包含：
- 模組邊界
- 資料建模
- 權限
- query / performance
- 文件同步

## 快速版提示

### 快速補 idea

請補完 `document/spec/<feature>/idea.md`，只補必要缺口，不改動已確認內容。

### 快速出 plan

請根據 `document/spec/<feature>/idea.md` 直接產出可執行的 `plan.md` 草稿。

### 快速做 check

請根據 `idea.md` 與 `plan.md` 產出 `check.md` 的驗收清單初稿。

### 快速收尾

請整理這次 feature 應同步更新的文件、詞彙與後續優化方向。

## History 操作提示

### 讀取 history 判斷現況

目前正在處理 `document/spec/<feature>/`。

請先讀 `document/spec/<feature>/history.md`，再回答：
- 目前在哪個 flow 階段
- 已完成哪些事
- 下一個最合理的小步驟是什麼

若 `history.md` 資訊不足，再指出還需要補讀哪些 spec 文件。

### 更新 history

請根據本輪對話與目前變更，更新 `document/spec/<feature>/history.md`。

至少包含：
- 本輪完成事項
- 目前進度
- 尚未解決的問題或風險
- 下一步

### 將結論回寫到 history

請把本輪 discuss / plan / check 的明確結論，整理成可直接附加到 `document/spec/<feature>/history.md` 的摘要。

要求：
- 不要重抄整段對話
- 只保留已確認的結論與目前狀態
- 若有需要回退確認的假設，要明確標出

### 檢查 history 是否落後

請檢查 `document/spec/<feature>/history.md` 是否已落後於 `idea.md`、`plan.md`、`check.md` 的最新狀態。

請指出：
- 哪些結論沒有同步進 history
- 哪些進度已過時
- 哪些下一步描述需要更新

### history 壓縮

請壓縮 `document/spec/<feature>/history.md`，讓下一輪對話可以快速承接。

要求：
- 保留已確認決議
- 保留目前進行中的 stage
- 保留未解問題與風險
- 保留下一步
- 刪除重複、已過時或不再需要逐字保留的過程紀錄

### 由 history 產生接手摘要

請根據 `document/spec/<feature>/history.md`，整理一份給下一輪對話直接使用的接手摘要。

至少包含：
- Feature 摘要
- 目前 flow 階段
- 已完成事項
- 當前阻塞或風險
- 下一步

## 多輪對話版提示詞

這一節用來處理同一個 feature 分多輪對話推進的情境。重點不是一次做完，而是每一輪都能先承接 `history.md`，再只往前推進一小步。

### 承接上一輪進度

目前正在處理 `document/spec/<feature>/`。

請先讀：
- `document/spec/<feature>/history.md`
- `document/spec/<feature>/idea.md`
- `document/spec/<feature>/plan.md`
- `document/spec/<feature>/check.md`
- 若需要，再讀 `document/spec/<feature>/optimization.md`

要求：
- 先摘要目前進度
- 明確指出現在卡在哪一個 flow 階段
- 只提出下一個最合理的小步驟，不要直接整包重做

### 只接續上一輪，不重跑全部流程

請承接 `document/spec/<feature>/` 的現況，只處理下一步。

要求：
- 先以 `history.md` 為主判斷現況
- 不要從 `idea` 重新開始，除非文件內容顯示前面確實未完成
- 若前一輪已有明確結論，直接沿用，不要重複討論
- 若發現前置假設有問題，指出是哪一段需要回退

### 根據上一輪結論更新 spec

根據上一輪對話的結論，更新 `document/spec/<feature>/idea.md` 或 `plan.md`。

要求：
- 只更新被新結論影響的段落
- 保留未變動的既有內容
- 補上這次變更對後續 stage 的影響

### 多輪 discuss 收斂

目前 `document/spec/<feature>/idea.md` 已有初稿，但仍有幾個未決問題。

請：
- 先整理目前已確定事項
- 再列出剩餘未決問題
- 只針對未決問題提出收斂建議
- 說明哪些問題解完就可以進 `plan`

### 多輪 plan 細化

`document/spec/<feature>/plan.md` 已有初稿。

請只做以下工作：
- 將 stage `<stage-number>` 細化成更可執行的子任務
- 補上該 stage 的驗收點與風險
- 不改動其他 stage，除非它們和本次細化直接衝突

### 多輪 stage 接力實作

前一輪已完成 `document/spec/<feature>/plan.md` 中的 stage `<stage-number>` 一部分。

請：
- 先確認已完成與未完成項
- 只補完這個 stage 剩下的部分
- 完成後更新對應的 `check.md` 驗證狀態

### 多輪 check 回補

`document/spec/<feature>/check.md` 已指出幾個缺口。

請：
- 先列出需要回補的項目
- 按風險高低排序
- 只處理本輪最重要的 1 至 2 項
- 處理完後重新對照 `check.md`

### 多輪文件同步

目前功能已開發到一半，請檢查是否有文件需要提前同步，而不要等到最後才一次補。

請優先檢查：
- `history.md`
- `glossary.md`
- guides / references 是否已有相關內容需要補註
- `plan.md` 與 `check.md` 是否反映最新決策

### 多輪上下文壓縮

請根據 `document/spec/<feature>/` 目前內容，整理一份可供下一輪對話直接承接的簡短狀態摘要。

請包含：
- 已完成事項
- 目前進行中的 stage
- 尚未解決的關鍵問題
- 下一輪最合理的起手動作

並同步更新 `history.md`，不要只輸出在對話中。

### 多輪 review 模式

目前這個 feature 已經經過數輪修改。

請不要重做設計，只做 review：
- 檢查這幾輪累積後是否出現邊界漂移
- 檢查 `idea.md`、`plan.md`、`check.md` 是否彼此仍一致
- 指出有哪些地方已經偏離原本 flow

### 多輪收尾模式

`document/spec/<feature>/` 已接近完成。

請：
- 判斷目前是否已可進入 `(Optimization)`
- 若還不行，指出最後缺哪幾項
- 若可以，列出本輪應完成的文件化與封存前準備事項

## 反例提示詞

這一節用來記錄容易把 flow 用壞的提示詞。重點不是這些 prompt 完全不能用，而是它們會讓 AI 太快跳步、模糊責任，或直接繞過 spec 文件。

建議閱讀方式：
- 先看錯誤問法
- 再看為什麼有問題
- 最後直接改用建議改寫版本

### 反例 1：直接要求產完整程式

錯誤問法：

請直接幫我把這個功能全部做完。

為什麼有問題：
- 跳過 `idea -> (discuss) -> plan`
- 容易直接把未確認的假設寫死進程式
- 需求一旦模糊，後面通常重工

建議改寫：

請先根據需求補完 `document/spec/<feature>/idea.md`，並指出進入 `plan` 前還有哪些未決問題。

### 反例 2：只從畫面反推實作

錯誤問法：

請照這張畫面直接做出後台欄位和 API。

為什麼有問題：
- 會把 UI 欄位直接當成資料模型
- 容易忽略 Entity、模組邊界與欄位落點
- 在 F3CMS 中常導致 `_meta` 濫用或跨層亂放邏輯

建議改寫：

請先根據這張畫面整理 `idea.md` 中的需求，判斷涉及哪些 Entity、欄位與模組層級，再說明哪些部分才需要後台欄位與 API。

### 反例 3：把 discuss 當成閒聊

錯誤問法：

先隨便聊聊有沒有什麼做法。

為什麼有問題：
- 沒有收斂目標
- 討論容易發散，最後無法進 `plan`
- 不利於把結論回寫到 `idea.md`

建議改寫：

請根據 `document/spec/<feature>/idea.md`，列出進入 `plan` 前必須先討論完成的議題，並區分哪些是阻塞問題、哪些可延後。

### 反例 4：plan 沒有 stage 與驗收點

錯誤問法：

請幫我規劃一下怎麼做就好。

為什麼有問題：
- 產出的 plan 常會過度抽象
- 工程師無法直接執行
- `check.md` 也會跟著無法落地

建議改寫：

請根據 `idea.md` 建立 `plan.md`，拆出 stage、子任務、驗收點、依賴、風險與 fallback。若是 F3CMS 功能，至少區分 schema、Feed、Reaction、Outfit、Kit、文件同步項。

### 反例 5：done 階段順手擴做

錯誤問法：

把這個 feature 順便能做的都一起做一做。

為什麼有問題：
- 很容易跨出目前 stage 範圍
- 使 `plan.md` 與 `check.md` 失真
- 增加 review 與回歸風險

建議改寫：

請只執行 `document/spec/<feature>/plan.md` 中的 stage `<stage-number>`，不要擴張到其他 stage；若需要改動原 plan，先說明原因。

### 反例 6：check 只看功能有沒有動

錯誤問法：

幫我確認這個功能有沒有能跑。

為什麼有問題：
- 驗收會過度偏向 happy path
- 可能漏掉權限、邊界、資料落點與文件同步
- 在 F3CMS 中尤其容易漏掉架構層面的問題

建議改寫：

請依 `document/spec/<feature>/check.md` 做驗收檢查，除了功能完成外，也檢查模組邊界、資料層落點、權限、query/performance 與文件同步。

### 反例 7：把 Optimization 當成可有可無

錯誤問法：

功能做完就好，文件之後再說。

為什麼有問題：
- 規則無法沉澱
- glossary、guides、references 會落後實作
- 團隊知識會留在對話與 PR，而不是文件

建議改寫：

請執行 `document/spec/<feature>` 的文件化與優化，指出這次應同步更新的 glossary、guide、reference 與 sidebar。

### 反例 8：每一輪都從頭重來

錯誤問法：

重新看一下這個 feature，從頭幫我規劃一次。

為什麼有問題：
- 多輪對話容易反覆重做已完成決策
- 已確認的結論會被覆蓋或稀釋
- 造成 spec 文件與對話紀錄不同步

更常見的根本問題是沒有維護 `history.md`，導致每一輪都得重新判讀整包文件。

建議改寫：

請先讀 `document/spec/<feature>/history.md`，承接目前進度與所處 flow 階段，再只提出下一個最合理的小步驟；若 history 不足，再指出需要補讀哪些文件。

### 反例 9：把 AI 當成只會產碼的工具

錯誤問法：

不要分析，直接給我程式碼。

為什麼有問題：
- 會失去 AI 在需求拆解、風險辨識、checklist 與 review 上的價值
- 對複雜功能來說，往往是最差的使用方式

建議改寫：

請先幫我拆需求、列風險與檢查缺口；等 `idea`、`discuss`、`plan` 足夠後，再進入對應 stage 的實作。

### 反例 10：對 F3CMS 問過度泛化的問題

錯誤問法：

這個功能照一般 MVC 最佳實務做就好。

為什麼有問題：
- 會忽略 F3CMS 自己的 Feed / Reaction / Outfit / Kit 分層
- 容易得到看似合理、實際上不適合這個 repo 的答案

建議改寫：

請以 F3CMS 的分層方式分析這個需求，說明哪些責任應放 Feed、Reaction、Outfit、Kit，以及哪些文件需要同步更新。


