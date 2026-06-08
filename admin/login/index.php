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

$SMARTCMS_HEAD = ['title' => '관리자 로그인'];
require SMARTCMS_ROOT . '/head.php';
?>
<main class="form-signin w-100 m-auto text-center">
  <form method="post">
    <?= smartcms_csrf_input() ?>
    <i class="bi bi-bootstrap-fill mb-4 text-primary smartcms-signin-logo" aria-hidden="true"></i>
    <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>

    <div class="form-floating">
      <input class="form-control" id="email" name="email" type="email"
             value="<?= smartcms_h($email) ?>" placeholder="name@example.com" autocomplete="email" required>
      <label for="email">Email address</label>
    </div>
    <div class="form-floating">
      <input class="form-control" id="password" name="password" type="password"
             placeholder="Password" autocomplete="current-password" required>
      <label for="password">Password</label>
    </div>

    <div class="form-check text-start my-3">
      <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
      <label class="form-check-label" for="remember">Remember me</label>
    </div>

    <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
    <p class="mt-5 mb-3 text-body-secondary">&copy; 2017&ndash;2025</p>
  </form>
</main>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
