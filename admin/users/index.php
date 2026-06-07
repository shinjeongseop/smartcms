<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$users = [];
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $user_id = (int)($_POST['user_id'] ?? 0);
    $level = max(1, min(10, (int)($_POST['level'] ?? 1)));
    $status = (string)($_POST['status'] ?? 'active');
    $role = (string)($_POST['role'] ?? 'user');
    $allowed_statuses = ['active', 'pending', 'blocked', 'left'];
    $allowed_roles = ['admin', 'manager', 'user'];

    if ($user_id === (int)$admin['id'] && $level < smartcms_setting_int('admin_level', (int)smartcms_config_value('admin_level', 8))) {
        $message = '현재 로그인한 관리자 자신의 레벨을 관리자 기준 아래로 낮출 수 없습니다.';
        $message_type = 'error';
    } elseif (!in_array($status, $allowed_statuses, true) || !in_array($role, $allowed_roles, true)) {
        $message = '올바르지 않은 회원 상태 또는 역할입니다.';
        $message_type = 'error';
    } else {
        smartcms_execute(
            "UPDATE " . smartcms_table('users') . "
             SET level = :level, status = :status, role = :role
             WHERE id = :id",
            [
                'id' => $user_id,
                'level' => $level,
                'status' => $status,
                'role' => $role,
            ]
        );
        $message = '회원 권한을 저장했습니다.';
        $message_type = 'success';
    }
}

try {
    $stmt = smartcms_db()->query(
        "SELECT id, email, name, role, level, status, last_login_at, created_at
         FROM " . smartcms_table('users') . "
         ORDER BY id DESC
         LIMIT 50"
    );
    $users = $stmt->fetchAll();
} catch (Throwable $e) {
    $message = '회원 목록을 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

$SMARTCMS_HEAD = ['title' => '회원 관리', 'body_class' => 'smartcms-admin-page'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_admin_page_header($admin, '회원 관리', 'users');
?>

<?php if ($message !== ''): ?>
  <?= smartcms_alert($message, $message_type) ?>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4 p-lg-5">
    <h2 class="h5 fw-bold mb-4">최근 회원</h2>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>이메일</th>
            <th>이름</th>
            <th>역할</th>
            <th>레벨</th>
            <th>상태</th>
            <th>최근 로그인</th>
            <th>관리</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?= smartcms_h($user['id']) ?></td>
              <td><?= smartcms_h($user['email']) ?></td>
              <td><?= smartcms_h($user['name']) ?></td>
              <td><?= smartcms_h($user['role']) ?></td>
              <td><?= smartcms_h($user['level']) ?></td>
              <td><?= smartcms_h($user['status']) ?></td>
              <td><?= smartcms_h($user['last_login_at'] ?? '-') ?></td>
              <td>
                <form class="d-flex flex-wrap gap-2" method="post">
                  <?= smartcms_csrf_input() ?>
                  <input type="hidden" name="user_id" value="<?= smartcms_h($user['id']) ?>">
                  <select class="form-select form-select-sm" name="role">
                    <?php foreach (['admin', 'manager', 'user'] as $role): ?>
                      <option value="<?= smartcms_h($role) ?>" <?= $role === $user['role'] ? 'selected' : '' ?>><?= smartcms_h($role) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select class="form-select form-select-sm" name="level">
                    <?php for ($level = 1; $level <= 10; $level++): ?>
                      <option value="<?= $level ?>" <?= $level === (int)$user['level'] ? 'selected' : '' ?>><?= $level ?></option>
                    <?php endfor; ?>
                  </select>
                  <select class="form-select form-select-sm" name="status">
                    <?php foreach (['active', 'pending', 'blocked', 'left'] as $status): ?>
                      <option value="<?= smartcms_h($status) ?>" <?= $status === $user['status'] ? 'selected' : '' ?>><?= smartcms_h($status) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-primary btn-sm" type="submit">저장</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$users): ?>
            <tr>
              <td colspan="8">표시할 회원이 없습니다.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= smartcms_admin_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
