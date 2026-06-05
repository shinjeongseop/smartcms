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

smartcms_render_head([
    'title' => '접속 로그',
    'body_class' => 'smartcms-admin-page',
]);
?>
<?= smartcms_admin_page_header($admin, '접속 로그', 'logs') ?>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <section class="smartcms-panel smartcms-admin-panel">
    <h2 class="smartcms-section-title">최근 접속 로그</h2>
    <div class="smartcms-table-wrap">
      <table class="smartcms-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>회원</th>
            <th>유형</th>
            <th>대상</th>
            <th>경로</th>
            <th>결과</th>
            <th>시간</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($access_logs as $log): ?>
            <tr>
              <td><?= smartcms_h($log['id']) ?></td>
              <td><?= smartcms_h($log['user_id'] ?? '-') ?></td>
              <td><?= smartcms_h($log['access_type']) ?></td>
              <td><?= smartcms_h($log['target_type']) ?> / <?= smartcms_h($log['target_key'] ?? '-') ?></td>
              <td><?= smartcms_h($log['method']) ?> <?= smartcms_h($log['request_path']) ?></td>
              <td><?= smartcms_h($log['result']) ?> <?= smartcms_h($log['status_code']) ?></td>
              <td><?= smartcms_h($log['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$access_logs): ?>
            <tr>
              <td colspan="7">접속 로그가 없습니다.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="smartcms-panel smartcms-admin-panel smartcms-stack-panel">
    <h2 class="smartcms-section-title">최근 로그인 로그</h2>
    <div class="smartcms-table-wrap">
      <table class="smartcms-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>회원</th>
            <th>이메일</th>
            <th>결과</th>
            <th>시간</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($login_logs as $log): ?>
            <tr>
              <td><?= smartcms_h($log['id']) ?></td>
              <td><?= smartcms_h($log['user_id'] ?? '-') ?></td>
              <td><?= smartcms_h($log['email']) ?></td>
              <td><?= smartcms_h($log['result']) ?></td>
              <td><?= smartcms_h($log['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$login_logs): ?>
            <tr>
              <td colspan="5">로그인 로그가 없습니다.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="smartcms-panel smartcms-admin-panel smartcms-stack-panel">
    <h2 class="smartcms-section-title">최근 게시판 감사 로그</h2>
    <div class="smartcms-table-wrap">
      <table class="smartcms-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>게시판</th>
            <th>글</th>
            <th>회원</th>
            <th>액션</th>
            <th>내용</th>
            <th>시간</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($board_logs as $log): ?>
            <tr>
              <td><?= smartcms_h($log['id']) ?></td>
              <td><?= smartcms_h($log['board_id'] ?? '-') ?></td>
              <td><?= smartcms_h($log['post_id'] ?? '-') ?></td>
              <td><?= smartcms_h($log['user_id'] ?? '-') ?></td>
              <td><?= smartcms_h($log['action']) ?></td>
              <td><?= smartcms_h($log['message']) ?></td>
              <td><?= smartcms_h($log['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$board_logs): ?>
            <tr>
              <td colspan="7">게시판 감사 로그가 없습니다.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  <?= smartcms_admin_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
