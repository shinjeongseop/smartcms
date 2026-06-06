<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
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

smartcms_render_head(['title' => '관리자 로그인']);
?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-4 p-md-5">
    <p class="text-uppercase text-muted small fw-semibold mb-1">Admin</p>
    <h1 class="h3 fw-bold mb-2">관리자 로그인</h1>
    <p class="text-body-secondary mb-4">level 8 이상의 계정으로 접근할 수 있습니다.</p>

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
        <?= smartcms_button('관리자 로그인', 'submit', 'w-100') ?>
      </div>
    </form>

    <p class="text-center mt-3 mb-0 small">
      <a href="<?= smartcms_h(smartcms_base_url('/')) ?>" class="text-muted">← 사이트 홈으로</a>
    </p>
  </div>
</div>
<?php smartcms_render_foot(); ?>
