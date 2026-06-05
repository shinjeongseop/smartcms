<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

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
?>
<main class="smartcms-content-shell">
  <header class="smartcms-page-hero">
    <p class="smartcms-eyebrow">Write</p>
    <h1 class="smartcms-title"><?= smartcms_h($board['board_name']) ?> 글쓰기</h1>
    <p class="smartcms-text-muted">게시판 권한에 맞는 회원만 글을 작성할 수 있습니다.</p>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <section class="smartcms-panel smartcms-admin-panel">
    <form class="smartcms-grid" method="post" enctype="multipart/form-data">
      <div class="smartcms-field">
        <label for="title">제목</label>
        <input class="smartcms-input" id="title" name="title" required>
      </div>
      <div class="smartcms-field">
        <label for="content">내용</label>
        <textarea class="smartcms-textarea" id="content" name="content" rows="12" required></textarea>
      </div>
      <div class="smartcms-actions">
        <?php if (smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)): ?>
          <label class="smartcms-check-field">
            <input type="checkbox" name="is_notice" value="1">
            공지글
          </label>
        <?php endif; ?>
        <label class="smartcms-check-field">
          <input type="checkbox" name="is_secret" value="1">
          비밀글
        </label>
      </div>
      <?php if ((int)($board['use_attachments'] ?? 1) === 1 && smartcms_has_level((int)($board['board_upload_level'] ?? 8), $user)): ?>
        <div class="smartcms-field">
          <label for="attachments">첨부파일</label>
          <input class="smartcms-input" id="attachments" name="attachments[]" type="file" multiple>
          <p class="smartcms-text-muted">파일당 10MB 이하로 업로드할 수 있습니다.</p>
        </div>
      <?php endif; ?>
      <div class="smartcms-actions">
        <?= smartcms_button('등록하기', 'submit') ?>
        <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">목록으로</a>
      </div>
    </form>
  </section>
</main>
<?php smartcms_render_foot(); ?>
