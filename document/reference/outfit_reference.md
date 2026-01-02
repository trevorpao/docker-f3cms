# Outfit 常用函式指南（初階工程師版）

目標：讓新人在 10 分鐘內會用 Outfit 輸出頁面、設定 SEO/麵包屑、切換語系、與 Twig 範本協作，並避開常見踩雷。

## Outfit 的角色
- 屬於表現層：負責頁面組裝、樣板渲染、靜態化快取；資料讀取仍交給 Feed。
- 與 Reaction 分工：Reaction 產 JSON / AJAX，Outfit 回應 HTML / XML / 檔案。
- 延伸 `Module`：共用 `_lang()`、`_mobile_user_agent()` 等基礎工具。

## 路由前後流程
- `_beforeRoute($args)`：設定語系、CSRF、行動裝置判斷，並可檢查站點開站時間（預設若尚未開站會 reroute 到 comingsoon）。
- `_afterRoute($args)`：預留掛點，預設空實作，可在子類別補後置處理。
- `_middleware($args, $next)`：依 method 名稱呼叫 PCMS/F3CMS 對應方法，並對 `$args` 進行 escape；找不到方法會丟 1004 例外。
- `__call($method, $args)`：Outfit 的動態入口，會套用 `_beforeRoute` → `_middleware` → `_afterRoute`，並輸出執行耗時註解。

## 靜態化與渲染
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

## 常用輸出工具
- `_setXls($filename)`：設定 Excel header 並回傳 Template 實例，可直接接著 render HTML 表格即輸出 xls。
- `_echoXML($filename)`：設定 XML header + utf8 清洗後輸出，樣板檔需為 `{name}.xml`。
- `breadcrumb($ary, $isLi=true, $home='')` / `breadcrumb_str($ary, $isLi=true)`：產麵包屑資料/HTML。
- `thumbnail($path, $type)` / `pathByDevice($path, $type)`：依縮圖設定產生對應檔名。
- `convertUrlsToLinks($text)`：自動把 https URL 轉成 `<a>`。
- `handleTag($tags)`：解析 JSON tags，設定 `rel_tag` / `pageKeyword` 供模板使用。
- `date($val, $format)` / `during($start,$end)`：日期格式化。
- `minify($html)`：非 DEBUG 環境壓縮 HTML。
- `assetsSite()` / `staticSite()` / `downloadSite()`（其中部分在 `_twig` filter 註冊）：依設定組合資源網址。

## 安全與字串處理（常用 filter）
- `nl2brSecurity()`：將換行轉 `<br>` 並處理 HTML 實體。
- `safeRaw()`：解碼後替換危險符號，避免 XSS。
- `htmlDecode()` / `urlencode()` / `fixSlug()` / `crop()` / `numFormat()` / `join()` / `avatar()` 等：已註冊成 Twig/Fat-Free filter，可直接在模板使用。

## 最小使用範例（以 oPost 為例）
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

## 常見踩雷
- 模板路徑：`render('post/detail.twig')` 會從 `UI/theme/` 下找檔，記得先在 `index.html` 內設定 `window.$docsify.theme` 及伺服器端的 `theme` 變數。
- 語系與 slug：`render` 會把 `slug` 寫入 `page.canonical`，請傳入帶語系的路徑（例 `/tw/post/slug`）。
- 快取：`_staticFile` 會用 `SRHelper` 存取快取；DEBUG>2 才會跳過快取，若看不到頁面更新請清快取或開 DEBUG。
- 未註冊 filter：若新增自訂 filter，記得在 `_origin()` 或 `_twig()` 註冊，否則模板會拋錯。
- Mobile 縮圖：`pathByDevice` 依裝置替換檔名，請確認 `*_thn` 設定存在，避免生成不存在的檔案路徑。

## 撰寫順序建議（新人版）
1) 在子 Outfit 類別撰寫路由方法（通常命名 `home/about/...`），在方法內呼叫 `render` 或 `wrapper`。
2) 若需靜態化，改用 `_staticFile` 包裝路由並配置 `SRHelper` 的儲存路徑與權限。
3) 需要額外的模板 filter 時，覆寫 `_twig()` 並加上 `parent::_twig()` 已有的設定。
4) 若要加全域 SEO/變數，覆寫 `_beforeRoute` 或 `_seoMeta`，但保留原本語系/CSRF 流程。

> 搭配文件：資料取得與命名規範請參考 `document/feed_reference.md` 和 `document/create_new_module.md`；若頁面需 AJAX，請搭配 `document/reaction_reference.md`。
