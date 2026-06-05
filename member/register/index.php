<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

smartcms_require_page_view('member_register', '/member/register/', '회원가입', 0);

$message = '';
$message_type = 'info';
$form = [
    'email' => trim((string)($_POST['email'] ?? '')),
    'name' => trim((string)($_POST['name'] ?? '')),
    'company_name' => trim((string)($_POST['company_name'] ?? '')),
];

if (smartcms_current_user()) {
    smartcms_redirect('/member/mypage/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result = smartcms_register_user(
        $form['email'],
        (string)($_POST['password'] ?? ''),
        $form['name'],
        $form['company_name']
    );
    $message = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';
}

smartcms_render_head([
    'title' => '회원가입',
    'body_class' => 'smartcms-auth-page',
]);
?>
<main class="smartcms-panel smartcms-auth-panel">
  <h1 class="smartcms-title">회원가입</h1>
  <p class="smartcms-text-muted">기본 회원은 level <?= smartcms_h(smartcms_setting_int('default_member_level', (int)smartcms_config_value('default_member_level', 2))) ?> 권한으로 생성됩니다.</p>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <form class="smartcms-grid" method="post">
    <?= smartcms_csrf_input() ?>
    <div class="smartcms-field">
      <label for="email">이메일</label>
      <input class="smartcms-input" id="email" name="email" type="email" value="<?= smartcms_h($form['email']) ?>" required>
    </div>
    <div class="smartcms-field">
      <label for="name">이름</label>
      <input class="smartcms-input" id="name" name="name" value="<?= smartcms_h($form['name']) ?>" required>
    </div>
    <div class="smartcms-field">
      <label for="company_name">회사명</label>
      <input class="smartcms-input" id="company_name" name="company_name" value="<?= smartcms_h($form['company_name']) ?>">
    </div>
    <div class="smartcms-field">
      <label for="password">비밀번호</label>
      <input class="smartcms-input" id="password" name="password" type="password" minlength="8" required>
    </div>
    <?= smartcms_button('가입하기', 'submit') ?>
  </form>

  <p><a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인으로 이동</a></p>
</main>
<?php smartcms_render_foot(); ?>
