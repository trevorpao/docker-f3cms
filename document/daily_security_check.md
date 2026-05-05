
# Daily Security Check

本文件以目前實作為準，對應入口腳本是 `bin/daily_security_check.sh`，實際檢查邏輯由 `bin/daily_security_check.php` 執行。

## 功能定位

`bin/daily_security_check.sh` 不是獨立的 bash 掃描器，而是一個很薄的 wrapper：

- 解析自己的所在目錄
- 使用 `PHP_BIN` 或系統上的 `php`
- 將所有參數原封不動轉交給 `bin/daily_security_check.php`

因此，日常使用應以 shell 腳本作為穩定入口，但實際輸出內容、檢查項目、退出碼與選項都以 PHP 主程式為準。

## 執行方式

### 基本執行

```bash
./bin/daily_security_check.sh
```

預設行為：

- 專案根目錄預設為 `bin/` 的上一層
- 報告輸出目錄預設為 `/home/ubuntu/checkresult`
- 同時輸出文字報告與 JSON 報告
- 會自動讀取專案根目錄下的 `.env` 取得資料庫連線資訊

### 常用範例

```bash
./bin/daily_security_check.sh --dry-run
./bin/daily_security_check.sh --only=1,4,7
./bin/daily_security_check.sh --output-dir=/home/ubuntu/checkresult
./bin/daily_security_check.sh --disk-min-gb=20 --mem-warn-pct=90 --cpu-warn-pct=90
```

### PHP 執行入口

如需直接執行主程式：

```bash
php ./bin/daily_security_check.php --help
```

## CLI 參數

目前支援以下參數：

- `--project-root=/path/to/repo`
    覆寫專案根目錄。預設為 `bin/` 的上一層。
- `--output-dir=/path/to/output`
    覆寫報告輸出目錄。預設為 `/home/ubuntu/checkresult`。
- `--dry-run`
    只顯示會執行哪些檢查，將報告輸出到 STDOUT，不連資料庫、不寫檔。
- `--only=1,4,7`
    只執行指定檢查編號。
- `--db-host=HOST`
- `--db-port=PORT`
- `--db-name=NAME`
- `--db-user=USER`
- `--db-password=PASSWORD`
    覆寫 `.env` 中的資料庫連線設定。
- `--disk-min-gb=10`
    硬碟剩餘容量告警門檻，預設 10 GB。
- `--disk-min-pct=15`
    硬碟剩餘百分比告警門檻，預設 15%。
- `--mem-warn-pct=85`
    記憶體使用率告警門檻，預設 85%。
- `--cpu-warn-pct=85`
    CPU 使用率告警門檻，預設 85%。
- `--help`
    顯示說明。

## 報告輸出

每次正常執行會產出兩個檔案：

- `security_check_YYYYmmdd_HHMMSS.log`
- `security_check_YYYYmmdd_HHMMSS.json`

預設輸出到 `/home/ubuntu/checkresult`。

文字報告包含：

- 產生時間
- 專案根目錄
- 是否為 dry-run
- 指定執行的檢查編號
- `ok / warn / error / info` 統計摘要
- 各檢查的 summary 與 details

## 退出碼

`bin/daily_security_check.php` 目前的退出碼規則如下：

- `0`：所有執行到的檢查皆為 `ok` 或 `info`
- `2`：至少一個檢查結果為 `warn` 或 `error`

這個設計可直接給 cron、CI 或外部監控系統判斷是否需要告警。

## 檢查範圍

目前實作共有 12 個檢查項目，內容如下。

| 編號 | 標題 | 實際檢查內容 | 主要來源 |
| --- | --- | --- | --- |
| 1 | 異常檔名 | 掃描公開目錄下是否存在 `nics115pt`、`nics` 數字變形、`56903`、`5692733992203` 等可疑檔名 | `www/` |
| 2 | 異常註冊帳號 | 檢查 `tbl_member.account`、`tbl_staff.account` 是否命中 `nics` 或 `56903` 模式 | `tbl_member`、`tbl_staff` |
| 3 | 頁面遭插入文字 | 檢查文章內容是否含 `nics115pt_xss56903`、`nics56903`、`5692733992203` 等標記 | `tbl_post_lang.content`、`tbl_press_lang.content` |
| 4 | 異常微型圖片 | 檢查最近 24 小時內、且小於 15 KB 的圖片檔 | `www/f3cms/upload/img` |
| 5 | 一次性或異常信箱 | 檢查 `hidingmail.net`、`mailinator.com`、`guerrillamail.com`、`10minutemail.com`、`tempmail.plus`、`yopmail.com` | `tbl_member.email`、`tbl_staff.email` |
| 6 | 異常程式與持久化 | 檢查可疑 process、使用者 crontab、系統 cron，以及最近 24 小時新增且內容可疑的程式檔 | 系統 process、crontab、`www/` |
| 7 | XSS 指標 | 檢查 `<script`、`javascript:`、`onerror=`、`onload=`、`<iframe`、`srcdoc=`、`document.cookie`、`alert(` 等字串 | `tbl_post_lang.content`、`tbl_press_lang.content` |
| 8 | 網站組態檔檢查 | 檢查監控檔案是否缺失、24 小時內異動，並檢查危險 PHP 設定 | `conf/php`、`conf/nginx`、`www/f3cms/robots.txt` |
| 9 | 網頁目錄外洩檢查 | 檢查 Nginx 是否開啟 `autoindex on;`，以及公開程式目錄中是否存在敏感檔或封存檔 | `conf/nginx`、`www/f3cms` |
| 10 | 硬碟可用空間 | 讀取專案所在磁區的剩餘空間與百分比，低於門檻時告警 | 專案根目錄所在磁區 |
| 11 | 記憶體用量 | 讀取 `/proc/meminfo` 計算記憶體使用率，超過門檻時告警 | Linux `/proc/meminfo` |
| 12 | CPU 用量 | 讀取 `/proc/stat` 兩次取樣計算 CPU busy 百分比，超過門檻時告警 | Linux `/proc/stat` |

## 執行限制

以下限制是目前實作的既有行為：

- 若資料庫無法連線，編號 2、3、5、7 會回報 `error`，不會中止整體腳本
- `--dry-run` 模式下所有被選到的檢查都會以 `info` 顯示，不會真的執行檢查
- 記憶體與 CPU 檢查依賴 Linux 的 `/proc`，若在不支援的環境執行，會回傳 `info` 或 `error`
- 第 6 項會檢查系統 process 與 crontab，因此實際可見範圍會受執行帳號權限影響
- 第 8、9 項目前以專案檔案內容為準，不會主動對外發 HTTP 探測

## 建議部署方式

### cron

建議直接以文件中的範例為準，不另外維護獨立 example 檔。範例如下：

```cron
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

0 2 * * * cd /opt/docker-f3cms && /usr/bin/env bash ./bin/daily_security_check.sh --output-dir=/home/ubuntu/checkresult >> /var/log/daily_security_check_cron.log 2>&1
```

### logrotate

建議直接以文件中的範例為準，不另外維護獨立 example 檔。範例如下：

```conf
/home/ubuntu/checkresult/security_check_*.log /home/ubuntu/checkresult/security_check_*.json {
        daily
        rotate 14
        missingok
        notifempty
        compress
        delaycompress
        dateext
        dateformat -%Y%m%d
        copytruncate
}
```

## 維運建議

- 正式環境建議使用唯讀資料庫帳號執行本檢查器
- 若執行環境與專案根目錄不一致，請明確傳入 `--project-root`
- 若要將這支檢查器接到監控平台，建議以退出碼 `2` 作為告警條件
- 若想降低報表量，可用 `--only=` 將排程拆成多個較小批次


