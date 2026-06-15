<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';

$message = '';
$message_type = 'info';
$email = trim((string)($_POST['email'] ?? ''));

if (smartcms_current_user()) {
    smartcms_redirect('/member/password/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result = smartcms_password_reset_request($email);
    $message = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';
}

$SMARTCMS_HEAD = ['title' => '비밀번호 재설정'];
require SMARTCMS_ROOT . '/head.php';
?>

<section class="container-fluid container-xxl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xl-6 col-xxl-5">
      <?php if ($message !== ''): ?>
        <?php $alert_theme = $message_type === 'error' ? 'danger' : ($message_type === 'success' ? 'success' : 'info'); ?>
        <aside class="alert alert-<?= smartcms_h($alert_theme) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
          <i class="bi <?= $alert_theme === 'danger' ? 'bi-exclamation-triangle-fill' : ($alert_theme === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill') ?> fs-5"></i>
          <div class="fw-medium small"><?= smartcms_h($message) ?></div>
        </aside>
      <?php endif; ?>

      <article class="card border shadow-lg overflow-hidden">
        <header class="card-header bg-primary text-white p-4 p-md-5">
          <div class="d-flex align-items-center gap-4">
            <div class="badge bg-white text-primary p-3 rounded-3 shadow-sm">
              <i class="bi bi-key-fill fs-4"></i>
            </div>
            <div>
              <p class="text-uppercase small fw-bold text-white-50 mb-1">Account Help</p>
              <h1 class="h3 fw-bold mb-0">비밀번호 재설정</h1>
            </div>
          </div>
        </header>

        <div class="card-body p-4 p-md-5">
          <p class="text-body-secondary mb-4 fw-medium">
            가입 이메일을 입력하면 비밀번호 재설정 메일을 보냅니다. 메일의 링크로 새 비밀번호를 설정해 주세요.
          </p>

          <form class="d-grid gap-4" method="post" autocomplete="on">
            <?= smartcms_csrf_input() ?>
            <div>
              <label for="email" class="form-label fw-bold small text-dark">가입 이메일</label>
              <input class="form-control py-2" id="email" name="email" type="email" value="<?= smartcms_h($email) ?>" placeholder="name@example.com" autocomplete="email" required>
            </div>
            <div class="d-grid pt-2">
              <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">초기화 요청하기</button>
            </div>
          </form>

          <footer class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-5 pt-4 border-top">
            <p class="text-body-secondary small mb-0 fw-medium">비밀번호를 기억하셨나요?</p>
            <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="btn btn-light border text-primary px-4 py-2 fw-bold shadow-none">로그인으로 돌아가기</a>
          </footer>
        </div>
      </article>
    </div>
  </div>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
