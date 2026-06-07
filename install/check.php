<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../head.php';
require_once __DIR__ . '/../foot.php';
require_once __DIR__ . '/../common/ui/components.php';

$checks = [
    'php_version' => [
        'label' => 'PHP 8.0 이상',
        'ok' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'value' => PHP_VERSION,
    ],
    'pdo' => [
        'label' => 'PDO 확장',
        'ok' => extension_loaded('pdo'),
        'value' => extension_loaded('pdo') ? 'loaded' : 'missing',
    ],
    'pdo_mysql' => [
        'label' => 'PDO MySQL 확장',
        'ok' => extension_loaded('pdo_mysql'),
        'value' => extension_loaded('pdo_mysql') ? 'loaded' : 'missing',
    ],
    'root_writable' => [
        'label' => '설정 파일 쓰기 권한',
        'ok' => is_writable(SMARTCMS_ROOT),
        'value' => SMARTCMS_ROOT,
    ],
];

$all_ok = !in_array(false, array_column($checks, 'ok'), true);

smartcms_render_head([
    'title' => 'smartcms 설치 점검',
    'body_class' => 'smartcms-install',
    'stylesheets' => ['/install/style.css'],
]);
?>
<div class="container py-4 py-md-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-8">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 fw-bold mb-2">환경 점검</h1>
      <p class="text-body-secondary">설치 전 서버 환경을 확인합니다.</p>
      <div class="list-group mb-4">
        <?php foreach ($checks as $check): ?>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <strong><?= smartcms_h($check['label']) ?></strong>
            <span class="badge <?= $check['ok'] ? 'text-bg-success' : 'text-bg-danger' ?>"><?= smartcms_h((string)$check['value']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <?= smartcms_alert($all_ok ? '설치 가능한 환경입니다.' : '설치 전에 실패 항목을 해결해야 합니다.', $all_ok ? 'success' : 'error') ?>
      <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>">설치 화면으로 이동</a>
    </div>
  </div>
    </div>
  </div>
</div>
<?php smartcms_render_foot(['scripts' => ['/install/app.js']]); ?>
