<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';

$user = smartcms_require_login();

$SMARTCMS_HEAD = ['title' => '마이페이지'];
require SMARTCMS_ROOT . '/head.php';
?>
<section class="container-fluid container-xxl py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xxl-6">
      <article class="card border shadow-lg overflow-hidden">
        <!-- 프로필 헤더 -->
        <header class="card-header bg-primary text-white p-4 p-md-5">
          <div class="d-flex align-items-center gap-4">
            <div class="badge bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm sc-avatar-72">
              <i class="bi bi-person-fill fs-1"></i>
            </div>
            <div>
              <p class="text-uppercase small fw-bold text-white-50 mb-1">Personal Account</p>
              <h1 class="h3 fw-bold mb-0"><?= smartcms_h($user['name']) ?>님의 프로필</h1>
            </div>
          </div>
        </header>

        <div class="card-body p-4 p-md-5">
          <section class="mb-5">
            <h2 class="h6 fw-bold text-uppercase text-primary letter-spacing-1 mb-4">계정 상세 정보</h2>
            <div class="row g-4">
              <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border-0">
                  <dt class="text-secondary small fw-bold text-uppercase mb-1">이름 / 닉네임</dt>
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
