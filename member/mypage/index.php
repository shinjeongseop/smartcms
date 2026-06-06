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
  <div class="sc-panel" style="max-width:560px;">
    <p class="sc-eyebrow">My Account</p>
    <h1 class="sc-section-title"><?= smartcms_h($user['name']) ?>님의 마이페이지</h1>

    <dl class="smartcms-summary-list mb-4">
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
        <dd>level <?= smartcms_h($user['level']) ?></dd>
      </div>
      <div>
        <dt>역할</dt>
        <dd><?= smartcms_h($user['role']) ?></dd>
      </div>
    </dl>

    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-primary rounded-pill px-4"
         href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>">비밀번호 변경</a>
      <a class="btn btn-outline-secondary rounded-pill px-4"
         href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
    </div>
  </div>

<?= smartcms_site_footer() ?>
<?php smartcms_render_foot(); ?>
