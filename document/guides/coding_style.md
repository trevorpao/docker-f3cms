# F3CMS Coding Style 指南

> 本文件補充 `.php-cs-fixer.dist.php` 的設定，說明專案遵守的程式風格、命名規範、以及常見工具使用方式，協助團隊維持一致的 PHP 代碼品質。

## 1. PHP-CS-Fixer 配置概述
- 主要規範：`@PSR2`, `@PHP73Migration`, `@Symfony`。
- 自訂規則：
  - `concat_space` 使用單一空白。
  - `binary_operator_spaces`：`=>` 與 `=` 皆對齊。
  - `phpdoc_summary`, `strict_comparison`, `heredoc_indentation` 等禁用。
  - 允許風險修正 (`setRiskyAllowed(true)`)。
- `php-cs-fixer` 會掃描 `www/f3cms/libs` 與 `www/f3cms/modules` 兩大目錄。
- 建議使用方式：
  ```bash
  vendor/bin/php-cs-fixer fix www/f3cms --allow-risky=yes
  ```
  > 可加上 `--dry-run --diff` 於 CI 或 pre-commit hook 內檢查。

## 2. 檔案結構與名稱
- Class/Interface 命名使用 PascalCase，檔名與 class 名稱一致。
- Handler / Helper 類別置於 `F3CMS\[Type]` 命名空間，對應資料夾層級。
- `require_once` 優先載入 `vendor/autoload.php` 與 `libs/Autoload.php`，避免重複。

## 3. 程式碼風格重點
- **縮排**：4 空白，不使用 Tab。
- **字串**：偏好單引號，除非需要字串插值或包含單引號。
- **陣列**：使用 `[]` 語法；多行陣列元素結尾保留逗號。
- **條件判斷**：避免巢狀過深，善用提早 return。
- **命名**：
  - 方法與變數採用 camelCase（例如 `$paymentHandler`, `validateToken()`）。
  - 常數使用全大寫 + 底線（例如 `DEFAULT_PROVIDER`）。
- **DocBlock**：
  - 針對公開方法撰寫 `@param`, `@return`，若行為複雜可描述副作用。
  - 不強制 `@throws`，但建議於例外情境補齊。

## 4. 依賴注入與單例
- 優先採用建構子注入（Constructor DI）傳入 Handler、Provider、或外部 Service。
- 單例 (`::instance()` 或 `::setInstance()`) 只留給舊程式碼過渡，新的元件應透過 Container 管理。
- 若需 Legacy 靜態 API，應在文件中註明並提供 DI 替代方案。

## 5. 測試與工具
- 建議搭配 `phpunit` 或 `pest` 撰寫單元測試，並於 CI 中串聯 php-cs-fixer。
- 可在 `composer.json` 加入指令：
  ```json
  "scripts": {
    "lint": "php-cs-fixer fix --dry-run --diff",
    "lint:fix": "php-cs-fixer fix"
  }
  ```
- 新增功能時應至少提供對應的使用指南（例如 `document/examples/*.md`），保持程式與文件同步。

## 6. Commit 與 Review 建議
- 一個 commit 限制在單一主題（feature/bugfix/doc），並附上相關說明。
- PR 需包含：
  1. 變更摘要。
  2. 測試方式（若有自動化測試，貼上命令與結果；無測試亦需說明）。
  3. 關聯文件或 ticket。
- Reviewer 需確認：
  - 程式符合此 Coding Style。
  - 無未處理的 TODO / FIXME。
  - 變更覆蓋主要情境，若缺測試需標記。

---
若此指南與 `.php-cs-fixer.dist.php` 更新互相矛盾，請以配置檔為準，並於文件內同步說明。歡迎提交 PR 擴充其他語言（JS/TS/Go 等）的 Coding Style 建議。
