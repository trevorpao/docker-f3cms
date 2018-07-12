# docker-simple-lamp  


### 版本資訊

```
php 5.6
apache 2.4
mysql 5.6
```



------



### 資料夾結構

```
conf           設定檔  
   apache      apache 設定檔  
   mysql       mysql 設定檔  
   php         php 設定檔  

database       資料庫檔案  

log            log  
   apache      apache log  
   mysql       mysql log  
   php         php log  

www            website 網站位置  
```

 

------



### .env (local folder)

```
### apache ######

APACHE_HOST_HTTP_PORT=80                // http port  
APACHE_HOST_HTTPS_PORT=443              // http port  
APACHE_HOST_LOG_PATH=./log/apache       // apache log path  
APACHE_CONF_PATH=./conf/apache          // apache config path
APACHE_WWW_PATH=./www                   // website path


### php ######

PHP_HOST_LOG_PATH=./log/php             // php log path
PHP_CONF_PATH=./conf/php                // php config path


### mysql ######

MYSQL_PORT=3306                         // mysql port
MYSQL_LOG_PATH=./log/mysql              // mysql log path
MYSQL_ROOT_PASSWORD=sPes4uBrEcHUq5qE    // mysql root password
MYSQL_DATABASE=db_name                  // mysql default database
MYSQL_USER=root                         // mysql root user
MYSQL_PASSWORD=sPes4uBrEcHUq5qE         // mysql user password
MYSQL_CONF_PATH=./conf/mysql/my.cnf     // mysql config path
MYSQL_DATA_PATH=./database              // mysql data path
```



------



### start the container

 `./up.sh`  

### close the container

 `./down.sh`  

 ------

 ### server test
 `http://0.0.0.0:8080/`
