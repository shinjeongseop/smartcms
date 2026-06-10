<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$message = '';
$message_type = 'info';

$board_key = smartcms_board_key((string)($_GET['key'] ?? ''));
if ($board_key === '') {
    smartcms_redirect('/admin/boards/');
}

// 게시판 및 권한 정보 조회
$board = smartcms_fetch_one("SELECT * FROM " . smartcms_table('boards') . " WHERE board_key = :key", ['key' => $board_key]);
$permission = smartcms_fetch_one("SELECT * FROM " . smartcms_table('board_permissions') . " WHERE board_key = :key", ['key' => $board_key]);

if (!$board || !$permission) {
    smartcms_redirect('/admin/boards/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();

    try {
        // 1. 게시판 기본 설정 업데이트
        smartcms_execute(
            "UPDATE " . smartcms_table('boards') . "
             SET board_name = :name, description = :desc, skin = :skin, items_per_page = :ipp,
                 use_editor = :editor, use_comments = :comments, use_attachments = :attachments, status = :status
             WHERE board_key = :key",
            [
                'key'         => $board_key,
                'name'        => trim((string)$_POST['board_name']),
                'desc'        => trim((string)$_POST['description']),
                'skin'        => trim((string)$_POST['skin']),
                'ipp'         => max(1, min(100, (int)$_POST['items_per_page'])),
                'editor'      => isset($_POST['use_editor']) ? 1 : 0,
                'comments'    => isset($_POST['use_comments']) ? 1 : 0,
                'attachments' => isset($_POST['use_attachments']) ? 1 : 0,
                'status'      => (string)$_POST['status']
            ]
        );

        // 2. 권한 설정 업데이트
        smartcms_execute(
            "UPDATE " . smartcms_table('board_permissions') . "
             SET board_name = :name, board_list_level = :list, board_view_level = :view,
                 board_write_level = :write, board_comment_level = :comment,
                 allow_guest_list = :g_list, allow_guest_view = :g_view
             WHERE board_key = :key",
            [
                'key'    => $board_key,
                'name'   => trim((string)$_POST['board_name']),
                'list'   => (int)$_POST['board_list_level'],
                'view'   => (int)$_POST['board_view_level'],
                'write'  => (int)$_POST['board_write_level'],
                'comment' => (int)$_POST['board_comment_level'],
                'g_list' => isset($_POST['allow_guest_list']) ? 1 : 0,
                'g_view' => isset($_POST['allow_guest_view']) ? 1 : 0
            ]
        );

        $message = '게시판 상세 설정이 저장되었습니다.';
        $message_type = 'success';

        // 데이터 새로고침
        $board = smartcms_fetch_one("SELECT * FROM " . smartcms_table('boards') . " WHERE board_key = :key", ['key' => $board_key]);
        $permission = smartcms_fetch_one("SELECT * FROM " . smartcms_table('board_permissions') . " WHERE board_key = :key", ['key' => $board_key]);
    } catch (Throwable $e) {
        $message = '오류 발생: ' . $e->getMessage();
        $message_type = 'error';
    }
}

$SMARTCMS_HEAD = [
    'title' => '게시판 상세 설정',
    'active_menu' => 'boards'
];
require SMARTCMS_ROOT . '/head.php';
?>

<article class="p-4">
  <header class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1">게시판 상세 설정</h1>
      <p class="text-secondary mb-0">게시판의 기능, 디자인 및 접근 권한을 관리합니다.</p>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/admin/boards/" class="text-decoration-none text-secondary">게시판 관리</a></li>
        <li class="breadcrumb-item active" aria-current="page">상세 설정</li>
      </ol>
    </nav>
  </header>

  <?php if ($message): ?>
    <div class="alert alert-<?= $message_type === 'error' ? 'danger' : ($message_type === 'success' ? 'success' : 'info') ?> d-flex align-items-start gap-2 mb-4" role="alert">
      <i class="bi bi-info-circle-fill mt-1"></i>
      <div><?= smartcms_h($message) ?></div>
    </div>
  <?php endif; ?>

  <form method="post">
    <?= smartcms_csrf_input() ?>
    <div class="row g-4">
      <!-- 기본 정보 -->
      <div class="col-12 col-xl-8">
        <section class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white border-bottom py-3 px-4">
            <h2 class="card-title h6 mb-0 fw-bold">기본 정보 및 디자인</h2>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">게시판 키</label>
                <input type="text" class="form-control bg-light" value="<?= smartcms_h($board['board_key']) ?>" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">게시판 이름</label>
                <input type="text" name="board_name" class="form-control" value="<?= smartcms_h($board['board_name']) ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold">게시판 설명</label>
                <textarea name="description" class="form-control" rows="2"><?= smartcms_h($board['description'] ?? '') ?></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">적용 스킨</label>
                <select name="skin" class="form-select">
                  <?php foreach(['default', 'table', 'card', 'gallery', 'qna', 'notice', 'faq', 'webzine'] as $skin): ?>
                    <option value="<?= $skin ?>" <?= $board['skin'] === $skin ? 'selected' : '' ?>><?= ucfirst($skin) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">페이지당 게시물 수</label>
                <input type="number" name="items_per_page" class="form-control" value="<?= (int)$board['items_per_page'] ?>" min="1" max="100">
              </div>
            </div>
          </div>
        </section>

        <section class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom py-3 px-4">
            <h2 class="card-title h6 mb-0 fw-bold">접근 및 권한 설정</h2>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <?php foreach (['list' => '목록', 'view' => '보기', 'write' => '쓰기', 'comment' => '댓글'] as $key => $label): ?>
                <div class="col-md-3">
                  <label class="form-label small fw-bold"><?= $label ?> 권한</label>
                  <select name="board_<?= $key ?>_level" class="form-select">
                    <?php for($i=0; $i<=10; $i++): ?>
                      <option value="<?= $i ?>" <?= (int)$permission["board_{$key}_level"] === $i ? 'selected' : '' ?>>Level <?= $i ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              <?php endforeach; ?>
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 border">
                  <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="allow_guest_list" id="g_list" <?= $permission['allow_guest_list'] ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="g_list">비회원 목록 접근 허용</label>
                  </div>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="allow_guest_view" id="g_view" <?= $permission['allow_guest_view'] ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="g_view">비회원 게시글 읽기 허용</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- 기능 스위치 및 저장 -->
      <div class="col-12 col-xl-4">
        <section class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white border-bottom py-3 px-4">
            <h2 class="card-title h6 mb-0 fw-bold">기능 활성화</h2>
          </div>
          <div class="card-body p-4">
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" name="use_editor" id="u_editor" <?= $board['use_editor'] ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold" for="u_editor">WYSIWYG 에디터 사용</label>
            </div>
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" name="use_comments" id="u_comments" <?= $board['use_comments'] ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold" for="u_comments">댓글 기능 활성화</label>
            </div>
            <div class="form-check form-switch mb-4">
              <input class="form-check-input" type="checkbox" name="use_attachments" id="u_files" <?= $board['use_attachments'] ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold" for="u_files">첨부파일 업로드 허용</label>
            </div>
            <label class="form-label small fw-bold">게시판 상태</label>
            <select name="status" class="form-select mb-4">
              <option value="active" <?= $board['status'] === 'active' ? 'selected' : '' ?>>Active (정상 운영)</option>
              <option value="hidden" <?= $board['status'] === 'hidden' ? 'selected' : '' ?>>Hidden (숨김)</option>
              <option value="disabled" <?= $board['status'] === 'disabled' ? 'selected' : '' ?>>Disabled (사용 중지)</option>
            </select>
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
              <i class="bi bi-check-circle-fill me-2"></i>상세 설정 저장하기
            </button>
            <a href="/admin/boards/" class="btn btn-light border w-100 mt-2 py-2 text-secondary">목록으로 돌아가기</a>
          </div>
        </section>
      </div>
    </div>
  </form>
</article>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
