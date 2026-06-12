<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function smartcms_security_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name((string)smartcms_config_value('session_name', 'smartcms_session'));
    session_set_cookie_params([
        'path' => (string)smartcms_config_value('cookie_path', '/'),
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function smartcms_csrf_token(): string
{
    smartcms_security_session_start();
    if (empty($_SESSION['smartcms_csrf_token'])) {
        $_SESSION['smartcms_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['smartcms_csrf_token'];
}

function smartcms_csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . smartcms_h(smartcms_csrf_token()) . '">';
}

function smartcms_verify_csrf(): bool
{
    smartcms_security_session_start();
    $session_token = (string)($_SESSION['smartcms_csrf_token'] ?? '');
    $posted_token = (string)($_POST['csrf_token'] ?? '');

    return $session_token !== '' && $posted_token !== '' && hash_equals($session_token, $posted_token);
}

function smartcms_verify_csrf_or_fail(): void
{
    if (smartcms_verify_csrf()) {
        return;
    }

    http_response_code(403);
    echo 'Invalid CSRF token.';
    exit;
}

function smartcms_flash_set(string $key, mixed $value): void
{
    smartcms_security_session_start();
    $_SESSION['smartcms_flash'] ??= [];
    $_SESSION['smartcms_flash'][$key] = $value;
}

function smartcms_flash_get(string $key, mixed $default = null): mixed
{
    smartcms_security_session_start();
    if (!isset($_SESSION['smartcms_flash']) || !is_array($_SESSION['smartcms_flash']) || !array_key_exists($key, $_SESSION['smartcms_flash'])) {
        return $default;
    }

    $value = $_SESSION['smartcms_flash'][$key];
    unset($_SESSION['smartcms_flash'][$key]);

    if (empty($_SESSION['smartcms_flash'])) {
        unset($_SESSION['smartcms_flash']);
    }

    return $value;
}
