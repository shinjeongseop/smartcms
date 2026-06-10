<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
require_once __DIR__ . '/../../common/ui/components.php';

$message      = '';
$message_type = 'info';
$email        = trim((string)($_POST['email'] ?? ''));
$next         = (string)smartcms_config_value('admin_home_url', '/admin/users/');

if (smartcms_current_user()) {
    smartcms_redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result       = smartcms_login($email, (string)($_POST['password'] ?? ''));
    $message      = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';

    if ($result['ok']) {
        $admin_level = smartcms_setting_int('admin_level', (int)smartcms_config_value('admin_level', 8));
        if (!smartcms_has_level($admin_level, $result['user'])) {
            smartcms_logout();
            $message      = '관리자 권한이 없는 계정입니다.';
            $message_type = 'error';
        } else {
            smartcms_redirect($next);
        }
    }
}

$SMARTCMS_HEAD = ['title' => '관리자 로그인', 'body_class' => 'smartcms-admin-auth'];
require SMARTCMS_ROOT . '/head.php';
?>
<main class="form-signin w-100 m-auto">
  <div class="text-center mb-4">
    <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none fw-bold text-dark fs-2 mb-4">
      <span class="badge bg-primary p-2 lh-1 rounded-3 shadow-sm"><i class="bi bi-app-indicator fs-3 text-white"></i></span>
      <span>smartcms</span>
    </a>
    <h1 class="h4 fw-bold mb-2">Welcome to Admin Panel! 👋</h1>
    <p class="text-secondary small">Please sign-in to your account and start the adventure</p>
  </div>

  <?php if ($message !== ''): ?>
    <div class="alert alert-<?= $message_type === 'error' ? 'danger' : ( $message_type === 'success' ? 'success' : 'info' ) ?> d-flex align-items-start gap-2 mb-4" role="alert">
      <i class="bi bi-info-circle-fill mt-1"></i>
      <div><?= smartcms_h($message) ?></div>
    </div>
  <?php endif; ?>

  <form method="post">
    <?= smartcms_csrf_input() ?>

    <div class="mb-3">
      <label for="email" class="form-label small fw-bold text-uppercase opacity-75">Email address</label>
      <input class="form-control fs-6" id="email" name="email" type="email"
             value="<?= smartcms_h($email) ?>" placeholder="Enter your email" autocomplete="email" required>
    </div>

    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <label for="password" class="form-label small fw-bold text-uppercase opacity-75 mb-0">Password</label>
        <a href="#" class="text-primary small text-decoration-none">Forgot Password?</a>
      </div>
      <input class="form-control fs-6" id="password" name="password" type="password"
             placeholder="············" autocomplete="current-password" required>
    </div>

    <div class="form-check text-start mb-4">
      <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
      <label class="form-check-label small" for="remember">Remember me</label>
    </div>

    <button class="btn btn-primary w-100 py-2 fw-bold shadow-sm" type="submit">LOGIN</button>

    <div class="text-center mt-4">
      <p class="mb-0 small text-secondary">New on our platform? <a href="/member/register/" class="text-primary text-decoration-none">Create an account</a></p>
    </div>
  </form>
</main>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
