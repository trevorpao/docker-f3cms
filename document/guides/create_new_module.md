# 建立新模組流程

以下流程示範如何依統一命名規範建立 `Draft` 模組。範例需求：建立一張 `draft` 資料表以管理每次透過 LLM 生成草稿的狀態與內容。

## Step1 分析需求

根據需求萃取資料欄位，先以 JSON 方式描述資料結構，確認欄位語意與命名。

### JSON 範例
```json
{
   "press_id": 0,
   "owner_id": 3,
   "status": "New", // ['New', 'Waiting', 'Done', 'Invaild']
   "lang": "tw", // ['tw', 'en', 'jp']
   "method": "gen_guideline", // 要接手的 LLM 函式，例如 gen_guideline, translate
   "intent": "常聽到人說\"祝您如願以償\"，但有趣的是如果想達到這個願望，那往往不是因為我們拿到了最好的結果，而是因為我們懂得限縮自身的慾望。也就是\"知足感恩\"才是幸福感的來由。但就像尼泊爾的幸福指數一樣，也許這也是自我說服的假象。重點應該在於找到尺度，我的尺度在於汗水的份量是否足夠。但不是把汗水跟收獲放在天平的兩端比較，而是單純考量我是否把握每一次可以努力的機會。", 
   "guideline": "",
   "content": ""
}
```

## Step2 產生 module SQL schema

取得確認後，依資料庫命名規則生成 SQL，確保命名一致、欄位語意清楚。

### 資料庫命名規則（統一版）

1. **資料表命名**
   - 一律使用 `tbl_` 前綴，名稱採單數＋小寫＋底線（`tbl_draft`, `tbl_press_lang`）。
   - 延伸表以 `${base}_XXXX` 命名 (如 `tbl_press_lang`, `tbl_press_meta`)。
   - 語系延伸表以 `${base}_lang` 命名，避免使用其他後綴。
   - 後設資料延伸表以 `${base}_meta` 命名，避免使用其他後綴。
   - LOG 資料延伸表以 `${base}_log` 命名，避免使用其他後綴。
   - 多對多資料延伸表以 `${module1}_$(module2)` 命名，避免使用其他後綴，主鍵為 `${module1}_id`,  `${module2}_id`（如 `press_id`, `tag_id`）。

2. **欄位命名**
   - 全小寫＋底線：`owner_id`, `order_no`, `insert_user`。
   - 主鍵固定為 `id`；外鍵使用 `${target}_id`（如 `press_id`, `member_id`）。
   - 延伸表主鍵固定為 `id`；主表外鍵使用 `parent_id`。
   - 布林/狀態欄位使用語意清楚的字：`status`, `is_active`, `is_default`。
   - 枚舉欄位使用 `ENUM` 並將預設值寫在第一個選項。
   - 排序欄位統一為 `sorter INT DEFAULT 99`。

3. **時間戳記欄位**
   - `insert_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP`。
   - `last_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`。

4. **建立/更新者欄位**
   - `insert_user INT DEFAULT 0`（建立者 ID）。
   - `last_user INT DEFAULT 0`（最後更新者 ID）。

5. **多語系資料表**
   - 表名 `tbl_xxx_lang`，並包含 `parent_id`、`lang`（例如 `ENUM('tw','en','jp')`）。

6. **多對多關聯表**
   - 表名 `tbl_{entity_a}_{entity_b}`，保留兩個外鍵欄位與 `idx_{table}_{column}` 索引。

7. **字符集與排序**
   - 統一使用 `utf8mb4_unicode_ci`。

8. **SQL 註解與避錯**
   - 重要欄位加 `COMMENT`，表名與欄位使用反引號 `` ` ``。
   - 避免 `NULL` 為預設值，INT 設為 `0`，字串設為空字串。

9. **命名消歧**
   - 相同語意的欄位維持同一名稱（如 `guideline`、`content`）。
   - API 參數與 DB 欄位同步命名，降低轉換成本。

### SQL 範例
```sql
CREATE TABLE `tbl_draft` (
   `id` INT AUTO_INCREMENT PRIMARY KEY,
   `press_id` INT NOT NULL DEFAULT 0 COMMENT '關聯的新聞稿 ID',
   `owner_id` INT NOT NULL DEFAULT 0 COMMENT '草稿擁有者 ID',
   `status` ENUM('New', 'Waiting', 'Done', 'Invalid') DEFAULT 'New' COMMENT '草稿狀態',
   `lang` ENUM('tw', 'en', 'jp') DEFAULT 'tw' COMMENT '語言代碼',
   `method` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'LLM 函式名稱 (如 gen_guideline)',
   `intent` TEXT DEFAULT '' COMMENT '使用者意圖 (JSON/文字)',
   `guideline` TEXT DEFAULT '' COMMENT '提示詞或操作指引',
   `content` TEXT DEFAULT '' COMMENT '生成結果',
   `insert_ts` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
   `last_ts` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最後更新時間',
   `insert_user` INT DEFAULT 0 COMMENT '建立者 ID',
   `last_user` INT DEFAULT 0 COMMENT '最後更新者 ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'LLM 草稿清單';
```

> 命名重點：模組資料表 `tbl_draft` 對應模組資料夾 `www/f3cms/modules/Draft/`，類別名稱使用 PascalCase（`fDraft`, `oDraft`, `rDraft` 等）。

### 模組檔案命名守則
- **資料夾**：`www/f3cms/modules/Draft`（首字大寫，與資料表去除 `tbl_` 後對應）。
- **Feed 類別**：`f{Module}`（如 `fDraft`），常數 `MTB` = 資料表名稱去除 `tbl_` 後的字串。
- **Reaction 類別**：`r{Module}`。
- **Outfit 類別**：`o{Module}`。
- **語言檔/樣板**：依 Outfit 需求存放於 `www/f3cms/themes/*`，命名需與模組代碼一致，避免重複含義。

## 建立 Module

建立 www/f3cms/modules/Draft

### 生成 Feed

以上方 sql 範例，則會生成下方的 www/f3cms/modules/Draft/feed.php

```php
namespace F3CMS;

/**
 * data feed
 */
class fDraft extends Feed
{
    public const MTB       = 'draft';
    public const MULTILANG = 0;

    public const ST_NEW     = 'New';
    public const ST_WAITING = 'Waiting';
    public const ST_DONE    = 'Done';
    public const ST_INVALID = 'Invalid';

    public const BE_COLS = 'm.id,m.press_id,m.owner_id,m.status,m.lang,m.method,m.intent,m.insert_ts,m.last_ts,m.last_user,m.insert_user';
}
```

### 生成 Reaction

以上方 sql 範例，則會生成下方的 www/f3cms/modules/Draft/reaction.php

```php
namespace F3CMS;

class rDraft extends Reaction
{
    public static function handleRow($row = [])
    {
        $row['press_id'] = ($row['press_id'] > 0) ? [fPress::oneOpt($row['press_id'])] : [[]];
        $row['owner_id'] = ($row['owner_id'] > 0) ? [fStaff::oneOpt($row['owner_id'])] : [[]];

        return $row;
    }
}
```

### 生成 Outfit

以上方 sql 範例，則會生成下方的 www/f3cms/modules/Draft/outfit.php

```php
namespace F3CMS;

class oDraft extends Outfit
{
}
```
