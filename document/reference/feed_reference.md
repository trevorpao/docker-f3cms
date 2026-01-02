# Feed 常用函式指南（初階工程師版）

目標：讓新人在 10 分鐘內會用 Feed 做「查、存、分頁、語系/中繼/標籤」操作，並避開常見踩雷。

## 先搞懂的常數
- `MTB`：資料表代碼（不含 `tbl_`）。例：Draft 模組設 `MTB = 'draft'`，實際表名 `tbl_draft`。
- `MULTILANG`：`1` 會自動 join `tbl_{MTB}_lang`，`0` 則單語系。
- `PAGELIMIT`：分頁預設筆數（預設 12）。
- `BE_COLS`：列表查詢時的基本欄位清單（逗號字串）。
- `PV_R / PV_U / PV_D`：讀 / 寫 / 刪權限，Reaction 會用 `chkAuth()` 檢查。
- `HARD_DEL`：`1` 才允許硬刪除，`0` 會被 Reaction 擋掉。

## 最常用 CRUD 與分頁
- `save($req, $tbl='')`：新增/更新。
	- 必填：更新時帶 `id`，新增不用。
	- 會呼叫 `_handleColumn()` 拆欄位與額外資料，再自動寫入 `insert_ts/last_ts`、`insert_user/last_user`。
	- 之後跑 `_afterSave()`：自動處理 `meta`、`tags`、`lang`。
	- 回傳新增或更新後的 `id`，失敗回傳 `null`。

- `published($req, $tbl='')`：僅更新 `status`（以及 `online_date` 若有帶），回傳布林。

- `delRow($id, $sub_table='')`：硬刪一筆，回傳刪除筆數。若搭配 Reaction，記得 `HARD_DEL` 要設為 `1` 才會放行。

- `one($val, $col='id', $condition=[], $multilang=1)`：取單筆。
	- `multilang` 預設 `1`：若開啟多語系會附帶 `lang` 處理。
	- 用 `LANG_ARY_MERGE` 可把當前語系欄位合併到主資料；用 `LANG_ARY_ALONE` 則回傳 `lang` 陣列。

- `lots($condition, $cols='*', $join=null, $limit=500)`：一般條件取多筆。

- `limitRows($query='', $page=0, $limit=0, $cols='')`：分頁列表（Reaction `do_list` 會用）。
	- `query` 可用 `a:1,b>2,ORDER:insert_ts!` 這類簡易語法。
	- 回傳 `{ subset, total, limit, count, pos, filter, sql }`。

- `paginate($tbl, $filter, $page, $limit, $cols, $join)`：通用分頁器，內部先計數 `_total()` 再查資料。

- `getOpts($query='', $column='title')` / `oneOpt($pid)`：給下拉選單用的精簡資料（預設回傳 id + title）。

## 語系 / 中繼 / 標籤三寶
- `saveLang($pid, $data)`：寫入 `tbl_{MTB}_lang`。`$data` 形如 `[['tw', ['title'=>'...', ...]], ['en', [...]]]`。
- `lotsLang($pid, $lang='')`：讀多語。指定 `lang` 則回傳單一語系資料，未指定回傳全語系陣列。
- `saveMeta($pid, $data, $replace=false)`：寫中繼資料（k/v）。`$replace=true` 會先刪同鍵再寫。
- `lotsMeta($pid, $key='')`：讀中繼；`key` 留空則全撈。
- `lotsTag($pid, $sorter=false)`：查此筆關聯的 tags。
- `_afterSave($pid, $other, $data)`：`save()` 後自動呼叫，會依 `$other` 實際寫入 `meta/tags/lang`。

## 查詢語法與預設行為
- `genFilter($query)`：接受字串或陣列；字串先經 `_handleQuery()`，再經 `adjustFilter()` 補上排序與特殊條件。
	- 運算子對照：`a:1` 等於 `a = 1`；`a>1` / `a<1`；`a<>1|2`；`a~abc` 模糊；`a!xyz` 不等於。
	- `ORDER:insert_ts!` 表示 `insert_ts DESC`；多個用 `|` 分隔。
- `genJoin()`：`MULTILANG=1` 時自動 join `lang` 表，語系來自 `Module::_lang()`。
- `genOrder()`：預設 `insert_ts DESC`，可在子類別覆寫。

## 欄位處理與安全
- `_handleColumn($req)`：把輸入拆兩份
	- `data`：直接寫入主表的欄位，會處理 slug 正規化、密碼雜湊、時間格式、JSON encode。
	- `other`：`meta/tags/lang` 會暫存，交給 `_afterSave()` 寫子表。
- `filterColumn($col)` / `filtered_column()`：決定欄位能不能寫。預設禁止 `id/last_ts/insert_ts/last_user/insert_user`，子類別可覆寫 `filtered_column()` 加入更多保護欄位。
- `saveCol($req, $table='', $pk='id')`：只改單一欄位，會檢查是否為保護欄位，回傳更新筆數。
- `onlyColumns($row, $allow)`：把資料列過濾成白名單欄位，避免多餘欄位外露。

## 實用小工具
- `fmTbl($sub='')`：組表名，會加 `tpf()` 前綴，並可指定子表（如 `lang/meta/tag`）。
- `chkErr($rtn=1)`：統一 SQL 錯誤處理並寫入 `sql_error.log`；DEBUG 模式會直接輸出錯誤與最後 SQL。
- `exec($query, $map=[], $isSole=false)`：直接跑 SQL；`isSole=true` 取單筆。
- `renderUniqueNo($len=6)`：產生隨機碼；`_genToken()` 產生 64 字元 token。
- `_setPsw()` / `_chkPsw()`：Bcrypt 雜湊與舊 md5 相容更新。
- `limit($offset, $limit)` / `format($sql)`：SQL Server 相容的 limit/引號處理。

## 最小實作範例（以 Draft 模組為例）
```php
class fDraft extends Feed {
		public const MTB       = 'draft';   // 對應 tbl_draft
		public const MULTILANG = 0;         // 無多語
		public const BE_COLS   = 'm.id,m.title,m.status,m.insert_ts';
}

// 建立或更新
$id = fDraft::save([
		'id'     => 0,            // 新增時可省略或給 0
		'title'  => 'My Draft',
		'status' => 'New',
]);

// 分頁列表（page 從 0 起算）
$list = fDraft::limitRows('status:New,ORDER:insert_ts!', 0, 20);

// 單筆查詢
$row = fDraft::one($id);
```

## 常見踩雷
- 忘記設 `MTB`：會導致查詢/寫入的表名錯誤。
- `HARD_DEL=0` 卻用 Reaction `do_del`：會被回傳 8008，記得依需求設為 `1` 或改用軟刪策略。
- 多語模組未設 `MULTILANG=1`：`getOpts/limitRows` 不會 join 語系表，導致標題為空。
- `_handleColumn` 會 JSON encode 陣列欄位：前端要記得解 JSON，或在 Feed 覆寫此方法改寫行為。
- `saveMeta($pid, $data, true)` 會刪同鍵：若要增量寫入請用 `false`。

## 寫程式順序建議（新人版）
1) 在子類別先定義好 `MTB`、`MULTILANG`、`BE_COLS`、`PAGELIMIT`。
2) 若有特別欄位處理，覆寫 `_handleColumn()`（記得呼叫 `parent` 邏輯或自行處理 meta/tags/lang）。
3) 若需要客製排序或 join，覆寫 `genOrder()`、`genJoin()`。
4) 在 Reaction 直接呼叫 `limitRows / one / save / delRow`，保持控制器瘦身。

> 進階：如需更多欄位安全限制，覆寫 `filtered_column()`；如需自訂錯誤訊息，搭配 Reaction 的 `formatMsgs()`。
