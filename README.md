# docker-simple-lamp

## 版本資訊
+ nginx alpine
+ php 7.2
+ maria 10

## 資料夾結構
```
conf           設定檔  
   apache      apache 設定檔  
   mysql       mysql 設定檔  
   nginx       nginx 設定檔
   php         php 設定檔  

database       資料庫檔案  

log            log  
   apache      apache log  
   nginx       nginx log  
   mysql       mysql log  
   php         php log  

www            website 網站位置  
   f3cms       f3cms submodule
   pma         phpmyadmin 
   vendor      php composer dir

gene-panel     backend submodule
operonjs       frontend submodule
```

## Preparation
> 
> # host loc.f3cms.com
> "0.0.0.0  loc.f3cms.com" >> /etc/hosts
> 
> # first build
> docker-compose build
> 

## Set .env (local folder)
```
### apache ######

APACHE_HOST_HTTP_PORT=8080              // http port  
APACHE_HOST_HTTPS_PORT=4433             // http port  
APACHE_HOST_LOG_PATH=./log/apache       // apache log path  
APACHE_CONF_PATH=./conf/apache          // apache config path
APACHE_WWW_PATH=./www                   // website path


### php ######

PHP_HOST_LOG_PATH=./log/php             // php log path
PHP_CONF_PATH=./conf/php                // php config path


### mysql ######

MYSQL_PORT=3366                         // mysql port
MYSQL_LOG_PATH=./log/mysql              // mysql log path
MYSQL_ROOT_PASSWORD=sPes4uBrEcHUq5qE    // mysql root password
MYSQL_DATABASE=target_db                // mysql default database
MYSQL_USER=root                         // mysql root user
MYSQL_PASSWORD=sPes4uBrEcHUq5qE         // mysql user password
MYSQL_CONF_PATH=./conf/mysql/my.cnf     // mysql config path
MYSQL_DATA_PATH=./database              // mysql data path
```

## Install Phpmyadmin
> 
> cd www/pma
> wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip
> unzip phpMyAdmin-latest-all-languages.zip -d ../
> mv ../phpMyAdmin-*/* ./
> rm -rf ../phpMyAdmin-*
> rm -rf ./phpMyAdmin-latest-all-languages.zip
> cd -
> 

## Start the Container
> 
> ./up.sh
> 

## Close the Container
> 
> ./down.sh
> 

## Docker
> 
> docker exec -it nginx_fc /bin/sh
> 
> docker exec nginx_fc tail -f /var/log/nginx/error.log
> 
> docker logs nginx_fc
> 
> docker restart nginx_fc
> 
> docker exec -it php-fpm_fc bash
> 

## Server Test
[loc.f3cms.com:4433](https://loc.f3cms.com:4433/)

## DB Init
open [loc.f3cms.com:4433/pma/index.php](https://loc.f3cms.com:4433/pma/index.php)  
and import conf/mysql/init.sql  
Or
> 
> mysql -uroot -p --port=3366 -h loc.f3cms.com target_db < ./conf/mysql/init.sql
> 

## Loc SSL
> 
> mkcert loc.f3cms.com my.f3cms.lo
> 

## Submodule
> 
> git submodule init
> 

& follow submodule's RESDME.md

