# Docker-F3CMS

## 版本資訊
+ nginx alpine
+ php 8.3
+ maria 10

## 資料夾結構

本頁作為 `document/` 目錄的主索引，提供本地 LAMP（Nginx + PHP 8.3 + MariaDB 10）開發環境的快速指南與延伸文件連結。

## 版本資訊
| 元件 | 版本/說明 |
| --- | --- |
| Nginx | Alpine 版，作為反向代理與靜態資源服務 |
| PHP | 8.3 FPM，掛載 `www/` 目錄 |
| MariaDB | 10.x，資料存於 `database/` |

## 資料夾結構
```text
conf/        # Apache / MySQL / Nginx / PHP 設定檔
database/    # MariaDB 資料檔案
document/    # 本說明與其他使用指南 (examples, payment, sql, setup)
log/         # 服務 log：apache / nginx / mysql / php
www/         # 網站程式碼與靜態資源 (f3cms, pma, vendor ...)
gene-panel/  # 後端 submodule
operonjs/    # 前端 submodule
```

更多主題請參考：
- `examples/*.md`：Cashier、Sender、SM Sender、OAuth 使用方式。
- `payment/*.md`：金流整合說明。
- `sql/init.sql`：預設資料表。
- `setup.md`：伺服器備份與 Cron 腳本做法。

## 建置前準備
1. **hosts 解析**：將 `loc.f3cms.com` 指向本機。
   ```sh
   sudo sh -c 'echo "0.0.0.0 loc.f3cms.com" >> /etc/hosts'
   ```
2. **本機憑證**：使用 `mkcert loc.f3cms.com` 生成測試憑證。
3. **設定** `.env`（置於專案根目錄，可依實際需求微調）：
   ```ini
   COMPOSE_PROJECT_NAME=f3cms
   APP_NAME=f3cms

   # Apache
   APACHE_HOST_HTTP_PORT=8080
   APACHE_HOST_HTTPS_PORT=4433
   APACHE_HOST_LOG_PATH=./log/apache
   APACHE_CONF_PATH=./conf/apache
   APACHE_WWW_PATH=./www
   VND_PATH=/var/www/vendor

   # PHP
   PHP_HOST_LOG_PATH=./log/php
   PHP_CONF_PATH=./conf/php

   # MySQL
   MYSQL_PORT=3366
   MYSQL_LOG_PATH=./log/mysql
   MYSQL_ROOT_PASSWORD=sPes4uBrEcHUq5qE
   MYSQL_DATABASE=target_db
   MYSQL_USER=root
   MYSQL_PASSWORD=sPes4uBrEcHUq5qE
   MYSQL_CONF_PATH=./conf/mysql/my.cnf
   MYSQL_DATA_PATH=./database
   ```
4. **phpMyAdmin 安裝至** `www/pma`：下載最新壓縮檔、解壓並移動內容。
   ```sh
   wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip
   unzip phpMyAdmin-latest-all-languages.zip
   mv phpMyAdmin-*/* ./www/pma
   rm -rf phpMyAdmin-*
   ```
5. **靜態檔案**：將 `themeSetting.json` 放在 `www/`，並更新 `browscap.ini`。
   ```sh
   wget -O ./www/browscap.ini https://browscap.org/stream?q=PHP_BrowsCapINI
   ```

## 容器操作
```sh
docker-compose build     # 第一次建置 image
./up.sh                  # 啟動服務 (等同 docker-compose up -d)
./down.sh                # 停止並清理容器

docker exec -it nginx_f3cms /bin/sh                       # 進入 Nginx
docker exec nginx_f3cms tail -f /var/log/nginx/error.log  # 追蹤錯誤
docker logs nginx_f3cms                                   # 檢視 log
docker restart nginx_f3cms                                # 重啟 Nginx
docker exec -it php-fpm_f3cms bash                        # 進入 PHP
```

## 驗證與初始化
- 主站測試：[https://loc.f3cms.com:4433/](https://loc.f3cms.com:4433/)
- phpMyAdmin：[https://loc.f3cms.com:4433/pma/](https://loc.f3cms.com:4433/pma/)
- 匯入預設 DB：
  ```sh
  mysql -uroot -p --port=3366 -h loc.f3cms.com target_db < ./conf/mysql/init.sql
  ```

## Submodule 作業
```sh
git submodule init
git submodule update
```
完成後依各子模組 README 進一步安裝。

---

## 說明文件安裝

更多自動化（備份、排程、金流、登入流程）請參閱本資料夾其他 Markdown 文件或透過 `document/index.html` 的 Docsify 入口瀏覽。 

### 設定 Docsify 入口

```sh
# 進入 PHP 容器
docker exec -it php-fpm_f3cms bash 
# 容器工作目錄
cd ./f3cms
ln -s ../document ./docu
```
