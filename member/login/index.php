<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../foot.php';

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
<main class="bg-body flex-grow-1 d-flex align-items-center py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5 col-xl-4">
        <div class="card border-0 shadow-sm overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="text-center mb-5">
              <a href="<?= smartcms_h(smartcms_base_url('/')) ?>" class="navbar-brand fs-2 fw-bold text-primary">smartcms<span class="text-dark">.</span></a>
              <p class="text-body-secondary mt-2 mb-0">커뮤니티에 오신 것을 환영합니다</p>
            </div>

            <?php if ($message !== ''): ?>
              <?= smartcms_alert($message, $message_type) ?>
            <?php endif; ?>

            <form class="d-grid gap-4" method="post">
              <?= smartcms_csrf_input() ?>
              <div>
                <label for="email" class="form-label fw-bold">이메일 주소</label>
                <input class="form-control bg-body border-0" id="email" name="email" type="email"
                       placeholder="name@example.com" value="<?= smartcms_h($email) ?>" autocomplete="email" required>
              </div>
              <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label for="password" class="form-label fw-bold mb-0">비밀번호</label>
                  <a href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>" class="small text-decoration-none">비밀번호 찾기</a>
                </div>
                <input class="form-control bg-body border-0" id="password" name="password" type="password"
                       placeholder="••••••••" autocomplete="current-password" required>
              </div>
              <div class="d-grid pt-2">
                <?= smartcms_button('로그인하기', 'submit', 'rounded-pill') ?>
              </div>
            </form>

            <div class="mt-5 text-center">
              <p class="text-body-secondary small mb-0">계정이 아직 없으신가요? <a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="fw-bold text-primary text-decoration-none">회원가입</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php
$SMARTCMS_FOOT = [];
..
?>
