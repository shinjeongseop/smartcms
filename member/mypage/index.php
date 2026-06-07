<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../common/ui/navigation.php';

$user = smartcms_require_login();

smartcms_render_head(['title' => '마이페이지']);
echo smartcms_site_header('');
?>
<?= smartcms_page_container_start() ?>
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xxl-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
          <p class="text-uppercase text-muted small fw-semibold mb-1">My Account</p>
          <h1 class="h3 fw-bold mb-4"><?= smartcms_h($user['name']) ?>님의 마이페이지</h1>

          <dl class="row g-3 mb-4">
            <div class="col-12 col-md-6">
              <dt class="text-muted small">이름</dt>
              <dd class="fw-semibold mb-0"><?= smartcms_h($user['name']) ?></dd>
            </div>
            <div class="col-12 col-md-6">
              <dt class="text-muted small">이메일</dt>
              <dd class="fw-semibold mb-0"><?= smartcms_h($user['email']) ?></dd>
            </div>
            <div class="col-12 col-md-6">
              <dt class="text-muted small">권한 레벨</dt>
              <dd class="fw-semibold mb-0">level <?= smartcms_h($user['level']) ?></dd>
            </div>
            <div class="col-12 col-md-6">
              <dt class="text-muted small">역할</dt>
              <dd class="fw-semibold mb-0"><?= smartcms_h($user['role']) ?></dd>
            </div>
          </dl>

          <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>">비밀번호 변경</a>
            <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?= smartcms_page_container_end() ?>
<?= smartcms_site_footer() ?>
<?php smartcms_render_foot(); ?>
