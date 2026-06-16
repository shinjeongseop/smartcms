<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';

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
    smartcms_render_access_denied_page('이 비밀글을 볼 권한이 없습니다.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $action = (string)($_POST['action'] ?? 'comment_create');
    if ($action === 'comment_hide' || $action === 'comment_toggle_visibility') {
        if (!$can_manage_board || !$user) {
            smartcms_flash_set('message', '댓글 숨김 권한이 없습니다.');
            smartcms_flash_set('message_type', 'error');
            smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
        } else {
            $result = smartcms_board_toggle_comment_visibility($board, $post, $user, (int)($_POST['comment_id'] ?? 0));
            smartcms_flash_set('message', $result['message']);
            smartcms_flash_set('message_type', $result['ok'] ? 'success' : 'error');
            smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
        }
    } elseif ($action === 'post_delete') {
        if (!$can_manage_post || !$user) {
            smartcms_flash_set('message', '글 삭제 권한이 없습니다.');
            smartcms_flash_set('message_type', 'error');
            smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
        } else {
            $result = smartcms_board_delete_post($board, $post, $user);
            smartcms_flash_set('message', $result['message']);
            smartcms_flash_set('message_type', $result['ok'] ? 'success' : 'error');
            smartcms_redirect('/board/?board=' . rawurlencode((string)$board['board_key']));
        }
    } elseif (!$can_comment || !$user) {
        smartcms_flash_set('message', '댓글 작성 권한이 없습니다.');
        smartcms_flash_set('message_type', 'error');
        smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
      } else {
        $parent_id = (int)($_POST['parent_id'] ?? 0);
        $result = smartcms_board_create_comment($board, $post, $user, (string)($_POST['content'] ?? ''), $parent_id > 0 ? $parent_id : null);
        smartcms_flash_set('message', $result['message']);
        smartcms_flash_set('message_type', $result['ok'] ? 'success' : 'error');
        smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
    }
}

if (smartcms_board_should_count_view($board, $post, $user) && smartcms_board_count_once('view', (int)$post['id'], 86400)) {
    smartcms_board_increment_view((int)$post['id']);
    $post['view_count'] = (int)$post['view_count'] + 1;
}
$comments = smartcms_board_comments((int)$post['id']);
$files = smartcms_board_files((int)$post['id']);

$active_menu = in_array((string)$board['board_key'], ['notice', 'free', 'qna'], true)
    ? (string)$board['board_key']
    : 'boards';
$SMARTCMS_HEAD = ['title' => (string)$post['title'], 'body_class' => 'bg-light', 'active_menu' => $active_menu, 'main_class' => 'flex-grow-1 pb-5'];
$board_view_stylesheet = smartcms_board_skin_stylesheet($board, 'view');
if ($board_view_stylesheet !== null) {
    $SMARTCMS_HEAD['stylesheets'][] = $board_view_stylesheet;
}
require SMARTCMS_ROOT . '/head.php';
?>

<div class="container-fluid container-xxl pt-4 pt-lg-5">
  <?php $message = (string)smartcms_flash_get('message', $message); ?>
  <?php $message_type = (string)smartcms_flash_get('message_type', $message_type); ?>
  <div class="row g-4 align-items-start">
    <section class="col-12">
      <?php if ($message !== ''): ?>
        <?php
          $alert_theme = $message_type === 'error' ? 'danger' : $message_type;
          $alert_icon = $alert_theme === 'danger' ? 'bi-exclamation-triangle-fill' : ($alert_theme === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill');
        ?>
        <aside class="alert alert-<?= smartcms_h($alert_theme) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
          <i class="bi <?= smartcms_h($alert_icon) ?> fs-5"></i>
          <div class="fw-bold small"><?= smartcms_h($message) ?></div>
        </aside>
      <?php endif; ?>

      <?php
        require smartcms_board_skin_template($board, 'view');
      ?>
    </section>
  </div>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
