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

    $stmt = smartcms_db()->query(
        "SELECT email, result, created_at FROM " . smartcms_table('login_logs') . " ORDER BY id DESC LIMIT 8"
    );
    $recent_logins = $stmt->fetchAll();

    $stmt = smartcms_db()->query(
        "SELECT action, message, created_at FROM " . smartcms_table('board_audit_logs') . " ORDER BY id DESC LIMIT 8"
    );
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

<!-- 통계 카드 -->
<div class="sc-stat-grid">
  <a class="card sc-stat-card" href="<?= smartcms_h(smartcms_base_url('/admin/users/')) ?>">
    <span><i class="bi bi-people me-1"></i>회원</span>
    <strong><?= number_format($stats['users']) ?></strong>
  </a>
  <a class="card sc-stat-card" href="<?= smartcms_h(smartcms_base_url('/admin/boards/')) ?>">
    <span><i class="bi bi-layout-text-window me-1"></i>게시판</span>
    <strong><?= number_format($stats['boards']) ?></strong>
  </a>
  <a class="card sc-stat-card" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">
    <span><i class="bi bi-file-text me-1"></i>게시글</span>
    <strong><?= number_format($stats['posts']) ?></strong>
  </a>
  <a class="card sc-stat-card" href="<?= smartcms_h(smartcms_base_url('/admin/logs/')) ?>">
    <span><i class="bi bi-chat me-1"></i>댓글</span>
    <strong><?= number_format($stats['comments']) ?></strong>
  </a>
</div>

<!-- 대시보드 그리드 -->
<div class="sc-dashboard-grid">
  <article class="card sc-panel">
    <h2 class="sc-section-title">최근 게시글</h2>
    <div class="sc-mini-list">
      <?php foreach ($recent_posts as $post): ?>
        <a class="sc-mini-list-item"
           href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
          <strong><?= smartcms_h($post['title']) ?></strong>
          <span><?= smartcms_h($post['board_name']) ?> · <?= smartcms_h($post['author_name']) ?> · <?= smartcms_h($post['created_at']) ?></span>
        </a>
      <?php endforeach; ?>
      <?php if (!$recent_posts): ?>
        <p class="sc-empty">최근 게시글이 없습니다.</p>
      <?php endif; ?>
    </div>
  </article>

  <article class="card sc-panel">
    <h2 class="sc-section-title">최근 로그인</h2>
    <div class="sc-mini-list">
      <?php foreach ($recent_logins as $login): ?>
        <div class="sc-mini-list-item">
          <strong><?= smartcms_h($login['email']) ?></strong>
          <span><?= smartcms_h($login['result']) ?> · <?= smartcms_h($login['created_at']) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$recent_logins): ?>
        <p class="sc-empty">최근 로그인 기록이 없습니다.</p>
      <?php endif; ?>
    </div>
  </article>

  <article class="card sc-panel">
    <h2 class="sc-section-title">게시판 감사 로그</h2>
    <div class="sc-mini-list">
      <?php foreach ($recent_audits as $audit): ?>
        <div class="sc-mini-list-item">
          <strong><?= smartcms_h($audit['action']) ?></strong>
          <span><?= smartcms_h($audit['message']) ?> · <?= smartcms_h($audit['created_at']) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$recent_audits): ?>
        <p class="sc-empty">최근 감사 로그가 없습니다.</p>
      <?php endif; ?>
    </div>
  </article>
</div>

<?= smartcms_admin_footer() ?>
<?php smartcms_render_foot(); ?>
