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
                'skin'        => smartcms_board_normalize_skin((string)($_POST['skin'] ?? 'default')),
                'ipp'         => max(1, min(100, (int)$_POST['items_per_page'])),
                'editor'      => (string)($_POST['use_editor'] ?? '0') === '1' ? 1 : 0,
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

        $message = '게시판 상세 설정이 성공적으로 저장되었습니다.';
        $message_type = 'success';

        // 데이터 새로고침
        $board = smartcms_fetch_one("SELECT * FROM " . smartcms_table('boards') . " WHERE board_key = :key", ['key' => $board_key]);
        $permission = smartcms_fetch_one("SELECT * FROM " . smartcms_table('board_permissions') . " WHERE board_key = :key", ['key' => $board_key]);
    } catch (Throwable $e) {
        $message = '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage();
        $message_type = 'error';
    }
}

$SMARTCMS_HEAD = [
    'title' => '게시판 상세 설정',
    'page_heading' => '게시판 세부 설정',
    'active_menu' => 'boards'
];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<section>
  <header class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 small fw-bold">
        <li class="breadcrumb-item"><a href="/admin/boards/" class="text-decoration-none text-secondary">게시판 관리</a></li>
        <li class="breadcrumb-item active text-primary" aria-current="page">상세 설정</li>
      </ol>
    </nav>
  </header>

  <?php if ($message): ?>
    <aside class="alert alert-<?= $message_type === 'error' ? 'danger' : ($message_type === 'success' ? 'success' : 'info') ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill fs-5"></i>
      <div class="fw-medium small"><?= smartcms_h($message) ?></div>
    </aside>
  <?php endif; ?>

  <form method="post">
    <?= smartcms_csrf_input() ?>
    <div class="row g-4">
      <!-- 기본 정보 및 디자인 영역 -->
      <div class="col-12 col-xl-8">
        <article class="card border shadow-sm mb-4 overflow-hidden">
          <div class="card-body p-4 p-lg-5">
            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-secondary text-uppercase">게시판 고유 키</label>
                <input type="text" class="form-control py-2 fw-bold" value="<?= smartcms_h($board['board_key']) ?>" readonly>
              </div>
              <div class="col-md-6">
                <label for="board_name" class="form-label fw-bold small text-dark text-uppercase">게시판 표시 이름</label>
                <input type="text" id="board_name" name="board_name" class="form-control py-2" value="<?= smartcms_h($board['board_name']) ?>" required>
              </div>
              <div class="col-12">
                <label for="description" class="form-label fw-bold small text-dark text-uppercase">게시판 상세 설명</label>
                <textarea id="description" name="description" class="form-control" rows="2"><?= smartcms_h($board['description'] ?? '') ?></textarea>
              </div>
              <div class="col-md-6">
                <label for="skin" class="form-label fw-bold small text-dark text-uppercase">적용 디자인 스킨</label>
                <select id="skin" name="skin" class="form-select py-2 fw-bold">
                  <?php foreach (smartcms_board_skin_options() as $skin_key => $skin_label): ?>
                    <option value="<?= smartcms_h($skin_key) ?>" <?= (string)$board['skin'] === $skin_key ? 'selected' : '' ?>><?= smartcms_h($skin_label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label for="items_per_page" class="form-label fw-bold small text-dark text-uppercase">페이지당 게시물 노출 수</label>
                <input type="number" id="items_per_page" name="items_per_page" class="form-control py-2" value="<?= (int)$board['items_per_page'] ?>" min="1" max="100">
              </div>
            </div>
          </div>
        </article>

        <article class="card border shadow-sm overflow-hidden">
          <header class="card-header bg-white border-bottom py-3 px-4">
            <h2 class="card-title h6 mb-0 fw-bold text-dark">접근 권한 및 상세 레벨 제어</h2>
          </header>
          <div class="card-body p-4 p-lg-5">
            <div class="row g-4">
              <?php foreach (['list' => '목록 조회', 'view' => '본문 읽기', 'write' => '글 쓰기', 'comment' => '댓글 쓰기'] as $key => $label): ?>
                <div class="col-md-3">
                  <label class="form-label fw-bold small text-secondary text-uppercase"><?= $label ?></label>
                  <select name="board_<?= $key ?>_level" class="form-select py-2 fw-bold">
                    <?php for($i=0; $i<=10; $i++): ?>
                      <option value="<?= $i ?>" <?= (int)$permission["board_{$key}_level"] === $i ? 'selected' : '' ?>>Level <?= $i ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              <?php endforeach; ?>
              
              <div class="col-12">
                <div class="p-4 bg-light rounded-4 border-0">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="form-check form-switch custom-switch">
                        <input class="form-check-input" type="checkbox" name="allow_guest_list" id="g_list" <?= $permission['allow_guest_list'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold text-dark ms-2" for="g_list">비회원 목록 접근 허용 (Guest List)</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check form-switch custom-switch">
                        <input class="form-check-input" type="checkbox" name="allow_guest_view" id="g_view" <?= $permission['allow_guest_view'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold text-dark ms-2" for="g_view">비회원 본문 읽기 허용 (Guest View)</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </article>
      </div>

      <!-- 기능 스위치 및 액션 패널 -->
      <div class="col-12 col-xl-4">
        <aside class="sticky-top sc-admin-sticky-panel">
          <article class="card border shadow-sm mb-4 overflow-hidden border-top border-primary border-4">
            <header class="card-header bg-white border-bottom py-3 px-4 text-center">
              <h2 class="card-title h6 mb-0 fw-bold text-primary text-uppercase">기능 활성화 스위치</h2>
            </header>
            <div class="card-body p-4">
              <div class="vstack gap-3 mb-4">
                <label class="form-label fw-bold small text-secondary text-uppercase" for="u_editor">본문 작성 방식</label>
                <select class="form-select py-2 fw-bold" name="use_editor" id="u_editor">
                  <option value="0" <?= !(int)$board['use_editor'] ? 'selected' : '' ?>>텍스트 모드</option>
                  <option value="1" <?= (int)$board['use_editor'] ? 'selected' : '' ?>>에디터 모드</option>
                </select>
                <div class="form-text small text-secondary">이 게시판의 글쓰기/수정 화면은 선택한 방식으로만 표시됩니다.</div>
                <div class="form-check form-switch p-3 bg-light rounded-3 border-0">
                  <input class="form-check-input ms-0" type="checkbox" name="use_comments" id="u_comments" <?= $board['use_comments'] ? 'checked' : '' ?>>
                  <label class="form-check-label fw-bold text-dark ms-3" for="u_comments">실시간 댓글 시스템</label>
                </div>
                <div class="form-check form-switch p-3 bg-light rounded-3 border-0">
                  <input class="form-check-input ms-0" type="checkbox" name="use_attachments" id="u_files" <?= $board['use_attachments'] ? 'checked' : '' ?>>
                  <label class="form-check-label fw-bold text-dark ms-3" for="u_files">멀티 첨부파일 업로드</label>
                </div>
              </div>

              <div class="mb-4 pt-2">
                <label for="status" class="form-label fw-bold small text-secondary text-uppercase">게시판 현재 상태</label>
                <select id="status" name="status" class="form-select border-2 fw-bold">
                  <option value="active" <?= $board['status'] === 'active' ? 'selected' : '' ?> class="text-success">● Active (정상 운영)</option>
                  <option value="hidden" <?= $board['status'] === 'hidden' ? 'selected' : '' ?> class="text-warning">● Hidden (목록 숨김)</option>
                  <option value="disabled" <?= $board['status'] === 'disabled' ? 'selected' : '' ?> class="text-danger">● Disabled (영구 중지)</option>
                </select>
              </div>

              <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm mb-2 rounded-3">
                <i class="bi bi-cloud-check-fill me-2"></i>모든 설정 저장하기
              </button>
              <a href="/admin/boards/" class="btn btn-light border-0 w-100 py-2 text-secondary small fw-bold">취소하고 목록으로 돌아가기</a>
            </div>
          </article>
        </aside>
      </div>
    </div>
  </form>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
