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

smartcms_render_head([
    'title' => (string)$post['title'],
    'body_class' => 'smartcms-board-page',
]);
?>
<?= smartcms_site_header((string)$board['board_key']) ?>

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

  <?php require smartcms_board_skin_template($board, 'view'); ?>
  <?= smartcms_site_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
