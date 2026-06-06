<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$message = '';
$message_type = 'info';
$email = trim((string)($_POST['email'] ?? ''));
$next = (string)smartcms_config_value('admin_home_url', '/admin/users/');

if (smartcms_current_user()) {
    smartcms_redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result = smartcms_login($email, (string)($_POST['password'] ?? ''));
    $message = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';

    if ($result['ok']) {
        if (!smartcms_has_level(smartcms_setting_int('admin_level', (int)smartcms_config_value('admin_level', 8)), $result['user'])) {
            smartcms_logout();
            $message = '관리자 권한이 없는 계정입니다.';
            $message_type = 'error';
        } else {
            smartcms_redirect($next);
        }
    }
}

smartcms_render_head([
    'title' => '관리자 로그인',
    'body_class' => 'smartcms-auth-page',
]);
?>
<main class="card smartcms-panel smartcms-auth-panel">
  <h1 class="smartcms-title">관리자 로그인</h1>
  <p class="smartcms-text-muted">level 8 이상의 관리자 계정으로 접근할 수 있습니다.</p>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <form class="smartcms-grid" method="post">
    <?= smartcms_csrf_input() ?>
    <div class="smartcms-field">
      <label for="email">이메일</label>
      <input class="form-control smartcms-input" id="email" name="email" type="email" value="<?= smartcms_h($email) ?>" required>
    </div>
    <div class="smartcms-field">
      <label for="password">비밀번호</label>
      <input class="form-control smartcms-input" id="password" name="password" type="password" required>
    </div>
    <?= smartcms_button('관리자 로그인', 'submit') ?>
  </form>
</main>
<?php smartcms_render_foot(); ?>
