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
<main class="smartcms-panel">
  <h1 class="smartcms-title">smartcms 설치 마법사</h1>
  <p class="smartcms-text-muted">DB 연결, 테이블 생성, 관리자 계정 생성을 순서대로 진행합니다.</p>
  <ol class="smartcms-install-steps">
    <li class="is-active">1 DB 설정</li>
    <li>2 테이블 생성</li>
    <li>3 관리자 계정</li>
    <li>4 설치 완료</li>
  </ol>

  <?php if ($locked): ?>
    <?= smartcms_alert('이미 설치가 완료되어 설치 마법사를 사용할 수 없습니다.', 'error') ?>
  <?php else: ?>
    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>
    <?php if ($saved): ?>
      <div class="smartcms-next-step">
        <a class="btn btn-primary rounded-pill px-4" href="./schema.php">다음: 테이블 생성</a>
        <p class="smartcms-text-muted">DB 설정 저장이 완료되었습니다. 다음 단계로 이동해 테이블을 생성하세요.</p>
      </div>
    <?php endif; ?>

    <form class="smartcms-grid" method="post">
      <?= smartcms_csrf_input() ?>
      <div class="smartcms-field">
        <label for="base_url">Base URL</label>
        <input class="smartcms-input" id="base_url" name="base_url" value="<?= smartcms_h($form['base_url']) ?>" placeholder="선택 사항, 예: https://example.com">
        <p class="smartcms-text-muted">비워두면 상대 경로로 동작합니다. 도메인을 고정하고 싶을 때만 입력하세요.</p>
      </div>
      <div class="smartcms-field">
        <label for="table_prefix">Table Prefix</label>
        <input class="smartcms-input" id="table_prefix" name="table_prefix" value="<?= smartcms_h($form['table_prefix']) ?>" placeholder="기본값 sc_">
        <p class="smartcms-text-muted">기본값은 sc_ 입니다. 같은 DB에 여러 시스템을 함께 쓸 때 테이블명을 구분합니다.</p>
      </div>
      <div class="smartcms-field">
        <label for="db_host">DB Host</label>
        <input class="smartcms-input" id="db_host" name="db_host" value="<?= smartcms_h($form['db_host']) ?>" required>
      </div>
      <div class="smartcms-field">
        <label for="db_name">DB Name</label>
        <input class="smartcms-input" id="db_name" name="db_name" value="<?= smartcms_h($form['db_name']) ?>" required>
      </div>
      <div class="smartcms-field">
        <label for="db_user">DB User</label>
        <input class="smartcms-input" id="db_user" name="db_user" value="<?= smartcms_h($form['db_user']) ?>" required>
      </div>
      <div class="smartcms-field">
        <label for="db_pass">DB Password</label>
        <input class="smartcms-input" id="db_pass" name="db_pass" type="password">
      </div>
      <div class="smartcms-field">
        <label for="db_charset">Charset</label>
        <input class="smartcms-input" id="db_charset" name="db_charset" value="<?= smartcms_h($form['db_charset']) ?>" required>
      </div>
      <?= smartcms_button('DB 연결 확인', 'submit') ?>
    </form>
  <?php endif; ?>
</main>
<?php smartcms_render_foot(['scripts' => ['/install/app.js']]); ?>
