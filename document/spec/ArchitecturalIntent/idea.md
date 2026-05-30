
# OntoCMS 核心類別架構註解與思維移植計畫 - idea.md

前情提要：F3CMS 被重新命名為 OntoCMS-f3 版本，同時目前有 OntoCMS-net、OntoCMS-go 等專案

### 1. 背景與問題定義 (Problem Statement)
OntoCMS 的核心價值在於其「以實體為優先 (Entity-First)」與「Hierarchical FORKS」的分層設計。這些核心的系統慣例與約束，大量實作於 `libs/` 目錄下的基礎類別中（如 `BaseFeed`、`WorkflowEngine` 等）。
然而，隨著系統面臨多語言重構（如移植至 .NET C#）與高度依賴 LLM 輔助開發的時代，單純的原始碼已不足以作為唯一的知識載體。若沒有將 OntoCMS 的「設計思維」與「開發慣例」明確固化於程式碼註解中，負責移植的工程師或 LLM 極易退化為「逐行語法翻譯」，從而遺失了關鍵的架構約束（例如：誤將關聯寫入 JSON、誤讓工作流引擎綁定實體資料表），最終導致新語言版本的架構漂移（Architecture Drift）。

### 2. 目標結果 (Target Outcome)
在現有 OntoCMS 的 `libs/` 核心類別中，建立一套 **「意圖導向 (Intent-Driven)」** 的高階註解體系。
這套註解不再只是傳統的 `@param` 或 `@return`，而是將該類別的「架構定位」、「邊界約束 (Red lines)」、「設計脈絡」與「跨語言移植的對齊點」直接寫入原始碼中。使未來的開發者或 LLM 讀取原始碼時，能 100% 精準捕捉 OntoCMS 的核心思維，確保未來無論以 C#、Go 或 Java 重構，其 FORKS 架構與實體驅動慣例皆能被完美移植。

本計畫的優先順序需再明文化：**先保住 FORKS 分層與 owner boundary，再在該邊界內保住 entity-first 的資料與生命週期語意。**
若兩者在局部實作便利性上看似衝突，應優先選擇能維持 FORKS 邊界清楚、owner 責任穩定、未來 FORK 維護成本較低的寫法。

### 3. 範圍 (Scope)
*   **標的目錄**：聚焦於 `src/conventions/` 或 `libs/` 下的核心類別與介面（如 `BaseFeedRepository`, `WorkflowEngine`, 核心中介軟體/攔截器等）。
*   **本輪明確納入的共享標的**：除既有 `Module`、`Feed`、`Reaction`、`WorkflowEngine`、`Smoke`、`Outfit` 外，也明確將 `Kit`、`EventRuleEngine`、`Autoload`、`Belong`、`Ression`、`Mession`、`Utils.php` 納入 ArchitecturalIntent 的候選範圍；其中 `Utils.php` 雖非 class，而是 shared global runtime surface，仍需以同等嚴格度承接架構註解。
*   **本輪明確納入的 module feed 標的**：在業務模組層只先納入 `fPress`、`fStaff`、`fRole`、`fMenu`、`fTag` 五個 feed；其餘 module feed 本輪明確略過，不在這一批 rollout 內擴張。
*   **註解規範結構**：每個核心類別必須包含以下層級的 Block Comments：
    1.  **Architecture Purpose (架構目的)**：該類別在 FORKS 架構中扮演的角色。
    2.  **Design Intent (設計意圖)**：為何如此設計（例如：為何 `_lang` 與 `_meta` 要分開寫入）。
    3.  **Strict Constraints (絕對約束/防呆紅線)**：移植時絕對不可妥協的原則（例如：禁止使用全自動 ORM Tracking）。
    4.  **Porting Guidelines (重構/移植指南)**：跨語言實作時的對應建議（如 PHP 的動態陣列應對應至 C# 的 DTO 或 Dictionary）。
*   **函式層級覆蓋要求**：納入本計畫的核心類別，不只類別本身要有架構註解；其內每一個函式也必須補上對應的新註解。差別只在詳略，而不是是否需要：核心流程函式需明寫 intent / red line / porting note，較薄的轉送或包裝函式至少也要寫出其所在邊界與不得越界的責任。

### 4. 非範圍 (Non-Scope)
*   **修改現有業務邏輯**：本計畫僅增加註解與 DocBlock，不改動任何現有程式碼的實際執行邏輯 (Runtime behavior)。
*   **未被點名的業務實體模組 (Business Modules)**：除本輪明確點名的 `fPress`、`fStaff`、`fRole`、`fMenu`、`fTag` 外，其餘 module feed 暫不納入，避免把範圍擴張成所有業務模組全面補註解。
*   **傳統低價值註解**：不強制為不包含業務邏輯的 Get/Set 方法補齊廢話式註解，專注於架構級說明。
*   **禁止空白函式承接**：即使不寫冗長說明，也不能讓納入範圍的函式完全沒有新的意圖註解；禁止只留下舊式 `@param` / `@return` 或完全無註解的狀態。

### 5. 核心物件與模組邊界 (Core Objects & Module Boundaries)
*   **`BaseFeed / BaseFeedRepository`**：註解需強調「實體生命週期管理」、「Transaction 邊界」與「Track A/B 雙軌自動化（關聯表與 JSON 快取雙寫）」的嚴格分際。
*   **`WorkflowEngine`**：註解需強調「無狀態領域服務 (Stateless Domain Service)」、「與業務資料解耦 (No persistence schema ownership)」以及「Module-owned log 策略」。
*   **`BaseReaction` (API/互動層基準)**：註解需強調「權限攔截 (Claim-based Auth)」與「拒絕乘載資料庫寫入邏輯」的邊界。
*   **`Kit`**：註解需強調它是 module-owned rule / validation contract 的共享基底，不得為了 reusable convenience 而把 owner-side business rule 推平進 generic helper。
*   **`EventRuleEngine`**：註解需強調它是 shared runtime evaluator，不擁有 owner-side context preload、business coordination 或持久化責任。
*   **`Autoload`**：註解需強調它承接 FORKS layer shift 與 type-prefix resolution，不能被擴張成任意 runtime bootstrap coordinator。
*   **`Belong`**：註解需強調它只承接 shared relation bind / count update 模式，不應吞掉 entity-owned relation policy judgment。
*   **`Ression` / `Mession`**：註解需強調它們是 shared session/security runtime handler，責任在 session fingerprint、storage adapter 與 suspect handling，而非 owner-side auth policy 或業務流程協調。
*   **`Utils.php`**：註解需強調它是 shared global runtime surface，像 `f3()`、`mh()`、`rc()` 這類入口函式要保護 runtime entry / service-locator 邊界，不能放任其退化為 generic utility bin。
*   **`fPress`**：註解需強調它是內容 entity 的 owner-side feed，直接承接 published lifecycle、workflow trace、relation writeback 與內容匯入邊界，不可把這些責任拆回 generic service 或 shared helper。
*   **`fStaff`**：註解需強調它承接 backend identity snapshot、sudo trail 與 role privilege bridge；不得把 staff identity truth 與 session snapshot 責任錯推回 shared session/runtime 層。
*   **`fRole`**：註解需強調它是 backend privilege / auth map 的 owner-side feed，負責角色權限資料語意與對外投影，不應被扁平成單純 option table 或 generic dictionary。
*   **`fMenu`**：註解需強調它承接 tree 結構、parent-child 關係、排序與 orphan cleanup 的 owner-side規則；不可只當成普通 CRUD feed。
*   **`fTag`**：註解需強調它承接內容分類/標記語意與 relation 支點，不應在移植時被錯當成無語意的 generic lookup table。

### 6. 角色與參與者 (Actors and Roles)
*   **架構師 / Senior SA (作者)**：負責將規格書 (`idea.md`, `data_modeling.md`) 中的決策，提煉並寫入程式碼註解。
*   **移植工程師 / SD (讀者)**：在進行 .NET 重構時，依賴這些註解進行強型別設計與架構映射。
*   **LLM 輔助代理 (消費者)**：透過讀取這些充滿 Context 的註解，生成不會違反 OntoCMS 慣例的跨語言程式碼。

### 7. 資料與狀態影響 (Data and State Implications)
*   **活文件化 (Living Documentation)**：程式碼即為架構合約庫。對資料庫無任何 Runtime 影響，但會決定未來新系統產生的 Schema 與 SQL Query 行為是否符合原本的效能與一致性預期。

### 8. 限制與依賴 (Constraints and Dependencies)
*   **註解標準格式**：必須相容於該語言主流的文件產生器工具（如 PHPDoc 或 C# 的 XML Documentation `/// <summary>`），以便未來能直接匯出為靜態技術文件。
*   **文件與註解的黃金交叉**：註解中若有大量背景知識，應直接使用 URL 或相對路徑參照 `document/spec/...`，避免原始碼過度臃腫。
*   **優先級規則**：若「重用便利性」、「局部抽象化」與「FORKS owner boundary」發生拉扯，註解必須明確站在 FORKS 優先的一側，防止移植時把 owner-side 協調錯推進 shared libs。

### 9. 風險與未決問題 (Risks and Open Questions)
*   **註解過期風險 (Comment Rot)**：未來若架構微調，工程師可能只改 Code 卻忘了改這份「架構註解」。
    *   *SA 應對建議*：將「檢查 Core Lib 註解一致性」納入 `pr_review_checklist.md`。

---

### 10. 實例化規格與架構流轉 (Specification by Example & Scenarios)

為了讓未來執行此計畫的工程師清楚「我們要的註解長什麼樣子」，以下提供兩個跨語言思維移植的對比實例：

**【場景一】BaseFeed 雙寫自動化 (Dual-Track Automation) 的註解保護**
*   **Given (假設)** `libs/BaseFeed` 中有一段 `_afterSave()` 方法，負責自動處理關聯寫入。
*   **When (當)** 一個不熟悉 OntoCMS 的 .NET 工程師（或 LLM）嘗試將其轉譯為 C#。
*   **Then (那麼)** 若沒有架構註解，他可能會直接把它寫成單純的關聯表 `INSERT`，遺漏了「為了效能必須同步寫入主表 JSON 快取」的 O(1) 讀取設計。
*   **And (並且)** 因此，我們必須在該方法上加上這段意圖約束：
    ```php
    /**
     * @Architecture_Intent 雙軌自動化 (Dual-Track Automation)
     * 處理多對多關聯時，不僅要寫入實體關聯表(Track A)供 Query JOIN 使用，
     * 必須同時將關聯摘要(ID, Title)轉為 JSON 寫入主表 tags_cache 欄位(Track B)，
     * 確保 CMS 列表頁(limitRows) 永遠不會發生 N+1 查詢。
     * 
     * @Porting_Guide_To_StrongType
     * 在 .NET/Go 重構時，請確保 Track A 與 Track B 包裝在同一個 DbTransaction 內，
     * 且 JSON 快取欄位僅限於 DTO Display 使用，嚴禁做為 WHERE 過濾條件！
     */
    protected function _afterSave() { ... }
    ```

**【場景二】WorkflowEngine 狀態與資料解耦的防腐護城河**
*   **Given (假設)** 工程師正在將 `WorkflowEngine::transit()` 方法移植到新框架。
*   **When (當)** 工程師習慣了 Entity Framework 等重型 ORM，想要在 `WorkflowEngine` 內直接呼叫 `DbContext.SaveChanges()`。
*   **Then (那麼)** 透過我們預先留下的架構註解，工程師會立即被紅線阻擋：
    ```php
    /**
     * @Strict_Constraint 禁止資料庫所有權 (No Persistence Ownership)
     * WorkflowEngine 是一個「無狀態的規則引擎」，它不擁有任何業務實體的資料庫所有權，
     * 也絕對沒有 tbl_workflow_instance 這種全域表。
     * 
     * @Porting_Guide
     * 移植時，此方法的回傳值只能是 TransitionResult 物件 (包含合法性與新狀態)。
     * 具體將 Audit Log 寫入 tbl_press_log 以及將新 status 寫回業務表的責任，
     * 必須交還給呼叫端 (Reaction 層) 在它的 Transaction 內完成。
     */
    public function transit($actionCode, $runtimeContext) { ... }
    ```

---

### SA 視覺化輔助 (Intent-Preservation Flow)

```mermaid
graph TD
    subgraph Original_PHP [現有系統 (PHP)]
        Code[原始碼 (Logic)]
        HiddenIntent[隱含的架構意圖<br>Entity-First, FORKS]
    end

    subgraph Core_Libs_Annotation [本計畫: 意圖固化防腐層]
        DocBlock[架構級註解<br>@Architecture_Intent<br>@Strict_Constraint]
    end

    subgraph Target_Language [未來重構系統 (e.g., C# / Go)]
        LLM[LLM / 工程師]
        NewCode[強型別架構原始碼]
        Preserved[保留了 O(1) 效能與<br>純淨的 FORKS 邊界]
    end

    Code --> Core_Libs_Annotation
    HiddenIntent --> Core_Libs_Annotation
    
    DocBlock -. 作為重構時的 System Prompt .-> LLM
    LLM ==> NewCode
    NewCode ===> Preserved

    classDef plan fill:#e3f2fd,stroke:#1e88e5,stroke-width:2px;
    classDef target fill:#e8f5e9,stroke:#2e7d32,stroke-width:2px;
    class Core_Libs_Annotation plan;
    class Target_Language,Preserved target;
```

---
### SA 結語
這份 `idea.md` 確認了「註解不只是給人看的，更是給架構審查與 LLM 生成程式碼用的『規格合約』」。
一旦您同意這個需求定調，下一步我們就可以在 `plan.md` 中，盤點出需要優先加上架構註解的 5~10 個最核心 `libs/` 類別清單，並開始動手執行「架構意圖的固化工程」！

