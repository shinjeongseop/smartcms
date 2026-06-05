<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$user = smartcms_require_login();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $new_password = (string)($_POST['new_password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($new_password !== $confirm_password) {
        $message = '새 비밀번호 확인이 일치하지 않습니다.';
        $message_type = 'error';
    } else {
        $result = smartcms_change_password((int)$user['id'], (string)($_POST['current_password'] ?? ''), $new_password);
        $message = $result['message'];
        $message_type = $result['ok'] ? 'success' : 'error';
    }
}

smartcms_render_head([
    'title' => '비밀번호 변경',
    'body_class' => 'smartcms-auth-page',
]);
?>
<main class="smartcms-panel smartcms-auth-panel">
  <h1 class="smartcms-title">비밀번호 변경</h1>
  <p class="smartcms-text-muted"><?= smartcms_h($user['email']) ?> 계정의 비밀번호를 변경합니다.</p>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <form class="smartcms-grid" method="post">
    <?= smartcms_csrf_input() ?>
    <div class="smartcms-field">
      <label for="current_password">현재 비밀번호</label>
      <input class="smartcms-input" id="current_password" name="current_password" type="password" required>
    </div>
    <div class="smartcms-field">
      <label for="new_password">새 비밀번호</label>
      <input class="smartcms-input" id="new_password" name="new_password" type="password" minlength="8" required>
    </div>
    <div class="smartcms-field">
      <label for="confirm_password">새 비밀번호 확인</label>
      <input class="smartcms-input" id="confirm_password" name="confirm_password" type="password" minlength="8" required>
    </div>
    <?= smartcms_button('비밀번호 변경', 'submit') ?>
  </form>

  <p><a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지로 이동</a></p>
</main>
<?php smartcms_render_foot(); ?>
