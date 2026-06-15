<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';

$message = '';
$message_type = 'info';
$token = trim((string)($_GET['token'] ?? ($_POST['token'] ?? '')));
$token_row = $token !== '' ? smartcms_password_reset_token_row($token) : null;

if (smartcms_current_user()) {
    smartcms_redirect('/member/password/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $new_password = (string)($_POST['new_password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($new_password !== $confirm_password) {
        $message = '새 비밀번호와 확인 값이 일치하지 않습니다.';
        $message_type = 'error';
    } else {
        $result = smartcms_password_reset_complete($token, $new_password);
        if ($result['ok']) {
            smartcms_flash_set('message', $result['message']);
            smartcms_flash_set('message_type', 'success');
            smartcms_redirect('/member/login/');
        }

        $message = $result['message'];
        $message_type = 'error';
    }
}

$SMARTCMS_HEAD = ['title' => '비밀번호 재설정'];
require SMARTCMS_ROOT . '/head.php';
?>

<section class="container-fluid container-xxl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xl-6 col-xxl-5">
      <?php if ($message !== ''): ?>
        <?php $alert_theme = $message_type === 'error' ? 'danger' : 'info'; ?>
        <aside class="alert alert-<?= smartcms_h($alert_theme) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
          <i class="bi <?= $alert_theme === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill' ?> fs-5"></i>
          <div class="fw-medium small"><?= smartcms_h($message) ?></div>
        </aside>
      <?php endif; ?>

      <article class="card border shadow-lg overflow-hidden">
        <header class="card-header bg-primary text-white p-4 p-md-5">
          <div class="d-flex align-items-center gap-4">
            <div class="badge bg-white text-primary p-3 rounded-3 shadow-sm">
              <i class="bi bi-shield-lock-fill fs-4"></i>
            </div>
            <div>
              <p class="text-uppercase small fw-bold text-white-50 mb-1">Account Security</p>
              <h1 class="h3 fw-bold mb-0">비밀번호 재설정</h1>
            </div>
          </div>
        </header>

        <div class="card-body p-4 p-md-5">
          <?php if (!$token_row): ?>
            <p class="text-body-secondary mb-4 fw-medium">
              유효하지 않거나 만료된 재설정 링크입니다. 다시 요청해 주세요.
            </p>
            <div class="d-flex gap-2">
              <a class="btn btn-primary px-4 py-2 fw-bold shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/forgot/')) ?>">재설정 요청</a>
              <a class="btn btn-light border text-primary px-4 py-2 fw-bold shadow-none" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a>
            </div>
          <?php else: ?>
            <p class="text-body-secondary mb-4 fw-medium">
              <strong><?= smartcms_h($token_row['email']) ?></strong> 계정의 새 비밀번호를 입력해 주세요.
            </p>

            <form class="d-grid gap-4" method="post" autocomplete="off">
              <?= smartcms_csrf_input() ?>
              <input type="hidden" name="token" value="<?= smartcms_h($token) ?>">
              <div>
                <label for="new_password" class="form-label fw-bold small text-dark">새 비밀번호</label>
                <input class="form-control py-2" id="new_password" name="new_password" type="password" minlength="8" required placeholder="8자 이상 새 비밀번호">
              </div>
              <div>
                <label for="confirm_password" class="form-label fw-bold small text-dark">새 비밀번호 확인</label>
                <input class="form-control py-2" id="confirm_password" name="confirm_password" type="password" minlength="8" required placeholder="비밀번호를 다시 입력하세요">
              </div>
              <div class="d-grid pt-2">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">비밀번호 변경하기</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </article>
    </div>
  </div>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
