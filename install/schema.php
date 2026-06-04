<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/schema.php';

if (smartcms_install_locked()) {
    http_response_code(403);
    echo 'Installation is locked.';
    exit;
}

try {
    smartcms_create_schema();
    echo 'Schema created.';
} catch (Throwable $e) {
    http_response_code(500);
    echo $e->getMessage();
}
