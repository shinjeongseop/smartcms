<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../common/ui/navigation.php';

$user = smartcms_require_login();

smartcms_render_head([
    'title' => '마이페이지',
    'body_class' => 'smartcms-board-page',
]);
?>
<?= smartcms_site_header('') ?>
  <section class="card smartcms-panel smartcms-auth-panel">
    <h1 class="smartcms-title">마이페이지</h1>
    <p class="smartcms-text-muted">현재 로그인한 회원 정보를 확인합니다.</p>

    <dl class="smartcms-summary-list">
      <div>
        <dt>이름</dt>
        <dd><?= smartcms_h($user['name']) ?></dd>
      </div>
      <div>
        <dt>이메일</dt>
        <dd><?= smartcms_h($user['email']) ?></dd>
      </div>
      <div>
        <dt>권한 레벨</dt>
        <dd><?= smartcms_h($user['level']) ?></dd>
      </div>
      <div>
        <dt>역할</dt>
        <dd><?= smartcms_h($user['role']) ?></dd>
      </div>
    </dl>

    <div class="smartcms-actions">
      <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>">비밀번호 변경</a>
      <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
    </div>
  </section>
  <?= smartcms_site_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
