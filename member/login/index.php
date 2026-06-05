<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$message = '';
$message_type = 'info';
$email = trim((string)($_POST['email'] ?? ''));
$next = (string)($_GET['next'] ?? '/member/mypage/');

if (smartcms_current_user()) {
    smartcms_redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = smartcms_login($email, (string)($_POST['password'] ?? ''));
    $message = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';

    if ($result['ok']) {
        smartcms_redirect($next);
    }
}

smartcms_render_head([
    'title' => '로그인',
    'body_class' => 'smartcms-auth-page',
]);
?>
<main class="smartcms-panel smartcms-auth-panel">
  <h1 class="smartcms-title">로그인</h1>
  <p class="smartcms-text-muted">smartcms 관리자와 회원 기능을 사용하려면 로그인하세요.</p>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <form class="smartcms-grid" method="post">
    <div class="smartcms-field">
      <label for="email">이메일</label>
      <input class="smartcms-input" id="email" name="email" type="email" value="<?= smartcms_h($email) ?>" required>
    </div>
    <div class="smartcms-field">
      <label for="password">비밀번호</label>
      <input class="smartcms-input" id="password" name="password" type="password" required>
    </div>
    <?= smartcms_button('로그인', 'submit') ?>
  </form>
</main>
<?php smartcms_render_foot(); ?>
