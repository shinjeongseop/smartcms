<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../common/ui/navigation.php';

$message      = '';
$message_type = 'info';
$email        = trim((string)($_POST['email'] ?? ''));
$next         = (string)($_GET['next'] ?? '/member/mypage/');

if (smartcms_current_user()) {
    smartcms_redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result       = smartcms_login($email, (string)($_POST['password'] ?? ''));
    $message      = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';

    if ($result['ok']) {
        smartcms_redirect($next);
    }
}

smartcms_render_head(['title' => '로그인']);
?>
<div class="sc-auth-wrap">
  <div class="sc-auth-box">
    <p class="sc-eyebrow">Welcome back</p>
    <h1 class="sc-title sc-auth-title">로그인</h1>
    <p class="sc-subtitle">smartcms 커뮤니티에 로그인하세요.</p>

    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>

    <form class="sc-form-grid" method="post">
      <?= smartcms_csrf_input() ?>
      <div class="sc-field">
        <label for="email">이메일</label>
        <input class="form-control sc-input" id="email" name="email" type="email"
               value="<?= smartcms_h($email) ?>" autocomplete="email" required>
      </div>
      <div class="sc-field">
        <label for="password">비밀번호</label>
        <input class="form-control sc-input" id="password" name="password" type="password"
               autocomplete="current-password" required>
      </div>
      <div class="d-grid mt-2">
        <?= smartcms_button('로그인', 'submit', 'w-100') ?>
      </div>
    </form>

    <p class="text-center mt-3 mb-0 sc-auth-linkline">
      계정이 없으신가요?
      <a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="fw-bold">회원가입</a>
    </p>
  </div>
</div>
<?php smartcms_render_foot(); ?>
