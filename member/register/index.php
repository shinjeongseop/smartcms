<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/components.php';

smartcms_require_page_view('member_register', '/member/register/', '회원가입', 0);

$message      = '';
$message_type = 'info';
$form = [
    'email'        => trim((string)smartcms_flash_get('form.email', ($_POST['email'] ?? ''))),
    'name'         => trim((string)smartcms_flash_get('form.name', ($_POST['name'] ?? ''))),
    'nickname'     => trim((string)smartcms_flash_get('form.nickname', ($_POST['nickname'] ?? ''))),
    'company_name' => trim((string)smartcms_flash_get('form.company_name', ($_POST['company_name'] ?? ''))),
];
$flash_message = smartcms_flash_get('message', '');
$flash_type = (string)smartcms_flash_get('message_type', 'info');

if (smartcms_current_user()) {
    smartcms_redirect('/member/mypage/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result = smartcms_register_user($form['email'], (string)($_POST['password'] ?? ''), $form['name'], $form['nickname'], $form['company_name']);
    if ($result['ok']) {
        smartcms_flash_set('message', $result['message']);
        smartcms_flash_set('message_type', 'success');
        smartcms_redirect('/member/login/');
    }

    smartcms_flash_set('message', $result['message']);
    smartcms_flash_set('message_type', 'error');
    smartcms_flash_set('form.email', $form['email']);
    smartcms_flash_set('form.name', $form['name']);
    smartcms_flash_set('form.nickname', $form['nickname']);
    smartcms_flash_set('form.company_name', $form['company_name']);
    smartcms_redirect('/member/register/');
}

$message = $flash_message !== '' ? $flash_message : $message;
$message_type = $flash_message !== '' ? $flash_type : $message_type;

$SMARTCMS_HEAD = ['title' => '회원가입'];
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
              <i class="bi bi-person-plus-fill fs-4"></i>
            </div>
            <div>
              <p class="text-uppercase small fw-bold text-white-50 mb-1">Create Account</p>
              <h1 class="h3 fw-bold mb-0">회원가입</h1>
            </div>
          </div>
        </header>

        <div class="card-body p-4 p-md-5">
          <p class="text-body-secondary mb-4 fw-medium">
            간단한 정보만 입력하면 바로 회원가입을 완료할 수 있습니다.
          </p>

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
              <label for="nickname" class="form-label fw-bold small text-dark">닉네임 <small class="fw-normal text-muted">(선택)</small></label>
              <input class="form-control py-2" id="nickname" name="nickname"
                     placeholder="프로필에 표시할 별명" value="<?= smartcms_h($form['nickname']) ?>" autocomplete="off">
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
              <div class="form-text small ps-2 pt-1 opacity-75">영문, 숫자, 특수문자 조합을 권장합니다.</div>
            </div>
            <div class="d-grid pt-2">
              <button type="submit" class="btn btn-primary rounded-2 px-4 py-2 fw-bold shadow-sm">회원가입 완료하기</button>
            </div>
          </form>

          <footer class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-5 pt-4 border-top">
            <p class="text-body-secondary small mb-0 fw-medium">
              이미 계정이 있으신가요?
            </p>
            <a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="btn btn-light border text-primary rounded-2 px-4 py-2 fw-bold shadow-none">로그인</a>
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
