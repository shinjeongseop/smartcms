<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/components.php';

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

$SMARTCMS_HEAD = ['title' => '회원가입'];
require SMARTCMS_ROOT . '/head.php';
?>
<section class="auth-page-container flex-grow-1 d-flex align-items-center py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
        <article class="card border shadow-lg overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <header class="text-center mb-5">
              <a href="<?= smartcms_h(smartcms_base_url('/')) ?>" class="navbar-brand fs-2 fw-bold text-primary">smartcms<span class="text-dark">.</span></a>
              <p class="text-body-secondary mt-2 mb-0 fw-medium">새로운 시작, 회원가입을 환영합니다</p>
            </header>

            <?php if ($message !== ''): ?>
              <?php
                $alert_theme = $message_type === 'error' ? 'danger' : $message_type;
                $alert_icon = $alert_theme === 'danger' ? 'bi-exclamation-triangle-fill' : ($alert_theme === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill');
              ?>
              <aside class="alert alert-<?= smartcms_h($alert_theme) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
                <i class="bi <?= smartcms_h($alert_icon) ?> fs-5"></i>
                <div class="fw-medium small"><?= smartcms_h($message) ?></div>
              </aside>
            <?php endif; ?>

            <form class="d-grid gap-4" method="post" autocomplete="off">
              <?= smartcms_csrf_input() ?>
              <div>
                <label for="email" class="form-label fw-bold small text-dark">이메일 주소 <span class="text-primary">*</span></label>
                <input class="form-control py-2" id="email" name="email" type="email"
                       placeholder="name@example.com" value="<?= smartcms_h($form['email']) ?>" autocomplete="off" required>
              </div>
              <div>
                <label for="name" class="form-label fw-bold small text-dark">사용자 이름 <span class="text-primary">*</span></label>
                <input class="form-control py-2" id="name" name="name"
                       placeholder="실명 또는 닉네임" value="<?= smartcms_h($form['name']) ?>" autocomplete="off" required>
              </div>
              <div>
                <label for="company_name" class="form-label fw-bold small text-dark">회사명 <small class="fw-normal text-muted">(선택)</small></label>
                <input class="form-control py-2" id="company_name" name="company_name"
                       placeholder="소속된 조직 이름" value="<?= smartcms_h($form['company_name']) ?>" autocomplete="off">
              </div>
              <div>
                <label for="password" class="form-label fw-bold small text-dark">비밀번호 <span class="text-primary">*</span></label>
                <input class="form-control py-2" id="password" name="password" type="password"
                       placeholder="8자 이상의 비밀번호" minlength="8" autocomplete="new-password" required>
                <div class="form-text text-xs ps-2 pt-1 opacity-75">영문, 숫자, 특수문자 조합을 권장합니다.</div>
              </div>
              <div class="d-grid pt-2">
                <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm">회원가입 완료하기</button>
              </div>
            </form>

            <footer class="mt-5 text-center border-top pt-4">
              <p class="text-body-secondary small mb-0 fw-medium">
                이미 계정이 있으신가요? <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="fw-bold text-primary text-decoration-none">로그인</a>
              </p>
            </footer>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
