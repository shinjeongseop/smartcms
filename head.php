<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/auth.php';

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

if (!function_exists('smartcms_site_header')) {
    function smartcms_site_header(string $active = '', string $extra_class = ''): string
    {
        $items = smartcms_site_nav_items();
        $is_active = static fn(string $key): bool => $key === $active;
        $brandHref = smartcms_h(smartcms_base_url('/'));
        $user = function_exists('smartcms_current_user') ? smartcms_current_user() : null;

        $mainClass = trim('min-vh-100 d-flex flex-column ' . $extra_class);
        $html  = '<main class="' . smartcms_h($mainClass) . '">';

        // 1. Top Utility Bar (Login/Register/Search)
        $html .= '<header class="bg-white border-bottom py-2 small text-body-secondary">';
        $html .= '<div class="container-xxl d-flex align-items-center justify-content-between">';
        $html .= '<div class="d-none d-md-flex gap-3">';
        $html .= '<a href="' . $brandHref . '" class="text-decoration-none text-body-secondary">커뮤니티 홈</a>';
        $html .= '<a href="' . smartcms_h(smartcms_base_url('/board/')) . '" class="text-decoration-none text-body-secondary">전체글</a>';
        $html .= '</div>';
        $html .= '<div class="d-flex gap-3 ms-auto">';
        if ($user) {
            $html .= '<span class="text-dark fw-bold"><i class="bi bi-person-circle me-1"></i>' . smartcms_h((string)$user['name']) . '</span>';
            $html .= '<a href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '" class="text-decoration-none text-body-secondary">로그아웃</a>';
        } else {
            $html .= '<a href="' . smartcms_h(smartcms_base_url('/member/login/')) . '" class="text-decoration-none text-body-secondary">로그인</a>';
            $html .= '<a href="' . smartcms_h(smartcms_base_url('/member/register/')) . '" class="text-decoration-none text-body-secondary">회원가입</a>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</header>';

        // 2. Main Brand & Search Section
        $html .= '<section class="bg-white py-4">';
        $html .= '<div class="container-xxl">';
        $html .= '<div class="row align-items-center g-4">';
        $html .= '<div class="col-12 col-md-3 text-center text-md-start">';
        $html .= '<a class="navbar-brand fs-2 fw-bold text-primary" href="' . $brandHref . '">';
        $html .= 'smartcms<span class="text-dark">.</span>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '<div class="col-12 col-md-6">';
        $html .= '<form action="' . smartcms_h(smartcms_base_url('/board/')) . '" method="get" class="position-relative">';
        $html .= '<div class="input-group">';
        $html .= '<input type="text" name="q" class="form-control bg-body border-0 rounded-pill ps-4" placeholder="궁금한 것을 검색해보세요" aria-label="Search">';
        $html .= '<button class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-primary z-3 me-2" type="submit"><i class="bi bi-search fs-5"></i></button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '<div class="col-md-3 d-none d-md-flex justify-content-end gap-2">';
        $html .= '<a href="' . smartcms_h(smartcms_base_url('/board/write/')) . '" class="btn btn-primary rounded-pill px-4"><i class="bi bi-pencil-square me-2"></i>글쓰기</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</section>';

        // 3. Main Navigation Bar
        $html .= '<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top p-0">';
        $html .= '<div class="container-xxl">';
        $html .= '<button class="navbar-toggler my-2 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav" aria-expanded="false" aria-label="Toggle navigation">';
        $html .= '<span class="navbar-toggler-icon"></span>';
        $html .= '</button>';
        $html .= '<div class="collapse navbar-collapse" id="siteNav">';
        $html .= '<ul class="navbar-nav w-100">';
        foreach ($items as $key => $item) {
            $activeClass = $is_active($key) ? ' active fw-bold text-primary border-bottom border-primary border-3' : '';
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link py-3 px-4 text-center' . $activeClass . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">';
            $html .= smartcms_h((string)$item['label']);
            $html .= '</a>';
            $html .= '</li>';
        }
        if ($user && (int)$user['level'] <= 2) {
            $html .= '<li class="nav-item ms-lg-auto">';
            $html .= '<a class="nav-link py-3 px-4 text-warning" href="' . smartcms_h(smartcms_base_url('/admin/')) . '">';
            $html .= '<i class="bi bi-speedometer2 me-1"></i>관리자';
            $html .= '</a>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</nav>';

        return $html;
    }
}

if (!function_exists('smartcms_site_footer')) {
    function smartcms_site_footer(): string
    {
        $year = date('Y');

        $html  = '<footer class="bg-dark text-white-50 py-5 mt-auto">';
        $html .= '<div class="container-xxl">';
        $html .= '<div class="row g-4">';
        $html .= '<div class="col-12 col-lg-4">';
        $html .= '<a class="navbar-brand fs-3 fw-bold text-white d-block mb-3" href="' . smartcms_h(smartcms_base_url('/')) . '">';
        $html .= 'smartcms<span class="text-primary">.</span>';
        $html .= '</a>';
        $html .= '<p class="small mb-4">Bootstrap 5 Native Community CMS<br>모던한 기술로 구축하는 커뮤니티의 새로운 기준</p>';
        $html .= '<div class="d-flex gap-3 fs-5 text-white">';
        $html .= '<i class="bi bi-github"></i>';
        $html .= '<i class="bi bi-discord"></i>';
        $html .= '<i class="bi bi-youtube"></i>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="col-6 col-lg-2 offset-lg-2">';
        $html .= '<h3 class="h6 fw-bold text-white mb-3">Service</h3>';
        $html .= '<ul class="list-unstyled small d-grid gap-2">';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/board/')) . '" class="text-decoration-none text-reset">전체 게시판</a></li>';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/member/login/')) . '" class="text-decoration-none text-reset">로그인</a></li>';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/member/register/')) . '" class="text-decoration-none text-reset">회원가입</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '<div class="col-6 col-lg-2">';
        $html .= '<h3 class="h6 fw-bold text-white mb-3">Support</h3>';
        $html .= '<ul class="list-unstyled small d-grid gap-2">';
        $html .= '<li><a href="#" class="text-decoration-none text-reset">이용약관</a></li>';
        $html .= '<li><a href="#" class="text-decoration-none text-reset text-primary">개인정보처리방침</a></li>';
        $html .= '<li><a href="#" class="text-decoration-none text-reset">운영정책</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '<div class="col-6 col-lg-2">';
        $html .= '<h3 class="h6 fw-bold text-white mb-3">Admin</h3>';
        $html .= '<ul class="list-unstyled small d-grid gap-2">';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/admin/')) . '" class="text-decoration-none text-reset">관리자 홈</a></li>';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/admin/settings/')) . '" class="text-decoration-none text-reset">시스템 설정</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="border-top border-secondary border-opacity-25 pt-4 mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small">';
        $html .= '<span>&copy; ' . smartcms_h($year) . ' smartcms. All rights reserved.</span>';
        $html .= '<span class="text-white-50">Powered by Bootstrap 5 & PHP</span>';
        $html .= '</div>';
        $html .= '</div></footer></main>';

        return $html;
    }
}

if (isset($SMARTCMS_HEAD) && is_array($SMARTCMS_HEAD)) {
    $title = (string)($SMARTCMS_HEAD['title'] ?? 'smartcms');
    $body_class = trim((string)($SMARTCMS_HEAD['body_class'] ?? ''));
    $css_url = (string)smartcms_config_value('theme.css_url', '/common/css/common.css');
    $stylesheets = (array)($SMARTCMS_HEAD['stylesheets'] ?? []);
    $request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');

    if (str_starts_with($request_path, '/admin/')) {
        $admin_class = str_starts_with($request_path, '/admin/login/') ? 'smartcms-admin-auth' : 'smartcms-admin-page';
        $body_classes = preg_split('/\s+/', $body_class, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (!in_array($admin_class, $body_classes, true)) {
            $body_classes[] = $admin_class;
        }

        $body_class = trim(implode(' ', $body_classes));

        if (!in_array('/admin/css/admin.css', $stylesheets, true)) {
            $stylesheets[] = '/admin/css/admin.css';
        }
    }

    ?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#03c75a">
  <title><?= smartcms_h($title) ?> · smartcms</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard-dynamic-subset.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap">
  <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url($css_url)) ?>">
  <?php foreach ($stylesheets as $stylesheet): ?>
    <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url((string)$stylesheet)) ?>">
  <?php endforeach; ?>
</head>
<body class="<?= smartcms_h($body_class) ?>">
    <?php
}
