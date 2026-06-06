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
<div class="sc-auth-wrap">
  <div class="sc-auth-box">
    <p class="sc-eyebrow">Admin</p>
    <h1 class="sc-section-title" style="font-size:26px;margin-bottom:4px;">관리자 로그인</h1>
    <p class="sc-muted mb-4" style="font-size:14px;">level 8 이상의 계정으로 접근할 수 있습니다.</p>

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
      <div class="d-grid mt-1">
        <?= smartcms_button('관리자 로그인', 'submit', 'w-100') ?>
      </div>
    </form>

    <p class="text-center mt-3 mb-0" style="font-size:13px;">
      <a href="<?= smartcms_h(smartcms_base_url('/')) ?>" class="text-muted">← 사이트 홈으로</a>
    </p>
  </div>
</div>
<?php smartcms_render_foot(); ?>
