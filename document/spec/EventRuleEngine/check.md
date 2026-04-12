# EventRuleEngine Check

## Purpose
- 作為 EventRuleEngine 第一版的驗收與風險清單。
- 確保後續討論與實作不會只停留在 `idea.md` 的敘事層。
- 讓後續 `check` 與 `(Optimization)` 有正式回看基準。

## Current Status

目前狀態：
- [x] 已有 `idea.md`
- [x] 已建立 `history.md`
- [x] 已建立 `plan.md`
- [x] 已建立 `check.md`
- [x] 已完成第一版骨架程式實作
- [x] 已完成 Docker smoke 驗證

## 第一版驗收清單

### A. 需求與邊界
- [x] JSON DSL / AST 的第一版 contract 是否已正式收斂
- [x] 第一版支援的 rule types 是否已明確列出
- [x] RuleEngine、PlayerContext、Evaluator 的責任邊界是否已明確
- [x] Non-Scope 是否足以阻止第一版滑向視覺化編輯器與全域反查 API

### B. 資料與 Schema
- [x] `tbl_duty` 的 JSON 欄位型態需求是否已明確
- [x] relation table 需求是否已明確，例如 `tbl_member_heraldry`
- [x] task / account log 的最小審計欄位是否已明確
- [x] current state 與 history / log 的資料承接是否已區分清楚

### C. 執行與防禦
- [x] 短路求值策略是否已明確
- [x] payload validator 與最大深度限制是否已明確
- [x] rule evaluator 的擴充方式是否符合開閉原則
- [x] PlayerContext 的預載與無狀態假設是否已明確
- [x] evaluator registry contract 是否已明確
- [x] validator / parser / engine 的責任切分是否已明確
- [x] fail-closed 的錯誤分類與回傳策略是否已明確

### D. 查詢與效能
- [x] 待審 / 條件驗證相關查詢需求是否已被盤點
- [x] history / reviewer 稽核查詢需求是否已被盤點
- [x] 大量 history 成長下的索引或歸檔風險是否已被記錄
- [x] JSON 全域反查為非範圍的限制是否已被明確記錄

### E. 模組整合邊界
- [x] module / reaction 與 RuleEngine 的責任分界是否已明確
- [x] task / account 寫回與 log insert 不進入 engine 的原則是否已明確

### F. 驗收與 Fallback 基線
- [x] fail-closed 情境是否已被整理成明確驗收點
- [x] context preload 缺值時的 default / error 原則是否已明確
- [x] registry 缺漏與 validator 白名單不一致時的處置是否已明確
- [x] module integration 不可退化的邊界是否已明確
- [x] 第一版實作前的最小落地順序是否已明確

### G. 第一版骨架實作驗收
- [x] payload validator、parser、registry 與 RuleEngine traversal 是否已有第一版實作
- [x] `WATCHED_VIDEO`、`EXAM_SCORE`、`HAS_BADGE` evaluator 是否已有第一版實作
- [x] Docker smoke 是否已覆蓋 `matched`、`not_matched`、`invalid_payload`、`missing_evaluator`、`context_error`
- [ ] module / reaction integration adapter 是否已實作
- [ ] payload source 是否已由實際 duty / claim / factor 載入路徑承接
- [ ] 是否已補獨立 fixture 或更多 edge-case smoke

## Current Next Step

下一步應承接 `check`，先確認第一版骨架的完成邊界與缺口，特別是 module integration、payload source 承接與更多 edge-case 驗證是否列為下一輪 `(done)` 範圍。