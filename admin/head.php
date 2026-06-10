<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/common/config.php';
require_once dirname(__DIR__) . '/common/auth.php';
require_once dirname(__DIR__) . '/common/ui/components.php';

$title = (string)($SMARTCMS_HEAD['title'] ?? 'Admin Panel');
$active_menu = (string)($SMARTCMS_HEAD['active_menu'] ?? '');
$request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
$is_login_page = str_contains($request_path, '/admin/login/');
$body_class = trim((string)($SMARTCMS_HEAD['body_class'] ?? ($is_login_page ? 'smartcms-admin-auth' : 'smartcms-admin-page bg-light')));
$stylesheets = (array)($SMARTCMS_HEAD['stylesheets'] ?? []);

if (!in_array('/admin/css/admin.css', $stylesheets, true)) {
    $stylesheets[] = '/admin/css/admin.css';
}
?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#03c75a">
  <title><?= smartcms_h($title) ?> · Admin · smartcms</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard-dynamic-subset.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap">
  <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url('/common/css/common.css')) ?>">
  <?php foreach ($stylesheets as $stylesheet): ?>
    <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url((string)$stylesheet)) ?>">
  <?php endforeach; ?>
</head>
<body class="<?= smartcms_h($body_class) ?>">
<?php if (!$is_login_page): ?>
  <div class="d-flex min-vh-100">
    <!-- [SIDEBAR] 반응형 사이드바 -->
    <aside class="offcanvas-md offcanvas-start sc-admin-sidebar flex-column bg-white border-end p-3" tabindex="-1" id="adminSidebarOffcanvas">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none" href="/admin/dashboard/">
          <span class="badge bg-primary-subtle text-primary rounded p-2 lh-1"><i class="bi bi-app-indicator fs-4"></i></span>
          <span class="fs-4">smartcms</span>
        </a>
        <button type="button" class="btn-close d-md-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-label="Close"></button>
      </div>

      <nav class="nav nav-pills flex-column gap-1" aria-label="관리자 메뉴">
        <p class="text-uppercase small fw-bold text-secondary opacity-50 mb-3 px-2" style="font-size: 0.65rem; letter-spacing: 0.05rem;">Admin Menu</p>
        <?php foreach (smartcms_admin_nav_items() as $key => $item): ?>
          <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?= $key === $active_menu ? 'active shadow-sm fw-bold' : 'text-secondary' ?>" href="<?= smartcms_h($item['href']) ?>">
            <i class="bi <?= smartcms_h($item['icon']) ?> fs-5"></i>
            <span class="fw-medium"><?= smartcms_h($item['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <?php $admin = smartcms_current_user(); if ($admin): ?>
        <div class="card mt-auto border-0 bg-light rounded-3 mb-2">
          <div class="card-body p-3 d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-circle p-2 lh-1" style="width:32px; height:32px;">
              <?= smartcms_h(mb_substr((string)$admin['name'], 0, 1)) ?>
            </span>
            <div class="min-w-0 flex-grow-1">
              <strong class="d-block text-body text-truncate small"><?= smartcms_h($admin['name']) ?></strong>
              <small class="text-secondary d-block" style="font-size: 0.7rem;">LV <?= (int)$admin['level'] ?></small>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <a class="btn btn-outline-danger btn-sm w-100 border-0 text-start px-3" href="/member/logout/"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </aside>

    <!-- [WORKSPACE] 워크스페이스 영역 -->
    <div class="flex-grow-1 d-flex flex-column bg-light" style="min-width: 0;">
      <header class="bg-white border-bottom px-3 px-md-4 py-3">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link p-0 border-0 text-dark d-md-none shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas">
              <i class="bi bi-list fs-2"></i>
            </button>
            <div class="d-none d-sm-block">
              <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-1"><li class="breadcrumb-item"><a href="/admin/dashboard/" class="text-decoration-none text-secondary">Admin</a></li><li class="breadcrumb-item active fw-bold" aria-current="page"><?= smartcms_h($title) ?></li></ol></nav>
              <h1 class="h4 fw-bold mb-0 text-body"><?= smartcms_h($title) ?></h1>
            </div>
            <h1 class="h5 fw-bold mb-0 text-body d-sm-none"><?= smartcms_h($title) ?></h1>
          </div>
          <a class="btn btn-light btn-sm rounded-pill border px-3" href="/"><i class="bi bi-house me-1"></i><span class="d-none d-sm-inline">사이트 홈</span></a>
        </div>
      </header>
      <main class="p-4 flex-grow-1">
<?php endif; ?>
