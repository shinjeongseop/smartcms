<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/components.php';

$message      = '';
$message_type = 'info';
$email        = trim((string)($_POST['email'] ?? ''));
$next         = (string)($_GET['next'] ?? '/member/mypage/');

if (smartcms_current_user()) {
    smartcms_redirect($next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result       = smartcms_login($email, (string)($_POST['password'] ?? ''));
    $message      = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';

    if ($result['ok']) {
        smartcms_redirect($next);
    }
}

$SMARTCMS_HEAD = ['title' => '로그인'];
require SMARTCMS_ROOT . '/head.php';
?>
<!-- [AUTH CONTAINER] 중앙 정렬 레이아웃 섹션 -->
<section class="auth-page-container flex-grow-1 d-flex align-items-center py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
        <article class="card border shadow-lg overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <header class="text-center mb-5">
              <a href="<?= smartcms_h(smartcms_base_url('/')) ?>" class="navbar-brand fs-2 fw-bold text-primary">smartcms<span class="text-dark">.</span></a>
              <p class="text-body-secondary mt-2 mb-0 fw-medium">커뮤니티에 오신 것을 환영합니다</p>
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

            <form class="d-grid gap-4" method="post" autocomplete="on">
              <?= smartcms_csrf_input() ?>
              <div>
                <label for="email" class="form-label fw-bold small text-dark">이메일 주소</label>
                <input class="form-control py-2" id="email" name="email" type="email"
                       placeholder="name@example.com" value="<?= smartcms_h($email) ?>" autocomplete="email" required>
              </div>
              <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label for="password" class="form-label fw-bold small text-dark mb-0">비밀번호</label>
                  <a href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>" class="text-xs text-decoration-none fw-bold">비밀번호 찾기</a>
                </div>
                <input class="form-control py-2" id="password" name="password" type="password"
                       placeholder="••••••••" autocomplete="current-password" required>
              </div>
              <div class="d-grid pt-2">
                <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm">로그인하기</button>
              </div>
            </form>

            <footer class="mt-5 text-center pt-4 border-top">
              <p class="text-body-secondary small mb-0 fw-medium">
                계정이 아직 없으신가요? <a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="fw-bold text-primary text-decoration-none">회원가입</a>
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
