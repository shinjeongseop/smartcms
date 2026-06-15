<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/components.php';

$message      = '';
$message_type = 'info';
$flash_message = smartcms_flash_get('message', '');
$flash_type = (string)smartcms_flash_get('message_type', 'info');
$email        = trim((string)(smartcms_flash_get('email', '') ?: ($_POST['email'] ?? '')));
$next         = smartcms_member_login_next_target();

if (smartcms_current_user()) {
    smartcms_redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result       = smartcms_login($email, (string)($_POST['password'] ?? ''));
    if ($result['ok']) {
        smartcms_redirect($next);
    }

    smartcms_flash_set('message', $result['message']);
    smartcms_flash_set('message_type', 'error');
    smartcms_flash_set('email', $email);
    smartcms_redirect('/member/login/?next=' . rawurlencode($next));
}

$message = $flash_message !== '' ? $flash_message : $message;
$message_type = $flash_message !== '' ? $flash_type : $message_type;

$SMARTCMS_HEAD = ['title' => '로그인'];
require SMARTCMS_ROOT . '/head.php';
?>
<section class="container-fluid container-xxl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xl-6 col-xxl-5">
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

      <article class="card border shadow-lg overflow-hidden">
        <header class="card-header bg-primary text-white p-4 p-md-5">
          <div class="d-flex align-items-center gap-4">
            <div class="badge bg-white text-primary p-3 rounded-3 shadow-sm">
              <i class="bi bi-person-check-fill fs-4"></i>
            </div>
            <div>
              <p class="text-uppercase small fw-bold text-white-50 mb-1">Account Access</p>
              <h1 class="h3 fw-bold mb-0">로그인</h1>
            </div>
          </div>
        </header>

        <div class="card-body p-4 p-md-5">
          <p class="text-body-secondary mb-4 fw-medium">
            기존 계정으로 로그인하여 게시판, 마이페이지, 회원 기능을 계속 이용하세요.
          </p>

          <form class="d-grid gap-4" method="post" autocomplete="on">
            <?= smartcms_csrf_input() ?>
            <input type="hidden" name="next" value="<?= smartcms_h($next) ?>">
            <div>
              <label for="email" class="form-label fw-bold small text-dark">이메일 주소</label>
              <input class="form-control py-2" id="email" name="email" type="email"
                     placeholder="name@example.com" value="<?= smartcms_h($email) ?>" autocomplete="email" required>
            </div>
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="password" class="form-label fw-bold small text-dark mb-0">비밀번호</label>
                <a href="<?= smartcms_h(smartcms_base_url('/member/forgot/')) ?>" class="small text-decoration-none fw-bold">비밀번호 찾기</a>
              </div>
              <input class="form-control py-2" id="password" name="password" type="password"
                     placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <div class="d-grid pt-2">
              <button type="submit" class="btn btn-primary rounded-2 px-4 py-2 fw-bold shadow-sm">로그인하기</button>
            </div>
          </form>

          <footer class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-5 pt-4 border-top">
            <p class="text-body-secondary small mb-0 fw-medium">
              계정이 아직 없으신가요?
            </p>
            <a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="btn btn-light border text-primary rounded-2 px-4 py-2 fw-bold shadow-none">회원가입</a>
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
