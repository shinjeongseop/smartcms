<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../common/ui/navigation.php';

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

smartcms_render_head([
    'title' => '글쓰기',
    'body_class' => 'smartcms-board-page',
]);
$form_action = 'create';
$form_enctype = 'multipart/form-data';
$form_values = ['title' => '', 'content' => '', 'is_notice' => false, 'is_secret' => false];
$show_attachments = (int)($board['use_attachments'] ?? 1) === 1 && smartcms_has_level((int)($board['board_upload_level'] ?? 8), $user);
$submit_label = '등록하기';
$back_url = smartcms_board_url((string)$board['board_key']);
$back_label = '목록으로';
?>
<?= smartcms_site_header((string)$board['board_key']) ?>

  <header class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5">
      <p class="text-uppercase small fw-semibold text-primary mb-2">Write</p>
      <h1 class="display-6 fw-bold mb-2"><?= smartcms_h($board['board_name']) ?> 글쓰기</h1>
      <p class="text-body-secondary mb-0">게시판 권한에 맞는 회원만 글을 작성할 수 있습니다.</p>
    </div>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <?php require smartcms_board_skin_template($board, 'form'); ?>
  <?= smartcms_site_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
