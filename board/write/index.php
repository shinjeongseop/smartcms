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
$SMARTCMS_HEAD = ['title' => '새 글 작성', 'body_class' => 'bg-light', 'active_menu' => $active_menu, 'main_class' => 'flex-grow-1'];
require SMARTCMS_ROOT . '/head.php';

// Skin variables
$form_action = 'create';
$form_enctype = 'multipart/form-data';
$form_values = ['title' => '', 'content' => '', 'is_notice' => false, 'is_secret' => false];
$show_attachments = (int)($board['use_attachments'] ?? 1) === 1 && smartcms_has_level((int)($board['board_upload_level'] ?? 8), $user);
$submit_label = '게시글 등록';
$back_url = smartcms_board_url((string)$board['board_key']);
$back_label = '목록으로';
$recent_board_posts = smartcms_board_recent_posts_by_key((string)$board['board_key'], 5);
?>

<div class="container-fluid container-xxl pt-4 pt-lg-5">
  <div class="row g-4 align-items-start">
    <section class="col-12 col-md-8 col-lg-9">
      <!-- 헤더 카드 -->
      <header class="card border shadow-sm mb-4 overflow-hidden bg-primary text-white">
        <div class="card-body p-4 p-lg-5">
          <p class="text-uppercase small fw-bold text-white-50 mb-2 letter-spacing-1"><?= smartcms_h($board['board_key']) ?> Community</p>
          <h1 class="display-6 fw-bold mb-0"><?= smartcms_h($board['board_name']) ?> 새 글 쓰기</h1>
        </div>
      </header>

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
    </section>

    <aside class="col-12 col-md-4 col-lg-3">
      <div class="sticky-top" style="top: 5.5rem;">
        <section class="card border shadow-sm mb-4 overflow-hidden">
          <header class="card-header bg-white border-bottom p-4">
            <h3 class="h6 fw-bold mb-0 text-dark text-uppercase letter-spacing-1">가이드라인</h3>
          </header>
          <div class="card-body p-4">
            <p class="text-secondary small fw-medium mb-3">글쓰기 전에 게시판의 성격과 공지사항을 반드시 확인해 주세요.</p>
            <div class="d-grid gap-2">
              <a class="btn btn-light border btn-sm fw-bold shadow-none" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">목록 보기</a>
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
                <a class="list-group-item list-group-item-action px-4 py-3 border-0 border-bottom d-flex align-items-center gap-3"
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
