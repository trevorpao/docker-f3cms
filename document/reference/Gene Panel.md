Gene Panel 是一款功能強大的後台管理工具，由三思資訊股份有限公司於 **2017 年自主建立**，並被列為該公司的「重要技術成就」之一。它特別為串接現代各種**無頭框架 (headless framework)** 的網頁應用程式而設計。

### Gene Panel 的多面向描述

Gene Panel 的設計旨在解決網站後台管理介面開發中常見的挑戰，例如效率不彰、難以客製化、維護成本高以及使用者體驗不理想等問題。它提供了一套全面且高效的解決方案，幫助企業有效提升管理效能。

以下是 Gene Panel 的主要特點和優勢：

- **核心概念與效能提升**：
    
    - 以**單頁應用程式 (Single Page Application, SPA)** 為核心概念。SPA 是一種網頁應用程序架構，透過動態重寫當前網頁內容，提供使用者流暢且接近桌面應用程式的使用體驗，無需重新載入整個頁面。
    - 具有 SPA 的優點，包括：
        - **流暢的使用者體驗**：頁面載入速度快且無縫，使用者感覺就像在使用桌面應用程式。
        - **效能提升**：減少了伺服器請求，從而提升了效能。
        - **減少伺服器負擔**：大部分界面變更和邏輯運算在客戶端進行，減輕伺服器處理負擔。
        - **增強交互性**：更容易實現豐富的使用者交互和動態效果。
    - 工程師只需專注於 **API 開發**，透過撰寫必要的表單 (HTML 檔) 即可快速建立後台。
- **開發效率**：
    
    - 大幅度提升生產力。
    - 提供**現成的 UI 元件和資源**，讓開發者能快速構建功能完整的後台管理系統。
    - 簡化的設置和自動化功能，大幅減少編碼工作，有效節省時間和精力。
    - 能為需要支援**多語系**的後台介面情境提供一致且可重複使用的解決方案。
- **高度客製化彈性**：
    
    - 開發者只需撰寫 HTML 碼呼叫組件，就能實現各種特殊功能，滿足不同應用需求。
    - 對於更進階、更複雜的需求，Gene Panel 也提供**多樣化元件**，使得高階客製化更加容易。
    - 透過這種設計，網站不再需要依賴大量硬編碼，開發者能快速寫出適合的 HTML 來因應各種特定需求。
- **維護與使用者體驗**：
    
    - **最簡化的代碼結構和大量的應用範例**，讓後續維護更加容易，降低長期運行的風險和成本。
    - **現代化且直覺的 UI 設計**，提升內部使用者的操作體驗，並優化工作效率。
    - **高度適應性設計**，確保它在各種裝置和螢幕尺寸上都能流暢運行，提高使用者滿意度。

### 無頭框架 (Headless Framework) 簡介

Gene Panel 專為串接現代各種**無頭框架 (headless framework)** 的網頁應用程式而設計。無頭框架是一種網站和應用程序開發架構，其核心特點是**將前端（使用者界面）和後端（內容管理、數據處理）分離**。其優勢為：

- 提供極高的**靈活性和可定製性**，開發者可自由選擇前端技術；
- 有助於**提升性能**和**可擴展性**；
- 支援將相同內容輸出到**多個渠道**（如網站、行動應用等）；
- 便於**技術獨立更新**。

### Gene Panel 搭配 F3CMS Reaction 的優勢討論

Gene Panel 作為後台 UI 搭配 F3CMS 系統的 **Reaction (互動反應)** 組件，能發揮顯著的協同優勢。

首先，我們了解 F3CMS 的 **Reaction 組件** 扮演的角色：

- **職責**：Reaction 專門負責處理 **AJAX 等互動式呼叫**，並以 **JSON 格式回應**。
- **特性**：它配合使用異步技術或 WebSocket，有效**提高用戶體驗和系統性能**，使操作反應更快速、即時和流暢。

而因為 F3CMS 的 **Reaction 組件** 存在，使得 F3CMS 類似於無頭框架。使得 Gene Panel 搭配 F3CMS 時，可以獲得以下優勢：

1. **完美配合 API 互動，實現高效數據交換**：
    
    - Gene Panel 以 **SPA** 為核心，其設計理念是透過 **API 進行數據交互**，並在前端動態更新內容，而非頻繁地重新載入整個頁面。
    - F3CMS 的 Reaction 組件正是為此而生，它提供 **JSON 格式的 API 回應**給前端的互動式呼叫。
    - 兩者結合，形成一個高度優化的互動模型，實現了**高效且無縫的數據交換**，充分利用了 SPA 和 RESTful API 的優勢。
2. **極致的動態使用者體驗**：
    
    - Gene Panel 的 SPA 特性本身就能提供「流暢的使用者體驗」。
    - 當它與 Reaction 組件的「快速、即時、流暢的互動反應」 相結合時，能夠為後台管理使用者提供如同桌面應用程式般無縫且即時的操作體驗。例如，在後台進行資料的新增、修改、刪除等操作時，頁面無需刷新，互動即時響應，大幅提升工作效率和使用者滿意度。
3. **減少伺服器負擔與提升整體效能**：
    
    - SPA 架構本身就能有效**減少伺服器請求**，因為許多界面操作和邏輯在客戶端完成。
    - Reaction 組件專注於以 JSON 格式高效傳輸僅必要的數據。這種組合進一步**減輕了伺服器的處理負擔**，同時最大化了前後端數據傳輸的效率和響應速度。這也強化了 F3CMS Hierarchical FORK 架構中 Reaction 組件「強化動態處理能力」的優勢。
4. **符合現代無頭架構趨勢與開發效率**：
    
    - Gene Panel 專為串接**無頭框架**設計。F3CMS Reaction 組件提供標準化的 JSON API 接口，使得 F3CMS 的後端功能可以作為一個強大的「無頭」內容源，供 Gene Panel 這樣的前端 UI 消費。
    - Gene Panel 讓工程師能專注於 **API 開發**並快速建立後台，而 F3CMS Reaction 則提供了這些 API 的快速可靠響應。這種**職責分離**與協同工作，符合 F3CMS 作為一個「高彈性、高效能、易於開發與維護」現代化 Web 應用程式框架的定位，同時也顯著提升了整體開發效率。F3CMS 的架構設計立意之一就是「提供高效開發解決方案」，其整合了不同的框架和服務（如 OperonJS 和 Gene Panel）。

總結來說，Gene Panel 作為一個 SPA 核心的後台管理工具，與 F3CMS 的 Reaction 組件搭配，能夠實現高效、流暢、互動性強的後台管理體驗，同時提升開發效率、降低伺服器負擔，並完全符合現代無頭架構的開發趨勢。

### Gene Panel 後台 API 測試範例

以下範例以本機開發環境 `https://loc.f3cms.com:4433/api/` 為基底，對應 Gene Panel 最常用的後台互動路由。

#### 通用返回格式

後台 Reaction API 預設返回統一 envelope：

```json
{
    "code": 1,
    "data": {},
    "csrf": "<csrf-token>"
}
```

說明：

- `code = 1` 代表成功。
- `data` 為各 API 的實際 payload。
- `csrf` 由 `_return()` 一併刷新。
- 下列 JSON 為測試與串接用範例，實際欄位會依登入者權限、語系、資料內容與模組 `handleIteratee()` / `handleRow()` 調整。

#### 登入檢查

URL:

```text
https://loc.f3cms.com:4433/api/staff/status
```

已登入範例：

```json
{
    "code": 1,
    "data": {
        "isLogin": 1,
        "user": {
            "id": 1,
            "name": "Admin",
            "account": "admin",
            "email": "admin@example.com",
            "lang": "tw",
            "status": "Enabled"
        }
    },
    "csrf": "<csrf-token>"
}
```

未登入範例：

```json
{
    "code": 1,
    "data": {
        "isLogin": 0
    },
    "csrf": "<csrf-token>"
}
```

#### 清單 API

這類 API 通常對應 Gene Panel 的列表畫面，預設採分頁或模組自訂輸出。最常見入口如下：

```text
https://loc.f3cms.com:4433/api/press/list
https://loc.f3cms.com:4433/api/tag/list
https://loc.f3cms.com:4433/api/menu/list
https://loc.f3cms.com:4433/api/option/list
```

`press/list` 返回範例：

```json
{
    "code": 1,
    "data": {
        "subset": [
            {
                "id": 12,
                "title": "首頁輪播公告",
                "slug": "home-banner-news",
                "status": "Enabled"
            },
            {
                "id": 11,
                "title": "隱私權政策",
                "slug": "privacy-policy",
                "status": "Disabled"
            }
        ],
        "limit": 24,
        "pos": 0,
        "sql": ""
    },
    "csrf": "<csrf-token>"
}
```

`tag/list` 返回範例：

```json
{
    "code": 1,
    "data": {
        "subset": [
            {
                "id": 7,
                "title": "熱門",
                "status": "Enabled"
            },
            {
                "id": 8,
                "title": "活動",
                "status": "Enabled"
            }
        ],
        "limit": 24,
        "pos": 0,
        "sql": ""
    },
    "csrf": "<csrf-token>"
}
```

`menu/list` 返回範例：

```json
{
    "code": 1,
    "data": {
        "subset": [
            {
                "id": 1,
                "title": "系統設定",
                "uri": "/backend/setting",
                "blank": "No",
                "icon": "cog",
                "parent_id": 0,
                "rows": [
                    {
                        "id": 3,
                        "title": "選單管理",
                        "uri": "/backend/menu",
                        "blank": "No",
                        "icon": "sitemap",
                        "parent_id": 1,
                        "rows": []
                    }
                ]
            }
        ],
        "limit": 1000,
        "pos": 0,
        "sql": ""
    },
    "csrf": "<csrf-token>"
}
```

`option/list` 返回範例：

```json
{
    "code": 1,
    "data": {
        "subset": {
            "site": {
                "title": "site",
                "rows": [
                    {
                        "id": 1,
                        "group": "site",
                        "loader": "Demand",
                        "status": "Enabled",
                        "name": "site_name",
                        "content": "demo 網站"
                    },
                    {
                        "id": 2,
                        "group": "site",
                        "loader": "Demand",
                        "status": "Enabled",
                        "name": "site_email",
                        "content": "service@example.com"
                    }
                ]
            }
        },
        "limit": 200,
        "pos": 0,
        "sql": ""
    },
    "csrf": "<csrf-token>"
}
```

#### 取得單一記錄

URL:

```text
https://loc.f3cms.com:4433/api/option/get
```

常見請求參數：

```text
id=1
```

返回範例：

```json
{
    "code": 1,
    "data": {
        "id": 1,
        "group": "site",
        "loader": "Demand",
        "status": "Enabled",
        "name": "site_name",
        "content": "demo 網站"
    },
    "csrf": "<csrf-token>"
}
```

#### 存檔

URL:

```text
https://loc.f3cms.com:4433/api/option/save
```

常見請求參數：

```text
id=1
group=site
loader=Demand
status=Enabled
name=site_name
content=demo 網站
```

成功返回範例：

```json
{
    "code": 1,
    "data": {
        "id": 1
    },
    "csrf": "<csrf-token>"
}
```

缺少必要欄位或權限不足時，通常仍走同一個 envelope，但 `code` 會改為錯誤碼，例如：

```json
{
    "code": 8004,
    "data": [],
    "csrf": "<csrf-token>"
}
```

#### 取得選單

URL:

```text
https://loc.f3cms.com:4433/api/menu/get_opts
```

用途：供 Gene Panel 下拉選單、自動完成、父節點選擇器使用。

返回範例：

```json
{
    "code": 1,
    "data": [
        {
            "id": 1,
            "title": "系統設定"
        },
        {
            "id": 3,
            "title": " 　 　選單管理"
        },
        {
            "id": 4,
            "title": " 　 　網站設定"
        }
    ],
    "csrf": "<csrf-token>"
}
```

#### 建議測試方式

以 `curl` 快速測試時，建議先維持登入狀態，再從 `staff/status` 開始確認 session 有效，接著依序測：

1. `staff/status`
2. `menu/get_opts`
3. `menu/list`
4. `option/list`
5. `option/get`
6. `option/save`

最小範例：

```sh
curl -k 'https://loc.f3cms.com:4433/api/staff/status'
curl -k 'https://loc.f3cms.com:4433/api/menu/get_opts'
curl -k 'https://loc.f3cms.com:4433/api/option/get' --data-urlencode 'id=1'
curl -k 'https://loc.f3cms.com:4433/api/option/save' \
    --data-urlencode 'id=1' \
    --data-urlencode 'group=site' \
    --data-urlencode 'loader=Demand' \
    --data-urlencode 'status=Enabled' \
    --data-urlencode 'name=site_name' \
    --data-urlencode 'content=demo 網站'
```