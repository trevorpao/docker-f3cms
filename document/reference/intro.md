# F3CMS Reference 入口頁

## Purpose
- 作為 document/reference 的正式入口頁。
- 幫助讀者快速判斷什麼時候該讀 reference，什麼時候該回到 guides。
- 提供 Feed、Reaction、Outfit 等技術參考文件的導覽地圖。

## Primary Readers
- Backend programmers
- SD
- 需要快速查 API、掛點與流程的工程師
- LLMs 做程式查找與技術摘要時的入口

## Scope
- reference 資料夾的定位
- guides 與 reference 的分工
- 各 reference 文件的閱讀入口
- 常見查詢情境的導航

## LLM Reading Contract
- 將本資料夾視為查表型技術參考，而不是架構決策文件。
- 若問題涉及需求拆解、模組邊界、資料建模或實作順序，優先回到 guides。
- 若問題涉及函式、掛點、輸入輸出格式、覆寫點或常見踩雷，優先閱讀本資料夾。

## Core Thesis
- guides 負責回答「應該怎麼設計」。
- reference 負責回答「實際有哪些方法、流程與掛點可以用」。
- 因此，reference 不是 guides 的重複版，而是工程師在進入實作時的快速查閱層。

## 這個資料夾是做什麼的

document/reference 的角色，是讓工程師在已經知道設計方向之後，可以快速查到實作細節。

它適合回答這類問題：
- Feed 常用哪些方法
- Reaction 的內建 `do_*` 流程是什麼
- Outfit 的渲染流程與常用 helper 有哪些
- 哪些方法適合覆寫，哪些不應該亂放邏輯
- 新人今天要查一個函式時，應該先去哪裡看

它不適合單獨回答這類問題：
- 為什麼這個需求應該拆成新模組
- 這個欄位應該放主表還是 `_lang` / `_meta`
- PR review 應該如何判斷架構風險
- 新工程師應該先建立什麼系統心智模型

這些問題應回到 guides。

## Guides 與 Reference 的分工

### 先讀 Guides 的情境
- 需要理解 F3CMS 的整體定位與適用場景
- 需要做需求拆解
- 需要判斷新模組或舊模組延伸
- 需要做資料建模與表設計
- 需要決定邏輯應放在 Feed、Reaction、Outfit、Kit 哪一層
- 需要做設計審查或 PR review

對應文件通常是：
- [../guides/index.md](../guides/index.md)
- [../guides/overall.md](../guides/overall.md)
- [../guides/sa_requirement_breakdown.md](../guides/sa_requirement_breakdown.md)
- [../guides/data_modeling.md](../guides/data_modeling.md)
- [../guides/module_design.md](../guides/module_design.md)
- [../guides/feed_guide.md](../guides/feed_guide.md)
- [../guides/sd_conventions.md](../guides/sd_conventions.md)

### 先讀 Reference 的情境
- 已經知道要改 Feed，但想查常用方法與踩雷
- 已經知道要改 Reaction，但想查 `do_list`、`do_save`、`do_get` 這些內建流程
- 已經知道要改 Outfit，但想查 `render()`、`wrapper()`、`_staticFile()` 等輸出行為
- 想快速查一個層級有哪些擴充點、常用常數與常見模式

## Reference 文件地圖

### [feed_reference.md](feed_reference.md)
- 用途：快速查 Feed 常用函式、常數、CRUD、分頁、語系、meta、tags、欄位處理與常見踩雷。
- 適合在已經確認資料邏輯應放在 Feed 後使用。
- 最適合的讀者：剛要動手寫或改 Feed 的工程師。

### [reaction_reference.md](reaction_reference.md)
- 用途：快速查 Reaction 的內建 `do_*` 動作、擴充掛點、權限流程、回傳格式與開發建議。
- 適合在已經知道要做後台互動或 JSON API 行為後使用。
- 最適合的讀者：需要接後台列表、儲存、刪除、取單筆的工程師。

### [outfit_reference.md](outfit_reference.md)
- 用途：快速查 Outfit 的路由流程、渲染方式、SEO 設定、Twig 協作與常用 helper。
- 適合在已經知道要改頁面輸出與前台渲染時使用。
- 最適合的讀者：要處理前台頁面、模板與靜態化快取的工程師。

## 常見查詢入口

如果你現在的問題是：
- Feed 有哪些常用 CRUD 與分頁方法，先看 [feed_reference.md](feed_reference.md)
- Reaction 的標準列表、儲存、刪除流程怎麼走，先看 [reaction_reference.md](reaction_reference.md)
- Outfit 如何 render 頁面、輸出 XML、設定 SEO，先看 [outfit_reference.md](outfit_reference.md)
- 不確定邏輯到底該放哪一層，先不要看 reference，先回 [../guides/feed_guide.md](../guides/feed_guide.md) 或 [../guides/module_design.md](../guides/module_design.md)
- 不確定是新模組還是舊模組延伸，回 [../guides/module_design.md](../guides/module_design.md)
- 不確定欄位應該放哪張表，回 [../guides/data_modeling.md](../guides/data_modeling.md)

## 建議閱讀順序

### 對新工程師
1. [../guides/new_engineer_30min.md](../guides/new_engineer_30min.md)
2. [../guides/overall.md](../guides/overall.md)
3. [../guides/data_modeling.md](../guides/data_modeling.md)
4. [../guides/feed_guide.md](../guides/feed_guide.md)
5. 回到 [feed_reference.md](feed_reference.md)、[reaction_reference.md](reaction_reference.md)、[outfit_reference.md](outfit_reference.md) 做實作查閱

### 對正在開發模組的工程師
1. [../guides/module_design.md](../guides/module_design.md)
2. [../guides/data_modeling.md](../guides/data_modeling.md)
3. [../guides/create_new_module.md](../guides/create_new_module.md)
4. 依需求回查 [feed_reference.md](feed_reference.md)、[reaction_reference.md](reaction_reference.md)、[outfit_reference.md](outfit_reference.md)

### 對正在 review 的工程師
1. [../guides/pr_review_checklist.md](../guides/pr_review_checklist.md)
2. 視變更類型回查 [feed_reference.md](feed_reference.md)、[reaction_reference.md](reaction_reference.md)、[outfit_reference.md](outfit_reference.md)

## 維護原則

- guides 更新的是設計原則、決策順序、角色導向閱讀路徑。
- reference 更新的是函式、流程、掛點、回傳格式、常見錯誤與實作示例。
- 若 reference 開始重寫產品定位或架構理念，代表它已經越界，應移回 guides。
- 若 guides 開始變成逐函式手冊，代表它已經越界，應拆回 reference。

## Related Documents
- [../guides/index.md](../guides/index.md)
- [../guides/overall.md](../guides/overall.md)
- [../guides/feed_guide.md](../guides/feed_guide.md)
- [feed_reference.md](feed_reference.md)
- [reaction_reference.md](reaction_reference.md)
- [outfit_reference.md](outfit_reference.md)

## Status
- Draft v1 complete

