<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/components.php';

$user = smartcms_require_login();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();

    $upload = $_FILES['avatar_file'] ?? [];
    $upload = is_array($upload) ? $upload : [];

    $result = smartcms_store_user_avatar_upload(
        (int)$user['id'],
        $upload,
        (string)($user['avatar_path'] ?? '')
    );

    $message = (string)($result['message'] ?? '');
    $message_type = !empty($result['ok']) ? 'success' : 'error';

    if (!empty($result['ok'])) {
        $user = smartcms_current_user() ?? $user;
    }
}

$SMARTCMS_HEAD = ['title' => '마이페이지'];
require SMARTCMS_ROOT . '/head.php';
?>
<section class="container-fluid container-xxl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xxl-6">
      <?php if ($message !== ''): ?>
        <aside class="alert alert-<?= $message_type === 'error' ? 'danger' : 'success' ?> d-flex align-items-center gap-2 mb-4" role="alert">
          <i class="bi bi-info-circle-fill fs-5"></i>
          <div class="fw-medium"><?= smartcms_h($message) ?></div>
        </aside>
      <?php endif; ?>

      <article class="card border shadow-lg overflow-hidden">
        <!-- 프로필 헤더 -->
        <header class="card-header bg-primary text-white p-4 p-md-5">
          <div class="d-flex align-items-center gap-4">
            <?= smartcms_user_avatar_markup($user, 'sc-avatar-72', 'fs-2 fw-bold') ?>
            <div>
              <p class="text-uppercase small fw-bold text-white-50 mb-1">Personal Account</p>
              <h1 class="h3 fw-bold mb-0"><?= smartcms_h($user['name']) ?>님의 프로필</h1>
            </div>
          </div>
        </header>

        <div class="card-body p-4 p-md-5">
          <section class="mb-5">
            <h2 class="h6 fw-bold text-uppercase text-primary mb-4">아바타 변경</h2>
            <form method="post" enctype="multipart/form-data" class="card border bg-light-subtle shadow-none">
              <div class="card-body p-4">
                <?= smartcms_csrf_input() ?>
                <div class="row g-3 align-items-end">
                  <div class="col-12 col-md-8">
                    <label for="avatar_file" class="form-label fw-bold text-dark mb-2">아바타 이미지</label>
                    <input class="form-control" type="file" name="avatar_file" id="avatar_file" accept="image/jpeg,image/png,image/gif,image/webp">
                  </div>
                  <div class="col-12 col-md-4 d-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm w-100">
                      <i class="bi bi-upload me-2"></i>아바타 저장
                    </button>
                  </div>
                  <div class="col-12">
                    <div class="form-text small text-secondary mb-0">JPG, PNG, GIF, WEBP 형식만 가능하며 최대 2MB까지 업로드할 수 있습니다.</div>
                  </div>
                </div>
              </div>
            </form>
          </section>

          <section class="mb-5">
            <h2 class="h6 fw-bold text-uppercase text-primary mb-4">계정 상세 정보</h2>
            <div class="row g-4">
              <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border-0">
                  <dt class="text-secondary small fw-bold text-uppercase mb-1">이름</dt>
                  <dd class="fw-bold text-dark mb-0 fs-5"><?= smartcms_h($user['name']) ?></dd>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border-0">
                  <dt class="text-secondary small fw-bold text-uppercase mb-1">이메일 주소</dt>
                  <dd class="fw-bold text-dark mb-0 fs-5"><?= smartcms_h($user['email']) ?></dd>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border-0">
                  <dt class="text-secondary small fw-bold text-uppercase mb-1">권한 등급</dt>
                  <dd class="fw-bold text-primary mb-0 fs-5">Level <?= smartcms_h($user['level']) ?></dd>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border-0">
                  <dt class="text-secondary small fw-bold text-uppercase mb-1">회원 역할</dt>
                  <dd class="fw-bold text-dark mb-0 fs-5 text-capitalize"><?= smartcms_h($user['role']) ?></dd>
                </div>
              </div>
            </div>
          </section>

          <footer class="d-flex gap-3 flex-wrap pt-4 border-top">
            <a class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>">
              <i class="bi bi-shield-lock me-2"></i>비밀번호 변경
            </a>
            <a class="btn btn-light border text-danger rounded-pill px-4 py-2 fw-bold shadow-none" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">
              <i class="bi bi-box-arrow-right me-2"></i>로그아웃
            </a>
          </footer>
        </div>
      </article>

      <nav class="mt-4 text-center">
        <a href="/" class="link-secondary text-decoration-none small fw-bold"><i class="bi bi-arrow-left me-1"></i>메인 페이지로 돌아가기</a>
      </nav>
    </div>
  </div>
</section>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
