<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/ui/components.php';

// 기본 변수 초기화 (오류 방지)
$request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
$is_admin = str_starts_with($request_path, '/admin/');
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
  <div class="d-flex min-vh-100">
    <!-- [SIDEBAR] 반응형 사이드바 (모바일은 Offcanvas, 데스크톱은 고정) -->
    <aside class="offcanvas-md offcanvas-start sc-admin-sidebar flex-column bg-white border-end p-3 sticky-top" tabindex="-1" id="adminSidebarOffcanvas" style="height: 100vh; overflow-y: auto;">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none" href="/admin/dashboard/">
          <span class="badge bg-primary-subtle text-primary rounded p-2 lh-1"><i class="bi bi-app-indicator fs-4"></i></span>
          <span class="fs-4">smartcms</span>
        </a>
        <!-- 모바일용 닫기 버튼 -->
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
      <!-- 상단 워크스페이스 헤더 -->
      <header class="bg-white border-bottom px-4 py-3">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <!-- 모바일용 햄버거 메뉴 버튼 -->
            <button class="btn btn-link p-0 border-0 text-dark d-md-none shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas">
              <i class="bi bi-list fs-2"></i>
            </button>

            <div class="d-none d-sm-block">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                  <li class="breadcrumb-item"><a href="/admin/dashboard/" class="text-decoration-none text-secondary">Admin</a></li>
                  <li class="breadcrumb-item active fw-bold" aria-current="page"><?= smartcms_h($title) ?></li>
                </ol>
              </nav>
              <h1 class="h4 fw-bold mb-0 text-body"><?= smartcms_h($title) ?></h1>
            </div>
            <!-- 모바일 전용 타이틀 -->
            <h1 class="h5 fw-bold mb-0 text-body d-sm-none"><?= smartcms_h($title) ?></h1>
          </div>
          <a class="btn btn-light btn-sm rounded-pill border px-3" href="/"><i class="bi bi-house me-1"></i><span class="d-none d-sm-inline">사이트 홈</span></a>
        </div>
      </header>

      <!-- 실제 본문 내용 영역 -->
      <main class="p-4 flex-grow-1">
<?php elseif (!$is_admin):
    $site_nav = smartcms_site_nav_items();
    $user = smartcms_current_user();
    $brand_url = smartcms_h(smartcms_base_url('/'));
?>
  <main class="min-vh-100 d-flex flex-column">
    <header class="bg-white border-bottom py-2 small text-body-secondary">
      <div class="container-xxl d-flex align-items-center justify-content-between">
        <div class="d-none d-md-flex gap-3">
          <a href="<?= $brand_url ?>" class="text-decoration-none text-body-secondary">커뮤니티 홈</a>
          <a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>" class="text-decoration-none text-body-secondary">전체글</a>
        </div>
        <div class="d-flex gap-3 ms-auto">
          <?php if ($user): ?>
            <span class="text-dark fw-bold"><i class="bi bi-person-circle me-1"></i><?= smartcms_h($user['name']) ?></span>
            <a href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>" class="text-decoration-none text-body-secondary">로그아웃</a>
          <?php else: ?>
            <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="text-decoration-none text-body-secondary">로그인</a>
            <a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="text-decoration-none text-body-secondary">회원가입</a>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <section class="bg-white py-4">
      <div class="container-xxl">
        <div class="row align-items-center g-4">
          <div class="col-12 col-md-3 text-center text-md-start">
            <a class="navbar-brand fs-2 fw-bold text-primary" href="<?= $brand_url ?>">smartcms<span class="text-dark">.</span></a>
          </div>
          <div class="col-12 col-md-6">
            <form action="<?= smartcms_h(smartcms_base_url('/board/')) ?>" method="get" class="position-relative">
              <div class="input-group input-group-lg">
                <input type="text" name="q" class="form-control bg-body border-0 rounded-pill ps-4" placeholder="궁금한 것을 검색해보세요">
                <button class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-primary z-3 me-2" type="submit"><i class="bi bi-search fs-5"></i></button>
              </div>
            </form>
          </div>
          <div class="col-md-3 d-none d-md-flex justify-content-end">
            <a href="<?= smartcms_h(smartcms_base_url('/board/write/')) ?>" class="btn btn-primary rounded-pill px-4"><i class="bi bi-pencil-square me-2"></i>글쓰기</a>
          </div>
        </div>
      </div>
    </section>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top p-0">
      <div class="container-xxl">
        <button class="navbar-toggler my-2 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNav">
          <ul class="navbar-nav w-100">
            <?php foreach ($site_nav as $key => $item):
                $is_active = ($key === $active_menu);
                $active_class = $is_active ? ' active fw-bold text-primary border-bottom border-primary border-3' : '';
            ?>
              <li class="nav-item"><a class="nav-link py-3 px-4 text-center<?= $active_class ?>" href="<?= smartcms_h(smartcms_base_url((string)$item['href'])) ?>"><?= smartcms_h((string)$item['label']) ?></a></li>
            <?php endforeach; ?>
            <?php if ($user && (int)$user['level'] >= 8): ?>
              <li class="nav-item ms-lg-auto"><a class="nav-link py-3 px-4 text-warning" href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>"><i class="bi bi-speedometer2 me-1"></i>관리자</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
<?php endif; ?>
    <?php
}
