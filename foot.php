<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';

if (isset($SMARTCMS_FOOT) && is_array($SMARTCMS_FOOT)) {
    $request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    $is_admin = str_starts_with($request_path, '/admin/');
    $is_login_page = str_contains($request_path, '/admin/login/');

    if ($is_admin && !$is_login_page) {
        echo '<footer class="mt-auto bg-white border-top py-4 mt-4 mx-n3 mx-lg-n4">' . PHP_EOL;
        echo '  <div class="container-fluid px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-secondary">' . PHP_EOL;
        echo '    <span>&copy; ' . date('Y') . ' <a href="#" class="text-decoration-none fw-bold">smartcms</a>. All rights reserved.</span>' . PHP_EOL;
        echo '    <div class="d-flex gap-3">' . PHP_EOL;
        echo '      <a href="' . smartcms_h(smartcms_base_url('/')) . '" class="text-decoration-none text-secondary">사이트 홈</a>' . PHP_EOL;
        echo '      <a href="#" class="text-decoration-none text-secondary">문서</a>' . PHP_EOL;
        echo '      <a href="#" class="text-decoration-none text-secondary text-primary fw-bold">smartcms 2.0</a>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '  </div>' . PHP_EOL;
        echo '</footer>' . PHP_EOL;
        echo '</main></div></div><!-- /.d-flex -->' . PHP_EOL;
    } elseif (!$is_admin) {
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
              <div class="col-6 col-lg-2 offset-lg-2"><h3 class="h6 fw-bold text-white mb-3">Service</h3>
                <ul class="list-unstyled small d-grid gap-2">
                  <li><a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>" class="text-decoration-none text-reset">전체 게시판</a></li>
                  <li><a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="text-decoration-none text-reset">로그인</a></li>
                  <li><a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="text-decoration-none text-reset">회원가입</a></li>
                </ul>
              </div>
              <div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-white mb-3">Support</h3>
                <ul class="list-unstyled small d-grid gap-2"><li><a href="#" class="text-decoration-none text-reset text-primary">개인정보처리방침</a></li></ul>
              </div>
              <div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-white mb-3">Admin</h3>
                <ul class="list-unstyled small d-grid gap-2"><li><a href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>" class="text-decoration-none text-reset">관리자 홈</a></li></ul>
              </div>
            </div>
            <div class="border-top border-secondary border-opacity-25 pt-4 mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small">
              <span>&copy; <?= $year ?> smartcms. All rights reserved.</span><span class="text-white-50">Powered by Bootstrap 5 & PHP</span>
            </div>
          </div>
        </footer>
        </main>
        <?php
    }

    $scripts = (array)($SMARTCMS_FOOT['scripts'] ?? []);

    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
    foreach ($scripts as $script) {
        echo '<script src="' . smartcms_h(smartcms_asset_url((string)$script)) . '"></script>' . PHP_EOL;
    }
    echo '</body></html>';
}
