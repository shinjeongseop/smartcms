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
<div class="card border-0 shadow-sm overflow-hidden">
  <div class="card-body p-4 p-md-5">
    <div class="text-center mb-5">
      <a href="<?= smartcms_h(smartcms_base_url('/')) ?>" class="navbar-brand fs-2 fw-bold text-primary">smartcms<span class="text-dark">.</span></a>
      <p class="text-body-secondary mt-2 mb-0">새로운 시작, 회원가입을 환영합니다</p>
    </div>

    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>

    <form class="d-grid gap-4" method="post" autocomplete="off">
      <?= smartcms_csrf_input() ?>
      <div>
        <label for="email" class="form-label fw-bold">이메일 주소 <span class="text-primary">*</span></label>
        <input class="form-control form-control-lg bg-body border-0" id="email" name="email" type="email"
               placeholder="name@example.com" value="<?= smartcms_h($form['email']) ?>" autocomplete="off" required>
      </div>
      <div>
        <label for="name" class="form-label fw-bold">사용자 이름 <span class="text-primary">*</span></label>
        <input class="form-control form-control-lg bg-body border-0" id="name" name="name"
               placeholder="실명 또는 닉네임" value="<?= smartcms_h($form['name']) ?>" autocomplete="off" required>
      </div>
      <div>
        <label for="company_name" class="form-label fw-bold">회사명 <small class="fw-normal text-muted">(선택)</small></label>
        <input class="form-control form-control-lg bg-body border-0" id="company_name" name="company_name"
               placeholder="소속된 조직 이름" value="<?= smartcms_h($form['company_name']) ?>" autocomplete="off">
      </div>
      <div>
        <label for="password" class="form-label fw-bold">비밀번호 <span class="text-primary">*</span></label>
        <input class="form-control form-control-lg bg-body border-0" id="password" name="password" type="password"
               placeholder="8자 이상의 비밀번호" minlength="8" autocomplete="new-password" required>
        <div class="form-text text-xs ps-2 pt-1">영문, 숫자, 특수문자 조합을 권장합니다.</div>
      </div>
      <div class="d-grid pt-2">
        <?= smartcms_button('회원가입 완료하기', 'submit', 'btn-lg rounded-pill') ?>
      </div>
    </form>

    <div class="mt-5 text-center border-top pt-4">
      <p class="text-body-secondary small mb-0">이미 계정이 있으신가요? <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="fw-bold text-primary text-decoration-none">로그인</a></p>
    </div>
  </div>
</div>
<?= smartcms_auth_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
