<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';
require_once __DIR__ . '/../common/ui/navigation.php';

$board_key    = smartcms_board_key((string)($_GET['board'] ?? ''));
$board        = $board_key !== '' ? smartcms_board_find($board_key) : null;
$boards       = [];
$board_counts = [];
$posts        = [];
$pagination   = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 10, 'pages' => 1, 'keyword' => ''];
$message      = '';
$message_type = 'info';
$user         = null;
$page         = max(1, (int)($_GET['page'] ?? 1));
$keyword      = trim((string)($_GET['q'] ?? ''));
$recent_board_posts = [];

try {
    if ($board) {
        $user       = smartcms_require_board_access($board, 'list');
        $pagination = smartcms_board_posts((int)$board['id'], $page, (int)$board['items_per_page'], $keyword);
        $posts      = $pagination['items'];
        $recent_board_posts = smartcms_board_recent_posts_by_key((string)$board['board_key'], 5);
    } else {
        $boards = smartcms_board_list();
        $board_counts = smartcms_board_post_counts();
    }
} catch (Throwable $e) {
    $message      = '게시판을 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

$page_title = $board ? (string)$board['board_name'] : '게시판';

smartcms_render_head(['title' => $page_title]);
echo smartcms_site_header($board ? (string)$board['board_key'] : '');
?>

<div class="container-fluid container-xxl py-4">
  <header class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5">
      <p class="text-uppercase small fw-semibold text-primary mb-2">Board</p>
      <h1 class="display-6 fw-bold mb-3"><?= smartcms_h($page_title) ?></h1>
      <p class="lead text-body-secondary mb-0"><?= smartcms_h($board ? ($board['description'] ?? '게시글을 확인하세요.') : '사용 가능한 게시판을 선택하세요.') ?></p>
    </div>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <?php if (!$board): ?>
    <?= smartcms_two_column_start(['main_class' => 'col-12 col-lg-8']) ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
          <?php require smartcms_board_skin_template(null, 'boards'); ?>
        </div>
      </div>
    <?= smartcms_two_column_middle() ?>
      <?= smartcms_sidebar_card('Boards', '사용 가능한 게시판을 선택하세요.') ?>
      <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-4">
          <h3 class="h6 fw-semibold mb-3">바로가기</h3>
          <div class="list-group list-group-flush">
            <?php foreach ($boards as $board_item): ?>
              <?php if ((string)$board_item['status'] === 'hidden') continue; ?>
              <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0"
                 href="<?= smartcms_h(smartcms_board_url((string)$board_item['board_key'])) ?>">
                <strong><?= smartcms_h($board_item['board_name']) ?></strong>
                <small class="text-body-secondary"><?= (int)($board_counts[(string)$board_item['board_key']] ?? 0) ?></small>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?= smartcms_two_column_end() ?>
  <?php else: ?>
    <?= smartcms_two_column_start() ?>
      <?php require smartcms_board_skin_template($board, 'list'); ?>
    <?= smartcms_two_column_middle() ?>
      <?= smartcms_sidebar_card(
        (string)$board['board_name'],
        '<p class="mb-0 text-body-secondary">' . smartcms_h((string)($board['description'] ?? '게시판을 확인하세요.')) . '</p>',
        '<div class="d-flex flex-wrap gap-2">'
        . '<a class="btn btn-primary btn-sm rounded-pill" href="' . smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) . '">글쓰기</a>'
        . '<a class="btn btn-outline-secondary btn-sm rounded-pill" href="' . smartcms_h(smartcms_board_url((string)$board['board_key'])) . '">새로고침</a>'
        . '</div>'
      ) ?>
      <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-4">
          <h3 class="h6 fw-semibold mb-3">최근 글</h3>
          <div class="list-group list-group-flush">
            <?php foreach ($recent_board_posts as $recent): ?>
              <a class="list-group-item list-group-item-action px-0 text-truncate"
                 href="<?= smartcms_h(smartcms_board_post_url((string)$recent['board_key'], (int)$recent['id'])) ?>">
                <?= smartcms_h($recent['title']) ?>
              </a>
            <?php endforeach; ?>
            <?php if (!$recent_board_posts): ?>
              <div class="text-body-secondary">최근 글이 없습니다.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?= smartcms_two_column_end() ?>
  <?php endif; ?>

  </div>
<?= smartcms_site_footer() ?>
<?php smartcms_render_foot(); ?>
