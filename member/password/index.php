<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
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

$SMARTCMS_HEAD = ['title' => '비밀번호 변경', 'body_class' => 'bg-body'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_site_header('');
?>

<main class="container-fluid container-xxl py-4 py-lg-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xl-7">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
          <p class="text-uppercase text-muted small fw-semibold mb-1">Account</p>
          <h1 class="h3 fw-bold mb-2">비밀번호 변경</h1>
          <p class="text-body-secondary mb-4"><?= smartcms_h($user['email']) ?> 계정의 비밀번호를 변경합니다.</p>

          <?php if ($message !== ''): ?>
            <?= smartcms_alert($message, $message_type) ?>
          <?php endif; ?>

          <form class="d-grid gap-3" method="post">
            <?= smartcms_csrf_input() ?>
            <div>
              <label for="current_password" class="form-label">현재 비밀번호</label>
              <input class="form-control" id="current_password" name="current_password" type="password" required>
            </div>
            <div>
              <label for="new_password" class="form-label">새 비밀번호</label>
              <input class="form-control" id="new_password" name="new_password" type="password" minlength="8" required>
            </div>
            <div>
              <label for="confirm_password" class="form-label">새 비밀번호 확인</label>
              <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="8" required>
            </div>
            <?= smartcms_button('비밀번호 변경', 'submit') ?>
          </form>

          <div class="mt-3">
            <a class="btn btn-outline-secondary px-4" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지로 이동</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?= smartcms_site_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
