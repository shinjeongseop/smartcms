<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin         = smartcms_admin_user();
$stats         = ['users' => 0, 'boards' => 0, 'posts' => 0, 'comments' => 0];
$recent_posts  = [];
$recent_logins = [];
$recent_audits = [];
$message       = '';

try {
    $stats['users']    = (int)(smartcms_fetch_one("SELECT COUNT(*) AS cnt FROM " . smartcms_table('users'))['cnt'] ?? 0);
    $stats['boards']   = (int)(smartcms_fetch_one("SELECT COUNT(*) AS cnt FROM " . smartcms_table('boards') . " WHERE status <> 'disabled'")['cnt'] ?? 0);
    $stats['posts']    = (int)(smartcms_fetch_one("SELECT COUNT(*) AS cnt FROM " . smartcms_table('board_posts') . " WHERE is_hidden = 0")['cnt'] ?? 0);
    $stats['comments'] = (int)(smartcms_fetch_one("SELECT COUNT(*) AS cnt FROM " . smartcms_table('board_comments') . " WHERE is_hidden = 0")['cnt'] ?? 0);

    $stmt = smartcms_db()->query(
        "SELECT p.id, p.title, p.author_name, p.created_at, b.board_key, b.board_name
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         WHERE p.is_hidden = 0 AND b.status <> 'disabled'
         ORDER BY p.id DESC LIMIT 8"
    );
    $recent_posts = $stmt->fetchAll();

    $stmt = smartcms_db()->query("SELECT email, result, created_at FROM " . smartcms_table('login_logs') . " ORDER BY id DESC LIMIT 8");
    $recent_logins = $stmt->fetchAll();

    $stmt = smartcms_db()->query("SELECT action, message, created_at FROM " . smartcms_table('board_audit_logs') . " ORDER BY id DESC LIMIT 8");
    $recent_audits = $stmt->fetchAll();
} catch (Throwable $e) {
    $message = '대시보드 데이터를 불러오지 못했습니다: ' . $e->getMessage();
}

smartcms_render_head(['title' => '관리자 대시보드']);
echo smartcms_admin_page_header($admin, '대시보드', 'dashboard');
?>

<?php if ($message !== ''): ?>
  <?= smartcms_alert($message, 'error') ?>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
  <div class="col">
    <a class="card h-100 text-decoration-none border-0 shadow-sm" href="<?= smartcms_h(smartcms_base_url('/admin/users/')) ?>">
      <div class="card-body">
        <p class="text-body-secondary small mb-1"><i class="bi bi-people me-1"></i>회원</p>
        <div class="h2 fw-bold mb-0"><?= number_format($stats['users']) ?></div>
      </div>
    </a>
  </div>
  <div class="col">
    <a class="card h-100 text-decoration-none border-0 shadow-sm" href="<?= smartcms_h(smartcms_base_url('/admin/boards/')) ?>">
      <div class="card-body">
        <p class="text-body-secondary small mb-1"><i class="bi bi-layout-text-window me-1"></i>게시판</p>
        <div class="h2 fw-bold mb-0"><?= number_format($stats['boards']) ?></div>
      </div>
    </a>
  </div>
  <div class="col">
    <a class="card h-100 text-decoration-none border-0 shadow-sm" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">
      <div class="card-body">
        <p class="text-body-secondary small mb-1"><i class="bi bi-file-text me-1"></i>게시글</p>
        <div class="h2 fw-bold mb-0"><?= number_format($stats['posts']) ?></div>
      </div>
    </a>
  </div>
  <div class="col">
    <a class="card h-100 text-decoration-none border-0 shadow-sm" href="<?= smartcms_h(smartcms_base_url('/admin/logs/')) ?>">
      <div class="card-body">
        <p class="text-body-secondary small mb-1"><i class="bi bi-chat me-1"></i>댓글</p>
        <div class="h2 fw-bold mb-0"><?= number_format($stats['comments']) ?></div>
      </div>
    </a>
  </div>
</div>

<?= smartcms_two_column_start() ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h2 class="h5 fw-bold mb-3">최근 게시글</h2>
      <div class="list-group list-group-flush">
        <?php foreach ($recent_posts as $post): ?>
          <a class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"
             href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
            <div>
              <div class="fw-semibold"><?= smartcms_h($post['title']) ?></div>
              <small class="text-body-secondary"><?= smartcms_h($post['board_name']) ?> · <?= smartcms_h($post['author_name']) ?></small>
            </div>
            <small class="text-body-secondary"><?= smartcms_h($post['created_at']) ?></small>
          </a>
        <?php endforeach; ?>
        <?php if (!$recent_posts): ?>
          <div class="list-group-item text-body-secondary">최근 게시글이 없습니다.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?= smartcms_two_column_middle() ?>
  <?= smartcms_sidebar_card(
    '최근 로그인',
    '<div class="list-group list-group-flush">'
    . implode('', array_map(static function (array $login): string {
        return '<div class="list-group-item px-0">'
            . '<div class="fw-semibold">' . smartcms_h($login['email']) . '</div>'
            . '<small class="text-body-secondary">' . smartcms_h($login['result']) . ' · ' . smartcms_h($login['created_at']) . '</small>'
            . '</div>';
    }, $recent_logins))
    . '</div>',
    $recent_logins ? '' : '최근 로그인 기록이 없습니다.'
  ) ?>
  <?= smartcms_sidebar_card(
    '게시판 감사 로그',
    '<div class="list-group list-group-flush">'
    . implode('', array_map(static function (array $audit): string {
        return '<div class="list-group-item px-0">'
            . '<div class="fw-semibold">' . smartcms_h($audit['action']) . '</div>'
            . '<small class="text-body-secondary">' . smartcms_h($audit['message']) . ' · ' . smartcms_h($audit['created_at']) . '</small>'
            . '</div>';
    }, $recent_audits))
    . '</div>',
    $recent_audits ? '' : '최근 감사 로그가 없습니다.'
  ) ?>
<?= smartcms_two_column_end() ?>

<?= smartcms_admin_footer() ?>
<?php smartcms_render_foot(); ?>
