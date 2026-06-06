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
<div class="card border-0 shadow-sm">
  <div class="card-body p-4 p-md-5">
    <p class="text-uppercase text-muted small fw-semibold mb-1">Welcome back</p>
    <h1 class="h3 fw-bold mb-2">로그인</h1>
    <p class="text-body-secondary mb-4">smartcms 커뮤니티에 로그인하세요.</p>

    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>

    <form class="d-grid gap-3" method="post">
      <?= smartcms_csrf_input() ?>
      <div>
        <label for="email" class="form-label">이메일</label>
        <input class="form-control form-control-lg" id="email" name="email" type="email"
               value="<?= smartcms_h($email) ?>" autocomplete="email" required>
      </div>
      <div>
        <label for="password" class="form-label">비밀번호</label>
        <input class="form-control form-control-lg" id="password" name="password" type="password"
               autocomplete="current-password" required>
      </div>
      <div class="d-grid">
        <?= smartcms_button('로그인', 'submit', 'w-100') ?>
      </div>
    </form>

    <p class="text-center mt-3 mb-0">
      계정이 없으신가요?
      <a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="fw-bold">회원가입</a>
    </p>
  </div>
</div>
<?php smartcms_render_foot(); ?>
