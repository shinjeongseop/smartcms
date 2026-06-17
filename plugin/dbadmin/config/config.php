<?php
define('MYADMIN_PLUGIN_URL', '/plugin/dbadmin');
define('MYADMIN_APP_NAME', 'DBAdmin');
define('MYADMIN_APP_CHARSET', 'UTF-8');
define('MYADMIN_DEBUG_MODE', true);

define('MYADMIN_DB_HOST', 'localhost');
define('MYADMIN_DB_USER', 'smartcms');
define('MYADMIN_DB_PASS', 'tjql1635!');
define('MYADMIN_DB_PORT', 3306);
define('MYADMIN_DB_CHARSET', 'utf8mb4');

define('MYADMIN_ADMIN_PASSWORD', 'tjql1635!');

$GLOBALS['MYADMIN_EXCLUDE_DATABASES'] = ['information_schema', 'performance_schema', 'mysql', 'sys'];
$GLOBALS['MYADMIN_BLOCKED_SQL_PATTERNS'] = [
    '/\bshutdown\b/i',
    '/\bgrant\b/i',
    '/\brevoke\b/i',
    '/\bflush\b/i',
    '/\breset\b/i',
    '/\bkill\b/i',
    '/\bload_file\s*\(/i',
    '/\binto\s+outfile\b/i',
    '/\binto\s+dumpfile\b/i',
    '/\bsleep\s*\(/i',
    '/\bbenchmark\s*\(/i',
    '/;.*;/s'
];
