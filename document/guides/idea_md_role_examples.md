# FDD idea.md Role Examples

## Purpose
- Show practical `idea.md` examples for SA, SD, and DBA using the same feature.
- Help readers understand that a strong `idea.md` keeps the same core requirement while changing emphasis by role.
- Complement a general `idea.md` writing guide with concrete role-based samples.

## Primary Readers
- SA
- SD
- DBA
- Tech leads reviewing early-stage feature documents
- LLMs generating or critiquing `idea.md`

## Scope
- one shared feature scenario
- three role-oriented `idea.md` examples
- explanation of what each version does well
- comparison guidance for mixed-role collaboration

## Recommended Background
- basic understanding of what `idea.md` is supposed to do in FDD
- familiarity with requirement decomposition, system design, and data modeling roles
- awareness that the same feature can be documented from different professional lenses

## Core Thesis
- A good `idea.md` does not change the feature's truth by role.
- What changes is emphasis.
- SA should emphasize business problem, scope, actors, and outcome.
- SD should emphasize structure, ownership, boundaries, and integration.
- DBA should emphasize data lifecycle, retention, traceability, and query risk.
- The same rule still applies: examples should not be appendix-only. A strong `idea.md` should usually show at least one mainline scenario and one boundary scenario so the feature truth is grounded before planning starts.

## Shared Scenario

The examples below all use the same feature request:

- The organization wants a document approval feature.
- Staff can submit a policy document for review.
- Reviewers can approve, reject, or return the document for revision.
- The system must preserve the current status, approval history, operator identity, and timestamps.
- The first version must support backend operations only.

The three examples are not three different features. They are three role-oriented ways to write the same `idea.md` foundation.

For alignment with the main writing guide, each example below is intentionally written with:
- one mainline scenario that shows the intended first-version behavior
- one boundary scenario that marks an important edge, exclusion, or future-extension line

## Example 1: SA-Oriented idea.md

```md
# Policy Document Approval Idea

## Purpose
- 建立一套政策文件送審與審核機制，讓文件從草稿到核准的流程可被系統追蹤。

## Background / Problem Statement
- 目前政策文件的送審與核准多依賴 email、口頭通知與人工追蹤。
- 文件是否已送審、卡在哪個審核節點、由誰退回、何時核准，缺少可查詢的單一來源。
- 當文件需要修改後再送審時，現況很難快速判斷最新版本是否已被核准。

## Target Outcome
- staff 可在系統中送出政策文件審核請求。
- reviewer 可在系統中看到待審文件並執行核准、退回、拒絕。
- 每筆文件都可查到目前審核狀態與最基本的審核歷程。

## Scope
- 第一版只處理後台政策文件審核。
- 第一版支援 `Draft`、`PendingReview`、`Approved`、`Rejected`、`Returned` 五種狀態。
- 第一版支援單一路徑審核，不處理會簽與平行審查。
- 第一版至少要能記錄送審人、審核人、操作時間與新舊狀態。

## Non-Scope
- 不處理前台公開發布。
- 不處理多層會簽與跨部門平行審核。
- 不處理全文版本比對與附件差異分析。

## Core Objects or Processes
- 政策文件
- 文件審核流程
- 文件審核操作紀錄

## Actors and Roles
- 文件編輯 staff：建立、修改、送審文件
- reviewer：核准、退回、拒絕文件
- admin：查詢與追蹤整體審核狀態

## Data and State Implications
- 文件需具備可查詢的當前審核狀態。
- 文件審核歷程需保留操作人、操作時間、舊狀態、新狀態與操作結果。
- 若文件被退回後重新送審，系統需能判斷當前有效狀態，而不是只看最後一次人工通知。

## Constraints and Dependencies
- 第一版需沿用既有 staff 權限體系。
- 第一版需與既有後台文件管理流程共存，不可要求整個文件模組重做。

## Risks and Open Questions
- 退回後再送審是否視為同一筆流程，或需形成新的審核輪次，仍待確認。
- 若未來加入會簽，是否會影響第一版狀態模型，仍需在 discuss 階段確認。

## Example Scenarios
- 主線 scenario：staff 將文件由 `Draft` 送到 `PendingReview`，reviewer 核准後文件進入 `Approved`，且 staff 與 reviewer 的操作時間都可被追蹤。
- 邊界 scenario：reviewer 將文件由 `PendingReview` 退回到 `Returned`，staff 修改後可再次送審；但第一版仍不處理多位 reviewer 平行審核。
```

### Why This Works For SA
- It states the business problem clearly.
- It separates first-version scope from future possibilities.
- It identifies actors and process steps early.
- It is strong enough for discuss without prematurely deciding exact table structure.

## Example 2: SD-Oriented idea.md

```md
# Policy Document Approval Idea

## Purpose
- 為既有政策文件管理能力補上可追蹤的審核流程，讓文件狀態轉移與審核紀錄可由系統一致判定與承接。

## Background / Problem Statement
- 目前政策文件的審核狀態主要由人工流程維持，系統沒有穩定的狀態轉移邊界。
- 這導致後台操作可能直接覆寫狀態，但沒有一致的 guard、history 與審核責任切分。

## Target Outcome
- 文件狀態轉移需由單一路徑控制，而不是由多個後台 action 各自判斷。
- 審核操作需保留最小 audit trail。
- 第一版需能讓既有文件模組延伸支援審核，而不是另建一套平行文件系統。

## Scope
- 以既有政策文件 entity 為主體延伸，不另拆新 module。
- 第一版只支援 backend approval flow。
- 第一版採單一路徑審核，不處理 parallel review。
- 審核規則需有明確的狀態轉移邊界與角色限制。

## Non-Scope
- 不在第一版導入公開發布流程。
- 不在第一版導入全文版本管理子系統。
- 不處理跨系統 workflow engine 整合。

## Core Objects or Processes
- policy document entity
- approval action request
- status transition boundary
- approval audit log

## Actors and Roles
- editor：建立與送審文件
- reviewer：核准、退回、拒絕
- admin：查詢與追蹤

## Structural Implications
- 現有文件 entity 應保有單一 current status 欄位。
- 審核紀錄應由文件自己的 log / trace 類型資料承接，而不是由 UI 層自行拼湊。
- 審核 action 的進入點應集中在單一 backend coordination path。
- 若未來需要抽成共用 approval capability，第一版也應先保留 entity ownership，不預設一開始就做成泛用平台。

## Data and State Implications
- 需要 current status。
- 需要 approval history，至少能記錄 actor、timestamp、old status、new status、action code。
- 需能區分「當前狀態」與「歷史操作紀錄」。

## Constraints and Dependencies
- 必須沿用既有 staff / permission model。
- 不可要求既有文件 CRUD 全面改寫。
- 需避免新增一組與既有文件 entity 脫鉤的平行資料主體。

## Risks and Open Questions
- 若現有 backend 有多個入口都能改寫文件 status，需先確認哪些入口要被收斂或退場。
- 若未來要支援多層審核，第一版的狀態模型是否足夠延展仍需確認。

## Example Scenarios
- 主線 scenario：editor 送審後，系統只允許 reviewer 透過單一 coordination path 核准、退回或拒絕，且 document current status 與 audit log 同步可查。
- 邊界 scenario：若未來引入第二層 reviewer，不能直接把第二條審核路徑塞進第一版流程；必須先重新確認狀態模型、guard 與 ownership 是否足以承接。
```

### Why This Works For SD
- It exposes ownership and extension strategy early.
- It states what should stay inside the existing entity boundary.
- It identifies where central coordination and status guard should live.
- It prevents discuss from collapsing into page-level pseudo-design.

## Example 3: DBA-Oriented idea.md

```md
# Policy Document Approval Idea

## Purpose
- 讓政策文件審核流程具備可查詢的狀態與歷程，並確保後續查詢、稽核與狀態一致性可以由資料層支撐。

## Background / Problem Statement
- 目前審核流程缺少穩定的資料承接方式，導致狀態、操作人與時間點難以對帳。
- 若後續需要查詢待審文件、追蹤退回原因、統計核准時間，現況資料基礎不足。

## Target Outcome
- 每筆政策文件要有單一可查詢的 current approval status。
- 每次審核操作都要留下最小可稽核紀錄。
- 第一版的資料設計需支撐常見查詢，而不是只夠畫面顯示。

## Scope
- 第一版至少支援文件主體資料與審核紀錄資料的分離。
- 第一版至少支援依 status 查詢待審文件。
- 第一版至少支援依文件查詢審核歷程。
- 第一版至少支援依 reviewer / 時間區間做基本追蹤。

## Non-Scope
- 不在第一版處理全文版本差異保存。
- 不在第一版處理跨文件批次審核分析報表。
- 不在第一版處理大型全文搜尋優化。

## Core Data Concerns
- 文件主資料
- 文件當前審核狀態
- 審核操作歷程
- 操作人與時間戳

## Data and State Implications
- current status 應屬於文件主體或其明確的一對一狀態承接位置。
- approval history 應屬於文件附屬的 log / trace 類型資料，而不是覆寫在主資料列上。
- 每筆 history 至少應能區分 old status、new status、action code、operator、acted_at。
- 若文件被退回後再次送審，資料模型需能支持多次輪轉，而不是只保留最後結果。

## Query and Performance Implications
- 待審列表查詢會以 current status 為核心條件。
- 單筆文件明細會需要 history by parent 查詢。
- reviewer 稽核查詢可能會需要 operator + time range 條件。
- 若 history 成長速度高，需提早考慮索引與歸檔策略。

## Constraints and Dependencies
- 第一版應優先沿用既有文件主鍵與 staff identity。
- 審核紀錄欄位命名需遵守既有命名規則。
- 若現有資料表已存在 log / trace 類型結構，應優先評估延用而非平行新建。

## Risks and Open Questions
- history 是否需要明確輪次欄位，仍待確認。
- 若未來有多 reviewer 平行審核，history 粒度是否足夠，仍待確認。
- 若待審列表量很大，current status 與常用過濾條件的索引策略需在 discuss 階段提前盤點。

## Example Scenarios
- 主線 scenario：系統可查詢所有 `PendingReview` 文件，並可依單筆文件查出完整審核歷程，包含 old status、new status、operator 與 acted_at。
- 邊界 scenario：若業務要求查詢「曾被退回兩次以上的文件」，第一版資料模型應至少保留足夠 history 粒度；但大型跨文件分析報表仍不屬於第一版範圍。
```

### Why This Works For DBA
- It surfaces lifecycle and audit requirements early.
- It makes expected query shapes visible before schema decisions begin.
- It distinguishes current-state storage from history storage.
- It exposes growth and indexing questions before planning turns into DDL work.

## How To Use These Examples

### When The Team Is Small
- one person may write a mixed-role `idea.md`
- use the three examples as a coverage checklist instead of copying one role literally
- still keep both a mainline scenario and a boundary scenario so the mixed-role draft does not collapse back into abstract prose only

### When SA, SD, and DBA All Participate
- SA should produce the initial business-facing version
- SD should enrich it with structure and boundary implications
- DBA should challenge missing data lifecycle, audit, and query assumptions

### When An LLM Drafts The First Version
- ask the model to choose one role lens first
- then review the draft using the other two role lenses
- avoid generating one vague compromise draft that is weak in all three dimensions

## Comparison Summary

The same feature should remain consistent across all three versions.

What changes by role is emphasis:
- SA asks whether the requirement is meaningful, bounded, and actor-aware
- SD asks whether ownership, boundaries, and extension strategy are clear
- DBA asks whether lifecycle, traceability, and query shape are clear

If one of these three views is absent, `idea.md` quality will usually degrade later in discuss or planning.

## Suggested Follow-Up Reading
- a general `idea.md` writing guide
- requirement decomposition guidance for SA
- data modeling guidance for SD and DBA
- module boundary guidance for system designers
- query and performance review guidance for data-heavy scenarios

## Status
- Draft v1