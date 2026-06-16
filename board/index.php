<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';
require_once __DIR__ . '/../common/ui/components.php';
$board_key    = smartcms_board_key((string)($_GET['board'] ?? ''));
$board        = $board_key !== '' ? smartcms_board_find($board_key) : null;
$boards       = [];
$board_counts = [];
$posts        = [];
$search_posts = [];
$pagination   = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 10, 'pages' => 1, 'keyword' => ''];
$search_pagination = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 12, 'pages' => 1, 'keyword' => ''];
$message      = '';
$message_type = 'info';
$user         = null;
$page         = max(1, (int)($_GET['page'] ?? 1));
$keyword      = trim((string)($_GET['q'] ?? ''));
$search_requested = array_key_exists('q', $_GET);
$keyword_length = function_exists('mb_strlen') ? mb_strlen($keyword) : strlen($keyword);

if ($search_requested && $keyword_length < 2) {
    $keyword = '';
    $search_requested = false;
}

try {
    if ($board) {
        $user = smartcms_require_board_access($board, 'list');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            smartcms_verify_csrf_or_fail();
            $bulk_action = strtolower(trim((string)($_POST['bulk_action'] ?? '')));
            if (in_array($bulk_action, ['delete', 'move', 'copy'], true)) {
                $selected_ids = smartcms_board_post_ids((array)($_POST['post_ids'] ?? []));
                $target_board_key = smartcms_board_key((string)($_POST['target_board'] ?? ''));
                $result = smartcms_board_bulk_action_posts($board, $user, $selected_ids, $bulk_action, $target_board_key !== '' ? $target_board_key : null);
                smartcms_flash_set('message', $result['message']);
                smartcms_flash_set('message_type', $result['ok'] ? 'success' : 'error');
                smartcms_redirect(smartcms_board_url((string)$board['board_key'])
                    . ($keyword !== '' ? '&q=' . rawurlencode($keyword) : '')
                    . '&page=' . $page);
            }
        }
        $board_keyword = ($search_requested && $keyword_length < 2) ? '' : $keyword;
        $pagination = smartcms_board_posts((int)$board['id'], $page, (int)$board['items_per_page'], $board_keyword);
        $posts = $pagination['items'];
    }
    $boards = smartcms_board_list();
    $board_counts = smartcms_board_post_counts();

    if (!$board && $keyword !== '' && $keyword_length >= 2) {
        $search_pagination = smartcms_board_search_posts($keyword, $page, 12);
        $search_posts = $search_pagination['items'];
    }
} catch (Throwable $e) {
    error_log('[smartcms board] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $message = '게시판을 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.';
    $message_type = 'error';
    if ($user && (int)$user['level'] >= 8) {
        $message = '게시판을 불러오지 못했습니다. 잠시 후 다시 시도해 주세요. (' . $e->getMessage() . ')';
    }
}

$message = (string)smartcms_flash_get('message', $message);
$message_type = (string)smartcms_flash_get('message_type', $message_type);

$page_title = $board ? (string)$board['board_name'] : '게시판';

$active_menu = $board
    ? 'board:' . (string)$board['board_key']
    : 'boards';
$SMARTCMS_HEAD = ['title' => $page_title, 'active_menu' => $active_menu, 'main_class' => 'flex-grow-1 pb-5'];
require SMARTCMS_ROOT . '/head.php';
?>

<div class="container-fluid container-xxl pt-4 pt-lg-5">
  <?php if ($message !== ''): ?>
    <div class="alert alert-<?= $message_type === 'error' ? 'danger' : ($message_type === 'warning' ? 'warning' : 'info') ?> d-flex align-items-start gap-2 mb-4" role="alert">
      <i class="bi bi-info-circle-fill mt-1"></i>
      <div><?= smartcms_h($message) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!$board): ?>
    <?php if ($keyword !== '' && $keyword_length >= 2): ?>
      <section class="card border shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <p class="text-uppercase small fw-semibold text-primary mb-2">Search Result</p>
            <h1 class="h4 fw-bold mb-2">"<?= smartcms_h($keyword) ?>" 검색 결과</h1>
            <p class="text-secondary mb-0">게시판 이름, 작성자, 제목, 본문에서 찾은 글을 보여줍니다.</p>
          </div>
          <a class="btn btn-light border rounded-2 px-4 fw-bold text-secondary" href="/board/">검색 초기화</a>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($keyword !== '' && $keyword_length >= 2): ?>
      <section class="card border shadow-sm overflow-hidden mb-4">
        <header class="card-header bg-white border-bottom p-4 d-flex align-items-center justify-content-between">
          <div>
            <h2 class="h5 fw-bold mb-1 text-dark">검색된 글</h2>
            <p class="small text-secondary mb-0">총 <?= number_format((int)$search_pagination['total']) ?>건</p>
          </div>
          <?php if ((int)$search_pagination['pages'] > 1): ?>
            <span class="badge bg-primary-subtle text-primary rounded-2 px-3 py-2 fw-semibold">
              <?= (int)$search_pagination['page'] ?> / <?= (int)$search_pagination['pages'] ?>
            </span>
          <?php endif; ?>
        </header>

        <div class="list-group list-group-flush">
          <?php foreach ($search_posts as $post): ?>
            <a class="list-group-item list-group-item-action p-4 d-flex flex-column flex-md-row align-items-md-center gap-3"
               href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
              <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge bg-primary-subtle text-primary rounded-2 px-3 py-2 fw-bold"><?= smartcms_board_highlight_text((string)$post['board_name'], $keyword) ?></span>
                <?php if ((int)$post['is_notice'] === 1): ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 px-2 py-1 small">공지</span>
                <?php endif; ?>
                <?php if ((int)$post['is_secret'] === 1): ?>
                  <span class="badge bg-dark rounded-2 px-2 py-1 small"><i class="bi bi-lock-fill me-1"></i>비밀</span>
                <?php endif; ?>
              </div>

              <div class="flex-grow-1 min-w-0">
                <div class="fw-bold text-dark text-truncate mb-1">
                  <?= smartcms_board_highlight_text(smartcms_board_truncate_title((string)$post['title']), $keyword) ?>
                </div>
                <div class="small text-secondary text-truncate">
                  <?= smartcms_board_highlight_text(smartcms_board_author_display_name(null, $post), $keyword) ?> · <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                </div>
              </div>

              <div class="d-flex align-items-center gap-3 text-secondary small flex-shrink-0">
                <span>조회 <?= number_format((int)$post['view_count']) ?></span>
                <span>댓글 <?= number_format((int)$post['comment_count']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>

          <?php if (!$search_posts): ?>
            <div class="p-5 text-center text-secondary">
              <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
              검색 결과가 없습니다.
            </div>
          <?php endif; ?>
        </div>

        <?php if ((int)$search_pagination['pages'] > 1): ?>
          <div class="card-footer bg-white border-top py-4">
            <nav aria-label="검색 결과 페이지">
              <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                <?php foreach (smartcms_pagination_window((int)$search_pagination['page'], (int)$search_pagination['pages']) as $i): ?>
                  <li class="page-item <?= $i === (int)$search_pagination['page'] ? 'active' : '' ?>">
                    <a class="page-link rounded-circle border shadow-sm"
                       href="?q=<?= rawurlencode((string)$search_pagination['keyword']) ?>&page=<?= $i ?>">
                      <?= $i ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </nav>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php require smartcms_board_skin_template(null, 'boards'); ?>
  <?php else: ?>
    <?php require smartcms_board_skin_template($board, 'list'); ?>
  <?php endif; ?>
</div>

<?php
$SMARTCMS_FOOT = is_array($SMARTCMS_FOOT ?? null) ? $SMARTCMS_FOOT : [];
require SMARTCMS_ROOT . '/foot.php';
?>
