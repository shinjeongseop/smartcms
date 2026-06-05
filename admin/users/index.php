<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_require_level((int)smartcms_config_value('admin_level', 8), (string)smartcms_config_value('admin_login_url', '/admin/login/'));
$users = [];

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
}

smartcms_render_head([
    'title' => '회원 관리',
    'body_class' => 'smartcms-admin-page',
]);
?>
<main class="smartcms-admin-shell">
  <header class="smartcms-admin-header">
    <div>
      <p class="smartcms-eyebrow">Admin</p>
      <h1 class="smartcms-title">회원 관리</h1>
      <p class="smartcms-text-muted"><?= smartcms_h($admin['name']) ?>님이 로그인했습니다.</p>
    </div>
    <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
  </header>

  <?php if (isset($message)): ?>
    <?= smartcms_alert($message, 'error') ?>
  <?php endif; ?>

  <section class="smartcms-panel smartcms-admin-panel">
    <h2 class="smartcms-section-title">최근 회원</h2>
    <div class="smartcms-table-wrap">
      <table class="smartcms-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>이메일</th>
            <th>이름</th>
            <th>역할</th>
            <th>레벨</th>
            <th>상태</th>
            <th>최근 로그인</th>
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
            </tr>
          <?php endforeach; ?>
          <?php if (!$users): ?>
            <tr>
              <td colspan="7">표시할 회원이 없습니다.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<?php smartcms_render_foot(); ?>
