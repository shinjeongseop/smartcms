<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/schema.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';

if (smartcms_install_locked()) {
    http_response_code(403);
    echo 'Installation is locked.';
    exit;
}

$message = '';
$message_type = 'info';

try {
    smartcms_create_users_table();
    $admin = smartcms_fetch_one(
        "SELECT id FROM " . smartcms_table('users') . " WHERE level >= :level LIMIT 1",
        ['level' => (int)smartcms_config_value('super_admin_level', 10)]
    );
} catch (Throwable $e) {
    $admin = null;
    $message = 'DB 설정 또는 users 테이블을 확인해야 합니다: ' . $e->getMessage();
    $message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $message_type !== 'error') {
    $email = trim((string)($_POST['email'] ?? ''));
    $name = trim((string)($_POST['name'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($admin) {
        $message = '이미 최고 관리자 계정이 존재합니다.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '올바른 이메일을 입력하세요.';
        $message_type = 'error';
    } elseif ($name === '') {
        $message = '관리자 이름을 입력하세요.';
        $message_type = 'error';
    } elseif (strlen($password) < 8) {
        $message = '비밀번호는 8자 이상이어야 합니다.';
        $message_type = 'error';
    } else {
        smartcms_execute(
            "INSERT INTO " . smartcms_table('users') . " (email, password_hash, name, role, level, status)
             VALUES (:email, :password_hash, :name, 'admin', :level, 'active')",
            [
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $name,
                'level' => (int)smartcms_config_value('super_admin_level', 10),
            ]
        );
        $message = '최초 관리자 계정을 생성했습니다.';
        $message_type = 'success';
        $admin = ['id' => smartcms_db()->lastInsertId()];
    }
}

smartcms_render_head([
    'title' => '최초 관리자 생성',
    'body_class' => 'smartcms-install',
    'stylesheets' => [smartcms_base_url('/install/style.css')],
]);
?>
<main class="smartcms-panel">
  <h1 class="smartcms-title">최초 관리자 생성</h1>
  <p class="smartcms-text-muted">설치 후 사용할 level 10 최고 관리자 계정을 만듭니다.</p>
  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>
  <?php if (!$admin): ?>
    <form class="smartcms-grid" method="post">
      <div class="smartcms-field">
        <label for="email">관리자 이메일</label>
        <input class="smartcms-input" id="email" name="email" type="email" required>
      </div>
      <div class="smartcms-field">
        <label for="name">관리자 이름</label>
        <input class="smartcms-input" id="name" name="name" required>
      </div>
      <div class="smartcms-field">
        <label for="password">비밀번호</label>
        <input class="smartcms-input" id="password" name="password" type="password" minlength="8" required>
      </div>
      <?= smartcms_button('관리자 생성', 'submit') ?>
    </form>
  <?php else: ?>
    <?= smartcms_alert('최고 관리자 계정이 준비되었습니다.', 'info') ?>
    <p><a class="smartcms-link-btn smartcms-link-btn--primary" href="<?= smartcms_h(smartcms_base_url('/install/finish.php')) ?>">다음: 설치 잠금 처리</a></p>
  <?php endif; ?>
</main>
<?php smartcms_render_foot(['scripts' => [smartcms_base_url('/install/app.js')]]); ?>
