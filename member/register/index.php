<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../common/ui/navigation.php';

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

smartcms_render_head(['title' => '회원가입']);
?>
<div class="sc-auth-wrap">
  <div class="sc-auth-box">
    <p class="sc-eyebrow">Join us</p>
    <h1 class="sc-title" style="font-size:28px;">회원가입</h1>
    <p class="sc-subtitle">가입 후 level <?= smartcms_h($default_level) ?> 권한이 부여됩니다.</p>

    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>

    <form class="sc-form-grid" method="post" autocomplete="off">
      <?= smartcms_csrf_input() ?>
      <div class="sc-field">
        <label for="email">이메일 <span class="text-danger">*</span></label>
        <input class="sc-input" id="email" name="email" type="email"
               value="<?= smartcms_h($form['email']) ?>" autocomplete="off" required>
      </div>
      <div class="sc-field">
        <label for="name">이름 <span class="text-danger">*</span></label>
        <input class="sc-input" id="name" name="name"
               value="<?= smartcms_h($form['name']) ?>" autocomplete="off" required>
      </div>
      <div class="sc-field">
        <label for="company_name">회사명</label>
        <input class="sc-input" id="company_name" name="company_name"
               value="<?= smartcms_h($form['company_name']) ?>" autocomplete="off">
      </div>
      <div class="sc-field">
        <label for="password">비밀번호 <span class="text-danger">*</span></label>
        <input class="sc-input" id="password" name="password" type="password"
               minlength="8" autocomplete="new-password" required>
        <p class="sc-field-hint">8자 이상 입력하세요.</p>
      </div>
      <div class="d-grid mt-2">
        <?= smartcms_button('가입하기', 'submit', 'w-100') ?>
      </div>
    </form>

    <p class="text-center mt-3 mb-0" style="font-size:13px;">
      이미 계정이 있으신가요?
      <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="fw-bold">로그인</a>
    </p>
  </div>
</div>
<?php smartcms_render_foot(); ?>
