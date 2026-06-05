<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';
require_once __DIR__ . '/../common/ui/navigation.php';

$board_key = smartcms_board_key((string)($_GET['board'] ?? ''));
$board = $board_key !== '' ? smartcms_board_find($board_key) : null;
$boards = [];
$posts = [];
$pagination = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 10, 'pages' => 1, 'keyword' => ''];
$message = '';
$message_type = 'info';
$user = null;
$page = max(1, (int)($_GET['page'] ?? 1));
$keyword = trim((string)($_GET['q'] ?? ''));

try {
    if ($board) {
        $user = smartcms_require_board_access($board, 'list');
        $pagination = smartcms_board_posts((int)$board['id'], $page, (int)$board['items_per_page'], $keyword);
        $posts = $pagination['items'];
    } else {
        $boards = smartcms_board_list();
    }
} catch (Throwable $e) {
    $message = '게시판을 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

smartcms_render_head([
    'title' => $board ? (string)$board['board_name'] : '게시판',
    'body_class' => 'smartcms-board-page',
]);
?>
<?= smartcms_site_header($board ? (string)$board['board_key'] : '') ?>

  <header class="smartcms-page-hero">
    <p class="smartcms-eyebrow">Board</p>
    <h1 class="smartcms-title"><?= smartcms_h($board ? $board['board_name'] : '게시판') ?></h1>
    <p class="smartcms-text-muted"><?= smartcms_h($board ? ($board['description'] ?? '게시글을 확인하세요.') : '사용 가능한 게시판을 선택하세요.') ?></p>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <?php if (!$board): ?>
    <?php require smartcms_board_skin_template(null, 'boards'); ?>
  <?php else: ?>
    <?php require smartcms_board_skin_template($board, 'list'); ?>
  <?php endif; ?>
  <?= smartcms_site_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
