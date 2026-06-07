<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/schema.php';
require_once __DIR__ . '/../head.php';
require_once __DIR__ . '/../foot.php';
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
    smartcms_verify_csrf_or_fail();
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
    'stylesheets' => ['/install/style.css'],
]);
?>
<div class="container py-4 py-md-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-8">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 fw-bold mb-2">최초 관리자 생성</h1>
      <p class="text-body-secondary">설치 후 사용할 level 10 최고 관리자 계정을 만듭니다.</p>
      <?php if ($message !== ''): ?>
        <?= smartcms_alert($message, $message_type) ?>
      <?php endif; ?>
      <?php if (!$admin): ?>
        <form class="d-grid gap-3" method="post">
          <?= smartcms_csrf_input() ?>
          <div>
            <label for="email" class="form-label">관리자 이메일</label>
            <input class="form-control" id="email" name="email" type="email" value="admin@smartcms.com" required>
          </div>
          <div>
            <label for="name" class="form-label">관리자 이름</label>
            <input class="form-control" id="name" name="name" value="최고관리자" required>
          </div>
          <div>
            <label for="password" class="form-label">비밀번호</label>
            <input class="form-control" id="password" name="password" type="password" minlength="8" required>
          </div>
          <?= smartcms_button('관리자 생성', 'submit') ?>
        </form>
      <?php else: ?>
        <?= smartcms_alert('최고 관리자 계정이 준비되었습니다.', 'info') ?>
        <p class="mb-0"><a class="btn btn-primary rounded-pill px-4" href="./finish.php">다음: 설치 잠금 처리</a></p>
      <?php endif; ?>
    </div>
  </div>
    </div>
  </div>
</div>
<?php smartcms_render_foot(['scripts' => ['/install/app.js']]); ?>
