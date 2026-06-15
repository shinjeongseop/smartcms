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

$user = smartcms_require_board_access($board, 'write');
$post = smartcms_board_post_find((int)$board['id'], $post_id);

if (!$post) {
    http_response_code(404);
    echo 'Post not found.';
    exit;
}

if (!smartcms_board_can_manage_post($board, $post, $user)) {
    smartcms_render_access_denied_page('이 게시글을 수정할 권한이 없습니다.');
}

$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $action = (string)($_POST['action'] ?? 'update');
    if ($action === 'hide') {
        $result = smartcms_board_hide_post($board, $post, $user);
        if ($result['ok']) {
            smartcms_flash_set('message', $result['message']);
            smartcms_flash_set('message_type', 'success');
            smartcms_redirect('/board/?board=' . rawurlencode((string)$board['board_key']));
        }
    } else {
        $content_mode = smartcms_board_normalize_content_mode((string)($_POST['content_mode'] ?? ((int)($board['use_editor'] ?? 1) === 1 ? 'editor' : 'text')));
        $result = smartcms_board_update_post(
            $board,
            $post,
            $user,
            (string)($_POST['title'] ?? ''),
            (string)($_POST['link_url_1'] ?? ''),
            (string)($_POST['link_url_2'] ?? ''),
            (string)($_POST['content'] ?? ''),
            $content_mode,
            isset($_POST['is_notice']),
            isset($_POST['is_secret'])
        );
        if ($result['ok']) {
            $remove_files = (array)($_POST['remove_files'] ?? []);
            if ($remove_files) {
                $delete_result = smartcms_board_delete_uploads($board, $post, $user, $remove_files);
                if (!$delete_result['ok']) {
                    $result = $delete_result;
                }
            }

            if ($result['ok'] && isset($_FILES['attachments'])) {
                $file_result = smartcms_board_store_uploads($board, (int)$post['id'], $user, $_FILES['attachments']);
                if (!$file_result['ok']) {
                    $result = $file_result;
                }
            }

            if ($result['ok']) {
                smartcms_flash_set('message', $result['message']);
                smartcms_flash_set('message_type', 'success');
                smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
            }
        }
    }

    smartcms_flash_set('message', $result['message']);
    smartcms_flash_set('message_type', 'error');
    smartcms_redirect('/board/edit/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']));
}

$existing_files = smartcms_board_files((int)$post['id']);

$active_menu = in_array((string)$board['board_key'], ['notice', 'free', 'qna'], true)
    ? (string)$board['board_key']
    : 'boards';
$SMARTCMS_HEAD = ['title' => '게시글 수정', 'body_class' => 'bg-light', 'active_menu' => $active_menu, 'main_class' => 'flex-grow-1 pb-5'];
$use_editor = (int)($board['use_editor'] ?? 1) === 1;
if ($use_editor) {
    $SMARTCMS_HEAD['stylesheets'][] = '/common/vendor/jodit/jodit.min.css';
}
require SMARTCMS_ROOT . '/head.php';

// Skin variables
$message = (string)smartcms_flash_get('message', $message);
$message_type = (string)smartcms_flash_get('message_type', $message_type);
$form_action = 'update';
$form_values = [
    'title' => (string)$post['title'],
    'link_url_1' => (string)($post['link_url_1'] ?? $post['link_url'] ?? ''),
    'link_url_2' => (string)($post['link_url_2'] ?? ''),
    'content' => (string)$post['content'],
    'content_mode' => $use_editor ? 'editor' : 'text',
    'is_notice' => (int)$post['is_notice'] === 1,
    'is_secret' => (int)$post['is_secret'] === 1,
];
$form_enctype = 'multipart/form-data';
$show_attachments = (int)($board['use_attachments'] ?? 1) === 1 && smartcms_has_level((int)($board['board_upload_level'] ?? 8), $user);
$show_hide_form = true;
$submit_label = '저장';
$show_submit_icon = false;
$back_url = smartcms_base_url('/board/view/') . '?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id']);
$back_label = '목록';
if ($use_editor) {
    $SMARTCMS_FOOT['scripts'][] = '/common/vendor/jodit/jodit.min.js';
    $SMARTCMS_FOOT['scripts'][] = '/common/js/board-editor.js';
}
?>

<div class="container-fluid container-xxl pt-4 pt-lg-5">
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

  <?php require smartcms_board_skin_template($board, 'form'); ?>
</div>
<?php require SMARTCMS_ROOT . '/foot.php'; ?>
