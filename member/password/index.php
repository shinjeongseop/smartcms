<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/components.php';

$user = smartcms_require_login();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $new_password = (string)($_POST['new_password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($new_password !== $confirm_password) {
        $message = '새 비밀번호 확인이 일치하지 않습니다.';
        $message_type = 'error';
    } else {
        $result = smartcms_change_password((int)$user['id'], (string)($_POST['current_password'] ?? ''), $new_password);
        $message = $result['message'];
        $message_type = $result['ok'] ? 'success' : 'error';
    }
}

$SMARTCMS_HEAD = ['title' => '비밀번호 변경', 'body_class' => 'bg-light'];
require SMARTCMS_ROOT . '/head.php';
?>

<main class="container-fluid container-xxl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xl-6 col-xxl-5">
      <article class="card border shadow-lg overflow-hidden">
        <header class="card-header bg-dark text-white p-4 p-md-5">
          <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary p-3 rounded-3 shadow-sm"><i class="bi bi-shield-lock-fill fs-4"></i></div>
            <div>
              <p class="text-uppercase small fw-bold text-white-50 mb-1">Security</p>
              <h1 class="h3 fw-bold mb-0">비밀번호 변경</h1>
            </div>
          </div>
        </header>

        <div class="card-body p-4 p-md-5">
          <p class="text-body-secondary mb-4 fw-medium">
            <strong><?= smartcms_h($user['email']) ?></strong> 계정의 보안을 위해<br class="d-none d-md-block"> 주기적으로 비밀번호를 변경하는 것을 권장합니다.
          </p>

          <?php if ($message !== ''): ?>
            <?php
              $alert_theme = $message_type === 'error' ? 'danger' : $message_type;
              $alert_icon = $alert_theme === 'danger' ? 'bi-exclamation-triangle-fill' : ($alert_theme === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill');
            ?>
            <aside class="alert alert-<?= smartcms_h($alert_theme) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
              <i class="bi <?= smartcms_h($alert_icon) ?> fs-5"></i>
              <div class="fw-bold small"><?= smartcms_h($message) ?></div>
            </aside>
          <?php endif; ?>

          <form class="d-grid gap-4" method="post">
            <?= smartcms_csrf_input() ?>
            <div>
              <label for="current_password" class="form-label fw-bold small text-dark">현재 비밀번호</label>
              <input class="form-control py-2.5" id="current_password" name="current_password" type="password" required placeholder="기존 비밀번호를 입력하세요.">
            </div>
            <div>
              <label for="new_password" class="form-label fw-bold small text-dark">새 비밀번호</label>
              <input class="form-control py-2.5" id="new_password" name="new_password" type="password" minlength="8" required placeholder="8자 이상의 새 비밀번호">
              <div class="form-text small opacity-75 mt-1">영문, 숫자, 특수문자 조합을 권장합니다.</div>
            </div>
            <div>
              <label for="confirm_password" class="form-label fw-bold small text-dark">새 비밀번호 확인</label>
              <input class="form-control py-2.5" id="confirm_password" name="confirm_password" type="password" minlength="8" required placeholder="새 비밀번호를 한 번 더 입력하세요.">
            </div>
            
            <div class="d-grid pt-3">
              <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm">비밀번호 변경 완료</button>
            </div>
          </form>

          <footer class="mt-5 text-center border-top pt-4">
            <a class="btn btn-link link-secondary text-decoration-none small fw-bold shadow-none" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">
              <i class="bi bi-arrow-left me-1"></i>마이페이지로 돌아가기
            </a>
          </footer>
        </div>
      </article>
    </div>
  </div>
</main>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
