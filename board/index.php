<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';

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
<main class="smartcms-content-shell">
  <header class="smartcms-page-hero">
    <p class="smartcms-eyebrow">Board</p>
    <h1 class="smartcms-title"><?= smartcms_h($board ? $board['board_name'] : '게시판') ?></h1>
    <p class="smartcms-text-muted"><?= smartcms_h($board ? ($board['description'] ?? '게시글을 확인하세요.') : '사용 가능한 게시판을 선택하세요.') ?></p>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <?php if (!$board): ?>
    <section class="smartcms-card-grid">
      <?php foreach ($boards as $item): ?>
        <?php if ((string)$item['status'] !== 'hidden'): ?>
          <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_board_url((string)$item['board_key'])) ?>">
            <strong><?= smartcms_h($item['board_name']) ?></strong>
            <span><?= smartcms_h($item['description'] ?? '게시판으로 이동') ?></span>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if (!$boards): ?>
        <?= smartcms_alert('생성된 게시판이 없습니다.', 'info') ?>
      <?php endif; ?>
    </section>
  <?php else: ?>
    <section class="smartcms-panel smartcms-admin-panel">
      <div class="smartcms-section-head">
        <h2 class="smartcms-section-title">글 목록</h2>
        <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
          <a class="smartcms-link-btn smartcms-link-btn--primary" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">글쓰기</a>
        <?php endif; ?>
      </div>
      <form class="smartcms-search-form" method="get">
        <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
        <input class="smartcms-input" name="q" value="<?= smartcms_h($pagination['keyword']) ?>" placeholder="제목, 내용, 작성자 검색">
        <button class="smartcms-small-btn" type="submit">검색</button>
        <?php if ($pagination['keyword'] !== ''): ?>
          <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
        <?php endif; ?>
      </form>
      <div class="smartcms-table-wrap">
        <table class="smartcms-table">
          <thead>
            <tr>
              <th>제목</th>
              <th>작성자</th>
              <th>조회</th>
              <th>작성일</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($posts as $post): ?>
              <tr>
                <td>
                  <?php if ((int)$post['is_notice'] === 1): ?>
                    <span class="smartcms-badge">공지</span>
                  <?php endif; ?>
                  <a class="smartcms-table-link" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/view/') . '&id=' . rawurlencode((string)$post['id'])) ?>">
                    <?= (int)$post['is_secret'] === 1 ? '비밀글 ' : '' ?><?= smartcms_h($post['title']) ?>
                    <?php if ((int)$post['comment_count'] > 0): ?>
                      <span class="smartcms-text-muted">(<?= smartcms_h($post['comment_count']) ?>)</span>
                    <?php endif; ?>
                    <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                      <span class="smartcms-badge smartcms-badge--muted">첨부</span>
                    <?php endif; ?>
                  </a>
                </td>
                <td><?= smartcms_h($post['author_name']) ?></td>
                <td><?= smartcms_h($post['view_count']) ?></td>
                <td><?= smartcms_h($post['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$posts): ?>
              <tr>
                <td colspan="4">등록된 글이 없습니다.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($pagination['pages'] > 1): ?>
        <nav class="smartcms-pagination" aria-label="게시글 페이지">
          <?php for ($i = 1; $i <= (int)$pagination['pages']; $i++): ?>
            <a class="smartcms-page-link <?= $i === (int)$pagination['page'] ? 'is-active' : '' ?>" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key']) . '&page=' . $i . '&q=' . rawurlencode((string)$pagination['keyword'])) ?>"><?= $i ?></a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>
<?php smartcms_render_foot(); ?>
