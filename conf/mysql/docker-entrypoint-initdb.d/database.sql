CREATE USER 'target_db'@'%' IDENTIFIED BY 'password';

CREATE DATABASE target_db;

GRANT ALL ON target_db.* TO 'target_db'@'%';

FLUSH PRIVILEGES;
