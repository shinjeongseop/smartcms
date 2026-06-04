<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';

if (!smartcms_install_locked()) {
    file_put_contents(SMARTCMS_ROOT . '/install.lock', date('c') . PHP_EOL, LOCK_EX);
}

echo 'Installation locked.';
