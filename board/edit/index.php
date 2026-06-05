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

$user = smartcms_require_board_access($board, 'write');
$post = smartcms_board_post_find((int)$board['id'], $post_id);

if (!$post) {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

if (!smartcms_board_can_manage_post($board, $post, $user)) {
    http_response_code(403);
    echo 'Permission denied.';
    exit;
}

$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $action = (string)($_POST['action'] ?? 'update');
    if ($action === 'hide') {
        $result = smartcms_board_hide_post($board, $post, $user);
        if ($result['ok']) {
            smartcms_redirect('/board/?board=' . rawurlencode((string)$board['board_key']));
        }
    } else {
        $result = smartcms_board_update_post(
            $board,
            $post,
            $user,
            (string)($_POST['title'] ?? ''),
            (string)($_POST['content'] ?? ''),
            isset($_POST['is_notice']),
            isset($_POST['is_secret'])
        );
        if ($result['ok']) {
            smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
        }
    }

    $message = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';
}

smartcms_render_head([
    'title' => '글 수정',
    'body_class' => 'smartcms-board-page',
]);
$form_action = 'update';
$form_values = [
    'title' => (string)$post['title'],
    'content' => (string)$post['content'],
    'is_notice' => (int)$post['is_notice'] === 1,
    'is_secret' => (int)$post['is_secret'] === 1,
];
$show_attachments = false;
$show_hide_form = true;
$submit_label = '수정 저장';
$back_url = smartcms_base_url('/board/view/') . '?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']);
$back_label = '상세로';
?>
<?= smartcms_site_header((string)$board['board_key']) ?>

  <header class="smartcms-page-hero">
    <p class="smartcms-eyebrow">Edit</p>
    <h1 class="smartcms-title"><?= smartcms_h($board['board_name']) ?> 글 수정</h1>
    <p class="smartcms-text-muted">작성자 또는 게시판 관리자만 글을 수정할 수 있습니다.</p>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <?php require smartcms_board_skin_template($board, 'form'); ?>
  <?= smartcms_site_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
