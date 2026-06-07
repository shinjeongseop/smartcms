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

    $html = '<nav class="list-group list-group-flush rounded-3 overflow-hidden" aria-label="관리자 메뉴">';
    foreach ($items as $key => $item) {
        $cls  = 'list-group-item list-group-item-action d-flex align-items-center gap-2 border-0' . ($key === $active ? ' active' : '');
        $href = smartcms_h(smartcms_base_url($item['href']));
        $html .= '<a class="' . smartcms_h($cls) . '" href="' . $href . '">'
               . '<i class="bi ' . smartcms_h($item['icon']) . '"></i>'
               . '<span>' . smartcms_h($item['label']) . '</span>'
               . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

/**
 * 관리자 모바일 상단바
 */
function smartcms_admin_mobile_topbar(array $admin, string $title): string
{
    $initial = smartcms_admin_initial((string)$admin['name']);

    return '<div class="d-lg-none sticky-top bg-body border-bottom px-3 py-2">'
         . '<div class="d-flex align-items-center justify-content-between gap-2">'
         . '<a class="d-inline-flex align-items-center gap-2 text-decoration-none text-body" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">'
         . '<span class="badge text-bg-primary rounded-3 p-2"><i class="bi bi-grid-3x3-gap-fill"></i></span>'
         . '<strong>smartcms</strong>'
         . '</a>'
         . '<button class="btn btn-outline-secondary btn-sm rounded-pill" type="button" data-bs-toggle="offcanvas" data-bs-target="#smartcmsAdminNav" aria-controls="smartcmsAdminNav" aria-label="관리자 메뉴 열기">'
         . '<i class="bi bi-list me-1"></i>메뉴'
         . '</button>'
         . '</div>'
         . '<div class="d-flex align-items-center justify-content-between gap-2 mt-2">'
         . '<div><p class="text-uppercase text-muted small fw-semibold mb-1">Admin Console</p><h1 class="h5 mb-0 text-body">' . smartcms_h($title) . '</h1></div>'
         . '<span class="badge text-bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-3 lh-1">' . smartcms_h($initial) . '</span>'
         . '</div>'
         . '</div>';
}

/**
 * 관리자 전체 페이지 헤더 (main ~ sc-admin-content 열기)
 */
function smartcms_admin_page_header(array $admin, string $title, string $active): string
{
    $initial = smartcms_admin_initial((string)$admin['name']);

    $html  = '<main class="container-xxl min-vh-100 bg-body">';
    $html .= smartcms_admin_mobile_topbar($admin, $title);
    $html .= '<div class="row g-0 min-vh-100">';

    $html .= '<aside class="d-none d-lg-flex col-lg-3 col-xxl-2 border-end bg-white p-3 p-lg-4 flex-column">';
    $html .= '<a class="d-flex align-items-center gap-2 text-decoration-none text-body mb-4" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '<span class="badge text-bg-primary rounded-3 p-2"><i class="bi bi-grid-3x3-gap-fill"></i></span>';
    $html .= '<strong>smartcms</strong></a>';
    $html .= '<p class="text-uppercase small fw-semibold text-muted mb-3">Admin Menu</p>';
    $html .= smartcms_admin_nav($active);
    $html .= '<div class="mt-auto pt-4">';
    $html .= '<div class="d-flex align-items-center gap-2 rounded-3 border p-3">';
    $html .= '<span class="badge text-bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-3 lh-1">' . smartcms_h($initial) . '</span>';
    $html .= '<div class="min-w-0">';
    $html .= '<strong class="d-block text-body text-truncate">' . smartcms_h($admin['name']) . '</strong>';
    $html .= '<small class="text-muted d-block">level ' . smartcms_h($admin['level']) . '</small>';
    $html .= '</div></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill w-100 mt-3" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">';
    $html .= '<i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div>';
    $html .= '</aside>';

    $html .= '<div class="offcanvas offcanvas-start" tabindex="-1" id="smartcmsAdminNav" aria-labelledby="smartcmsAdminNavLabel">';
    $html .= '<div class="offcanvas-header border-bottom">';
    $html .= '<h2 class="offcanvas-title h5 mb-0" id="smartcmsAdminNavLabel">관리자 메뉴</h2>';
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="닫기"></button>';
    $html .= '</div>';
    $html .= '<div class="offcanvas-body p-0">';
    $html .= '<div class="p-3">';
    $html .= '<a class="d-flex align-items-center gap-2 text-decoration-none text-body mb-4" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '<span class="badge text-bg-primary rounded-3 p-2"><i class="bi bi-grid-3x3-gap-fill"></i></span>';
    $html .= '<strong>smartcms</strong></a>';
    $html .= smartcms_admin_nav($active);
    $html .= '<div class="mt-4 rounded-3 border p-3">';
    $html .= '<div class="d-flex align-items-center gap-2">';
    $html .= '<span class="badge text-bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-3 lh-1">' . smartcms_h($initial) . '</span>';
    $html .= '<div>';
    $html .= '<strong class="d-block text-body">' . smartcms_h($admin['name']) . '</strong>';
    $html .= '<small class="text-muted d-block">level ' . smartcms_h($admin['level']) . '</small>';
    $html .= '</div></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill w-100 mt-3" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">';
    $html .= '<i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div></div></div></div>';

    $html .= '<section class="col-12 col-lg-9 col-xxl-10 p-3 p-lg-4">';
    $html .= '<header class="card border-0 shadow-sm mb-4 d-none d-lg-block">';
    $html .= '<div class="card-body d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">';
    $html .= '<div><p class="text-uppercase text-muted small fw-semibold mb-1">Admin Console</p><h1 class="h3 mb-0 text-body">' . smartcms_h($title) . '</h1></div>';
    $html .= '<div class="d-flex align-items-center gap-3">';
    $html .= '<span class="badge text-bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-3 lh-1">' . smartcms_h($initial) . '</span>';
    $html .= '<div><strong class="d-block text-body">' . smartcms_h($admin['name']) . '</strong><small class="text-muted d-block">level ' . smartcms_h($admin['level']) . '</small></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">';
    $html .= '<i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div></div></header>';
    $html .= '<div class="d-grid gap-4">';

    return $html;
}

/**
 * 관리자 페이지 닫힘 (푸터 포함)
 */
function smartcms_admin_footer(): string
{
    return '</div>'
         . '<footer class="d-flex justify-content-between gap-2 mt-4 pt-3 border-top small text-muted">'
         . '<span>&copy; ' . smartcms_h(date('Y')) . ' smartcms admin</span>'
         . '<a href="' . smartcms_h(smartcms_base_url('/')) . '" class="text-decoration-none">사이트 홈</a>'
         . '</footer>'
         . '</section>'
         . '</div>'
         . '</main>';
}
