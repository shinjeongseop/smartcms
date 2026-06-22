    </main>
    <footer class="bg-white border-top py-5 mt-auto text-body-secondary">
      <div class="container-xxl">
        <div class="row g-4">
          <div class="col-12 col-lg-4">
            <a class="navbar-brand fs-3 fw-bold text-primary d-block mb-3" href="<?= smartcms_h(smartcms_base_url('/')) ?>"><?= smartcms_h(smartcms_site_name()) ?></a>
            <p class="small mb-0">정보와 경험이 이어지는 커뮤니티 플랫폼</p>
          </div>
          <div class="col-6 col-lg-2 offset-lg-2"><h3 class="h6 fw-bold text-dark mb-3 text-uppercase">Service</h3>
            <ul class="list-unstyled small d-grid gap-2 fw-medium">
              <li><a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>" class="text-decoration-none link-secondary">전체 게시판</a></li>
              <li><a href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>" class="text-decoration-none link-secondary">로그인</a></li>
              <li><a href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>" class="text-decoration-none link-secondary">회원가입</a></li>
            </ul>
          </div>
          <div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-dark mb-3 text-uppercase">Support</h3>
            <ul class="list-unstyled small d-grid gap-2 fw-medium">
              <li><a href="<?= smartcms_h(smartcms_base_url('/privacy/')) ?>" class="text-decoration-none link-secondary fw-bold">개인정보처리방침</a></li>
            </ul>
          </div>
          <div class="col-6 col-lg-2"><h3 class="h6 fw-bold text-dark mb-3 text-uppercase">Admin</h3>
            <ul class="list-unstyled small d-grid gap-2 fw-medium">
              <li><a href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>" class="text-decoration-none link-secondary">관리자 홈</a></li>
            </ul>
          </div>
        </div>
        <div class="border-top pt-4 mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small fw-medium">
          <span>&copy; <?= date('Y') ?> <?= smartcms_h(smartcms_site_name()) ?>. All rights reserved.</span><span class="text-body-secondary">Powered by Bootstrap 5 & Optimized PHP</span>
        </div>
      </div>
    </footer>
    <?php

$scripts = (array)($SMARTCMS_FOOT['scripts'] ?? []);
if (!in_array('/common/js/search-validator.js', $scripts, true)) {
    $scripts[] = '/common/js/search-validator.js';
}

echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
foreach ($scripts as $script) {
    $script = (string)$script;
    $script_url = smartcms_asset_url($script);
    $asset_file = null;

    if (!(preg_match('#^(https?:)?//#', $script_url) === 1 || str_starts_with($script_url, 'data:'))) {
        $candidate = str_starts_with($script, '/') ? SMARTCMS_ROOT . $script : realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $script);
        if ($candidate && is_file($candidate)) {
            $asset_file = $candidate;
        }
    }

    echo '<script src="' . smartcms_h($script_url) . '"></script>' . PHP_EOL;
}
?>
</body></html>
