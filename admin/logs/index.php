<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$access_logs = [];
$login_logs = [];
$board_logs = [];
$message = '';
$message_type = 'info';

try {
    $stmt = smartcms_db()->query(
        "SELECT id, user_id, access_type, target_type, target_key, request_path, method, result, status_code, created_at
         FROM " . smartcms_table('access_logs') . "
         ORDER BY id DESC
         LIMIT 50"
    );
    $access_logs = $stmt->fetchAll();

    $stmt = smartcms_db()->query(
        "SELECT id, user_id, email, result, created_at
         FROM " . smartcms_table('login_logs') . "
         ORDER BY id DESC
         LIMIT 50"
    );
    $login_logs = $stmt->fetchAll();

    $stmt = smartcms_db()->query(
        "SELECT id, board_id, post_id, user_id, action, message, created_at
         FROM " . smartcms_table('board_audit_logs') . "
         ORDER BY id DESC
         LIMIT 50"
    );
    $board_logs = $stmt->fetchAll();
} catch (Throwable $e) {
    $message = '로그를 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

smartcms_render_head(['title' => '접속 로그', 'body_class' => 'smartcms-admin-page']);
echo smartcms_admin_page_header($admin, '접속 로그', 'logs');
?>

<?php if ($message !== ''): ?>
  <?= smartcms_alert($message, $message_type) ?>
<?php endif; ?>

<?php foreach ([
  ['title' => '최근 접속 로그', 'rows' => $access_logs, 'columns' => ['ID', '회원', '유형', '대상', '경로', '결과', '시간']],
  ['title' => '최근 로그인 로그', 'rows' => $login_logs, 'columns' => ['ID', '회원', '이메일', '결과', '시간']],
  ['title' => '최근 게시판 감사 로그', 'rows' => $board_logs, 'columns' => ['ID', '게시판', '글', '회원', '액션', '내용', '시간']],
] as $section): ?>
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
      <h2 class="h5 fw-bold mb-3"><?= smartcms_h($section['title']) ?></h2>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <?php foreach ($section['columns'] as $column): ?>
                <th><?= smartcms_h($column) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($section['rows'] as $row): ?>
              <tr>
                <?php if ($section['title'] === '최근 접속 로그'): ?>
                  <td><?= smartcms_h($row['id']) ?></td>
                  <td><?= smartcms_h($row['user_id'] ?? '-') ?></td>
                  <td><?= smartcms_h($row['access_type']) ?></td>
                  <td><?= smartcms_h($row['target_type']) ?> / <?= smartcms_h($row['target_key'] ?? '-') ?></td>
                  <td><?= smartcms_h($row['method']) ?> <?= smartcms_h($row['request_path']) ?></td>
                  <td><?= smartcms_h($row['result']) ?> <?= smartcms_h($row['status_code']) ?></td>
                  <td><?= smartcms_h($row['created_at']) ?></td>
                <?php elseif ($section['title'] === '최근 로그인 로그'): ?>
                  <td><?= smartcms_h($row['id']) ?></td>
                  <td><?= smartcms_h($row['user_id'] ?? '-') ?></td>
                  <td><?= smartcms_h($row['email']) ?></td>
                  <td><?= smartcms_h($row['result']) ?></td>
                  <td><?= smartcms_h($row['created_at']) ?></td>
                <?php else: ?>
                  <td><?= smartcms_h($row['id']) ?></td>
                  <td><?= smartcms_h($row['board_id'] ?? '-') ?></td>
                  <td><?= smartcms_h($row['post_id'] ?? '-') ?></td>
                  <td><?= smartcms_h($row['user_id'] ?? '-') ?></td>
                  <td><?= smartcms_h($row['action']) ?></td>
                  <td><?= smartcms_h($row['message']) ?></td>
                  <td><?= smartcms_h($row['created_at']) ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
            <?php if (!$section['rows']): ?>
              <tr>
                <td colspan="<?= count($section['columns']) ?>" class="text-body-secondary">데이터가 없습니다.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?= smartcms_admin_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
