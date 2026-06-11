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
$recent_board_posts = smartcms_board_recent_posts_by_key((string)$board['board_key'], 5);

$active_menu = in_array((string)$board['board_key'], ['notice', 'free', 'qna'], true)
    ? (string)$board['board_key']
    : 'boards';
$SMARTCMS_HEAD = ['title' => (string)$post['title'], 'body_class' => 'bg-light', 'active_menu' => $active_menu, 'main_class' => 'min-vh-100'];
require SMARTCMS_ROOT . '/head.php';
?>

<div class="container-fluid container-xxl pt-4 pt-lg-5">
  <div class="row g-4 align-items-start">
    <section class="col-12 col-md-8 col-lg-9">
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

    <aside class="col-12 col-md-4 col-lg-3">
      <div class="sticky-top" style="top: 5.5rem;">
        <section class="card border shadow-sm mb-4">
          <div class="card-body p-4">
            <p class="text-uppercase small fw-bold text-primary mb-2 letter-spacing-1"><?= smartcms_h($board['board_name']) ?></p>
            <p class="mb-4 text-secondary small fw-medium"><?= smartcms_h((string)($board['description'] ?? '게시판을 확인하세요.')) ?></p>
            <div class="d-grid gap-2">
              <a class="btn btn-primary rounded-pill fw-bold shadow-sm" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
                <i class="bi bi-pencil-square me-1"></i>글쓰기
              </a>
              <a class="btn btn-outline-secondary rounded-pill btn-sm fw-bold shadow-none" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
                <i class="bi bi-list-ul me-1"></i>목록으로
              </a>
            </div>
          </div>
        </section>

        <section class="card border shadow-sm overflow-hidden">
          <header class="card-header bg-white border-bottom p-4">
            <h3 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2 text-uppercase letter-spacing-1">
              <i class="bi bi-clock-history text-primary"></i>
              최근 글
            </h3>
          </header>
          <div class="card-body p-0">
            <div class="list-group list-group-flush small">
              <?php foreach ($recent_board_posts as $recent): ?>
                <a class="list-group-item list-group-item-action bg-white px-4 py-3 border-0 border-bottom d-flex align-items-center gap-3"
                   href="<?= smartcms_h(smartcms_board_post_url((string)$recent['board_key'], (int)$recent['id'])) ?>">
                  <span class="text-dark fw-bold text-truncate flex-grow-1"><?= smartcms_h(smartcms_board_truncate_title((string)$recent['title'], (int)($recent['title_length_limit'] ?? 0))) ?></span>
                  <i class="bi bi-chevron-right text-secondary opacity-50"></i>
                </a>
              <?php endforeach; ?>
              <?php if (!$recent_board_posts): ?>
                <div class="p-4 text-center text-secondary small opacity-75 fw-medium">최근 글이 없습니다.</div>
              <?php endif; ?>
            </div>
          </div>
        </section>
      </div>
    </aside>
  </div>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
