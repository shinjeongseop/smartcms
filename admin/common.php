<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/auth.php';

function smartcms_admin_user(): array
{
    return smartcms_require_level(smartcms_setting_int('admin_level', (int)smartcms_config_value('admin_level', 8)), (string)smartcms_config_value('admin_login_url', '/admin/login/'));
}

function smartcms_admin_initial(string $name): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($name, 0, 1);
    }

    return substr($name, 0, 1);
}

function smartcms_admin_nav(string $active = ''): string
{
    $items = [
        'dashboard' => ['label' => '대시보드', 'href' => '/admin/dashboard/', 'icon' => 'bi-speedometer2'],
        'users' => ['label' => '회원 관리', 'href' => '/admin/users/', 'icon' => 'bi-people'],
        'boards' => ['label' => '게시판 관리', 'href' => '/admin/boards/', 'icon' => 'bi-layout-text-window'],
        'pages' => ['label' => '페이지 권한', 'href' => '/admin/pages/', 'icon' => 'bi-shield-lock'],
        'logs' => ['label' => '접속 로그', 'href' => '/admin/logs/', 'icon' => 'bi-activity'],
        'database' => ['label' => 'DB 관리', 'href' => '/admin/database/', 'icon' => 'bi-database'],
        'settings' => ['label' => '환경 설정', 'href' => '/admin/settings/', 'icon' => 'bi-gear'],
    ];

    $html = '<nav class="smartcms-admin-nav" aria-label="관리자 메뉴"><ul>';
    foreach ($items as $key => $item) {
        $class = $key === $active ? 'smartcms-admin-nav-link is-active' : 'smartcms-admin-nav-link';
        $html .= '<li><a class="' . smartcms_h($class) . '" href="' . smartcms_h(smartcms_base_url($item['href'])) . '">';
        $html .= '<i class="bi ' . smartcms_h($item['icon']) . '"></i><span>' . smartcms_h($item['label']) . '</span></a></li>';
    }
    $html .= '</ul></nav>';

    return $html;
}

function smartcms_admin_header(array $admin, string $title, string $active): string
{
    return '<aside class="smartcms-admin-sidebar">
        <a class="smartcms-admin-brand" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">
          <span><i class="bi bi-grid-3x3-gap-fill"></i></span>
          <strong>smartcms</strong>
        </a>
        <p class="smartcms-admin-menu-label">Admin Menu</p>
        ' . smartcms_admin_nav($active) . '
      </aside>
      <section class="smartcms-admin-workspace">
        <header class="smartcms-admin-topbar">
          <div>
            <p class="smartcms-eyebrow">Admin Console</p>
            <h1 class="smartcms-admin-page-title">' . smartcms_h($title) . '</h1>
          </div>
          <div class="smartcms-admin-profile">
            <span class="smartcms-admin-avatar">' . smartcms_h(smartcms_admin_initial((string)$admin['name'])) . '</span>
            <div>
              <strong>' . smartcms_h($admin['name']) . '</strong>
              <small>level ' . smartcms_h($admin['level']) . '</small>
            </div>
            <a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">
              <i class="bi bi-box-arrow-right me-1"></i>로그아웃
            </a>
          </div>
        </header>
        <div class="smartcms-admin-content">';
}

function smartcms_admin_page_header(array $admin, string $title, string $active): string
{
    return '<main class="smartcms-admin-shell"><div class="smartcms-admin-layout">' . PHP_EOL . smartcms_admin_header($admin, $title, $active);
}

function smartcms_admin_footer(): string
{
    return '</div>
        <footer class="smartcms-admin-footer">
          <span>&copy; ' . smartcms_h(date('Y')) . ' smartcms admin</span>
          <a href="' . smartcms_h(smartcms_base_url('/')) . '">사이트 홈</a>
        </footer>
      </section>
    </div>';
}
