<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/auth.php';

function smartcms_admin_user(): array
{
    return smartcms_require_level(smartcms_setting_int('admin_level', (int)smartcms_config_value('admin_level', 8)), (string)smartcms_config_value('admin_login_url', '/admin/login/'));
}

function smartcms_admin_nav(string $active = ''): string
{
    $items = [
        'dashboard' => ['label' => '대시보드', 'href' => '/admin/dashboard/'],
        'users' => ['label' => '회원 관리', 'href' => '/admin/users/'],
        'boards' => ['label' => '게시판 관리', 'href' => '/admin/boards/'],
        'pages' => ['label' => '페이지 권한', 'href' => '/admin/pages/'],
        'logs' => ['label' => '접속 로그', 'href' => '/admin/logs/'],
        'database' => ['label' => 'DB 관리', 'href' => '/admin/database/'],
        'settings' => ['label' => '환경 설정', 'href' => '/admin/settings/'],
    ];

    $html = '<nav class="smartcms-admin-nav" aria-label="관리자 메뉴">';
    foreach ($items as $key => $item) {
        $class = $key === $active ? 'smartcms-admin-nav-link is-active' : 'smartcms-admin-nav-link';
        $html .= '<a class="' . smartcms_h($class) . '" href="' . smartcms_h(smartcms_base_url($item['href'])) . '">' . smartcms_h($item['label']) . '</a>';
    }
    $html .= '</nav>';

    return $html;
}

function smartcms_admin_header(array $admin, string $title, string $active): string
{
    return '<header class="smartcms-admin-header">
        <div>
          <p class="smartcms-eyebrow">Admin</p>
          <h1 class="smartcms-title">' . smartcms_h($title) . '</h1>
          <p class="smartcms-text-muted">' . smartcms_h($admin['name']) . '님이 로그인했습니다.</p>
        </div>
        <a class="btn btn-outline-secondary rounded-pill px-4" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">로그아웃</a>
      </header>' . smartcms_admin_nav($active);
}
