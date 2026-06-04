<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/database.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';

$locked = smartcms_install_locked();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $db = [
        'host' => trim((string)($_POST['db_host'] ?? 'localhost')),
        'name' => trim((string)($_POST['db_name'] ?? '')),
        'user' => trim((string)($_POST['db_user'] ?? '')),
        'pass' => (string)($_POST['db_pass'] ?? ''),
        'charset' => trim((string)($_POST['db_charset'] ?? 'utf8mb4')),
    ];
    $result = smartcms_db_check($db);
    $message = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';
}

smartcms_render_head([
    'title' => 'smartcms 설치',
    'body_class' => 'smartcms-install',
    'stylesheets' => [smartcms_base_url('/install/style.css')],
]);
?>
<main class="smartcms-panel">
  <h1 class="smartcms-title">smartcms 설치 마법사</h1>
  <p class="smartcms-text-muted">DB 연결을 확인한 뒤 스키마 생성과 최초 관리자 계정 생성을 진행합니다.</p>

  <?php if ($locked): ?>
    <?= smartcms_alert('이미 설치가 완료되어 설치 마법사를 사용할 수 없습니다.', 'error') ?>
  <?php else: ?>
    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, $message_type) ?>
    <?php endif; ?>

    <form class="smartcms-grid" method="post">
      <div class="smartcms-field">
        <label for="db_host">DB Host</label>
        <input class="smartcms-input" id="db_host" name="db_host" value="localhost" required>
      </div>
      <div class="smartcms-field">
        <label for="db_name">DB Name</label>
        <input class="smartcms-input" id="db_name" name="db_name" required>
      </div>
      <div class="smartcms-field">
        <label for="db_user">DB User</label>
        <input class="smartcms-input" id="db_user" name="db_user" required>
      </div>
      <div class="smartcms-field">
        <label for="db_pass">DB Password</label>
        <input class="smartcms-input" id="db_pass" name="db_pass" type="password">
      </div>
      <div class="smartcms-field">
        <label for="db_charset">Charset</label>
        <input class="smartcms-input" id="db_charset" name="db_charset" value="utf8mb4" required>
      </div>
      <?= smartcms_button('DB 연결 확인', 'submit') ?>
    </form>
  <?php endif; ?>
</main>
<?php smartcms_render_foot(['scripts' => [smartcms_base_url('/install/app.js')]]); ?>
