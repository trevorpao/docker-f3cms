<?php

function tests_smoke_create_entity_seen_table($tableName)
{
    $statement = tests_smoke_load_entity_seen_table_statement($tableName);
    mh()->query($statement);
}

function tests_smoke_drop_entity_seen_table($tableName)
{
    $tableName = trim((string) $tableName);
    if ('' === $tableName) {
        throw new \RuntimeException('Entity seen table drop requires a table name.');
    }

    mh()->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $tableName) . '`');
}

function tests_smoke_load_entity_seen_table_statement($tableName)
{
    $tableName = trim((string) $tableName);
    if ('' === $tableName) {
        throw new \RuntimeException('Entity seen table create requires a table name.');
    }

    $sqlPath = dirname(__DIR__, 3) . '/document/sql/260425.sql';
    $sql = file_get_contents($sqlPath);
    if (false === $sql || '' === trim($sql)) {
        throw new \RuntimeException('Unable to load entity seen SQL artifact: ' . $sqlPath);
    }

    $pattern = '/CREATE TABLE IF NOT EXISTS\s+`' . preg_quote($tableName, '/') . '`\s*\(.*?\) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT=.*?;/s';
    if (!preg_match($pattern, $sql, $matches)) {
        throw new \RuntimeException('Entity seen SQL statement not found for table: ' . $tableName);
    }

    return $matches[0];
}