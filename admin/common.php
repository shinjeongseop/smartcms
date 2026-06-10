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

/**
 * 관리자 메뉴 항목 정의
 */
function smartcms_admin_nav_items(): array
{
    return [
        'dashboard' => ['label' => '대시보드', 'href' => '/admin/dashboard/', 'icon' => 'bi-speedometer2'],
        'users'     => ['label' => '회원 관리', 'href' => '/admin/users/', 'icon' => 'bi-people-fill'],
        'boards'    => ['label' => '게시판 관리', 'href' => '/admin/boards/', 'icon' => 'bi-layout-text-window'],
        'pages'     => ['label' => '페이지 권한', 'href' => '/admin/pages/', 'icon' => 'bi-shield-lock-fill'],
        'logs'      => ['label' => '접속 로그', 'href' => '/admin/logs/', 'icon' => 'bi-activity'],
        'database'  => ['label' => 'DB 관리', 'href' => '/admin/database/', 'icon' => 'bi-database-fill'],
        'settings'  => ['label' => '환경 설정', 'href' => '/admin/settings/', 'icon' => 'bi-gear-fill'],
    ];
}

/**
 * 관리자 페이지 헤더 (Sneat-inspired Layout)
 */
function smartcms_admin_page_header(array $admin, string $title, string $active): string
{
    $initial = smartcms_admin_initial((string)$admin['name']);
    $items = smartcms_admin_nav_items();

    $html = '<div class="d-flex min-vh-100">';

    // 1. Sidebar (Fixed on Desktop)
    $html .= '<aside class="d-none d-lg-flex flex-column bg-white border-end sticky-top" style="height: 100vh; width: 260px; overflow-y: auto; z-index: 1030;">';
    $html .= '  <div class="px-4 py-4 mb-2">';
    $html .= '    <a class="d-flex align-items-center gap-2 text-decoration-none fw-bold text-dark fs-4" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '      <span class="badge bg-primary p-2 lh-1 rounded-3"><i class="bi bi-app-indicator fs-4 text-white"></i></span>';
    $html .= '      <span>smartcms</span>';
    $html .= '    </a>';
    $html .= '  </div>';

    $html .= '  <nav class="nav nav-pills flex-column px-3 gap-1 mb-auto">';
    $html .= '    <p class="text-uppercase small fw-bold text-secondary opacity-50 px-3 mb-2" style="font-size: 0.65rem; letter-spacing: 0.05rem;">Main Menu</p>';
    foreach ($items as $key => $item) {
        $isActive = $key === $active;
        $activeClass = $isActive ? ' active shadow-sm fw-bold bg-primary text-white' : ' text-secondary';
        $iconColor = $isActive ? 'text-white' : 'text-primary';

        $html .= '<a class="nav-link d-flex align-items-center gap-3 py-2 px-3 rounded-2' . $activeClass . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">';
        $html .= '  <i class="bi ' . smartcms_h((string)$item['icon']) . ' fs-5 ' . $iconColor . '"></i>';
        $html .= '  <span class="fw-medium">' . smartcms_h((string)$item['label']) . '</span>';
        $html .= '</a>';
    }
    $html .= '  </nav>';

    $html .= '  <div class="p-3 mt-auto border-top">';
    $html .= '    <section class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">';
    $html .= '      <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px;">' . smartcms_h($initial) . '</div>';
    $html .= '      <div class="overflow-hidden">';
    $html .= '        <div class="fw-bold text-dark text-truncate small">' . smartcms_h((string)$admin['name']) . '</div>';
    $html .= '        <div class="text-secondary" style="font-size: 0.7rem;">LV ' . smartcms_h((string)$admin['level']) . ' · Admin</div>';
    $html .= '      </div>';
    $html .= '    </section>';
    $html .= '    <a href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '" class="btn btn-light text-danger btn-sm w-100 mt-3 border-0 text-start px-3"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>';
    $html .= '  </div>';
    $html .= '</aside>';

    // 2. Main Workspace
    $html .= '<main class="flex-grow-1 d-flex flex-column bg-light" style="min-width: 0;">';

    // Topbar
    $html .= '  <header class="sticky-top bg-light px-3 px-lg-4 py-3" style="z-index: 1020;">';
    $html .= '    <div class="container-fluid p-0">';
    $html .= '      <nav class="navbar navbar-expand bg-white shadow-sm rounded-3 px-3 py-2">';
    $html .= '        <div class="container-fluid p-0">';
    // Mobile Toggler (Placeholder)
    $html .= '          <button class="navbar-toggler d-lg-none border-0 p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#adminMobileNav">';
    $html .= '            <i class="bi bi-list fs-2"></i>';
    $html .= '          </button>';

    $html .= '          <nav aria-label="breadcrumb">';
    $html .= '            <ol class="breadcrumb mb-0">';
    $html .= '              <li class="breadcrumb-item small"><a href="/admin/dashboard/" class="text-decoration-none text-secondary">Admin</a></li>';
    $html .= '              <li class="breadcrumb-item active small fw-bold" aria-current="page">' . smartcms_h($title) . '</li>';
    $html .= '          </nav>';

    $html .= '          <div class="ms-auto d-flex align-items-center gap-3">';
    $html .= '            <div class="d-none d-md-block text-end">';
    $html .= '              <div class="fw-bold small">' . smartcms_h((string)$admin['name']) . '</div>';
    $html .= '              <div class="text-secondary" style="font-size: 0.7rem;">Administrator</div>';
    $html .= '            </div>';
    $html .= '            <div class="dropdown">';
    $html .= '              <a href="#" class="d-block link-dark text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">';
    $html .= '                <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px;">' . smartcms_h($initial) . '</div>';
    $html .= '              </a>';
    $html .= '              <ul class="dropdown-menu dropdown-menu-end shadow border-0">';
    $html .= '                <li><a class="dropdown-item py-2" href="' . smartcms_h(smartcms_base_url('/member/mypage/')) . '"><i class="bi bi-person me-2"></i>Profile</a></li>';
    $html .= '                <li><a class="dropdown-item py-2" href="' . smartcms_h(smartcms_base_url('/admin/settings/')) . '"><i class="bi bi-gear me-2"></i>Settings</a></li>';
    $html .= '                <li><hr class="dropdown-divider"></li>';
    $html .= '                <li><a class="dropdown-item py-2 text-danger" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>';
    $html .= '              </ul>';
    $html .= '            </div>';
    $html .= '          </div>';
    $html .= '        </div>';
    $html .= '      </nav>';
    $html .= '    </div>';
    $html .= '  </header>';

    // Content Area
    $html .= '  <article class="flex-grow-1 p-3 p-lg-4 pt-0">';
    $html .= '    <div class="container-fluid p-0">';

    // Page Title for content area
    $html .= '    <header class="mb-4">';
    $html .= '      <h1 class="h3 fw-bold mb-1">' . smartcms_h($title) . '</h1>';
    $html .= '      <p class="text-secondary small mb-0">smartcms 관리자 대시보드 및 시스템 관리</p>';
    $html .= '    </header>';

    return $html;
}

/**
 * 관리자 페이지 푸터
 */
function smartcms_admin_footer(): string
{
    $html  = '    </div>'; // container-fluid
    $html .= '  </article>'; // sc-admin-content

    $html .= '  <footer class="mt-auto bg-white border-top py-4">';
    $html .= '    <div class="container-fluid px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-secondary">';
    $html .= '      <span>&copy; ' . date('Y') . ' <a href="#" class="text-decoration-none fw-bold">smartcms</a>. All rights reserved.</span>';
    $html .= '      <div class="d-flex gap-3">';
    $html .= '        <a href="' . smartcms_h(smartcms_base_url('/')) . '" class="text-decoration-none text-secondary">사이트 홈</a>';
    $html .= '        <a href="#" class="text-decoration-none text-secondary">문서</a>';
    $html .= '        <a href="#" class="text-decoration-none text-secondary text-primary fw-bold">smartcms 2.0</a>';
    $html .= '      </div>';
    $html .= '    </div>';
    $html .= '  </footer>';

    $html .= '</main>'; // sc-admin-workspace
    $html .= '</div>'; // sc-admin-layout

    return $html;
}
