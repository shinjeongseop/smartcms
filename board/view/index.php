<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

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

smartcms_render_head([
    'title' => (string)$post['title'],
    'body_class' => 'smartcms-board-page',
]);
?>
<main class="smartcms-content-shell">
  <header class="smartcms-page-hero">
    <p class="smartcms-eyebrow"><?= smartcms_h($board['board_name']) ?></p>
    <h1 class="smartcms-title"><?= smartcms_h($post['title']) ?></h1>
    <p class="smartcms-text-muted">
      <?= smartcms_h($post['author_name']) ?> · 조회 <?= smartcms_h($post['view_count']) ?> · <?= smartcms_h($post['created_at']) ?>
    </p>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <article class="smartcms-panel smartcms-admin-panel smartcms-post-view">
    <?php if ((int)$post['is_notice'] === 1): ?>
      <span class="smartcms-badge">공지</span>
    <?php endif; ?>
    <?php if ((int)$post['is_secret'] === 1): ?>
      <span class="smartcms-badge smartcms-badge--muted">비밀글</span>
    <?php endif; ?>
    <?php if ($can_manage_post): ?>
      <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/board/edit/') . '?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id'])) ?>">수정</a>
    <?php endif; ?>
    <div class="smartcms-post-content"><?= nl2br(smartcms_h($post['content'])) ?></div>
    <?php if ($files): ?>
      <div class="smartcms-file-list">
        <h2 class="smartcms-section-title">첨부파일</h2>
        <?php foreach ($files as $file): ?>
          <a class="smartcms-file-link" href="<?= smartcms_h(smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$file['id'])) ?>">
            <?= smartcms_h($file['original_name']) ?>
            <span><?= number_format((int)$file['file_size']) ?> bytes · 다운로드 <?= smartcms_h($file['download_count']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>

  <section class="smartcms-panel smartcms-admin-panel smartcms-stack-panel">
    <div class="smartcms-section-head">
      <h2 class="smartcms-section-title">댓글 <?= smartcms_h(count($comments)) ?></h2>
      <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">목록으로</a>
    </div>

    <div class="smartcms-comment-list">
      <?php foreach ($comments as $comment): ?>
        <article class="smartcms-comment">
          <strong><?= smartcms_h($comment['author_name']) ?></strong>
          <span class="smartcms-text-muted"><?= smartcms_h($comment['created_at']) ?></span>
          <p><?= nl2br(smartcms_h((int)$comment['is_hidden'] === 1 ? '숨김 처리된 댓글입니다.' : $comment['content'])) ?></p>
          <?php if ($can_manage_board && (int)$comment['is_hidden'] !== 1): ?>
            <form class="smartcms-inline-form" method="post">
              <?= smartcms_csrf_input() ?>
              <input type="hidden" name="action" value="comment_hide">
              <input type="hidden" name="comment_id" value="<?= smartcms_h($comment['id']) ?>">
              <button class="smartcms-small-muted-btn" type="submit">댓글 숨김</button>
            </form>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
      <?php if (!$comments): ?>
        <p class="smartcms-text-muted">등록된 댓글이 없습니다.</p>
      <?php endif; ?>
    </div>

    <?php if ($can_comment && $user): ?>
      <form class="smartcms-grid smartcms-comment-form" method="post">
        <?= smartcms_csrf_input() ?>
        <input type="hidden" name="action" value="comment_create">
        <div class="smartcms-field">
          <label for="content">댓글 작성</label>
          <textarea class="smartcms-textarea" id="content" name="content" rows="4" required></textarea>
        </div>
        <?= smartcms_button('댓글 등록', 'submit') ?>
      </form>
    <?php else: ?>
      <?= smartcms_alert('댓글 작성 권한이 없습니다.', 'info') ?>
    <?php endif; ?>
  </section>
</main>
<?php smartcms_render_foot(); ?>
