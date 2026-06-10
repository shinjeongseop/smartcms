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
        $html .= '<div class="input-group input-group-lg">';
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

if (!function_exists('smartcms_admin_page_header')) {
    function smartcms_admin_page_header(array $admin, string $title, string $active): string
    {
        $initial = smartcms_admin_initial((string)$admin['name']);
        $items = smartcms_admin_nav_items();

        $html  = '<main class="min-vh-100 d-flex flex-column bg-body-tertiary">';
        $html .= '<header class="navbar navbar-expand-md navbar-light bg-white border-bottom sticky-top">';
        $html .= '<div class="container-fluid px-3 px-lg-4">';
        $html .= '<a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
        $html .= '<span class="badge bg-primary-subtle text-primary rounded p-2 lh-1"><i class="bi bi-app-indicator fs-4"></i></span>';
        $html .= '<span>smartcms</span>';
        $html .= '</a>';
        $html .= '<button class="navbar-toggler border-0 shadow-none d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#smartcmsAdminNav" aria-controls="smartcmsAdminNav" aria-expanded="false" aria-label="관리자 메뉴 열기">';
        $html .= '<span class="navbar-toggler-icon"></span>';
        $html .= '</button>';
        $html .= '<div class="collapse navbar-collapse d-md-none mt-3 mt-md-0" id="smartcmsAdminNav">';
        $html .= '<div class="container-fluid px-0 py-3 border-top bg-white">';
        $html .= '<nav class="nav nav-pills flex-column gap-2" aria-label="관리자 메뉴">';
        foreach ($items as $key => $item) {
            $activeClass = $key === $active ? ' active shadow-sm' : ' text-secondary';
            $html .= '<a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2' . $activeClass . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
                  . '<i class="bi ' . smartcms_h((string)$item['icon']) . '"></i>'
                  . '<span>' . smartcms_h((string)$item['label']) . '</span>'
                  . '</a>';
        }
        $html .= '</nav>';
        $html .= '</header>';

        $html .= '<div class="container-fluid flex-grow-1 px-0">';
        $html .= '<div class="row g-0 min-vh-100 align-items-stretch">';
        $html .= '<aside class="col-12 col-md-3 col-xxl-2 d-none d-md-flex flex-column bg-white border-end p-3 sticky-top" style="height: 100vh; overflow-y: auto;">';
        $html .= '<a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none mb-4" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
        $html .= '<span class="badge bg-primary-subtle text-primary rounded p-2 lh-1"><i class="bi bi-app-indicator fs-4"></i></span>';
        $html .= '<span class="fs-4">smartcms</span>';
        $html .= '</a>';
        $html .= '<p class="text-uppercase small fw-bold text-secondary opacity-50 mb-3 px-2" style="font-size: 0.65rem; letter-spacing: 0.05rem;">Admin Menu</p>';
        $html .= '<nav class="nav nav-pills flex-column gap-1" aria-label="관리자 메뉴">';
        foreach ($items as $key => $item) {
            $activeClass = $key === $active ? ' active shadow-sm fw-bold' : ' text-secondary';
            $html .= '<a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2' . $activeClass . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
                  . '<i class="bi ' . smartcms_h((string)$item['icon']) . ' fs-5"></i>'
                  . '<span class="fw-medium">' . smartcms_h((string)$item['label']) . '</span>'
                  . '</a>';
        }
        $html .= '</nav>';
        $html .= '<div class="card mt-auto border-0 bg-light rounded-3 mb-2">';
        $html .= '<div class="card-body p-3 d-flex align-items-center gap-2">';
        $html .= '<span class="badge bg-primary rounded-circle p-2 lh-1" style="width:36px; height:36px; line-height:20px;">' . smartcms_h($initial) . '</span>';
        $html .= '<div class="min-w-0 flex-grow-1">';
        $html .= '<strong class="d-block text-body text-truncate small">' . smartcms_h((string)$admin['name']) . '</strong>';
        $html .= '<small class="text-secondary d-block" style="font-size: 0.7rem;">LV ' . smartcms_h((string)$admin['level']) . ' · Admin</small>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<a class="btn btn-outline-danger btn-sm w-100 border-0 text-start px-3" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>';

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

// 기본 변수 초기화 (오류 방지)
$request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
$is_admin = str_contains($request_path, '/admin/');
$active_menu = '';

if (isset($SMARTCMS_HEAD) && is_array($SMARTCMS_HEAD)) {
    $title = (string)($SMARTCMS_HEAD['title'] ?? 'smartcms');
    $active_menu = (string)($SMARTCMS_HEAD['active_menu'] ?? '');
    $body_class = trim((string)($SMARTCMS_HEAD['body_class'] ?? ($is_admin ? 'smartcms-admin-page bg-light' : '')));
    $css_url = (string)smartcms_config_value('theme.css_url', '/common/css/common.css');
    $stylesheets = (array)($SMARTCMS_HEAD['stylesheets'] ?? []);

    if ($is_admin) {
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
<?php if ($is_admin && !str_contains($request_path, '/admin/login/')): ?>
  <div class="container-fluid flex-grow-1 px-0">
    <div class="row g-0 min-vh-100 align-items-stretch">
      <!-- [SIDEBAR] 너비를 col-md-3 col-lg-2로 완전 고정 -->
      <aside class="col-12 col-md-3 col-lg-2 d-none d-md-flex flex-column bg-white border-end p-3 sticky-top" style="height: 100vh; overflow-y: auto;">
        <a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none mb-4" href="/admin/dashboard/">
          <span class="badge bg-primary-subtle text-primary rounded p-2 lh-1"><i class="bi bi-app-indicator fs-4"></i></span>
          <span class="fs-4">smartcms</span>
        </a>
        <p class="text-uppercase small fw-bold text-secondary opacity-50 mb-3 px-2" style="font-size: 0.65rem; letter-spacing: 0.05rem;">Admin Menu</p>
        <nav class="nav nav-pills flex-column gap-1">
          <?php foreach (smartcms_admin_nav_items() as $key => $item): ?>
            <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?= $key === $active_menu ? 'active shadow-sm fw-bold' : 'text-secondary' ?>" href="<?= smartcms_h($item['href']) ?>">
              <i class="bi <?= smartcms_h($item['icon']) ?> fs-5"></i>
              <span class="fw-medium"><?= smartcms_h($item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>

        <?php if (isset($admin)): ?>
          <div class="card mt-auto border-0 bg-light rounded-3 mb-2">
            <div class="card-body p-3 d-flex align-items-center gap-2">
              <span class="badge bg-primary rounded-circle p-2 lh-1" style="width:32px; height:32px;"><?= smartcms_h(smartcms_admin_initial($admin['name'])) ?></span>
              <div class="min-w-0 flex-grow-1">
                <strong class="d-block text-body text-truncate small"><?= smartcms_h($admin['name']) ?></strong>
                <small class="text-secondary d-block" style="font-size: 0.7rem;">LV <?= (int)$admin['level'] ?></small>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <a class="btn btn-outline-danger btn-sm w-100 border-0 text-start px-3" href="/member/logout/"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
      </aside>

      <!-- [WORKSPACE] 너비를 col-md-9 col-lg-10으로 완전 고정 -->
      <main class="col-12 col-md-9 col-lg-10">
<?php endif; ?>
    <?php
}
