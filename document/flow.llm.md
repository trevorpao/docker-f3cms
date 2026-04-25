# Flow Driven Development for LLM

## 0. 指令對應

- `FDD Focus`：切換 current spec，建立本輪承接焦點；不是 stage，本身是所有 stage 的前置入口
- `FDD Sprint`：推進目前 spec 的最小下一步，主要服務 `plan`、`(done)`、`check`
- `FDD Review`：校準目前 spec 的 stage、文件一致性、驗收狀態與程式 / 文件漂移
- `FDD Retrospective`：只在主要實作與驗收完成後使用，直接對應 `(Optimization)`

## 1. Stage 判斷順序

1. 先讀 `document/spec/<feature>/history.md`
2. 用最新一輪判斷目前 stage
3. 再讀 `plan.md` 與 `check.md` 確認下一步
4. 只有在前段未完成或前提失效時才回讀 `idea.md`

### Stage 快速判斷
- `idea`：需求、範圍、限制、依賴、風險仍未成形，或仍無法用穩定的 example / scenario 說清主要需求
- `(discuss)`：仍有影響 scope、stage、責任邊界、資料落點或驗收方式的未決議題
- `plan`：方向已收斂，但尚未拆成可執行 stage / 驗收點
- `(done)`：已有明確 stage 可實作，正在做程式或文件變更與驗證
- `check`：已有實作結果，正在驗收完成項、未完成項與風險
- `(Optimization)`：主要實作與驗收已完成，只剩規則沉澱、詞彙整理、共用文件同步與封存前收尾

## 2. 文件閱讀優先序

1. 先讀 `document/` 內相關共用文件
2. 若是 feature work，先讀 `history.md`
3. 再讀 `plan.md` 與 `check.md`
4. 最後才回讀 `idea.md`

### 補充
- 架構、術語、流程、責任邊界以 `document/` 為主
- 不用 generic framework assumptions 取代專案文件
- 若共用文件與 feature 文件不一致，先標示漂移，不直接改設計

## 3. 驗證環境優先序

1. 先用專案既有 Docker 環境
2. 先用既有 smoke script、task、container command、驗證入口
3. 只有 Docker 不可用或任務明確要求時才用 host

### 資料庫驗證
- 以 `.env` 為連線資訊真實來源
- 不猜測帳密
- 不硬編碼帳密
- 不自行替換 `.env` 已提供的驗證目標

### 結果判斷
- Docker 與 host 結果不同時，以 Docker 為準
- host-only failure 不直接判定為 code regression

## 4. `(Optimization)` 進入條件

1. `check.md` 已確認主要實作完成
2. `check.md` 已確認主要驗收完成
3. 沒有阻擋 release 或主流程承接的關鍵缺口
4. 下一步已收斂為文件同步、規則沉澱、詞彙整理、封存前處理

### 未達條件時
- 不進入 `(Optimization)`
- 先回到 `check` 或前一個仍未完成的 stage

## 5. 每輪最小輸出要求

- `idea`：至少更新一次 `idea.md`，或明確指出還缺哪些資訊；若需求仍抽象，優先補 example / scenario
- `(discuss)`：至少新增一輪 `history.md`
- `plan`：至少新增或修正一段可執行 stage、驗收點或 fallback
- `(done)`：至少留下可驗證的變更、驗證結果、`history.md` 更新
- `check`：至少標示已完成項、未完成項、下一步
- `(Optimization)`：至少完成一項共用規則回寫、詞彙整理或封存前整理

## 6. Drift 發現後的處置順序

1. 先指出 drift 發生在哪裡
2. 先分類 drift 類型
3. 先判斷以哪份文件作為當前承接基準
4. 再決定要同步、回退或補文件
5. 最後才決定是否需要改程式或改設計

### Drift 類型
- 文件漂移：`history.md`、`plan.md`、`check.md`、`idea.md` 彼此不一致
- Stage 漂移：文件描述的當前 stage 與實際工作狀態不一致
- 驗收漂移：`check.md` 與實際驗證結果不一致
- 程式 / 文件漂移：程式現況與 spec 文件不一致

### 承接基準
- `history.md` 優先用來判斷目前 stage 與下一步
- `plan.md` 與 `check.md` 用來確認正式拆分與驗收是否跟上
- `idea.md` 用來判斷是否是需求真的改了

### 處置規則
- 若穩定結論只存在 `history.md`，回寫到 `plan.md` 或 `check.md`
- 若前提失效，明確指出要回退哪一段文件或哪一個 stage
- 未處理重要 drift 前，不進入 `(Optimization)`

## 7. 禁止事項

- 不要跳過 `history.md`
- 不要每次承接都重跑 `idea.md`
- 不要直接重做設計
- 不要把 host 結果覆蓋 Docker 結果
- 不要猜測資料庫帳密
- 不要把進度、阻塞、下一步只留在對話中
- 不要在 feature 尚未完成前提前撰寫正式 `optimization.md`
- 不要在尚有重大 drift 時宣告 feature 完成

## 7.1 因框架慣例而重構的條件

- `check` 階段必做一次 convention-refactor 評估，不是想到才做
- 先按 FORK 分工優先級判斷：第一級違反先修，不能先處理第二級、第三級
- 第一級：`mh()` 只能在 Feed，transaction 只能由 Feed 持有
- 第二級：只服務單一 caller、沒有穩定語意邊界的函式應優先收斂
- 第三級：跨 module 優先只呼叫 Kit / Feed
- 評估徵兆可包含：框架邊界失效、僅被呼叫一次的函式（不含 smoke test）、過度窄化的 Feed 函式
- 評估結果只分兩類：必須立即重構，或可延後重構
- 若 drift 已影響 implementation、review、驗收或下一步承接，判定為必須立即重構
- 若只是局部徵兆，尚未形成擴散依賴，可先記錄到 `history.md` / `check.md`，不必立刻展開重構
- 只有在已出現明確 convention drift 時，才把框架慣例重構當成正式工作
- convention drift 需已影響 implementation、review、驗收或長期維護，不只是風格偏好
- 重構必須能收斂為局部 resync，不可藉機重開 feature 設計或擴張 scope
- 若主要前提、runtime gap、或驗收缺口仍未關閉，先完成 feature，再處理框架慣例重構

## 8. 單次執行最短路徑

1. 先讀相關 `document/` 文件
2. 先讀 `history.md`
3. 判斷目前 stage
4. 讀 `plan.md` 與 `check.md`
5. 定義本輪最小下一步
6. 若需驗證，優先走 Docker / 既有 smoke
7. 若發現 drift，先處理 drift
8. 完成後更新對應文件