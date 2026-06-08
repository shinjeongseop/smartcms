<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../foot.php';

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

$SMARTCMS_HEAD = ['title' => '글 수정', 'body_class' => 'bg-body'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_site_header((string)$board['board_key']);
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
$recent_board_posts = smartcms_board_recent_posts_by_key((string)$board['board_key'], 5);
?>

<main class="container-fluid container-xxl py-4 py-lg-5">
  <header class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5">
      <p class="text-uppercase small fw-semibold text-primary mb-2">Edit</p>
      <h1 class="display-6 fw-bold mb-2"><?= smartcms_h($board['board_name']) ?> 글 수정</h1>
      <p class="text-body-secondary mb-0">작성자 또는 게시판 관리자만 글을 수정할 수 있습니다.</p>
    </div>
  </header>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <div class="row g-4 align-items-start">
    <div class="col-12 col-md-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
          <?php require smartcms_board_skin_template($board, 'form'); ?>
        </div>
      </div>
    </div>
    <aside class="col-12 col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <p class="text-uppercase small fw-semibold text-primary mb-2"><?= smartcms_h($board['board_name']) ?></p>
          <p class="mb-3 text-body-secondary">수정 후에는 본문과 첨부 파일이 함께 반영됩니다.</p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-secondary btn-sm" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">게시판 보기</a>
            <a class="btn btn-primary btn-sm" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">목록</a>
          </div>
        </div>
      </div>

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
          </div>
        </div>
      </div>
    </aside>
  </div>
</main>

<?= smartcms_site_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
