

### 1. 備份 `/www/site` 的腳本

首先，在 43 伺服器上創建一個腳本 `backup_site.sh`，其內容應如下：

```bash
#!/bin/bash

# 設定變數
SOURCE_DIR="/www/site"
DEST_HOST="11_ip_address" # 替換為實際的 IP 地址
DEST_DIR="/backup"
DATE=$(date +\%Y-\%m-\%d)
BACKUP_FILE="site_backup_\$DATE.tar.gz"

# 備份資料夾
tar -czf /tmp/\$BACKUP_FILE \$SOURCE_DIR

# 備份文件傳輸到目標伺服器
scp /tmp/\$BACKUP_FILE user@\$DEST_HOST:\$DEST_DIR
# Setup & Backup Playbook

這份指南整理了三支常駐腳本，協助 43 與 11 兩台伺服器每日自動備份站點與資料庫，同時清理舊檔並監控磁碟空間。

## 環境需求
- Bash 4+ 與可用的 `scp`, `tar`, `gzip`, `mysqldump`, `find`, `df` 指令。
- 43 主機能以 SSH 私鑰連線到 11 主機（用於站點備份傳送）。
- 11 主機具有寫入 `/backup`, `/web_backup`, `/db_backup` 等目錄的權限。
- 具備 `crontab` 操作權限，可設定排程於 root 或指定帳號。

> 下列腳本皆預設與 cron 置於 `/usr/local/sbin`，可依專案實際目錄調整。

## 時程一覽
| 腳本 | 主機 | 目的 | 預設 Cron |
| --- | --- | --- | --- |
| `backup_site.sh` | 43 | 備份 `/www/site` 並傳至 11 | `0 1 * * *` |
| `backup_db.sh` | 11 | 匯出全部 MariaDB | `30 1 * * *` |
| `clearup.sh` | 11 | 清除舊備份與檢查磁碟 | `50 0 * * *` |

## Step 1. 備份 `/www/site`
在 43 主機建立 `backup_site.sh`：

```bash
#!/bin/bash
set -euo pipefail

SOURCE_DIR="/www/site"
DEST_HOST="11_ip_address"   # TODO: 改為實際 IP
DEST_USER="user"            # TODO: 改為備援帳號
DEST_DIR="/backup"
DATE=$(date +%Y-%m-%d)
BACKUP_FILE="site_backup_${DATE}.tar.gz"

tar -czf /tmp/${BACKUP_FILE} ${SOURCE_DIR}
scp /tmp/${BACKUP_FILE} ${DEST_USER}@${DEST_HOST}:${DEST_DIR}/
rm /tmp/${BACKUP_FILE}

echo "[OK] Site backup sent to ${DEST_HOST}:${DEST_DIR}/${BACKUP_FILE}"
```

授權並加入 cron：

```bash
chmod +x /usr/local/sbin/backup_site.sh
(crontab -l ; echo "0 1 * * * /usr/local/sbin/backup_site.sh") | crontab -
```

> **Tip：** 首次部署可加上 `BACKUP_RETENTION_DAYS` 等參數延伸，或以 `rsync` 取代 `scp` 以減少傳輸量。

## Step 2. 備份 MariaDB
在 11 主機建立 `backup_db.sh`：

```bash
#!/bin/bash
set -euo pipefail

DEST_DIR="/backup"
DATE=$(date +%Y-%m-%d)
BACKUP_FILE="db_backup_${DATE}.sql.gz"

mysqldump --all-databases \
  | gzip > ${DEST_DIR}/${BACKUP_FILE}

echo "[OK] DB dump saved to ${DEST_DIR}/${BACKUP_FILE}"
```

授權與排程：

```bash
chmod +x /usr/local/sbin/backup_db.sh
(crontab -l ; echo "30 1 * * * /usr/local/sbin/backup_db.sh") | crontab -
```

> **建議：** 若資料庫較大，請增加 `mysqldump` 參數（如 `--single-transaction`、`--quick`），並將輸出導向分割磁碟。

## Step 3. 清理備份並檢查磁碟
在 11 主機建立 `clearup.sh`：

```bash
#!/bin/bash
set -euo pipefail

WEB_BACKUP_DIR="/web_backup"
DB_BACKUP_DIR="/db_backup"
MIN_DISK_SPACE=3000000  # 3 GB (KB)

find ${WEB_BACKUP_DIR} -name 'web_backup_*.tar.gz' -type f -mtime +8 -delete
find ${DB_BACKUP_DIR}  -name 'db_backup_*.sql.gz' -type f -mtime +8 -delete

AVAILABLE_SPACE=$(df / | tail -1 | awk '{print $4}')

if [ ${AVAILABLE_SPACE} -lt ${MIN_DISK_SPACE} ]; then
  echo "[WARN] Not enough disk space, skipping backups." >&2
  exit 1
fi

echo "[OK] Cleanup finished, ${AVAILABLE_SPACE} KB free."
```

授權與排程：

```bash
chmod +x /usr/local/sbin/clearup.sh
(crontab -l ; echo "50 0 * * * /usr/local/sbin/clearup.sh") | crontab -
```

> 可加入 `journalctl -u backup` 或自訂 log 目錄做追蹤，並於 `crontab` 中加上 `MAILTO=ops@example.com` 接收錯誤。

## 驗證與監控
- **手動測試**：逐一執行三支腳本，確認成功訊息與備份檔案大小。
- **恢復演練**：每月至少解壓一份 `site_backup` 與 `db_backup`，驗證可用性。
- **磁碟告警**：建議搭配 `Prometheus node_exporter` 或簡單 `df` 閾值通知，避免清理腳本過晚觸發。
- **版本控管**：將腳本放入 git 並同時維護 checksum，以便審核修改。

依此流程即可在 F3CMS 專案中快速複製一致的備份策略，同時保有擴充與監控彈性。
