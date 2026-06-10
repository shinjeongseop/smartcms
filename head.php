<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/ui/components.php';

// 기본 변수 초기화 (오류 방지)
$active_menu = '';
$title = (string)($SMARTCMS_HEAD['title'] ?? 'smartcms');
$active_menu = (string)($SMARTCMS_HEAD['active_menu'] ?? '');
$body_class = trim((string)($SMARTCMS_HEAD['body_class'] ?? 'bg-body'));
$css_url = (string)smartcms_config_value('theme.css_url', '/common/css/common.css');
$stylesheets = (array)($SMARTCMS_HEAD['stylesheets'] ?? []);
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
