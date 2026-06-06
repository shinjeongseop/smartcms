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

/* ──────────────────────────────────────
   아래 함수들은 components.php 에서 이전되어
   admin/common.php 에서도 호출 가능하도록 유지
──────────────────────────────────────── */

/**
 * 관리자 사이드바 내비 HTML
 */
function smartcms_admin_nav(string $active = ''): string
{
    $items = [
        'dashboard' => ['label' => '대시보드',   'href' => '/admin/dashboard/', 'icon' => 'bi-speedometer2'],
        'users'     => ['label' => '회원 관리',   'href' => '/admin/users/',     'icon' => 'bi-people-fill'],
        'boards'    => ['label' => '게시판 관리', 'href' => '/admin/boards/',    'icon' => 'bi-layout-text-window'],
        'pages'     => ['label' => '페이지 권한', 'href' => '/admin/pages/',     'icon' => 'bi-shield-lock-fill'],
        'logs'      => ['label' => '접속 로그',   'href' => '/admin/logs/',      'icon' => 'bi-activity'],
        'database'  => ['label' => 'DB 관리',     'href' => '/admin/database/',  'icon' => 'bi-database-fill'],
        'settings'  => ['label' => '환경 설정',   'href' => '/admin/settings/',  'icon' => 'bi-gear-fill'],
    ];

    $html = '<nav class="sc-admin-nav" aria-label="관리자 메뉴"><ul>';
    foreach ($items as $key => $item) {
        $cls  = 'sc-admin-nav-link' . ($key === $active ? ' is-active' : '');
        $href = smartcms_h(smartcms_base_url($item['href']));
        $html .= '<li><a class="' . smartcms_h($cls) . '" href="' . $href . '">'
               . '<i class="bi ' . smartcms_h($item['icon']) . '"></i>'
               . '<span>' . smartcms_h($item['label']) . '</span>'
               . '</a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}

/**
 * 관리자 전체 페이지 헤더 (main ~ sc-admin-content 열기)
 */
function smartcms_admin_page_header(array $admin, string $title, string $active): string
{
    $initial = smartcms_admin_initial((string)$admin['name']);

    $html  = '<main class="sc-admin-page">';
    $html .= '<div class="sc-admin-layout">';

    // 사이드바
    $html .= '<aside class="sc-admin-sidebar">';
    $html .= '<a class="sc-admin-brand" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '<span class="sc-admin-brand-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>';
    $html .= '<strong>smartcms</strong></a>';
    $html .= '<p class="sc-admin-menu-label">Admin Menu</p>';
    $html .= smartcms_admin_nav($active);
    $html .= '</aside>';

    // 워크스페이스
    $html .= '<section class="sc-admin-workspace">';
    $html .= '<header class="sc-admin-topbar">';
    $html .= '<div><p class="sc-eyebrow mb-0">Admin Console</p><h1 class="sc-admin-page-title">' . smartcms_h($title) . '</h1></div>';
    $html .= '<div class="sc-admin-profile">';
    $html .= '<span class="sc-admin-avatar">' . smartcms_h($initial) . '</span>';
    $html .= '<div><strong>' . smartcms_h($admin['name']) . '</strong><small>level ' . smartcms_h($admin['level']) . '</small></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">';
    $html .= '<i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div></header>';
    $html .= '<div class="sc-admin-content">';

    return $html;
}

/**
 * 관리자 페이지 닫힘 (푸터 포함)
 */
function smartcms_admin_footer(): string
{
    return '</div>'   // .sc-admin-content
         . '<footer class="sc-admin-footer">'
         . '<span>&copy; ' . smartcms_h(date('Y')) . ' smartcms admin</span>'
         . '<a href="' . smartcms_h(smartcms_base_url('/')) . '">사이트 홈</a>'
         . '</footer>'
         . '</section>'  // .sc-admin-workspace
         . '</div>'      // .sc-admin-layout
         . '</main>';    // .sc-admin-page
}
