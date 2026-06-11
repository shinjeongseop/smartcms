<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';
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

try {
    if ($board) {
        $user = smartcms_require_board_access($board, 'list');
        $pagination = smartcms_board_posts((int)$board['id'], $page, (int)$board['items_per_page'], $keyword);
        $posts = $pagination['items'];
    } else {
        $boards = smartcms_board_list();
        $board_counts = smartcms_board_post_counts();
        if ($keyword !== '') {
            $needle = function_exists('mb_strtolower') ? mb_strtolower($keyword) : strtolower($keyword);
            $boards = array_values(array_filter($boards, static function (array $item) use ($needle): bool {
                $haystack = trim((string)($item['board_key'] ?? '') . ' ' . (string)($item['board_name'] ?? '') . ' ' . (string)($item['description'] ?? ''));
                $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack) : strtolower($haystack);
                return $needle === '' || str_contains($haystack, $needle);
            }));
        }
    }
} catch (Throwable $e) {
    $message = '게시판을 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

$page_title = $board ? (string)$board['board_name'] : ($keyword !== '' ? '게시판 검색' : '게시판');

$active_menu = $board && in_array((string)$board['board_key'], ['notice', 'free', 'qna'], true)
    ? (string)$board['board_key']
    : 'boards';
$SMARTCMS_HEAD = ['title' => $page_title, 'active_menu' => $active_menu, 'main_class' => 'flex-grow-1 pb-5'];
require SMARTCMS_ROOT . '/head.php';
?>

<div class="container-fluid container-xxl pt-4 pt-lg-5">
  <?php if ($message !== ''): ?>
    <div class="alert alert-<?= $message_type === 'error' ? 'danger' : 'info' ?> d-flex align-items-start gap-2 mb-4" role="alert">
      <i class="bi bi-info-circle-fill mt-1"></i>
      <div><?= smartcms_h($message) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!$board): ?>
    <?php require smartcms_board_skin_template(null, 'boards'); ?>
  <?php else: ?>
    <?php require smartcms_board_skin_template($board, 'list'); ?>
  <?php endif; ?>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
