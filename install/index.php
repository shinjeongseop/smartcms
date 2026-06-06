<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/database.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';

$locked = smartcms_install_locked();
$message = '';
$message_type = 'info';
$saved = false;
$auto_project_key = 'smartcms';
$form = [
    'base_url' => (string)smartcms_config_value('base_url', ''),
    'table_prefix' => (string)smartcms_config_value('table_prefix', 'sc_'),
    'db_host' => (string)smartcms_config_value('db.host', 'localhost'),
    'db_name' => (string)smartcms_config_value('db.name', ''),
    'db_user' => (string)smartcms_config_value('db.user', ''),
    'db_charset' => (string)smartcms_config_value('db.charset', 'utf8mb4'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    smartcms_verify_csrf_or_fail();
    $form = [
        'base_url' => trim((string)($_POST['base_url'] ?? '')),
        'table_prefix' => trim((string)($_POST['table_prefix'] ?? 'sc_')) ?: 'sc_',
        'db_host' => trim((string)($_POST['db_host'] ?? 'localhost')),
        'db_name' => trim((string)($_POST['db_name'] ?? '')),
        'db_user' => trim((string)($_POST['db_user'] ?? '')),
        'db_charset' => trim((string)($_POST['db_charset'] ?? 'utf8mb4')),
    ];
    $auto_project_key = preg_replace('/[^a-zA-Z0-9_]/', '', $form['db_name']) ?: 'smartcms';
    $db = [
        'host' => $form['db_host'],
        'name' => $form['db_name'],
        'user' => $form['db_user'],
        'pass' => (string)($_POST['db_pass'] ?? ''),
        'charset' => $form['db_charset'],
    ];
    $result = smartcms_db_check($db);
    if ($result['ok']) {
        $saved = smartcms_write_local_config([
            'project_key' => $auto_project_key,
            'base_url' => $form['base_url'],
            'table_prefix' => $form['table_prefix'],
            'db' => $db,
        ]);
        $message = $saved ? 'DB 연결을 확인했고 config.local.php를 저장했습니다.' : 'DB 연결은 성공했지만 config.local.php 저장에 실패했습니다.';
        $message_type = $saved ? 'success' : 'error';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

smartcms_render_head([
    'title' => 'smartcms 설치',
    'body_class' => 'smartcms-install',
    'stylesheets' => ['/install/style.css'],
]);
?>
<div class="container py-4 py-md-5">
  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 fw-bold mb-2">smartcms 설치 마법사</h1>
      <p class="text-body-secondary">DB 연결, 테이블 생성, 관리자 계정 생성을 순서대로 진행합니다.</p>
      <ol class="list-group list-group-horizontal-md mb-4 overflow-auto">
        <li class="list-group-item flex-fill text-center active">1 DB 설정</li>
        <li class="list-group-item flex-fill text-center">2 테이블 생성</li>
        <li class="list-group-item flex-fill text-center">3 관리자 계정</li>
        <li class="list-group-item flex-fill text-center">4 설치 완료</li>
      </ol>

      <?php if ($locked): ?>
        <?= smartcms_alert('이미 설치가 완료되어 설치 마법사를 사용할 수 없습니다.', 'error') ?>
      <?php else: ?>
        <?php if ($message !== ''): ?>
          <?= smartcms_alert($message, $message_type) ?>
        <?php endif; ?>
        <?php if ($saved): ?>
          <div class="mb-3">
            <a class="btn btn-primary rounded-pill px-4" href="./schema.php">다음: 테이블 생성</a>
            <p class="text-body-secondary mt-2 mb-0">DB 설정 저장이 완료되었습니다. 다음 단계로 이동해 테이블을 생성하세요.</p>
          </div>
        <?php endif; ?>

        <form class="d-grid gap-3" method="post">
          <?= smartcms_csrf_input() ?>
          <div>
            <label for="base_url" class="form-label">Base URL</label>
            <input class="form-control" id="base_url" name="base_url" value="<?= smartcms_h($form['base_url']) ?>" placeholder="선택 사항, 예: https://example.com">
            <div class="form-text">비워두면 상대 경로로 동작합니다. 도메인을 고정하고 싶을 때만 입력하세요.</div>
          </div>
          <div>
            <label for="table_prefix" class="form-label">Table Prefix</label>
            <input class="form-control" id="table_prefix" name="table_prefix" value="<?= smartcms_h($form['table_prefix']) ?>" placeholder="기본값 sc_">
            <div class="form-text">기본값은 sc_ 입니다. 같은 DB에 여러 시스템을 함께 쓸 때 테이블명을 구분합니다.</div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="db_host" class="form-label">DB Host</label>
              <input class="form-control" id="db_host" name="db_host" value="<?= smartcms_h($form['db_host']) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="db_name" class="form-label">DB Name</label>
              <input class="form-control" id="db_name" name="db_name" value="<?= smartcms_h($form['db_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="db_user" class="form-label">DB User</label>
              <input class="form-control" id="db_user" name="db_user" value="<?= smartcms_h($form['db_user']) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="db_pass" class="form-label">DB Password</label>
              <input class="form-control" id="db_pass" name="db_pass" type="password">
            </div>
            <div class="col-md-6">
              <label for="db_charset" class="form-label">Charset</label>
              <input class="form-control" id="db_charset" name="db_charset" value="<?= smartcms_h($form['db_charset']) ?>" required>
            </div>
          </div>
          <?= smartcms_button('DB 연결 확인', 'submit') ?>
        </form>
      <?php endif; ?>
    </div>
  </div>
    </div>
  </div>
</div>
<?php smartcms_render_foot(["scripts"=>["/install/app.js"]]); ?>
