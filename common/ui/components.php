<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';
require_once dirname(__DIR__) . '/auth.php';

/* ─────────────────────────────────────────
   1. 알림 / 버튼
───────────────────────────────────────── */

/**
 * 알림 박스 HTML 반환
 * $type: info | success | error | warning
 */
function smartcms_alert(string $message, string $type = 'info'): string
{
    $classes = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
    ];
    $class = $classes[$type] ?? 'alert-info';

    return '<div class="alert ' . $class . ' d-flex align-items-start gap-2" role="alert">'
         . '<i class="bi bi-info-circle-fill mt-1"></i>'
         . '<div>' . smartcms_h($message) . '</div>'
         . '</div>';
}

/**
 * 기본 제출 버튼
 */
function smartcms_button(string $label, string $type = 'button', string $extra_class = ''): string
{
    $cls = trim('btn btn-primary px-4 ' . $extra_class);
    return '<button class="' . smartcms_h($cls) . '" type="' . smartcms_h($type) . '">'
         . smartcms_h($label)
         . '</button>';
}

/* ─────────────────────────────────────────
   2. 공통 UI 조각
───────────────────────────────────────── */

/**
 * 섹션 타이틀 + 우측 액션 조합 헤더
 */
function smartcms_section_head(string $title, string $action_html = ''): string
{
    return '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">'
         . '<h2 class="h5 mb-0 fw-semibold text-body">' . smartcms_h($title) . '</h2>'
         . ($action_html !== '' ? $action_html : '')
         . '</div>';
}

/**
 * 사이드바 카드
 */
function smartcms_sidebar_card(string $title, string $body_html, string $meta_html = '', string $extra_class = ''): string
{
    $cls = trim('card border-0 shadow-sm ' . $extra_class);
    $html = '<section class="' . smartcms_h($cls) . '">';
    $html .= '<div class="card-body p-4">';
    if ($title !== '') {
        $html .= '<p class="text-uppercase small fw-semibold text-primary mb-2">' . smartcms_h($title) . '</p>';
    }
    $html .= $body_html;
    if ($meta_html !== '') {
        $html .= '<div class="mt-3 pt-3 border-top text-body-secondary small">' . $meta_html . '</div>';
    }
    $html .= '</div></section>';
    return $html;
}

/* ─────────────────────────────────────────
   3. 사이트 레이아웃 헬퍼 (Header / Footer)
───────────────────────────────────────── */

if (!function_exists('smartcms_site_nav_items')) {
    function smartcms_site_nav_items(): array
    {
        return [
            'home' => ['label' => '홈', 'href' => '/', 'icon' => 'bi-house-fill'],
            'boards' => ['label' => '게시판', 'href' => '/board/', 'icon' => 'bi-grid-3x3-gap-fill'],
            'notice' => ['label' => '공지사항', 'href' => '/board/?board=notice', 'icon' => 'bi-megaphone-fill'],
            'free' => ['label' => '자유게시판', 'href' => '/board/?board=free', 'icon' => 'bi-chat-square-text-fill'],
            'qna' => ['label' => '질문과 답변', 'href' => '/board/?board=qna', 'icon' => 'bi-question-circle-fill'],
        ];
    }
}

if (!function_exists('smartcms_admin_nav_items')) {
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
}

if (!function_exists('smartcms_site_header')) {
function smartcms_site_header(string $active = '', string $extra_class = ''): string
{
    $items = smartcms_site_nav_items();
    $is_active = static fn(string $key): bool => $key === $active;
    $brandHref = smartcms_h(smartcms_base_url('/'));
    $user = smartcms_current_user();

    $mainClass = trim('min-vh-100 d-flex flex-column ' . $extra_class);
    $html  = '<main class="' . smartcms_h($mainClass) . '">';

    // 1. Top Utility Bar
    $html .= '<header class="bg-white border-bottom py-2 small text-body-secondary">'
           . '<div class="container-xxl d-flex align-items-center justify-content-between">'
           . '<div class="d-none d-md-flex gap-3">'
           . '<a href="' . $brandHref . '" class="text-decoration-none text-body-secondary">커뮤니티 홈</a>'
           . '</div>'
           . '<div class="d-flex gap-3 ms-auto">';

    if ($user) {
        $html .= '<span class="text-dark fw-bold"><i class="bi bi-person-circle me-1"></i>' . smartcms_h((string)$user['name']) . '</span>'
               . '<a href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '" class="text-decoration-none text-body-secondary">로그아웃</a>';
    } else {
        $html .= '<a href="' . smartcms_h(smartcms_base_url('/member/login/')) . '" class="text-decoration-none text-body-secondary">로그인</a>'
               . '<a href="' . smartcms_h(smartcms_base_url('/member/register/')) . '" class="text-decoration-none text-body-secondary">회원가입</a>';
    }
    $html .= '</div></div></header>';

    // 2. Main Brand & Search
    $html .= '<section class="bg-white py-4"><div class="container-xxl"><div class="row align-items-center g-4">'
           . '<div class="col-12 col-md-3 text-center text-md-start">'
           . '<a class="navbar-brand fs-2 fw-bold text-primary" href="' . $brandHref . '">smartcms<span class="text-dark">.</span></a>'
           . '</div>'
           . '<div class="col-12 col-md-6">'
           . '<form action="' . smartcms_h(smartcms_base_url('/board/')) . '" method="get" class="position-relative">'
           . '<div class="input-group input-group-lg">'
           . '<input type="text" name="q" class="form-control bg-body border-0 rounded-pill ps-4" placeholder="궁금한 것을 검색해보세요">'
           . '<button class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-primary z-3 me-2" type="submit"><i class="bi bi-search fs-5"></i></button>'
           . '</div></form></div>'
           . '<div class="col-md-3 d-none d-md-flex justify-content-end">'
           . '<a href="' . smartcms_h(smartcms_base_url('/board/write/')) . '" class="btn btn-primary rounded-pill px-4"><i class="bi bi-pencil-square me-2"></i>글쓰기</a>'
           . '</div></div></div></section>';

    // 3. Main Navigation Bar
    $html .= '<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top p-0"><div class="container-xxl">'
           . '<button class="navbar-toggler my-2 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav"><span class="navbar-toggler-icon"></span></button>'
           . '<div class="collapse navbar-collapse" id="siteNav"><ul class="navbar-nav w-100">';

    foreach ($items as $key => $item) {
        $activeClass = $is_active($key) ? ' active fw-bold text-primary border-bottom border-primary border-3' : '';
        $html .= '<li class="nav-item"><a class="nav-link py-3 px-4 text-center' . $activeClass . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">' . smartcms_h((string)$item['label']) . '</a></li>';
    }

    if ($user && (int)$user['level'] <= 2) {
        $html .= '<li class="nav-item ms-lg-auto"><a class="nav-link py-3 px-4 text-warning" href="' . smartcms_h(smartcms_base_url('/admin/')) . '"><i class="bi bi-speedometer2 me-1"></i>관리자</a></li>';
    }
    $html .= '</ul></div></div></nav>';

    return $html;
}
}

if (!function_exists('smartcms_site_footer')) {
function smartcms_site_footer(): string
{
    $year = date('Y');
    $html = '<footer class="bg-dark text-white-50 py-5 mt-auto"><div class="container-xxl"><div class="row g-4">'
          . '<div class="col-12 col-lg-4">'
          . '<a class="navbar-brand fs-3 fw-bold text-white d-block mb-3" href="' . smartcms_h(smartcms_base_url('/')) . '">smartcms<span class="text-primary">.</span></a>'
          . '<p class="small mb-4">Bootstrap 5 Native Community CMS<br>모던한 기술로 구축하는 커뮤니티의 새로운 기준</p>'
          . '<div class="d-flex gap-3 fs-5 text-white"><i class="bi bi-github"></i><i class="bi bi-discord"></i><i class="bi bi-youtube"></i></div>'
          . '</div>'
          . '<div class="col-6 col-lg-2 offset-lg-2"><h3 class="h6 fw-bold text-white mb-3">Service</h3>'
          . '<ul class="list-unstyled small d-grid gap-2">'
          . '<li><a href="' . smartcms_h(smartcms_base_url('/board/')) . '" class="text-decoration-none text-reset">전체 게시판</a></li>'
          . '<li><a href="' . smartcms_h(smartcms_base_url('/member/login/')) . '" class="text-decoration-none text-reset">로그인</a></li>'
          . '<li><a href="' . smartcms_h(smartcms_base_url('/member/register/')) . '" class="text-decoration-none text-reset">회원가입</a></li>'
          . '</ul></div>'
          . '<div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-white mb-3">Support</h3>'
          . '<ul class="list-unstyled small d-grid gap-2">'
          . '<li><a href="#" class="text-decoration-none text-reset text-primary">개인정보처리방침</a></li>'
          . '</ul></div>'
          . '<div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-white mb-3">Admin</h3>'
          . '<ul class="list-unstyled small d-grid gap-2">'
          . '<li><a href="' . smartcms_h(smartcms_base_url('/admin/')) . '" class="text-decoration-none text-reset">관리자 홈</a></li>'
          . '<li><a href="' . smartcms_h(smartcms_base_url('/admin/settings/')) . '" class="text-decoration-none text-reset">시스템 설정</a></li>'
          . '</ul></div></div>'
          . '<div class="border-top border-secondary border-opacity-25 pt-4 mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small">'
          . '<span>&copy; ' . smartcms_h($year) . ' smartcms. All rights reserved.</span>'
          . '<span class="text-white-50">Powered by Bootstrap 5 & PHP</span>'
          . '</div></div></footer></main>';
    return $html;
}
}
