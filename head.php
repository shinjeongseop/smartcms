<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';

function smartcms_render_head(array $page = []): void
{
    $title      = (string)($page['title'] ?? 'smartcms');
    $body_class = trim((string)($page['body_class'] ?? ''));
    $css_url    = (string)smartcms_config_value('theme.css_url', '/common/css/common.css');
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
  <?php foreach (($page['stylesheets'] ?? []) as $stylesheet): ?>
    <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url((string)$stylesheet)) ?>">
  <?php endforeach; ?>
</head>
<body class="<?= smartcms_h($body_class) ?>">
    <?php
}

function smartcms_site_nav_items(): array
{
    $installed = is_file(smartcms_config_local_path()) && smartcms_install_locked();

    $items = [
        'home' => ['label' => '홈', 'href' => '/', 'icon' => 'bi-house-fill'],
    ];

    if ($installed) {
        $items += [
            'notice' => ['label' => '공지', 'href' => '/board/?board=notice', 'icon' => 'bi-megaphone-fill'],
            'free'   => ['label' => '자유', 'href' => '/board/?board=free', 'icon' => 'bi-chat-square-text-fill'],
            'qna'    => ['label' => 'Q&A', 'href' => '/board/?board=qna', 'icon' => 'bi-question-circle-fill'],
        ];
    }

    return $items;
}

function smartcms_site_header(string $active = '', string $extra_class = ''): string
{
    $installed = is_file(smartcms_config_local_path()) && smartcms_install_locked();
    $items = smartcms_site_nav_items();
    $currentBoard = $active !== '' && $active !== 'home' ? $active : '';
    $brandHref = smartcms_h(smartcms_base_url('/'));
    $searchAction = smartcms_h(smartcms_base_url('/board/'));

    $html  = '<main class="sc-shell sc-site-shell ' . smartcms_h(trim($extra_class)) . '">';
    $html .= '<header class="sc-site-header border-bottom">';
    $html .= '<div class="container-xxl py-3 py-lg-4">';
    $html .= '<div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3">';
    $html .= '<a class="sc-brand d-inline-flex align-items-center gap-2 text-decoration-none" href="' . $brandHref . '">';
    $html .= '<span class="sc-brand-mark"><i class="bi bi-n-square-fill"></i></span>';
    $html .= '<span class="sc-brand-text">smartcms</span>';
    $html .= '</a>';
    $html .= '<form class="sc-site-search flex-grow-1" action="' . $searchAction . '" method="get">';
    $html .= '<div class="input-group">';
    $html .= '<span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>';
    $html .= '<input class="form-control border-start-0" type="search" name="q" value="" placeholder="게시판에서 검색">';
    if ($currentBoard !== '') {
        $html .= '<input type="hidden" name="board" value="' . smartcms_h($currentBoard) . '">';
    }
    $html .= '<button class="btn btn-primary" type="submit">검색</button>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '<div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">';
    $html .= $installed
        ? '<a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/admin/')) . '"><i class="bi bi-speedometer2 me-1"></i>관리자</a>'
        : '<a class="btn btn-primary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/install/')) . '"><i class="bi bi-magic me-1"></i>설치하기</a>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/login/')) . '"><i class="bi bi-box-arrow-in-right me-1"></i>로그인</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="sc-site-nav-wrap mt-3">';
    $html .= '<nav class="sc-site-nav d-flex flex-wrap gap-2" aria-label="사이트 메뉴">';

    foreach ($items as $key => $item) {
        $isActive = $key === $active;
        $cls = 'sc-site-nav-link' . ($isActive ? ' is-active' : '');
        $html .= '<a class="' . smartcms_h($cls) . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
              . '<i class="bi ' . smartcms_h((string)$item['icon']) . ' me-1"></i>'
              . smartcms_h((string)$item['label'])
              . '</a>';
    }

    $html .= '</nav></div></div></header>';
    return $html;
}

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

function smartcms_admin_page_header(array $admin, string $title, string $active): string
{
    $initial = smartcms_admin_initial((string)$admin['name']);
    $items = smartcms_admin_nav_items();

    $html  = '<main class="sc-shell sc-admin-shell">';
    $html .= '<div class="sc-admin-bar border-bottom d-md-none">';
    $html .= '<div class="container-fluid px-3 py-3">';
    $html .= '<div class="d-flex align-items-center justify-content-between gap-3">';
    $html .= '<a class="sc-brand text-decoration-none" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '<span class="sc-brand-mark"><i class="bi bi-n-square-fill"></i></span>';
    $html .= '<span class="sc-brand-text">smartcms</span>';
    $html .= '</a>';
    $html .= '<button class="btn btn-outline-secondary btn-sm rounded-pill" type="button" data-bs-toggle="offcanvas" data-bs-target="#smartcmsAdminNav" aria-controls="smartcmsAdminNav" aria-label="관리자 메뉴 열기">';
    $html .= '<i class="bi bi-list me-1"></i>메뉴';
    $html .= '</button>';
    $html .= '</div>';
    $html .= '<div class="d-flex align-items-end justify-content-between gap-3 mt-3">';
    $html .= '<div><p class="text-uppercase text-success small fw-semibold mb-1">Admin Console</p><h1 class="h4 mb-0 text-body">' . smartcms_h($title) . '</h1></div>';
    $html .= '<span class="sc-avatar">' . smartcms_h($initial) . '</span>';
    $html .= '</div>';
    $html .= '</div></div>';

    $html .= '<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="smartcmsAdminNav" aria-labelledby="smartcmsAdminNavLabel">';
    $html .= '<div class="offcanvas-header border-bottom">';
    $html .= '<h2 class="offcanvas-title h5 mb-0" id="smartcmsAdminNavLabel">관리자 메뉴</h2>';
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="닫기"></button>';
    $html .= '</div>';
    $html .= '<div class="offcanvas-body p-0">';
    $html .= '<div class="p-3">';
    $html .= '<a class="sc-brand d-inline-flex align-items-center gap-2 text-decoration-none mb-4" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '<span class="sc-brand-mark"><i class="bi bi-n-square-fill"></i></span>';
    $html .= '<span class="sc-brand-text">smartcms</span>';
    $html .= '</a>';
    $html .= '<nav class="sc-admin-nav list-group list-group-flush" aria-label="관리자 메뉴">';
    foreach ($items as $key => $item) {
        $html .= '<a class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0' . ($key === $active ? ' active' : '') . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
              . '<i class="bi ' . smartcms_h((string)$item['icon']) . '"></i>'
              . '<span>' . smartcms_h((string)$item['label']) . '</span>'
              . '</a>';
    }
    $html .= '</nav>';
    $html .= '<div class="mt-4 rounded-4 border p-3 bg-white">';
    $html .= '<div class="d-flex align-items-center gap-2">';
    $html .= '<span class="sc-avatar">' . smartcms_h($initial) . '</span>';
    $html .= '<div class="min-w-0">';
    $html .= '<strong class="d-block text-body text-truncate">' . smartcms_h((string)$admin['name']) . '</strong>';
    $html .= '<small class="text-body-secondary d-block">level ' . smartcms_h((string)$admin['level']) . '</small>';
    $html .= '</div></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill w-100 mt-3" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div></div></div>';

    $html .= '<div class="container-fluid">';
    $html .= '<div class="row g-0 min-vh-100">';
    $html .= '<aside class="col-12 col-md-3 col-xxl-2 d-none d-md-flex sc-admin-sidebar flex-column p-4">';
    $html .= '<a class="sc-brand d-inline-flex align-items-center gap-2 text-decoration-none mb-4" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">';
    $html .= '<span class="sc-brand-mark"><i class="bi bi-n-square-fill"></i></span>';
    $html .= '<span class="sc-brand-text">smartcms</span>';
    $html .= '</a>';
    $html .= '<p class="text-uppercase small fw-semibold text-muted mb-3">Admin Menu</p>';
    $html .= '<nav class="sc-admin-nav list-group list-group-flush" aria-label="관리자 메뉴">';
    foreach ($items as $key => $item) {
        $html .= '<a class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0' . ($key === $active ? ' active' : '') . '" href="' . smartcms_h(smartcms_base_url((string)$item['href'])) . '">'
              . '<i class="bi ' . smartcms_h((string)$item['icon']) . '"></i>'
              . '<span>' . smartcms_h((string)$item['label']) . '</span>'
              . '</a>';
    }
    $html .= '</nav>';
    $html .= '<div class="mt-auto pt-4">';
    $html .= '<div class="d-flex align-items-center gap-2 rounded-4 border p-3 bg-white">';
    $html .= '<span class="sc-avatar">' . smartcms_h($initial) . '</span>';
    $html .= '<div class="min-w-0">';
    $html .= '<strong class="d-block text-body text-truncate">' . smartcms_h((string)$admin['name']) . '</strong>';
    $html .= '<small class="text-body-secondary d-block">level ' . smartcms_h((string)$admin['level']) . '</small>';
    $html .= '</div></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill w-100 mt-3" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div></aside>';

    $html .= '<section class="col-12 col-md-9 col-xxl-10 sc-admin-workspace p-3 p-lg-4">';
    $html .= '<header class="sc-admin-pagehead mb-4 d-none d-md-block">';
    $html .= '<div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">';
    $html .= '<div><p class="text-uppercase text-success small fw-semibold mb-1">Admin Console</p><h1 class="h2 mb-0 text-body">' . smartcms_h($title) . '</h1></div>';
    $html .= '<div class="d-flex align-items-center gap-3">';
    $html .= '<span class="sc-avatar">' . smartcms_h($initial) . '</span>';
    $html .= '<div><strong class="d-block text-body">' . smartcms_h((string)$admin['name']) . '</strong><small class="text-body-secondary d-block">level ' . smartcms_h((string)$admin['level']) . '</small></div>';
    $html .= '<a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a>';
    $html .= '</div></div>';
    $html .= '</header>';
    $html .= '<div class="d-grid gap-4">';

    return $html;
}
