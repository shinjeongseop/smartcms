<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function smartcms_db_identifier(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

function smartcms_db_managed_tables(): array
{
    $prefix = (string)smartcms_config_value('table_prefix', 'sc_');
    $pattern = $prefix !== '' ? $prefix . '%' : '%';
    $stmt = smartcms_db()->query('SHOW TABLES LIKE ' . smartcms_db()->quote($pattern));
    $tables = [];

    foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
        if (!empty($row[0])) {
            $tables[] = (string)$row[0];
        }
    }

    sort($tables);
    return $tables;
}

function smartcms_db_backup_sql(): string
{
    $pdo = smartcms_db();
    $tables = smartcms_db_managed_tables();
    $generatedAt = date('Y-m-d H:i:s');
    $prefix = (string)smartcms_config_value('table_prefix', 'sc_');
    $sql = "-- SmartCMS database backup\n";
    $sql .= "-- Generated at: {$generatedAt}\n";
    $sql .= "-- Table prefix: {$prefix}\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        $identifier = smartcms_db_identifier($table);
        $createRow = $pdo->query('SHOW CREATE TABLE ' . $identifier)->fetch(PDO::FETCH_ASSOC);
        $createSql = is_array($createRow) ? (string)($createRow['Create Table'] ?? array_values($createRow)[1] ?? '') : '';

        if ($createSql === '') {
            continue;
        }

        $sql .= $createSql . ";\n\n";

        $rows = $pdo->query('SELECT * FROM ' . $identifier)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $columns = array_map(static fn(string $column): string => smartcms_db_identifier($column), array_keys($row));
            $values = array_map(static function (mixed $value) use ($pdo): string {
                return $value === null ? 'NULL' : $pdo->quote((string)$value);
            }, array_values($row));

            $sql .= 'INSERT INTO ' . $identifier . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n";
        }

        $sql .= "\n";
    }

    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $sql;
}
