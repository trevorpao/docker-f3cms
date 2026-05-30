# ArchitecturalIntent Check

## Purpose
- 作為 ArchitecturalIntent 第一版的驗收清單。
- 確保這個 spec 的工作重點維持在架構意圖固化，而不是滑向功能重構。
- 讓後續每輪都能用同一份 checklist 判斷是否真的有在降低移植漂移風險。

## Check Basis

本文件依據以下內容建立：
- [idea.md](idea.md)
- [plan.md](plan.md)
- [history.md](history.md)

## 全域驗收清單

### A. 決議承接
- [x] `plan.md` 已承接 `idea.md` 的主軸，目標是固化架構意圖而非修改 runtime
- [x] 已明確限制範圍在核心類別 / conventions / libs
- [x] 已明確要求註解需包含 Architecture Purpose、Design Intent、Strict Constraints、Porting Guidelines
- [x] 已明確要求納入範圍的核心類別內每一個函式都要補新的註解
- [x] 已明確排除低價值註解與逐行解說式註解
- [x] 已明確把 FORKS 分層與 owner boundary 寫成高於局部抽象便利性的優先順序

### B. 邊界保護
- [x] 是否已為第一批核心標的定義 owner boundary 與資料責任
- [x] 是否已把 entity-first 與 Hierarchical FORKS 的紅線寫進註解
- [x] 是否已把 module-owned boundary 與 generic helper 禁區寫進註解
- [x] 是否已避免把 workflow、feed、reaction 的責任重新混寫

### C. 移植可用性
- [x] 註解是否足以讓移植工程師理解跨語言對應策略
- [x] 註解是否足以讓 LLM 避開常見錯誤映射
- [x] 是否已明確指出不可妥協的 persistence / transaction / owner boundary 規則

### D. 文件同步
- [x] `history.md` 已建立並能描述目前 stage 與下一步
- [x] `plan.md` 已建立並能提供可執行 stage
- [x] `check.md` 已建立作為後續驗收基準
- [ ] 若有穩定術語，是否已同步回寫 `document/` 共用文件

## Stage 驗收清單

### Stage 1: 固定註解樣板與評估基準
- [x] 已固定核心註解模板
- [x] 已固定函式層級註解覆蓋規則
- [x] 已固定最小欄位與紅線
- [x] 已說明何時引用 `document/` 而不把背景全文塞進原始碼

### Stage 2: 盤點第一批核心標的
- [x] 已列出第一批與後續延伸候選的核心類別、介面或必要的 shared global runtime surface
- [x] 已說明每個標的的優先順序與風險
- [x] 已覆蓋至少一條關鍵 owner boundary 主線

### Stage 3: 補第一批類別的架構意圖註解
- [x] 已在第一批標的上落地註解
- [x] 納入範圍的函式是否已完成逐一註解覆蓋
- [x] 若標的是 shared global runtime surface，是否已明確要求檔案層級定位與函式層級覆蓋
- [x] 註解未改動 runtime behavior
- [x] 註解已明確保護跨語言移植紅線

### Stage 4: 文件同步與封存前整理
- [x] 穩定規則已回寫到共用文件
- [x] `history.md`、`plan.md`、`check.md` 已與實際結果同步
- [x] 已判斷是否可進入 `(Optimization)`

## Current Status

- [x] 已完成 spec 基礎承接文件初始化
- [x] 已完成註解模板、紅線與第一批核心標的盤點
- [x] 已開始註解實作，`Module`、`Feed`、`WorkflowEngine`、`Reaction`、`Smoke`、`Outfit` 六個 FORKS 核心共享基底 / 類別已完成
- [x] 第二批 target `Kit`、`EventRuleEngine`、`Autoload`、`Belong`、`Ression`、`Mession`、`Utils.php` 已完成 class / trait / file-level 與函式層級註解 rollout
- [x] `Module.php` 已完成函式層級註解覆蓋
- [x] `Reaction.php` 已完成函式層級註解覆蓋
- [x] `Feed.php` 已完成函式層級註解覆蓋
- [x] `WorkflowEngine.php` 已完成函式層級註解覆蓋
- [x] `Smoke.php` 已完成函式層級註解覆蓋
- [x] `Outfit.php` 已完成函式層級註解覆蓋
- [x] `Kit.php` 已完成函式層級註解覆蓋
- [x] `EventRuleEngine.php` 已完成函式層級註解覆蓋
- [x] `Autoload.php` 已完成函式層級註解覆蓋
- [x] `Belong.php` 已完成函式層級註解覆蓋
- [x] `Ression.php` 已完成函式層級註解覆蓋
- [x] `Mession.php` 已完成函式層級註解覆蓋
- [x] `Utils.php` 已完成檔案層級與函式層級註解覆蓋
- [x] 已完成重要 module feed 評估，並挑出下一批具有架構註解價值的 feed 候選
- [x] module feed 範圍已收斂為 `fPress`、`fStaff`、`fRole`、`fMenu`、`fTag` 五個指定 target，其餘 feed 本輪略過

## Current Next Step

先只做一件事：若要把 ArchitecturalIntent 從 shared libs 延伸到 module feed，先從使用者指定清單中的 `fPress` 開始切第一個最小 target；`fStaff`、`fRole`、`fMenu`、`fTag` 依序排後，其餘 feed 本輪不再承接。