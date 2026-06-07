<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../foot.php';

smartcms_require_page_view('member_register', '/member/register/', '회원가입', 0);

$message      = '';
$message_type = 'info';
$form = [
    'email'        => trim((string)($_POST['email']        ?? '')),
    'name'         => trim((string)($_POST['name']         ?? '')),
    'company_name' => trim((string)($_POST['company_name'] ?? '')),
];

if (smartcms_current_user()) {
    smartcms_redirect('/member/mypage/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result       = smartcms_register_user($form['email'], (string)($_POST['password'] ?? ''), $form['name'], $form['company_name']);
    $message      = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';
}

$default_level = smartcms_setting_int('default_member_level', (int)smartcms_config_value('default_member_level', 2));

$SMARTCMS_HEAD = ['title' => '회원가입'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_auth_header();
?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-4 p-md-5">
    <p class="text-uppercase text-muted small fw-semibold mb-1">Join us</p>
    <h1 class="h3 fw-bold mb-2">회원가입</h1>
    <p class="text-body-secondary mb-4">가입 후 level <?= smartcms_h($default_level) ?> 권한이 부여됩니다.</p>

    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>

    <form class="d-grid gap-3" method="post" autocomplete="off">
      <?= smartcms_csrf_input() ?>
      <div>
        <label for="email" class="form-label">이메일 <span class="text-danger">*</span></label>
        <input class="form-control form-control-lg" id="email" name="email" type="email"
               value="<?= smartcms_h($form['email']) ?>" autocomplete="off" required>
      </div>
      <div>
        <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
        <input class="form-control form-control-lg" id="name" name="name"
               value="<?= smartcms_h($form['name']) ?>" autocomplete="off" required>
      </div>
      <div>
        <label for="company_name" class="form-label">회사명</label>
        <input class="form-control form-control-lg" id="company_name" name="company_name"
               value="<?= smartcms_h($form['company_name']) ?>" autocomplete="off">
      </div>
      <div>
        <label for="password" class="form-label">비밀번호 <span class="text-danger">*</span></label>
        <input class="form-control form-control-lg" id="password" name="password" type="password"
               minlength="8" autocomplete="new-password" required>
        <div class="form-text">8자 이상 입력하세요.</div>
      </div>
      <div class="d-grid">
        <?= smartcms_button('가입하기', 'submit', 'w-100') ?>
      </div>
    </form>

    <p class="text-center mt-3 mb-0">
      이미 계정이 있으신가요?
      <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="fw-bold">로그인</a>
    </p>
  </div>
</div>
<?= smartcms_auth_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
