<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';

if (isset($SMARTCMS_FOOT) && is_array($SMARTCMS_FOOT)) {
    $request_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    $is_admin = str_starts_with($request_path, '/admin/');
    $is_login_page = str_contains($request_path, '/admin/login/');

    if ($is_admin && !$is_login_page) {
        echo '</main></div></div><!-- /.container-fluid -->' . PHP_EOL;
    }

    $scripts = (array)($SMARTCMS_FOOT['scripts'] ?? []);

    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
    foreach ($scripts as $script) {
        echo '<script src="' . smartcms_h(smartcms_asset_url((string)$script)) . '"></script>' . PHP_EOL;
    }
    echo '</body></html>';
}
