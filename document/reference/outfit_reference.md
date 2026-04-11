# Outfit 常用函式參考

## Purpose
- 作為 Outfit 層的快速查閱文件。
- 幫助工程師快速找到頁面輸出、靜態化、SEO 設定、Twig 協作與常見工具。
- 補足 guides 中頁面層責任說明的操作層細節。

## Primary Readers
- Frontend-adjacent backend programmers
- SD
- 正在撰寫或維護 Outfit 的工程師
- LLMs 需要快速查詢 Outfit 行為時

## Scope
- Outfit 的角色
- 路由前後流程
- 頁面輸出與靜態化
- 常用 filter 與輔助工具
- 實作建議與常見踩雷

## LLM Reading Contract
- 將本文件視為 Outfit 的操作型 reference，而不是畫面資訊架構或模組邊界文件。
- 若問題是「頁面需求應由哪個模組負責」，先回 [../guides/module_design.md](../guides/module_design.md)。
- 若問題是「Outfit 目前有哪些輸出流程與工具可用」，優先閱讀本文件。

## Core Thesis
- guides 解決的是頁面層在整體模組架構中的位置與責任。
- 本文件解決的是 Outfit 在實作上的既有輸出流程、渲染方法、filter 與注意事項。
- 因此，這份文件最適合在已經知道需求要落在頁面層之後使用。

## 什麼時候該讀這份文件

適合的情境：
- 你已經知道這是 HTML / XML / 檔案輸出的需求
- 你需要查 `render()`、`wrapper()`、`_staticFile()`、`_echoXML()` 的差異
- 你要確認 SEO、麵包屑、語系、Twig filter 應該在哪個階段設定
- 你要排查頁面快取、模板輸出或裝置版縮圖路徑

不適合單獨解決的情境：
- 這個需求應該做成頁面還是 API
- 這個資料欄位應該怎麼建模
- 這個模組是否應該新增 Feed / Reaction 支援

這些問題請先回：
- [../guides/module_design.md](../guides/module_design.md)
- [../guides/data_modeling.md](../guides/data_modeling.md)
- [../guides/create_new_module.md](../guides/create_new_module.md)

閱讀目標：讓工程師快速完成 Outfit 的頁面輸出、SEO 設定、快取配置與模板協作，並避開常見風險。

## 架構角色
- 屬於表現層：負責頁面組裝、樣板渲染、靜態化快取；資料讀取仍交給 Feed。
- 與 Reaction 分工：Reaction 產 JSON / AJAX，Outfit 回應 HTML / XML / 檔案。
- 延伸 `Module`：共用 `_lang()`、`_mobile_user_agent()` 等基礎工具。

## 常用流程與輸出

### 路由前後流程
- `_beforeRoute($args)`：設定語系、CSRF、行動裝置判斷，並可檢查站點開站時間（預設若尚未開站會 reroute 到 comingsoon）。
- `_afterRoute($args)`：預留掛點，預設空實作，可在子類別補後置處理。
- `_middleware($args, $next)`：依 method 名稱呼叫 PCMS/F3CMS 對應方法，並對 `$args` 進行 escape；找不到方法會丟 1004 例外。
- `__call($method, $args)`：Outfit 的動態入口，會套用 `_beforeRoute` → `_middleware` → `_afterRoute`，並輸出執行耗時註解。

### 頁面渲染與靜態化
- `_staticFile($args, $force=false)`：靜態化入口。
  - DEBUG>2 直接 render；非 DEBUG 先嘗試 `SRHelper::get()` 取快取，沒有就 render 後 `SRHelper::save()`。
  - 完成後 `_showVariables()` 在 DEBUG>1 時輸出偵錯資訊。
- `render($html, $title='', $slug='', $rtn=false)`：最常用的頁面輸出。
  - 自動設定 SEO、麵包屑、canonical/share link、主題設定、feVersion、csrf 等變數。
  - 預設直接 `echo`，`$rtn=true` 時回傳字串（方便單元測試或嵌入）。
- `wrapper($html, $title='', $slug='', $rtn=false)`：包覆式渲染，會載入導覽選單（`rMenu::sort_menus`）、footer 選單，並設定年份/共用變數，適合單純靜態頁。
- `_origin()`：取得 Fat-Free Template 實例並註冊常用 filter；較少直接呼叫，通常由 `wrapper()` 使用。
- `_twig()`：建立 Twig 實例，註冊大量 filter（breadcrumb、thumbnail、avatar、safeRaw…），並設定 cache 目錄；`render()` 用它來輸出 Twig 模板。
- `_seoMeta($title='')`：載入 `fOption::load('page')` 與 `fOption::load('social')`，合併自訂 page 變數，最後設置 `page.title`。
- `_setAlternate($slug='')`：預留多語 alternate link（目前為 TODO）。

## 輔助工具
- `_setXls($filename)`：設定 Excel header 並回傳 Template 實例，可直接接著 render HTML 表格即輸出 xls。
- `_echoXML($filename)`：設定 XML header + utf8 清洗後輸出，樣板檔需為 `{name}.xml`。
- `breadcrumb($ary, $isLi=true, $home='')` / `breadcrumb_str($ary, $isLi=true)`：產麵包屑資料/HTML。
- `thumbnail($path, $type)` / `pathByDevice($path, $type)`：依縮圖設定產生對應檔名。
- `convertUrlsToLinks($text)`：自動把 https URL 轉成 `<a>`。
- `handleTag($tags)`：解析 JSON tags，設定 `rel_tag` / `pageKeyword` 供模板使用。
- `date($val, $format)` / `during($start,$end)`：日期格式化。
- `minify($html)`：非 DEBUG 環境壓縮 HTML。
- `assetsSite()` / `staticSite()` / `downloadSite()`（其中部分在 `_twig` filter 註冊）：依設定組合資源網址。

## 常用 filter 與字串處理
- `nl2brSecurity()`：將換行轉 `<br>` 並處理 HTML 實體。
- `safeRaw()`：解碼後替換危險符號，避免 XSS。
- `htmlDecode()` / `urlencode()` / `fixSlug()` / `crop()` / `numFormat()` / `join()` / `avatar()` 等：已註冊成 Twig/Fat-Free filter，可直接在模板使用。

## 最小使用範例
```php
class oPost extends Outfit {
    // 路由方法：/post/home
    public static function home($args) {
        parent::render('index.twig', '首頁', '/');
    }

    // 靜態 sitemap：/post/sitemap
    public static function sitemap($args) {
        $subset = fPress::limitRows(['m.status' => [fPress::ST_PUBLISHED]], 0, 1000);
        f3()->set('rows', $subset);
        parent::_echoXML('sitemap');
    }
}
```

## 實作建議
1. 保持 Outfit 專注在輸出組裝、模板變數與頁面流程，不要把查詢規則與商業判斷直接塞進來。
2. 若畫面需要共用 SEO、語系或全域變數，優先透過 `_beforeRoute()`、`_seoMeta()` 或 `wrapper()` 收斂。
3. 若需擴充模板行為，先在 `_origin()` 或 `_twig()` 註冊 filter，再交由模板使用，避免在 view 夾帶過多 PHP。
4. 若使用 `_staticFile()`，請同步確認快取路徑、清除策略與 DEBUG 行為，避免以為 render 未生效。

## 常見踩雷
- 模板路徑：`render('post/detail.twig')` 會從 `UI/theme/` 下找檔，記得先在 `index.html` 內設定 `window.$docsify.theme` 及伺服器端的 `theme` 變數。
- 語系與 slug：`render` 會把 `slug` 寫入 `page.canonical`，請傳入帶語系的路徑（例 `/tw/post/slug`）。
- 快取：`_staticFile` 會用 `SRHelper` 存取快取；DEBUG>2 才會跳過快取，若看不到頁面更新請清快取或開 DEBUG。
- 未註冊 filter：若新增自訂 filter，記得在 `_origin()` 或 `_twig()` 註冊，否則模板會拋錯。
- Mobile 縮圖：`pathByDevice` 依裝置替換檔名，請確認 `*_thn` 設定存在，避免生成不存在的檔案路徑。

## Related Documents
- [intro.md](intro.md)
- [feed_reference.md](feed_reference.md)
- [reaction_reference.md](reaction_reference.md)
- [../guides/module_design.md](../guides/module_design.md)
- [../guides/create_new_module.md](../guides/create_new_module.md)
- [../guides/data_modeling.md](../guides/data_modeling.md)

## Status
- Draft v1 aligned with reference entry
