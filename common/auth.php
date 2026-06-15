<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/http_error.php';
require_once __DIR__ . '/routes.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/security.php';

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

function smartcms_ensure_user_avatar_column(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $exists = (int)smartcms_fetch_value(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = 'avatar_path'",
            ['table_name' => smartcms_table('users')]
        );

        if ($exists === 0) {
            smartcms_execute(
                "ALTER TABLE " . smartcms_table('users') . "
                 ADD COLUMN avatar_path VARCHAR(255) DEFAULT NULL AFTER company_name"
            );
        }
    } catch (Throwable $e) {
        // Keep the app usable even if schema migration is not allowed.
    }
}

function smartcms_ensure_user_nickname_column(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $exists = (int)smartcms_fetch_value(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = 'nickname'",
            ['table_name' => smartcms_table('users')]
        );

        if ($exists === 0) {
            smartcms_execute(
                "ALTER TABLE " . smartcms_table('users') . "
                 ADD COLUMN nickname VARCHAR(80) DEFAULT NULL AFTER name"
            );
        }
    } catch (Throwable $e) {
        // Keep the app usable even if schema migration is not allowed.
    }
}

function smartcms_ensure_password_reset_tokens_table(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('password_reset_tokens') . " (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            email VARCHAR(190) NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_password_reset_tokens_hash (token_hash),
            INDEX idx_password_reset_tokens_user (user_id, used_at, expires_at),
            INDEX idx_password_reset_tokens_email (email, used_at, expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
        // Keep login-related pages usable even if schema creation is unavailable.
    }
}

function smartcms_password_reset_token_hash(string $token): string
{
    return hash('sha256', $token);
}

function smartcms_password_reset_random_token(): string
{
    return rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
}

function smartcms_password_reset_from_email(): string
{
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');
    $host = preg_replace('/:\d+$/', '', $host);
    if ($host === '') {
        $host = 'localhost';
    }

    return (string)smartcms_config_value('mail_from_email', 'no-reply@' . $host);
}

function smartcms_send_mail(string $to, string $subject, string $body): bool
{
    if (!function_exists('mail')) {
        return false;
    }

    $from = smartcms_password_reset_from_email();
    $from_name = str_replace(["\r", "\n"], '', smartcms_site_name());
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encoded_from_name = '=?UTF-8?B?' . base64_encode($from_name) . '?=';
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $encoded_from_name . ' <' . $from . '>',
        'Reply-To: ' . $from,
    ];

    return @mail($to, $encoded_subject, $body, implode("\r\n", $headers));
}

function smartcms_password_reset_email_body(string $name, string $reset_url): string
{
    $site_name = smartcms_site_name();

    return trim(
        "안녕하세요, {$name}님.\n\n"
        . "{$site_name} 비밀번호 재설정 요청이 접수되었습니다.\n"
        . "아래 링크를 열어 새 비밀번호를 설정해 주세요.\n\n"
        . $reset_url . "\n\n"
        . "이 링크는 일정 시간이 지나면 만료됩니다.\n"
        . "본인이 요청하지 않았다면 이 메일을 무시하셔도 됩니다.\n"
    ) . "\n";
}

function smartcms_password_reset_store_token(int $user_id, string $email, string $token, int $ttl_seconds = 3600): void
{
    $expires_at = date('Y-m-d H:i:s', time() + max(300, $ttl_seconds));
    smartcms_execute(
        "DELETE FROM " . smartcms_table('password_reset_tokens') . " WHERE user_id = :user_id AND used_at IS NULL",
        ['user_id' => $user_id]
    );
    smartcms_execute(
        "INSERT INTO " . smartcms_table('password_reset_tokens') . "
         (user_id, email, token_hash, expires_at)
         VALUES (:user_id, :email, :token_hash, :expires_at)",
        [
            'user_id' => $user_id,
            'email' => $email,
            'token_hash' => smartcms_password_reset_token_hash($token),
            'expires_at' => $expires_at,
        ]
    );
}

function smartcms_password_reset_request(string $email): array
{
    smartcms_ensure_password_reset_tokens_table();
    $email = trim($email);

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => '올바른 이메일을 입력하세요.'];
    }

    $user = smartcms_fetch_one(
        "SELECT id, email, name, status
         FROM " . smartcms_table('users') . "
         WHERE email = :email
         LIMIT 1",
        ['email' => $email]
    );

    if (!$user || (string)$user['status'] !== 'active') {
        return ['ok' => true, 'message' => '해당 이메일로 가입된 계정이 확인되면, 비밀번호 재설정 메일을 보냅니다.'];
    }

    $token = smartcms_password_reset_random_token();
    smartcms_password_reset_store_token((int)$user['id'], (string)$user['email'], $token);

    $reset_url = smartcms_base_url('/member/reset/') . '?token=' . rawurlencode($token);
    $subject = smartcms_site_name() . ' 비밀번호 재설정 안내';
    $body = smartcms_password_reset_email_body((string)$user['name'], $reset_url);

    if (!smartcms_send_mail((string)$user['email'], $subject, $body)) {
        smartcms_execute(
            "DELETE FROM " . smartcms_table('password_reset_tokens') . " WHERE token_hash = :token_hash",
            ['token_hash' => smartcms_password_reset_token_hash($token)]
        );
        return ['ok' => false, 'message' => '비밀번호 재설정 이메일을 보내지 못했습니다. 잠시 후 다시 시도해 주세요.'];
    }

    smartcms_log_access('page_view', 'member', 'password_reset_request', 'success', 200, (int)$user['id']);

    return ['ok' => true, 'message' => '비밀번호 재설정 이메일을 보냈습니다. 메일함을 확인해 주세요.'];
}

function smartcms_password_reset_token_row(string $token): ?array
{
    smartcms_ensure_password_reset_tokens_table();
    $token = trim($token);
    if ($token === '') {
        return null;
    }

    return smartcms_fetch_one(
        "SELECT id, user_id, email, token_hash, expires_at, used_at
         FROM " . smartcms_table('password_reset_tokens') . "
         WHERE token_hash = :token_hash
           AND used_at IS NULL
           AND expires_at > NOW()
         LIMIT 1",
        ['token_hash' => smartcms_password_reset_token_hash($token)]
    );
}

function smartcms_password_reset_complete(string $token, string $new_password): array
{
    $row = smartcms_password_reset_token_row($token);
    if (!$row) {
        return ['ok' => false, 'message' => '유효하지 않거나 만료된 재설정 링크입니다.'];
    }

    if (strlen($new_password) < 8) {
        return ['ok' => false, 'message' => '새 비밀번호는 8자 이상이어야 합니다.'];
    }

    smartcms_execute(
        "UPDATE " . smartcms_table('users') . "
         SET password_hash = :password_hash
         WHERE id = :id",
        [
            'id' => (int)$row['user_id'],
            'password_hash' => password_hash($new_password, PASSWORD_DEFAULT),
        ]
    );

    smartcms_execute(
        "UPDATE " . smartcms_table('password_reset_tokens') . "
         SET used_at = NOW()
         WHERE id = :id",
        ['id' => (int)$row['id']]
    );

    smartcms_log_access('page_view', 'member', 'password_reset', 'success', 200, (int)$row['user_id']);

    return ['ok' => true, 'message' => '비밀번호를 변경했습니다. 로그인해 주세요.'];
}

function smartcms_user_display_name(?array $user): string
{
    $nickname = trim((string)($user['nickname'] ?? ''));
    if ($nickname !== '') {
        return $nickname;
    }

    return trim((string)($user['name'] ?? ''));
}

function smartcms_user_avatar_url(?array $user): ?string
{
    $path = trim((string)($user['avatar_path'] ?? ''));
    if ($path === '') {
        return null;
    }

    $relative_path = ltrim($path, '/');
    $absolute_path = SMARTCMS_ROOT . '/' . $relative_path;
    if (!is_file($absolute_path)) {
        return null;
    }

    return smartcms_asset_url('/' . $relative_path);
}

function smartcms_store_user_avatar_upload(int $user_id, array $upload, ?string $current_avatar_path = null): array
{
    if (!isset($upload['error']) || (int)$upload['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => '아바타 이미지를 선택하세요.'];
    }

    $size = (int)($upload['size'] ?? 0);
    if ($size <= 0) {
        return ['ok' => false, 'message' => '아바타 파일을 읽을 수 없습니다.'];
    }
    if ($size > 2 * 1024 * 1024) {
        return ['ok' => false, 'message' => '아바타 이미지는 2MB 이하만 업로드할 수 있습니다.'];
    }

    $tmp_path = (string)($upload['tmp_name'] ?? '');
    if ($tmp_path === '' || !is_uploaded_file($tmp_path)) {
        return ['ok' => false, 'message' => '업로드된 파일을 확인할 수 없습니다.'];
    }

    $original_name = basename((string)($upload['name'] ?? 'avatar'));
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowed_extensions, true)) {
        return ['ok' => false, 'message' => 'JPG, PNG, GIF, WEBP 파일만 허용됩니다.'];
    }

    $image_info = @getimagesize($tmp_path);
    if (!is_array($image_info) || empty($image_info['mime'])) {
        return ['ok' => false, 'message' => '이미지 파일만 업로드할 수 있습니다.'];
    }

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array((string)$image_info['mime'], $allowed_mimes, true)) {
        return ['ok' => false, 'message' => '이미지 형식을 확인할 수 없습니다.'];
    }

    $upload_root = SMARTCMS_ROOT . '/uploads/avatar';
    if (!is_dir($upload_root)) {
        mkdir($upload_root, 0755, true);
    }

    $safe_extension = $extension === 'jpeg' ? 'jpg' : $extension;
    $stored_name = 'user_' . $user_id . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $safe_extension;
    $target_path = $upload_root . '/' . $stored_name;

    if (!move_uploaded_file($tmp_path, $target_path)) {
        return ['ok' => false, 'message' => '아바타 저장에 실패했습니다.'];
    }

    $avatar_path = 'uploads/avatar/' . $stored_name;
    smartcms_execute(
        "UPDATE " . smartcms_table('users') . " SET avatar_path = :avatar_path WHERE id = :id",
        [
            'avatar_path' => $avatar_path,
            'id' => $user_id,
        ]
    );

    $previous_path = trim((string)$current_avatar_path);
    if ($previous_path !== '' && $previous_path !== $avatar_path) {
        $previous_file = SMARTCMS_ROOT . '/' . ltrim($previous_path, '/');
        if (is_file($previous_file)) {
            @unlink($previous_file);
        }
    }

    return ['ok' => true, 'message' => '아바타를 변경했습니다.', 'avatar_path' => $avatar_path];
}

function smartcms_current_user(): ?array
{
    smartcms_session_start();
    smartcms_ensure_user_avatar_column();
    smartcms_ensure_user_nickname_column();
    $user_id = (int)($_SESSION['smartcms_user_id'] ?? 0);
    if ($user_id <= 0) {
        return null;
    }

    return smartcms_fetch_one(
        "SELECT id, email, name, nickname, company_name, avatar_path, role, level, status, last_login_at, created_at
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

function smartcms_member_login_next_target(string $fallback = '/'): string
{
    $next = trim((string)($_POST['return_to'] ?? ''));

    if ($next === '') {
        $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
        if ($referer !== '') {
            $current_host = (string)($_SERVER['HTTP_HOST'] ?? '');
            $referer_parts = parse_url($referer);
            if (is_array($referer_parts)) {
                $referer_host = (string)($referer_parts['host'] ?? '');
                $referer_path = (string)($referer_parts['path'] ?? '');
                $is_login_referer = in_array($referer_path, ['/member/login/', '/admin/login/'], true);
                if (!$is_login_referer && $referer_path !== '' && ($referer_host === '' || $current_host === '' || strcasecmp($referer_host, $current_host) === 0)) {
                    $next = $referer_path;
                    if (isset($referer_parts['query']) && $referer_parts['query'] !== '') {
                        $next .= '?' . $referer_parts['query'];
                    }
                }
            }
        }
    }

    if ($next === '') {
        $next = $fallback;
    }

    $parts = parse_url($next);
    if (!is_array($parts)) {
        return $fallback;
    }

    if (isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
        return $fallback;
    }

    $path = (string)($parts['path'] ?? '');
    if ($path === '' || $path[0] !== '/') {
        return $fallback;
    }

    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';

    return $path . $query . $fragment;
}

function smartcms_require_level(int $required_level, ?string $redirect_to = null): array
{
    $user = smartcms_require_login($redirect_to);
    if (smartcms_has_level($required_level, $user)) {
        return $user;
    }

    smartcms_log_access('permission_denied', 'page', null, 'denied', 403, (int)$user['id']);
    smartcms_render_access_denied_page('이 페이지를 볼 권한이 없습니다.');
}

function smartcms_page_permission_defaults(): array
{
    return [
        'member_register' => [
            'page_key' => 'member_register',
            'page_path' => '/member/register/',
            'title' => '회원가입',
            'page_view_level' => 0,
            'allow_guest' => 1,
            'status' => 'active',
        ],
    ];
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
        $defaults = smartcms_page_permission_defaults()[$page_key] ?? null;
        $permission = $defaults ?? [
            'page_key' => $page_key,
            'page_path' => $page_path,
            'title' => $title,
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
        smartcms_render_access_denied_page('현재 비활성화된 페이지입니다.');
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
        smartcms_render_access_denied_page('이 페이지를 볼 권한이 없습니다.');
    }

    smartcms_log_access('page_view', 'page', $page_key, 'success', 200, (int)$user['id']);
    return $user;
}

function smartcms_register_user(string $email, string $password, string $name, ?string $nickname = null, ?string $company_name = null): array
{
    smartcms_ensure_user_avatar_column();
    smartcms_ensure_user_nickname_column();

    if (!smartcms_setting_bool('allow_registration', true)) {
        return ['ok' => false, 'message' => '현재 회원가입이 중지되어 있습니다.'];
    }

    $email = trim($email);
    $name = trim($name);
    $nickname = trim((string)$nickname);
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
    if (function_exists('mb_strlen') ? mb_strlen($name) > 80 : strlen($name) > 80) {
        return ['ok' => false, 'message' => '이름은 80자 이하로 입력하세요.'];
    }
    if ($nickname !== '' && (function_exists('mb_strlen') ? mb_strlen($nickname) > 80 : strlen($nickname) > 80)) {
        return ['ok' => false, 'message' => '닉네임은 80자 이하로 입력하세요.'];
    }
    if ($company_name !== '' && (function_exists('mb_strlen') ? mb_strlen($company_name) > 120 : strlen($company_name) > 120)) {
        return ['ok' => false, 'message' => '회사명은 120자 이하로 입력하세요.'];
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
         (email, password_hash, name, nickname, company_name, avatar_path, role, level, status)
         VALUES (:email, :password_hash, :name, :nickname, :company_name, NULL, 'user', :level, 'active')",
        [
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'nickname' => $nickname !== '' ? $nickname : null,
            'company_name' => $company_name !== '' ? $company_name : null,
            'level' => smartcms_setting_int('default_member_level', (int)smartcms_config_value('default_member_level', 2)),
        ]
    );

    return ['ok' => true, 'message' => '회원가입이 완료되었습니다. 로그인하세요.'];
}

function smartcms_update_user_profile(int $user_id, string $name, ?string $nickname = null, ?string $company_name = null): array
{
    smartcms_ensure_user_avatar_column();
    smartcms_ensure_user_nickname_column();

    $name = trim($name);
    $nickname = trim((string)$nickname);
    $company_name = trim((string)$company_name);

    if ($name === '') {
        return ['ok' => false, 'message' => '이름을 입력하세요.'];
    }
    if (function_exists('mb_strlen') ? mb_strlen($name) > 80 : strlen($name) > 80) {
        return ['ok' => false, 'message' => '이름은 80자 이하로 입력하세요.'];
    }
    if ($nickname !== '' && (function_exists('mb_strlen') ? mb_strlen($nickname) > 80 : strlen($nickname) > 80)) {
        return ['ok' => false, 'message' => '닉네임은 80자 이하로 입력하세요.'];
    }
    if ($company_name !== '' && (function_exists('mb_strlen') ? mb_strlen($company_name) > 120 : strlen($company_name) > 120)) {
        return ['ok' => false, 'message' => '회사명은 120자 이하로 입력하세요.'];
    }

    smartcms_execute(
        "UPDATE " . smartcms_table('users') . "
         SET name = :name,
             nickname = :nickname,
             company_name = :company_name
         WHERE id = :id",
        [
            'id' => $user_id,
            'name' => $name,
            'nickname' => $nickname !== '' ? $nickname : null,
            'company_name' => $company_name !== '' ? $company_name : null,
        ]
    );

    return ['ok' => true, 'message' => '프로필을 저장했습니다.'];
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
    smartcms_ensure_user_avatar_column();
    $email = trim($email);
    $user = smartcms_fetch_one(
        "SELECT id, email, password_hash, name, avatar_path, role, level, status
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
