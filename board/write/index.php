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

$SMARTCMS_HEAD = ['title' => '글쓰기', 'body_class' => 'bg-body'];
require SMARTCMS_ROOT . '/head.php';
$form_action = 'create';
$form_enctype = 'multipart/form-data';
$form_values = ['title' => '', 'content' => '', 'is_notice' => false, 'is_secret' => false];
$show_attachments = (int)($board['use_attachments'] ?? 1) === 1 && smartcms_has_level((int)($board['board_upload_level'] ?? 8), $user);
$submit_label = '등록하기';
$back_url = smartcms_board_url((string)$board['board_key']);
$back_label = '목록으로';
$recent_board_posts = smartcms_board_recent_posts_by_key((string)$board['board_key'], 5);
?>

<div class="container-fluid container-xxl py-4 py-lg-5">
  <header class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5">
      <p class="text-uppercase small fw-semibold text-primary mb-2">Write</p>
      <h1 class="display-6 fw-bold mb-2"><?= smartcms_h($board['board_name']) ?> 글쓰기</h1>
      <p class="text-body-secondary mb-0">게시판 권한에 맞는 회원만 글을 작성할 수 있습니다.</p>
    </div>
  </header>

  <?php if ($message !== ''): ?>
    <div class="alert alert-<?= $message_type === 'error' ? 'danger' : 'success' ?> d-flex align-items-start gap-2 mb-4" role="alert">
      <i class="bi bi-info-circle-fill mt-1"></i>
      <div><?= smartcms_h($message) ?></div>
    </div>
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
          <p class="mb-3 text-body-secondary">글쓰기 전에 게시판 성격과 공지사항을 확인하세요.</p>
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
            <?php if (!$recent_board_posts): ?>
              <div class="text-body-secondary">최근 글이 없습니다.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
