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
?>
<main class="smartcms-content-shell">
  <header class="smartcms-page-hero">
    <p class="smartcms-eyebrow">Edit</p>
    <h1 class="smartcms-title"><?= smartcms_h($board['board_name']) ?> 글 수정</h1>
    <p class="smartcms-text-muted">작성자 또는 게시판 관리자만 글을 수정할 수 있습니다.</p>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <section class="smartcms-panel smartcms-admin-panel">
    <form class="smartcms-grid" method="post">
      <input type="hidden" name="action" value="update">
      <div class="smartcms-field">
        <label for="title">제목</label>
        <input class="smartcms-input" id="title" name="title" value="<?= smartcms_h($post['title']) ?>" required>
      </div>
      <div class="smartcms-field">
        <label for="content">내용</label>
        <textarea class="smartcms-textarea" id="content" name="content" rows="12" required><?= smartcms_h($post['content']) ?></textarea>
      </div>
      <div class="smartcms-actions">
        <?php if (smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)): ?>
          <label class="smartcms-check-field">
            <input type="checkbox" name="is_notice" value="1" <?= (int)$post['is_notice'] === 1 ? 'checked' : '' ?>>
            공지글
          </label>
        <?php endif; ?>
        <label class="smartcms-check-field">
          <input type="checkbox" name="is_secret" value="1" <?= (int)$post['is_secret'] === 1 ? 'checked' : '' ?>>
          비밀글
        </label>
      </div>
      <div class="smartcms-actions">
        <?= smartcms_button('수정 저장', 'submit') ?>
        <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/board/view/') . '?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id'])) ?>">상세로</a>
      </div>
    </form>

    <form class="smartcms-danger-form" method="post">
      <input type="hidden" name="action" value="hide">
      <button class="smartcms-danger-btn" type="submit">글 숨김 처리</button>
      <p class="smartcms-text-muted">데이터는 삭제하지 않고 목록에서 숨깁니다.</p>
    </form>
  </section>
</main>
<?php smartcms_render_foot(); ?>
