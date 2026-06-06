<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/navigation.php';

$user = smartcms_current_user();

smartcms_render_head([
    'title' => '회원 센터',
    'body_class' => 'smartcms-board-page',
]);
?>
<?= smartcms_site_header('') ?>
<div class="container-fluid container-xxl py-4">
  <header class="mb-4">
    <p class="text-uppercase text-muted small fw-semibold mb-1">Member</p>
    <h1 class="h2 fw-bold mb-2">회원 센터</h1>
    <p class="text-body-secondary mb-0">로그인 상태와 회원 관련 기능을 확인합니다.</p>
  </header>

  <section class="row row-cols-1 row-cols-md-2 g-3">
    <?php if ($user): ?>
      <div class="col">
        <a class="card h-100 text-decoration-none shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">
          <div class="card-body">
            <strong class="d-block mb-1">마이페이지</strong>
            <span class="text-body-secondary"><?= smartcms_h($user['name']) ?>님의 회원 정보를 확인합니다.</span>
          </div>
        </a>
      </div>
      <div class="col">
        <a class="card h-100 text-decoration-none shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>">
          <div class="card-body">
            <strong class="d-block mb-1">비밀번호 변경</strong>
            <span class="text-body-secondary">현재 비밀번호 확인 후 새 비밀번호로 변경합니다.</span>
          </div>
        </a>
      </div>
      <div class="col">
        <a class="card h-100 text-decoration-none shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">
          <div class="card-body">
            <strong class="d-block mb-1">로그아웃</strong>
            <span class="text-body-secondary">현재 세션을 종료합니다.</span>
          </div>
        </a>
      </div>
    <?php else: ?>
      <div class="col">
        <a class="card h-100 text-decoration-none shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">
          <div class="card-body">
            <strong class="d-block mb-1">로그인</strong>
            <span class="text-body-secondary">가입한 계정으로 로그인합니다.</span>
          </div>
        </a>
      </div>
      <div class="col">
        <a class="card h-100 text-decoration-none shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>">
          <div class="card-body">
            <strong class="d-block mb-1">회원가입</strong>
            <span class="text-body-secondary">기본 회원 계정을 생성합니다.</span>
          </div>
        </a>
      </div>
  <?php endif; ?>
  </section>
</div>
<?= smartcms_site_footer() ?>
<?php smartcms_render_foot(); ?>
