<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/routes.php';

function smartcms_session_start(): void
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

function smartcms_current_user(): ?array
{
    smartcms_session_start();
    $user_id = (int)($_SESSION['smartcms_user_id'] ?? 0);
    if ($user_id <= 0) {
        return null;
    }

    return smartcms_fetch_one(
        "SELECT id, email, name, company_name, role, level, status, last_login_at, created_at
         FROM " . smartcms_table('users') . "
         WHERE id = :id AND status = 'active'
         LIMIT 1",
        ['id' => $user_id]
    );
}

function smartcms_user_level(?array $user = null): int
{
    $user = $user ?? smartcms_current_user();
    return $user ? (int)$user['level'] : 0;
}

function smartcms_has_level(int $required_level, ?array $user = null): bool
{
    return smartcms_user_level($user) >= $required_level;
}

function smartcms_require_login(?string $redirect_to = null): array
{
    $user = smartcms_current_user();
    if ($user) {
        return $user;
    }

    $target = $redirect_to ?? (string)smartcms_config_value('login_url', '/member/login/');
    smartcms_redirect($target);
}

function smartcms_require_level(int $required_level, ?string $redirect_to = null): array
{
    $user = smartcms_require_login($redirect_to);
    if (smartcms_has_level($required_level, $user)) {
        return $user;
    }

    smartcms_log_access('permission_denied', 'page', null, 'denied', 403, (int)$user['id']);
    http_response_code(403);
    echo 'Permission denied.';
    exit;
}

function smartcms_require_page_view(string $page_key, string $page_path, string $title, int $default_view_level = 0): ?array
{
    $permission = smartcms_fetch_one(
        "SELECT page_view_level, allow_guest, status
         FROM " . smartcms_table('page_permissions') . "
         WHERE page_key = :page_key
         LIMIT 1",
        ['page_key' => $page_key]
    );

    if (!$permission) {
        smartcms_execute(
            "INSERT INTO " . smartcms_table('page_permissions') . "
             (page_key, page_path, title, page_view_level, allow_guest, status)
             VALUES (:page_key, :page_path, :title, :page_view_level, :allow_guest, 'active')",
            [
                'page_key' => $page_key,
                'page_path' => $page_path,
                'title' => $title,
                'page_view_level' => $default_view_level,
                'allow_guest' => $default_view_level <= 0 ? 1 : 0,
            ]
        );
        $permission = [
            'page_view_level' => $default_view_level,
            'allow_guest' => $default_view_level <= 0 ? 1 : 0,
            'status' => 'active',
        ];
    }

    $required_level = (int)$permission['page_view_level'];
    $user = smartcms_current_user();
    $user_id = $user ? (int)$user['id'] : null;

    if ((string)$permission['status'] !== 'active') {
        smartcms_log_access('permission_denied', 'page', $page_key, 'denied', 403, $user_id);
        http_response_code(403);
        echo 'Page disabled.';
        exit;
    }

    if ($required_level <= 0 || ((int)$permission['allow_guest'] === 1 && !$user)) {
        smartcms_log_access('page_view', 'page', $page_key, 'success', 200, $user_id);
        return $user;
    }

    if (!$user) {
        smartcms_redirect((string)smartcms_config_value('login_url', '/member/login/'));
    }

    if (!smartcms_has_level($required_level, $user)) {
        smartcms_log_access('permission_denied', 'page', $page_key, 'denied', 403, (int)$user['id']);
        http_response_code(403);
        echo 'Permission denied.';
        exit;
    }

    smartcms_log_access('page_view', 'page', $page_key, 'success', 200, (int)$user['id']);
    return $user;
}

function smartcms_register_user(string $email, string $password, string $name, ?string $company_name = null): array
{
    $email = trim($email);
    $name = trim($name);
    $company_name = trim((string)$company_name);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => '올바른 이메일을 입력하세요.'];
    }
    if ($name === '') {
        return ['ok' => false, 'message' => '이름을 입력하세요.'];
    }
    if (strlen($password) < 8) {
        return ['ok' => false, 'message' => '비밀번호는 8자 이상이어야 합니다.'];
    }

    $exists = smartcms_fetch_one(
        "SELECT id FROM " . smartcms_table('users') . " WHERE email = :email LIMIT 1",
        ['email' => $email]
    );
    if ($exists) {
        return ['ok' => false, 'message' => '이미 가입된 이메일입니다.'];
    }

    smartcms_execute(
        "INSERT INTO " . smartcms_table('users') . "
         (email, password_hash, name, company_name, role, level, status)
         VALUES (:email, :password_hash, :name, :company_name, 'user', :level, 'active')",
        [
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'company_name' => $company_name !== '' ? $company_name : null,
            'level' => (int)smartcms_config_value('default_member_level', 2),
        ]
    );

    return ['ok' => true, 'message' => '회원가입이 완료되었습니다. 로그인하세요.'];
}

function smartcms_change_password(int $user_id, string $current_password, string $new_password): array
{
    if (strlen($new_password) < 8) {
        return ['ok' => false, 'message' => '새 비밀번호는 8자 이상이어야 합니다.'];
    }

    $user = smartcms_fetch_one(
        "SELECT id, password_hash FROM " . smartcms_table('users') . " WHERE id = :id AND status = 'active' LIMIT 1",
        ['id' => $user_id]
    );

    if (!$user || !password_verify($current_password, (string)$user['password_hash'])) {
        return ['ok' => false, 'message' => '현재 비밀번호가 올바르지 않습니다.'];
    }

    smartcms_execute(
        "UPDATE " . smartcms_table('users') . " SET password_hash = :password_hash WHERE id = :id",
        [
            'id' => $user_id,
            'password_hash' => password_hash($new_password, PASSWORD_DEFAULT),
        ]
    );

    return ['ok' => true, 'message' => '비밀번호를 변경했습니다.'];
}

function smartcms_login(string $email, string $password): array
{
    $email = trim($email);
    $user = smartcms_fetch_one(
        "SELECT id, email, password_hash, name, role, level, status
         FROM " . smartcms_table('users') . "
         WHERE email = :email
         LIMIT 1",
        ['email' => $email]
    );

    if (!$user) {
        smartcms_log_login(null, $email, 'fail');
        return ['ok' => false, 'message' => '이메일 또는 비밀번호가 올바르지 않습니다.'];
    }

    if ((string)$user['status'] !== 'active') {
        smartcms_log_login((int)$user['id'], $email, 'blocked');
        return ['ok' => false, 'message' => '사용할 수 없는 계정입니다.'];
    }

    if (!password_verify($password, (string)$user['password_hash'])) {
        smartcms_log_login((int)$user['id'], $email, 'fail');
        return ['ok' => false, 'message' => '이메일 또는 비밀번호가 올바르지 않습니다.'];
    }

    smartcms_session_start();
    session_regenerate_id(true);
    $_SESSION['smartcms_user_id'] = (int)$user['id'];
    $_SESSION['smartcms_user_level'] = (int)$user['level'];
    $_SESSION['smartcms_user_role'] = (string)$user['role'];

    smartcms_execute(
        "UPDATE " . smartcms_table('users') . " SET last_login_at = NOW() WHERE id = :id",
        ['id' => (int)$user['id']]
    );
    smartcms_log_login((int)$user['id'], $email, 'success');
    smartcms_log_access('login_success', 'member', 'login', 'success', 200, (int)$user['id']);

    return ['ok' => true, 'message' => '로그인했습니다.', 'user' => $user];
}

function smartcms_logout(): void
{
    $user = smartcms_current_user();
    if ($user) {
        smartcms_log_access('logout', 'member', 'logout', 'success', 200, (int)$user['id']);
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
}

function smartcms_log_login(?int $user_id, string $email, string $result): void
{
    try {
        smartcms_execute(
            "INSERT INTO " . smartcms_table('login_logs') . " (user_id, email, ip_hash, user_agent, result)
             VALUES (:user_id, :email, :ip_hash, :user_agent, :result)",
            [
                'user_id' => $user_id,
                'email' => $email,
                'ip_hash' => smartcms_ip_hash(),
                'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'result' => $result,
            ]
        );
    } catch (Throwable) {
        // Logging must not block login flow.
    }
}

function smartcms_log_access(string $access_type, string $target_type, ?string $target_key = null, string $result = 'success', int $status_code = 200, ?int $user_id = null): void
{
    try {
        smartcms_execute(
            "INSERT INTO " . smartcms_table('access_logs') . "
             (user_id, session_key, access_type, target_type, target_key, request_path, method, ip_hash, origin, referer, user_agent, result, status_code)
             VALUES (:user_id, :session_key, :access_type, :target_type, :target_key, :request_path, :method, :ip_hash, :origin, :referer, :user_agent, :result, :status_code)",
            [
                'user_id' => $user_id,
                'session_key' => session_id() ?: null,
                'access_type' => $access_type,
                'target_type' => $target_type,
                'target_key' => $target_key,
                'request_path' => substr((string)($_SERVER['REQUEST_URI'] ?? ''), 0, 255),
                'method' => substr((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'), 0, 10),
                'ip_hash' => smartcms_ip_hash(),
                'origin' => substr((string)($_SERVER['HTTP_ORIGIN'] ?? ''), 0, 255),
                'referer' => substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 500),
                'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'result' => $result,
                'status_code' => $status_code,
            ]
        );
    } catch (Throwable) {
        // Access logs are observational and should never break the page.
    }
}

function smartcms_ip_hash(): ?string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip === '') {
        return null;
    }

    return hash('sha256', $ip . '|' . (string)smartcms_config_value('project_key', 'smartcms'));
}
