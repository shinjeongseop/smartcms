<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$user = smartcms_require_login();

smartcms_render_head([
    'title' => '마이페이지',
    'body_class' => 'smartcms-auth-page',
]);
?>
<main class="smartcms-panel smartcms-auth-panel">
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

  <p><a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a></p>
</main>
<?php smartcms_render_foot(); ?>
