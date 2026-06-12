<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/ui/components.php';

$title = (string)($SMARTCMS_HEAD['title'] ?? 'smartcms');
$active_menu = (string)($SMARTCMS_HEAD['active_menu'] ?? '');
$body_class = trim((string)($SMARTCMS_HEAD['body_class'] ?? 'bg-light'));
if ($body_class !== '') {
    $body_class .= ' ';
}
$body_class .= 'd-flex flex-column min-vh-100';
$main_class = trim((string)($SMARTCMS_HEAD['main_class'] ?? 'flex-grow-1 py-4 py-lg-5'));
$css_url = (string)smartcms_config_value('theme.css_url', '/common/css/common.css');
$stylesheets = (array)($SMARTCMS_HEAD['stylesheets'] ?? []);
?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= smartcms_h($title) ?> · smartcms</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard-dynamic-subset.min.css">
  <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url($css_url)) ?>">
  <?php foreach ($stylesheets as $stylesheet): ?>
    <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url((string)$stylesheet)) ?>">
  <?php endforeach; ?>
</head>
<body class="<?= smartcms_h($body_class) ?>">
  <?php
    $site_nav = smartcms_site_nav_items();
    $user = smartcms_current_user();
    $brand_url = smartcms_h(smartcms_base_url('/'));
  ?>

  <!-- [HEADER] 상단 유틸리티 및 브랜드 -->
  <header class="bg-white border-bottom shadow-sm">
    <div class="container-xxl">
      <div class="d-flex flex-wrap align-items-center justify-content-between py-2 small border-bottom mb-3 text-secondary">
        <div class="d-none d-md-flex gap-3">
          <a href="<?= $brand_url ?>" class="link-secondary text-decoration-none">커뮤니티 홈</a>
          <a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>" class="link-secondary text-decoration-none">전체글</a>
        </div>
        <div class="d-flex gap-3 ms-auto">
          <?php if ($user): ?>
            <span class="text-dark fw-bold"><i class="bi bi-person-circle me-1"></i><?= smartcms_h($user['name']) ?></span>
            <a href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>" class="link-secondary text-decoration-none">로그아웃</a>
          <?php else: ?>
            <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="link-secondary text-decoration-none">로그인</a>
            <a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="link-secondary text-decoration-none">회원가입</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="row align-items-center g-3 py-3">
        <div class="col-12 col-md-3 text-center text-md-start">
          <a class="navbar-brand fs-2 fw-bold text-primary" href="<?= $brand_url ?>">smartcms</a>
        </div>
        <div class="col-12 col-md-9 d-flex justify-content-md-center">
          <form action="<?= smartcms_h(smartcms_base_url('/board/')) ?>" method="get" role="search" data-search-min-length="2" class="sc-header-search mx-auto">
            <div class="input-group input-group-lg">
              <span class="input-group-text">
                <i class="bi bi-search text-primary"></i>
              </span>
              <input type="search" name="q" class="form-control" placeholder="궁금한 것을 검색해보세요" aria-label="게시판 검색">
              <button class="btn btn-primary" type="submit">
                검색
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- [NAV] 메인 네비게이션 -->
    <nav class="navbar bg-white border-top p-0 mt-3" aria-label="메인 메뉴">
      <div class="container-xxl">
        <button class="btn btn-light border rounded-pill d-md-none my-2 px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#siteNavOffcanvas" aria-controls="siteNavOffcanvas" aria-label="메뉴 열기">
          <i class="bi bi-list fs-4 lh-1"></i>
        </button>
        <div class="d-none d-md-block w-100">
          <ul class="nav w-100">
            <?php foreach ($site_nav as $key => $item):
                $is_active = ($key === $active_menu);
            ?>
              <li class="nav-item">
                <a class="nav-link py-3 px-4 text-center text-body border-bottom border-3 <?= $is_active ? 'active fw-bold text-primary border-primary' : 'border-white' ?>"
                   href="<?= smartcms_h(smartcms_base_url((string)$item['href'])) ?>">
                  <?= smartcms_h((string)$item['label']) ?>
                </a>
              </li>
            <?php endforeach; ?>
            <?php if ($user && (int)$user['level'] >= 8): ?>
              <li class="nav-item ms-lg-auto">
                <a class="nav-link py-3 px-4 text-primary fw-bold" href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>">
                  <i class="bi bi-speedometer2 me-1"></i>관리자
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>

    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="siteNavOffcanvas" aria-labelledby="siteNavOffcanvasLabel">
      <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5 fw-bold text-primary" id="siteNavOffcanvasLabel">smartcms</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="메뉴 닫기"></button>
      </div>
      <div class="offcanvas-body">
        <nav aria-label="모바일 메뉴">
          <ul class="nav nav-pills flex-column gap-1">
            <?php foreach ($site_nav as $key => $item):
                $is_active = ($key === $active_menu);
            ?>
              <li class="nav-item">
                <a class="nav-link <?= $is_active ? 'active fw-bold' : 'text-body' ?>"
                   href="<?= smartcms_h(smartcms_base_url((string)$item['href'])) ?>">
                  <?= smartcms_h((string)$item['label']) ?>
                </a>
              </li>
            <?php endforeach; ?>
            <?php if ($user && (int)$user['level'] >= 8): ?>
              <li class="nav-item">
                <a class="nav-link text-primary fw-bold" href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>">
                  <i class="bi bi-speedometer2 me-1"></i>관리자
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <!-- [MAIN] 메인 콘텐츠 영역 -->
  <main class="<?= smartcms_h($main_class) ?>">
