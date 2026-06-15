<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/common/config.php';
require_once dirname(__DIR__) . '/common/auth.php';
require_once dirname(__DIR__) . '/common/ui/components.php';

$title = (string)($SMARTCMS_HEAD['title'] ?? 'Admin Panel');
$page_heading = (string)($SMARTCMS_HEAD['page_heading'] ?? $title);
$active_menu = (string)($SMARTCMS_HEAD['active_menu'] ?? '');
$request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
$is_login_page = str_contains($request_path, '/admin/login/');
$body_class = (string)($SMARTCMS_HEAD['body_class'] ?? '');

if ($body_class === '') {
    $body_class = $is_login_page ? 'smartcms-admin-auth' : 'smartcms-admin-page bg-light';
} elseif (!$is_login_page && !str_contains($body_class, 'bg-')) {
    $body_class .= ' bg-light';
}

$body_class = trim($body_class);
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
  <title><?= smartcms_h($title) ?> · Admin · smartcms</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard-dynamic-subset.min.css">
  <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url('/common/css/common.css')) ?>">
  <?php foreach ($stylesheets as $stylesheet): ?>
    <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url((string)$stylesheet)) ?>">
  <?php endforeach; ?>
</head>
<body class="<?= smartcms_h($body_class) ?>">
<?php if (!$is_login_page): $admin = smartcms_current_user(); ?>
  <div class="d-flex min-vh-100">
    <!-- [SIDEBAR] 어드민 사이드바 -->
    <aside class="offcanvas-md offcanvas-start sc-admin-sidebar flex-column bg-white border-end p-3 flex-shrink-0 shadow-sm" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarTitle">
      <header class="d-flex align-items-center justify-content-between mb-4 px-2">
        <a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none" href="/admin/dashboard/">
          <span class="badge bg-primary-subtle text-primary rounded p-2 lh-1 shadow-sm"><i class="bi bi-app-indicator fs-4"></i></span>
          <span class="fs-4" id="adminSidebarTitle">smartcms</span>
        </a>
        <button type="button" class="btn-close d-md-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-label="Close"></button>
      </header>

      <nav class="nav nav-pills flex-column gap-1 mb-auto" aria-label="관리자 메뉴">
        <p class="text-uppercase small fw-bold text-secondary opacity-50 mb-3 px-2 sc-admin-menu-eyebrow">Admin Menu</p>
        <?php foreach (smartcms_admin_nav_items() as $key => $item): ?>
          <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-2 <?= $key === $active_menu ? 'active fw-bold' : 'text-secondary' ?>" href="<?= smartcms_h($item['href']) ?>">
            <i class="bi <?= smartcms_h($item['icon']) ?> fs-5"></i>
            <span class="fw-medium"><?= smartcms_h($item['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <?php if ($admin): ?>
        <section class="card border rounded-3 mb-2 mt-4 shadow-none border">
          <div class="card-body p-3 d-flex align-items-center gap-2">
            <?= smartcms_user_avatar_markup($admin, 'sc-admin-avatar-34', 'fw-bold small') ?>
            <div class="min-w-0 flex-grow-1">
              <strong class="d-block text-body text-truncate small fw-bold"><?= smartcms_h($admin['name']) ?></strong>
              <small class="text-secondary d-block fw-medium sc-admin-time-xs">LV <?= (int)$admin['level'] ?> Admin</small>
            </div>
          </div>
        </section>
      <?php endif; ?>
      
      <footer class="mt-2 pt-2 border-top">
        <a class="btn btn-danger btn-sm w-100 border-0 text-start px-3 py-2 rounded-2 fw-bold shadow-none" href="/member/logout/">
          <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
      </footer>
    </aside>

    <!-- [WORKSPACE] 워크스페이스 영역 -->
    <div class="flex-grow-1 d-flex flex-column bg-light sc-admin-workspace">
      <header class="bg-white border-bottom px-3 px-md-4 py-3 sticky-top z-1 shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link p-0 border-0 text-dark d-md-none shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas">
              <i class="bi bi-list fs-2"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-sm-block">
              <ol class="breadcrumb small mb-1 fw-medium">
                <li class="breadcrumb-item"><a href="/admin/dashboard/" class="text-decoration-none text-secondary">Admin</a></li>
              <li class="breadcrumb-item active fw-bold text-primary" aria-current="page"><?= smartcms_h($page_heading) ?></li>
            </ol>
              <h1 class="h4 fw-bold mb-0 text-dark"><?= smartcms_h($page_heading) ?></h1>
            </nav>
            <h1 class="h5 fw-bold mb-0 text-dark d-sm-none"><?= smartcms_h($page_heading) ?></h1>
          </div>
          <div class="d-flex gap-2">
            <a class="btn btn-light border btn-sm rounded-2 px-3 fw-bold shadow-none" href="/">
              <i class="bi bi-house me-1"></i><span class="d-none d-sm-inline">사이트 홈</span>
            </a>
          </div>
        </div>
      </header>

      <main class="flex-grow-1">
        <div class="px-3 py-4 px-md-4">
<?php endif; ?>
