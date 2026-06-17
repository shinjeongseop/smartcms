<?php
// Copy this file to config.php and adjust it per environment.
define('MYADMIN_PLUGIN_URL', '');
define('MYADMIN_APP_NAME', 'My DB Admin');
define('MYADMIN_APP_CHARSET', 'UTF-8');
define('MYADMIN_DEBUG_MODE', false);

define('MYADMIN_DB_HOST', 'localhost');
define('MYADMIN_DB_USER', 'your_db_user');
define('MYADMIN_DB_PASS', 'your_db_password');
define('MYADMIN_DB_PORT', 3306);
define('MYADMIN_DB_CHARSET', 'utf8mb4');

define('MYADMIN_ADMIN_PASSWORD', 'change-me');

// System databases are hidden from the UI.
$GLOBALS['MYADMIN_EXCLUDE_DATABASES'] = ['information_schema', 'performance_schema', 'mysql', 'sys'];

// Block a small set of destructive SQL patterns in the SQL runner.
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
    '/;.*;/s',
];
