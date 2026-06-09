<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function smartcms_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = (string)smartcms_config_value('db.host', 'localhost');
    $name = (string)smartcms_config_value('db.name', '');
    $charset = (string)smartcms_config_value('db.charset', 'utf8mb4');
    $user = (string)smartcms_config_value('db.user', '');
    $pass = (string)smartcms_config_value('db.pass', '');

    if ($name === '') {
        throw new RuntimeException('Database name is not configured.');
    }

    $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function smartcms_db_check(array $db): array
{
    $host = (string)($db['host'] ?? 'localhost');
    $name = (string)($db['name'] ?? '');
    $charset = (string)($db['charset'] ?? 'utf8mb4');
    $user = (string)($db['user'] ?? '');
    $pass = (string)($db['pass'] ?? '');

    if ($name === '') {
        return ['ok' => false, 'message' => 'DB 이름을 입력해야 합니다.'];
    }

    try {
        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return ['ok' => true, 'message' => 'DB 연결에 성공했습니다.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

function smartcms_fetch_one(string $sql, array $params = []): ?array
{
    $stmt = smartcms_db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function smartcms_fetch_all(string $sql, array $params = []): array
{
    $stmt = smartcms_db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function smartcms_fetch_value(string $sql, array $params = []): mixed
{
    $stmt = smartcms_db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function smartcms_execute(string $sql, array $params = []): bool
{
    $stmt = smartcms_db()->prepare($sql);
    return $stmt->execute($params);
}
