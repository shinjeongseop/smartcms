<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';
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

try {
    if ($board) {
        $user = smartcms_require_board_access($board, 'list');
        $pagination = smartcms_board_posts((int)$board['id'], $page, (int)$board['items_per_page'], $keyword);
        $posts = $pagination['items'];
    } else {
        if ($keyword !== '') {
            $search_pagination = smartcms_board_search_posts($keyword, $page, 12);
            $search_posts = $search_pagination['items'];
        } else {
            $boards = smartcms_board_list();
            $board_counts = smartcms_board_post_counts();
        }
    }
} catch (Throwable $e) {
    $message = '게시판을 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

$page_title = $board ? (string)$board['board_name'] : ($keyword !== '' ? '게시글 검색' : '게시판');

$active_menu = $board && in_array((string)$board['board_key'], ['notice', 'free', 'qna'], true)
    ? (string)$board['board_key']
    : 'boards';
$SMARTCMS_HEAD = ['title' => $page_title, 'active_menu' => $active_menu, 'main_class' => 'flex-grow-1 pb-5'];
require SMARTCMS_ROOT . '/head.php';
?>

<div class="container-fluid container-xxl pt-4 pt-lg-5">
  <?php if ($keyword !== '' && !$board): ?>
    <header class="card border shadow-sm mb-4">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <p class="text-uppercase small fw-semibold text-primary mb-2">Search Result</p>
            <h1 class="display-6 fw-bold mb-2">"<?= smartcms_h($keyword) ?>" 검색 결과</h1>
            <p class="lead text-body-secondary mb-0">
              전체 게시글에서 검색한 결과입니다. 게시판 이름, 작성자, 제목, 본문을 함께 찾습니다.
            </p>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary rounded-pill px-4 fw-bold" href="/board/">게시판 목록</a>
            <a class="btn btn-light border rounded-pill px-4 fw-bold text-secondary" href="/">홈으로</a>
          </div>
        </div>
      </div>
    </header>
  <?php endif; ?>

  <?php if ($message !== ''): ?>
    <div class="alert alert-<?= $message_type === 'error' ? 'danger' : 'info' ?> d-flex align-items-start gap-2 mb-4" role="alert">
      <i class="bi bi-info-circle-fill mt-1"></i>
      <div><?= smartcms_h($message) ?></div>
    </div>
  <?php endif; ?>

  <?php if ($keyword !== '' && !$board): ?>
    <section class="row g-4">
      <div class="col-12 col-lg-8">
        <div class="card border shadow-sm overflow-hidden">
          <header class="card-header bg-white border-bottom p-4 d-flex align-items-center justify-content-between">
            <div>
              <h2 class="h5 fw-bold mb-1 text-dark">검색된 글</h2>
              <p class="small text-secondary mb-0">총 <?= number_format((int)$search_pagination['total']) ?>건</p>
            </div>
            <?php if ((int)$search_pagination['pages'] > 1): ?>
              <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold">
                <?= (int)$search_pagination['page'] ?> / <?= (int)$search_pagination['pages'] ?>
              </span>
            <?php endif; ?>
          </header>

          <div class="list-group list-group-flush">
            <?php foreach ($search_posts as $post): ?>
              <a class="list-group-item list-group-item-action p-4 d-flex flex-column flex-md-row align-items-md-center gap-3"
                 href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                  <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold"><?= smartcms_h($post['board_name']) ?></span>
                  <?php if ((int)$post['is_notice'] === 1): ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">공지</span>
                  <?php endif; ?>
                  <?php if ((int)$post['is_secret'] === 1): ?>
                    <span class="badge bg-dark rounded-pill px-2 py-1 small"><i class="bi bi-lock-fill me-1"></i>비밀</span>
                  <?php endif; ?>
                </div>

                <div class="flex-grow-1 min-w-0">
                  <div class="fw-bold text-dark text-truncate mb-1">
                    <?= smartcms_h(smartcms_board_truncate_title((string)$post['title'], (int)($post['title_length_limit'] ?? 0))) ?>
                  </div>
                  <div class="small text-secondary text-truncate">
                    <?= smartcms_h($post['author_name']) ?> · <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
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
                  <?php for ($i = 1; $i <= (int)$search_pagination['pages']; $i++): ?>
                    <li class="page-item <?= $i === (int)$search_pagination['page'] ? 'active' : '' ?>">
                      <a class="page-link rounded-circle border shadow-sm"
                         href="?q=<?= rawurlencode((string)$search_pagination['keyword']) ?>&page=<?= $i ?>">
                        <?= $i ?>
                      </a>
                    </li>
                  <?php endfor; ?>
                </ul>
              </nav>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <aside class="col-12 col-lg-4">
        <div class="card border shadow-sm">
          <div class="card-body p-4">
            <h3 class="h6 fw-bold mb-3">검색 팁</h3>
            <ul class="small text-secondary mb-0 ps-3">
              <li>게시판 이름, 작성자, 제목, 본문을 함께 검색합니다.</li>
              <li>검색어를 더 짧게 입력하면 결과가 더 잘 나올 수 있습니다.</li>
              <li>원하는 게시판이 있으면 상단 메뉴에서 바로 이동할 수 있습니다.</li>
            </ul>
          </div>
        </div>
      </aside>
    </section>
  <?php elseif (!$board): ?>
    <?php require smartcms_board_skin_template(null, 'boards'); ?>
  <?php else: ?>
    <?php require smartcms_board_skin_template($board, 'list'); ?>
  <?php endif; ?>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
