<?php
if (!defined('MYADMIN_ACCESS')) {
    exit;
}

if (!defined('MYADMIN_SET_TIME_LIMIT')) {
    define('MYADMIN_SET_TIME_LIMIT', 0);
}
@set_time_limit(MYADMIN_SET_TIME_LIMIT);

if (!defined('MYADMIN_INIT')) {
    define('MYADMIN_INIT', true);
    define('MYADMIN_PLUGIN_PATH', realpath(__DIR__ . '/../../'));

    $myadminConfig = MYADMIN_PLUGIN_PATH . '/config/config.php';
    if (!is_file($myadminConfig)) {
        $myadminConfig = MYADMIN_PLUGIN_PATH . '/config/config.sample.php';
    }
    require_once $myadminConfig;

    define('ROOT_PATH', MYADMIN_PLUGIN_PATH);
    define('ROOT_URL', MYADMIN_PLUGIN_URL);
    define('ASSETS_PATH', ROOT_PATH . '/assets');
    define('ASSETS_URL', ROOT_URL . '/assets');
    define('COMMON_PATH', ROOT_PATH . '/common');
    define('COMMON_PHP_PATH', COMMON_PATH . '/php');
    define('COMMON_JS_PATH', COMMON_PATH . '/js');
    define('COMMON_CSS_PATH', COMMON_PATH . '/css');
    define('COMMON_JS_URL', ROOT_URL . '/common/js');
    define('COMMON_CSS_URL', ROOT_URL . '/common/css');
    define('DBADMIN_PATH', ROOT_PATH);
    define('DBADMIN_URL', ROOT_URL);
    define('APP_NAME', MYADMIN_APP_NAME);
    define('APP_CHARSET', MYADMIN_APP_CHARSET);
    define('DEBUG_MODE', MYADMIN_DEBUG_MODE);
    define('DB_HOST', MYADMIN_DB_HOST);
    define('DB_USER', MYADMIN_DB_USER);
    define('DB_PASS', MYADMIN_DB_PASS);
    define('DB_PORT', MYADMIN_DB_PORT);
    define('DB_CHARSET', MYADMIN_DB_CHARSET);
    define('ADMIN_PASSWORD', MYADMIN_ADMIN_PASSWORD);

    $GLOBALS['EXCLUDE_DATABASES'] = $GLOBALS['MYADMIN_EXCLUDE_DATABASES'] ?? [];
    $GLOBALS['BLOCKED_SQL_PATTERNS'] = $GLOBALS['MYADMIN_BLOCKED_SQL_PATTERNS'] ?? [];

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAuthed = !empty($_SESSION['myadmin_auth']) || !empty($_SESSION['dbadmin_auth']);
    if (!defined('SKIP_AUTH') && !$isAuthed) {
        $isPostJson = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
        if ($isPostJson) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.', 'data' => []], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Location: ' . DBADMIN_URL . '/login.php');
        exit;
    }
}
