<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';

/* ─────────────────────────────────────────
   1. 알림 / 버튼
───────────────────────────────────────── */

/**
 * 알림 박스 HTML 반환
 * $type: info | success | error | warning
 */
function smartcms_alert(string $message, string $type = 'info'): string
{
    $icons = [
        'info'    => 'bi-info-circle-fill',
        'success' => 'bi-check-circle-fill',
        'error'   => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
    ];
    $icon = $icons[$type] ?? 'bi-info-circle-fill';

    return '<div class="sc-alert sc-alert--' . smartcms_h($type) . '">'
         . '<i class="bi ' . $icon . '"></i>'
         . '<span>' . smartcms_h($message) . '</span>'
         . '</div>';
}

/**
 * 기본 제출 버튼
 */
function smartcms_button(string $label, string $type = 'button', string $extra_class = ''): string
{
    $cls = trim('btn btn-primary rounded-pill px-4 ' . $extra_class);
    return '<button class="' . smartcms_h($cls) . '" type="' . smartcms_h($type) . '">'
         . smartcms_h($label)
         . '</button>';
}

/* ─────────────────────────────────────────
   2. 관리자 레이아웃
───────────────────────────────────────── */

/**
 * 관리자 내비 항목 정의
 */
function smartcms_admin_nav_items(): array
{
    return [
        'dashboard' => ['label' => '대시보드',   'href' => '/admin/dashboard/', 'icon' => 'bi-speedometer2'],
        'users'     => ['label' => '회원 관리',   'href' => '/admin/users/',     'icon' => 'bi-people-fill'],
        'boards'    => ['label' => '게시판 관리', 'href' => '/admin/boards/',    'icon' => 'bi-layout-text-window'],
        'pages'     => ['label' => '페이지 권한', 'href' => '/admin/pages/',     'icon' => 'bi-shield-lock-fill'],
        'logs'      => ['label' => '접속 로그',   'href' => '/admin/logs/',      'icon' => 'bi-activity'],
        'database'  => ['label' => 'DB 관리',     'href' => '/admin/database/',  'icon' => 'bi-database-fill'],
        'settings'  => ['label' => '환경 설정',   'href' => '/admin/settings/',  'icon' => 'bi-gear-fill'],
    ];
}

/**
 * 관리자 사이드바 내비 HTML
 */
function smartcms_admin_nav(string $active = ''): string
{
    $items = smartcms_admin_nav_items();
    $html  = '<nav class="sc-admin-nav" aria-label="관리자 메뉴"><ul>';

    foreach ($items as $key => $item) {
        $cls   = 'sc-admin-nav-link' . ($key === $active ? ' is-active' : '');
        $href  = smartcms_h(smartcms_base_url($item['href']));
        $html .= '<li>'
               . '<a class="' . smartcms_h($cls) . '" href="' . $href . '">'
               . '<i class="bi ' . smartcms_h($item['icon']) . '"></i>'
               . '<span>' . smartcms_h($item['label']) . '</span>'
               . '</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * 관리자 페이지 전체 헤더 출력
 * 반환: <main> ~ <div class="sc-admin-content"> 까지
 */
function smartcms_admin_page_header(array $admin, string $title, string $active): string
{
    $initial = function_exists('mb_substr') ? mb_substr((string)$admin['name'], 0, 1) : substr((string)$admin['name'], 0, 1);

    $html  = '<main class="sc-admin-page">';
    $html .= '<div class="sc-admin-layout">';

    // ── 사이드바
    $html .= '<aside class="sc-admin-sidebar">';
    $html .= '<a class="sc-admin-brand" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '<span class="sc-admin-brand-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>';
    $html .= '<strong>smartcms</strong></a>';
    $html .= '<p class="sc-admin-menu-label">Admin Menu</p>';
    $html .= smartcms_admin_nav($active);
    $html .= '</aside>';

    // ── 워크스페이스
    $html .= '<section class="sc-admin-workspace">';

    // 상단 바
    $html .= '<header class="sc-admin-topbar">';
    $html .= '<div>';
    $html .= '<p class="sc-eyebrow mb-0">Admin Console</p>';
    $html .= '<h1 class="sc-admin-page-title">' . smartcms_h($title) . '</h1>';
    $html .= '</div>';
    $html .= '<div class="sc-admin-profile">';
    $html .= '<span class="sc-admin-avatar">' . smartcms_h($initial) . '</span>';
    $html .= '<div><strong>' . smartcms_h($admin['name']) . '</strong><small>level ' . smartcms_h($admin['level']) . '</small></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">';
    $html .= '<i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div></header>';

    // 컨텐츠 영역 시작
    $html .= '<div class="sc-admin-content">';

    return $html;
}

/**
 * 관리자 페이지 닫힘 (푸터 포함)
 */
function smartcms_admin_footer(): string
{
    return '</div>' // .sc-admin-content
         . '<footer class="sc-admin-footer">'
         . '<span>&copy; ' . smartcms_h(date('Y')) . ' smartcms admin</span>'
         . '<a href="' . smartcms_h(smartcms_base_url('/')) . '">사이트 홈</a>'
         . '</footer>'
         . '</section>' // .sc-admin-workspace
         . '</div>'     // .sc-admin-layout
         . '</main>';   // .sc-admin-page
}

/* ─────────────────────────────────────────
   3. 인증 페이지 래퍼
───────────────────────────────────────── */

/**
 * 로그인/회원가입 등 인증 페이지 시작
 */
function smartcms_auth_header(string $active = ''): string
{
    return '<div class="sc-auth-wrap">'
         . '<div class="sc-auth-box">';
}

/**
 * 인증 페이지 닫힘
 */
function smartcms_auth_footer(): string
{
    return '</div></div>';
}

/* ─────────────────────────────────────────
   4. 공통 UI 조각
───────────────────────────────────────── */

/**
 * 섹션 타이틀 + 우측 액션 조합 헤더
 */
function smartcms_section_head(string $title, string $action_html = ''): string
{
    $html = '<div class="sc-section-head">';
    $html .= '<h2 class="sc-section-title">' . smartcms_h($title) . '</h2>';
    if ($action_html !== '') {
        $html .= $action_html;
    }
    $html .= '</div>';
    return $html;
}
