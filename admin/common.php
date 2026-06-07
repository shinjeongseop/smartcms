<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/auth.php';

/**
 * 관리자 권한 확인 후 사용자 반환
 */
function smartcms_admin_user(): array
{
    return smartcms_require_level(
        smartcms_setting_int('admin_level', (int)smartcms_config_value('admin_level', 8)),
        (string)smartcms_config_value('admin_login_url', '/admin/login/')
    );
}

/**
 * 이름 첫 글자 반환 (아바타용)
 */
function smartcms_admin_initial(string $name): string
{
    return function_exists('mb_substr') ? mb_substr($name, 0, 1) : substr($name, 0, 1);
}
