<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';
$board_key = smartcms_board_key((string)($_GET['board'] ?? ''));
$board = $board_key !== '' ? smartcms_board_find($board_key) : null;

if (!$board) {
    http_response_code(404);
    echo 'Board not found.';
    exit;
}

$user = smartcms_require_board_access($board, 'write');
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $result = smartcms_board_create_post(
        $board,
        $user,
        (string)($_POST['title'] ?? ''),
        (string)($_POST['content'] ?? ''),
        isset($_POST['is_notice']) && smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user),
        isset($_POST['is_secret'])
    );
    $message = $result['message'];
    $message_type = $result['ok'] ? 'success' : 'error';

    if ($result['ok']) {
        if (isset($_FILES['attachments'])) {
            $file_result = smartcms_board_store_uploads($board, (int)$result['post_id'], $user, $_FILES['attachments']);
            if (!$file_result['ok']) {
                $message = $file_result['message'];
                $message_type = 'error';
            }
        }
        if ($message_type === 'success') {
            smartcms_redirect('/board/view/?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$result['post_id']));
        }
    }
}

$active_menu = in_array((string)$board['board_key'], ['notice', 'free', 'qna'], true)
    ? (string)$board['board_key']
    : 'boards';
$SMARTCMS_HEAD = ['title' => '새 글 작성', 'body_class' => 'bg-light', 'active_menu' => $active_menu, 'main_class' => 'flex-grow-1 pb-5'];
require SMARTCMS_ROOT . '/head.php';

// Skin variables
$form_action = 'create';
$form_enctype = 'multipart/form-data';
$form_values = ['title' => '', 'content' => '', 'is_notice' => false, 'is_secret' => false];
$show_attachments = (int)($board['use_attachments'] ?? 1) === 1 && smartcms_has_level((int)($board['board_upload_level'] ?? 8), $user);
$submit_label = '게시글 등록';
$back_url = smartcms_board_url((string)$board['board_key']);
$back_label = '목록으로';
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

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
