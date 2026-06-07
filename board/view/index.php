<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../common/ui/navigation.php';

$board_key = smartcms_board_key((string)($_GET['board'] ?? ''));
$post_id = (int)($_GET['id'] ?? 0);
$board = $board_key !== '' ? smartcms_board_find($board_key) : null;

if (!$board) {
    http_response_code(404);
    echo 'Board not found.';
    exit;
}

$user = smartcms_require_board_access($board, 'view');
$post = smartcms_board_post_find((int)$board['id'], $post_id);

if (!$post) {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

$message = '';
$message_type = 'info';
$can_comment = smartcms_has_level((int)($board['board_comment_level'] ?? 2), $user);
$can_manage_post = smartcms_board_can_manage_post($board, $post, $user);
$can_manage_board = $user && smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);

if ((int)$post['is_secret'] === 1 && (!$user || ((int)$post['author_id'] !== (int)$user['id'] && !smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)))) {
    http_response_code(403);
    echo 'Secret post.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $action = (string)($_POST['action'] ?? 'comment_create');
    if ($action === 'comment_hide') {
        if (!$can_manage_board || !$user) {
            $message = '댓글 숨김 권한이 없습니다.';
            $message_type = 'error';
        } else {
            $result = smartcms_board_hide_comment($board, $post, $user, (int)($_POST['comment_id'] ?? 0));
            $message = $result['message'];
            $message_type = $result['ok'] ? 'success' : 'error';
            if ($result['ok']) {
                smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
            }
        }
    } elseif (!$can_comment || !$user) {
        $message = '댓글 작성 권한이 없습니다.';
        $message_type = 'error';
    } else {
        $result = smartcms_board_create_comment($board, $post, $user, (string)($_POST['content'] ?? ''));
        $message = $result['message'];
        $message_type = $result['ok'] ? 'success' : 'error';
        if ($result['ok']) {
            smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
        }
    }
}

smartcms_board_increment_view((int)$post['id']);
$post['view_count'] = (int)$post['view_count'] + 1;
$comments = smartcms_board_comments((int)$post['id']);
$files = smartcms_board_files((int)$post['id']);
$recent_board_posts = smartcms_board_recent_posts_by_key((string)$board['board_key'], 5);

smartcms_render_head([
    'title' => (string)$post['title'],
    'body_class' => 'smartcms-board-page',
]);
?>
<?= smartcms_site_header((string)$board['board_key']) ?>

<?= smartcms_page_container_start() ?>
  <header class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5">
      <p class="text-uppercase small fw-semibold text-primary mb-2"><?= smartcms_h($board['board_name']) ?></p>
      <h1 class="display-6 fw-bold mb-2"><?= smartcms_h($post['title']) ?></h1>
      <p class="text-body-secondary mb-0">
        <?= smartcms_h($post['author_name']) ?> · 조회 <?= smartcms_h($post['view_count']) ?> · <?= smartcms_h($post['created_at']) ?>
      </p>
    </div>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <?= smartcms_two_column_start() ?>
    <article class="card border-0 shadow-sm">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
          <div>
            <?php if ((int)$post['is_notice'] === 1): ?><span class="badge text-bg-primary me-1">공지</span><?php endif; ?>
            <?php if ((int)$post['is_secret'] === 1): ?><span class="badge text-bg-secondary me-1">비밀글</span><?php endif; ?>
          </div>
          <?php if ($can_manage_post): ?>
            <a class="btn btn-outline-secondary btn-sm rounded-pill flex-shrink-0"
               href="<?= smartcms_h(smartcms_base_url('/board/edit/')
                   . '?board=' . rawurlencode((string)$board['board_key'])
                   . '&id=' . rawurlencode((string)$post['id'])) ?>">
              <i class="bi bi-pencil me-1"></i>수정
            </a>
          <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-3 py-3 border-top border-bottom text-body-secondary small mb-4">
          <span><i class="bi bi-person me-1"></i><?= smartcms_h($post['author_name']) ?></span>
          <span><i class="bi bi-clock me-1"></i><?= smartcms_h($post['created_at']) ?></span>
          <span><i class="bi bi-eye me-1"></i><?= number_format((int)$post['view_count']) ?></span>
          <span><i class="bi bi-chat me-1"></i><?= count($comments) ?></span>
        </div>

        <div class="mb-4 text-break lh-lg">
          <?= nl2br(smartcms_h($post['content'])) ?>
        </div>

        <?php if ($files): ?>
          <div class="mb-4">
            <h3 class="h6 fw-semibold mb-3">첨부파일</h3>
            <div class="list-group">
              <?php foreach ($files as $file): ?>
                <a class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"
                   href="<?= smartcms_h(smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$file['id'])) ?>">
                  <span class="fw-semibold"><i class="bi bi-paperclip me-1"></i><?= smartcms_h($file['original_name']) ?></span>
                  <small class="text-body-secondary"><?= number_format((int)$file['file_size']) ?> bytes · 다운로드 <?= (int)$file['download_count'] ?>회</small>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="pt-3 border-top">
          <a class="btn btn-outline-secondary rounded-pill"
             href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
            <i class="bi bi-list-ul me-1"></i>목록으로
          </a>
        </div>
      </div>
    </article>
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

  <section class="card border-0 shadow-sm mt-4">
    <div class="card-body p-4 p-lg-5">
      <h2 class="h5 fw-bold mb-4">댓글 <span class="text-body-secondary fw-normal"><?= count($comments) ?></span></h2>

      <?php if ($comments): ?>
        <div class="vstack gap-3">
          <?php foreach ($comments as $comment): ?>
            <article class="border rounded-3 p-3">
              <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <span class="fw-semibold"><?= smartcms_h($comment['author_name']) ?></span>
                <span class="text-body-secondary small"><?= smartcms_h($comment['created_at']) ?></span>
              </div>
              <p class="mb-0"><?= nl2br(smartcms_h((int)$comment['is_hidden'] === 1 ? '숨김 처리된 댓글입니다.' : $comment['content'])) ?></p>
              <?php if ($can_manage_board && (int)$comment['is_hidden'] !== 1): ?>
                <form class="mt-2" method="post">
                  <?= smartcms_csrf_input() ?>
                  <input type="hidden" name="action" value="comment_hide">
                  <input type="hidden" name="comment_id" value="<?= smartcms_h($comment['id']) ?>">
                  <button class="btn btn-outline-danger btn-sm rounded-pill" type="submit">댓글 숨김</button>
                </form>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-body-secondary mb-0">등록된 댓글이 없습니다.</p>
      <?php endif; ?>

      <?php if ($can_comment && $user): ?>
        <div class="mt-4 pt-4 border-top">
          <h3 class="h6 fw-semibold mb-3">댓글 작성</h3>
          <form class="vstack gap-3" method="post">
            <?= smartcms_csrf_input() ?>
            <input type="hidden" name="action" value="comment_create">
            <div>
              <textarea class="form-control" id="content" name="content" rows="4" required placeholder="댓글을 입력하세요."></textarea>
            </div>
            <div>
              <?= smartcms_button('댓글 등록', 'submit') ?>
            </div>
          </form>
        </div>
      <?php elseif (!$user): ?>
        <?= smartcms_alert('로그인 후 댓글을 작성할 수 있습니다.', 'info') ?>
      <?php else: ?>
        <?= smartcms_alert('댓글 작성 권한이 없습니다.', 'info') ?>
      <?php endif; ?>
    </div>
  </section>

  <?= smartcms_page_container_end() ?>
<?= smartcms_site_footer() ?>
<?php smartcms_render_foot(); ?>
