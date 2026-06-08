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
        $user = function_exists('smartcms_current_user') ? smartcms_current_user() : null;

        $mainClass = trim('min-vh-100 d-flex flex-column ' . $extra_class);
        $html  = '<main class="' . smartcms_h($mainClass) . '">';
        if ($active === 'home') {
            $recent_notice = function_exists('smartcms_board_recent_posts_by_key')
                ? smartcms_board_recent_posts_by_key('notice', 1)
                : [];
            $notice_link = !empty($recent_notice)
                ? smartcms_board_post_url((string)$recent_notice[0]['board_key'], (int)$recent_notice[0]['id'])
                : smartcms_base_url('/board/?board=notice');
            $board_count = function_exists('smartcms_board_list') ? count(smartcms_board_list()) : 0;
            $post_count = function_exists('smartcms_board_post_counts') ? array_sum(smartcms_board_post_counts()) : 0;

            $html .= '<header class="bg-white border-bottom py-2 small text-body-secondary">';
            $html .= '<div class="container-fluid container-xxl d-flex flex-wrap align-items-center gap-2">';
            $html .= '<div class="d-none d-sm-flex align-items-center gap-3">';
            $html .= '<span class="text-primary fw-semibold"><i class="bi bi-megaphone-fill me-1"></i>공지</span>';
            $html .= '<a class="text-decoration-none text-body-secondary" href="' . smartcms_h($notice_link) . '">';
            $html .= !empty($recent_notice) ? smartcms_h((string)$recent_notice[0]['title']) : '새로운 반응형 커뮤니티 레이아웃 배포 안내';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '<div class="d-flex gap-3 ms-auto flex-wrap justify-content-end" id="topUtilLinks">';
            if ($user) {
                $html .= '<span class="text-body fw-semibold"><i class="bi bi-person-circle me-1"></i>' . smartcms_h((string)$user['name']) . '님</span>';
                $html .= '<a href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '" class="text-decoration-none text-body-secondary"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
            } else {
                $html .= '<a href="' . smartcms_h(smartcms_base_url('/member/login/')) . '" class="text-decoration-none text-body-secondary"><i class="bi bi-box-arrow-in-right me-1"></i>로그인</a>';
                $html .= '<a href="' . smartcms_h(smartcms_base_url('/member/register/')) . '" class="text-decoration-none text-body-secondary"><i class="bi bi-person-plus me-1"></i>회원가입</a>';
            }
            $html .= '<a href="' . smartcms_h(smartcms_base_url('/board/?board=notice')) . '" class="text-decoration-none text-body-secondary"><i class="bi bi-question-circle me-1"></i>고객센터</a>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</header>';

            $html .= '<section class="bg-white border-bottom py-3">';
            $html .= '<div class="container-fluid container-xxl">';
            $html .= '<div class="row align-items-center g-3">';
            $html .= '<div class="col-12 col-md-4 text-center text-md-start">';
            $html .= '<a class="text-decoration-none d-inline-flex align-items-center gap-2" href="' . $brandHref . '">';
            $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1"><i class="bi bi-n-square-fill"></i></span>';
            $html .= '<span class="fs-3 fw-bold text-dark">smartcms <span class="fs-5 text-body-secondary fw-light">Community</span></span>';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '<div class="col-12 col-md-5">';
            $html .= '<form class="input-group" action="' . smartcms_h(smartcms_base_url('/board/')) . '" method="get">';
            $html .= '<input type="text" class="form-control" name="q" placeholder="게시글, 태그, 회원 검색..." aria-label="검색어 입력">';
            $html .= '<button class="btn btn-primary px-4" type="submit"><i class="bi bi-search"></i></button>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '<div class="col-12 col-md-3 d-none d-md-flex justify-content-end align-items-center gap-2">';
            $html .= '<span class="badge text-bg-light border p-2"><i class="bi bi-layout-text-window text-primary me-1"></i> 게시판 ' . number_format($board_count) . '개</span>';
            $html .= '<span class="badge text-bg-light border p-2"><i class="bi bi-people-fill text-info me-1"></i> 글 ' . number_format($post_count) . '개</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</section>';

            $html .= '<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top p-0 shadow-sm">';
            $html .= '<div class="container-fluid container-xxl">';
            $html .= '<button class="navbar-toggler my-2 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#smartcmsSiteNav" aria-controls="smartcmsSiteNav" aria-expanded="false" aria-label="메뉴 열기">';
            $html .= '<span class="navbar-toggler-icon"></span>';
            $html .= '</button>';
            $html .= '<div class="collapse navbar-collapse" id="smartcmsSiteNav">';
            $html .= '<ul class="navbar-nav w-100 nav-fill">';
            foreach ($items as $key => $item) {
                $html .= '<li class="nav-item' . ($key !== array_key_last($items) ? ' border-end border-secondary border-opacity-25' : '') . '">'
                      . '<a class="nav-link py-3 fw-semibold' . ($is_active($key) ? ' active text-white' : '') . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
                      . (in_array($key, ['home', 'boards', 'notice', 'free', 'qna'], true) ? '<i class="bi ' . smartcms_h((string)$item['icon']) . ' me-1"></i>' : '')
                      . smartcms_h((string)$item['label'])
                      . '</a>'
                      . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</nav>';
        } else {
            $html .= '<header class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">';
            $html .= '<div class="container-xxl py-3">';
            $html .= '<a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none" href="' . $brandHref . '">';
            $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1"><i class="bi bi-n-square-fill"></i></span>';
            $html .= '<span>smartcms</span>';
            $html .= '</a>';
            $html .= '<button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#smartcmsSiteNav" aria-controls="smartcmsSiteNav" aria-expanded="false" aria-label="사이트 메뉴 열기">';
            $html .= '<span class="navbar-toggler-icon"></span>';
            $html .= '</button>';
            $html .= '<div class="collapse navbar-collapse" id="smartcmsSiteNav">';
            $html .= '<ul class="navbar-nav ms-auto me-lg-3 gap-2 mt-3 mt-lg-0 nav nav-pills">';
            foreach ($items as $key => $item) {
                $html .= '<li class="nav-item">'
                      . '<a class="nav-link rounded-pill px-3' . ($is_active($key) ? ' active' : '') . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
                      . '<i class="bi ' . smartcms_h((string)$item['icon']) . ' me-1"></i>'
                      . smartcms_h((string)$item['label'])
                      . '</a>'
                      . '</li>';
            }
            $html .= '</ul>';
            $html .= '<div class="d-grid gap-2 d-lg-flex">';
            $html .= '<a class="btn btn-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/login/')) . '"><i class="bi bi-box-arrow-in-right me-1"></i>로그인</a>';
            $html .= '<a class="btn btn-primary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/admin/')) . '"><i class="bi bi-speedometer2 me-1"></i>관리자</a>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</header>';
        }

        return $html;
    }
}

if (!function_exists('smartcms_admin_page_header')) {
    function smartcms_admin_page_header(array $admin, string $title, string $active): string
    {
        $initial = smartcms_admin_initial((string)$admin['name']);
        $items = smartcms_admin_nav_items();

        $html  = '<main class="min-vh-100 d-flex flex-column bg-body-tertiary">';
        $html .= '<header class="navbar navbar-expand-md navbar-light bg-white border-bottom sticky-top">';
        $html .= '<div class="container-fluid px-3 px-lg-4">';
        $html .= '<a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
        $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1"><i class="bi bi-n-square-fill"></i></span>';
        $html .= '<span>smartcms</span>';
        $html .= '</a>';
        $html .= '<button class="navbar-toggler border-0 shadow-none d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#smartcmsAdminNav" aria-controls="smartcmsAdminNav" aria-expanded="false" aria-label="관리자 메뉴 열기">';
        $html .= '<span class="navbar-toggler-icon"></span>';
        $html .= '</button>';
        $html .= '<div class="collapse navbar-collapse d-md-none mt-3 mt-md-0" id="smartcmsAdminNav">';
        $html .= '<div class="container-fluid px-0 py-3 border-top bg-white">';
        $html .= '<div class="d-grid gap-3">';
        $html .= '<div class="d-flex align-items-center justify-content-between gap-3">';
        $html .= '<div><p class="text-uppercase text-success small fw-semibold mb-1 mb-0">Admin Console</p><h1 class="h4 mb-0 text-body">' . smartcms_h($title) . '</h1></div>';
        $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1">' . smartcms_h($initial) . '</span>';
        $html .= '</div>';
        $html .= '<nav class="nav nav-pills flex-column gap-2" aria-label="관리자 메뉴">';
        foreach ($items as $key => $item) {
            $html .= '<a class="nav-link d-flex align-items-center gap-2' . ($key === $active ? ' active' : '') . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
                  . '<i class="bi ' . smartcms_h((string)$item['icon']) . '"></i>'
                  . '<span>' . smartcms_h((string)$item['label']) . '</span>'
                  . '</a>';
        }
        $html .= '</nav>';
        $html .= '<div class="card">';
        $html .= '<div class="card-body d-flex align-items-center gap-2">';
        $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1">' . smartcms_h($initial) . '</span>';
        $html .= '<div class="min-w-0 flex-grow-1">';
        $html .= '<strong class="d-block text-body text-truncate">' . smartcms_h((string)$admin['name']) . '</strong>';
        $html .= '<small class="text-body-secondary d-block">level ' . smartcms_h((string)$admin['level']) . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="d-grid">';
        $html .= '<a class="btn btn-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</header>';

        $html .= '<div class="container-fluid flex-grow-1 px-0">';
        $html .= '<div class="row g-0 min-vh-100 align-items-stretch">';
        $html .= '<aside class="col-12 col-md-3 col-xxl-2 d-none d-md-flex flex-column bg-white border-end p-4">';
        $html .= '<a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none mb-4" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
        $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1"><i class="bi bi-n-square-fill"></i></span>';
        $html .= '<span>smartcms</span>';
        $html .= '</a>';
        $html .= '<p class="text-uppercase small fw-semibold text-muted mb-3">Admin Menu</p>';
        $html .= '<nav class="nav nav-pills flex-column gap-2" aria-label="관리자 메뉴">';
        foreach ($items as $key => $item) {
            $html .= '<a class="nav-link d-flex align-items-center gap-2' . ($key === $active ? ' active' : '') . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
                  . '<i class="bi ' . smartcms_h((string)$item['icon']) . '"></i>'
                  . '<span>' . smartcms_h((string)$item['label']) . '</span>'
                  . '</a>';
        }
        $html .= '</nav>';
        $html .= '<div class="card mt-auto">';
        $html .= '<div class="card-body d-flex align-items-center gap-2">';
        $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1">' . smartcms_h($initial) . '</span>';
        $html .= '<div class="min-w-0 flex-grow-1">';
        $html .= '<strong class="d-block text-body text-truncate">' . smartcms_h((string)$admin['name']) . '</strong>';
        $html .= '<small class="text-body-secondary d-block">level ' . smartcms_h((string)$admin['level']) . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="d-grid mt-3">';
        $html .= '<a class="btn btn-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
        $html .= '</div>';
        $html .= '</aside>';

        $html .= '<section class="col-12 col-md-9 col-xxl-10 p-3 p-lg-4">';
        $html .= '<header class="card mb-4 d-none d-md-block">';
        $html .= '<div class="card-body">';
        $html .= '<div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">';
        $html .= '<div><p class="text-uppercase text-success small fw-semibold mb-1">Admin Console</p><h1 class="h2 mb-0 text-body">' . smartcms_h($title) . '</h1></div>';
        $html .= '<div class="d-flex align-items-center gap-3">';
        $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1">' . smartcms_h($initial) . '</span>';
        $html .= '<div><strong class="d-block text-body">' . smartcms_h((string)$admin['name']) . '</strong><small class="text-body-secondary d-block">level ' . smartcms_h((string)$admin['level']) . '</small></div>';
        $html .= '<a class="btn btn-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</header>';
        $html .= '<div class="d-grid gap-4">';

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
