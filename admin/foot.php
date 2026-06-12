<?php
declare(strict_types=1);

$request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
$is_login_page = str_contains($request_path, '/admin/login/');

if (!$is_login_page) {
    ?>
    </div>
    </main>
    <footer class="mt-auto bg-white border-top py-4 mt-4 mx-n3 mx-lg-n4">
      <div class="container-fluid px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-secondary">
        <span>&copy; <?= date('Y') ?> <a href="#" class="text-decoration-none fw-bold">smartcms</a>. All rights reserved.</span>
        <div class="d-flex gap-3">
          <a href="<?= smartcms_h(smartcms_base_url('/')) ?>" class="text-decoration-none text-secondary">사이트 홈</a>
          <a href="#" class="text-decoration-none text-secondary">문서</a>
          <a href="#" class="text-decoration-none text-secondary text-primary fw-bold">smartcms 2.0</a>
        </div>
      </div>
    </footer>
    </div></div><!-- /.d-flex -->
    <?php
}

$scripts = (array)($SMARTCMS_FOOT['scripts'] ?? []);
if (!in_array('/common/js/search-validator.js', $scripts, true)) {
    $scripts[] = '/common/js/search-validator.js';
}

echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
foreach ($scripts as $script) {
    echo '<script src="' . smartcms_h(smartcms_asset_url((string)$script)) . '"></script>' . PHP_EOL;
}
?>
</body></html>
