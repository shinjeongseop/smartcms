<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';

    $year = date('Y');
    ?>
    <footer class="bg-dark text-white-50 py-5 mt-auto">
      <div class="container-xxl">
        <div class="row g-4">
          <div class="col-12 col-lg-4">
            <a class="navbar-brand fs-3 fw-bold text-white d-block mb-3" href="<?= smartcms_h(smartcms_base_url('/')) ?>">smartcms<span class="text-primary">.</span></a>
            <p class="small mb-4">Bootstrap 5 Native Community CMS<br>모던한 기술로 구축하는 커뮤니티의 새로운 기준</p>
            <div class="d-flex gap-3 fs-5 text-white"><i class="bi bi-github"></i><i class="bi bi-discord"></i><i class="bi bi-youtube"></i></div>
          </div>
          <div class="col-6 col-lg-2 offset-lg-2"><h3 class="h6 fw-bold text-white mb-3 text-uppercase letter-spacing-1">Service</h3>
            <ul class="list-unstyled small d-grid gap-2 fw-medium">
              <li><a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>" class="text-decoration-none text-reset hover-white">전체 게시판</a></li>
              <li><a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="text-decoration-none text-reset hover-white">로그인</a></li>
              <li><a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="text-decoration-none text-reset hover-white">회원가입</a></li>
            </ul>
          </div>
          <div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-white mb-3 text-uppercase letter-spacing-1">Support</h3>
            <ul class="list-unstyled small d-grid gap-2 fw-medium">
              <li><a href="#" class="text-decoration-none text-reset text-primary fw-bold">개인정보처리방침</a></li>
            </ul>
          </div>
          <div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-white mb-3 text-uppercase letter-spacing-1">Admin</h3>
            <ul class="list-unstyled small d-grid gap-2 fw-medium">
              <li><a href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>" class="text-decoration-none text-reset hover-white">관리자 홈</a></li>
            </ul>
          </div>
        </div>
        <div class="border-top border-secondary border-opacity-25 pt-4 mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small fw-medium">
          <span>&copy; <?= $year ?> smartcms. All rights reserved.</span><span class="text-white-50">Powered by Bootstrap 5 & Optimized PHP</span>
        </div>
      </div>
    </footer>
    </main>
    <?php

$scripts = (array)($SMARTCMS_FOOT['scripts'] ?? []);

echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
foreach ($scripts as $script) {
    echo '<script src="' . smartcms_h(smartcms_asset_url((string)$script)) . '"></script>' . PHP_EOL;
}
?>
</body></html>
