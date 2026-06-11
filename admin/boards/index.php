<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'create') {
        $result = smartcms_board_create(
            (string)($_POST['board_key'] ?? ''),
            (string)($_POST['board_name'] ?? ''),
            (string)($_POST['description'] ?? ''),
            (int)$admin['id']
        );
        $message = $result['message'];
        $message_type = $result['ok'] ? 'success' : 'error';
    }

    if ($action === 'update') {
        $board_key = smartcms_board_key((string)($_POST['board_key'] ?? ''));
        $board_name = trim((string)($_POST['board_name'] ?? ''));
        $status = (string)($_POST['status'] ?? 'active');

        if ($board_key === '' || $board_name === '' || !in_array($status, ['active', 'hidden', 'disabled'], true)) {
            $message = '게시판 설정값을 확인하세요.';
            $message_type = 'error';
        } else {
            smartcms_execute(
                "UPDATE " . smartcms_table('boards') . "
                 SET board_name = :board_name, description = :description, status = :status
                 WHERE board_key = :board_key",
                [
                    'board_key' => $board_key,
                    'board_name' => $board_name,
                    'description' => trim((string)($_POST['description'] ?? '')) ?: null,
                    'status' => $status,
                ]
            );
            $message = '게시판 설정을 저장했습니다.';
            $message_type = 'success';
        }
    }
}

$boards = [];
try {
    $boards = smartcms_board_list();
} catch (Throwable $e) {
    $message = '게시판 목록을 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

$SMARTCMS_HEAD = [
    'title' => '게시판 관리',
    'page_heading' => '게시판 운영',
    'active_menu' => 'boards'
];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<section class="container-fluid py-2">
  <header class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h1 class="h3 fw-bold mb-1 text-dark">게시판 관리</h1>
      <p class="text-secondary small mb-0 fw-medium">시스템의 게시판 생성 및 운영 설정을 통합 관리합니다.</p>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 small fw-bold">
        <li class="breadcrumb-item"><a href="/admin/dashboard/" class="text-decoration-none text-secondary">관리자 홈</a></li>
        <li class="breadcrumb-item active text-primary" aria-current="page">게시판 관리</li>
      </ol>
    </nav>
  </header>

  <?php if ($message !== ''): ?>
    <aside class="alert alert-<?= $message_type === 'error' ? 'danger' : ($message_type === 'success' ? 'success' : 'info') ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill fs-5"></i>
      <div class="fw-medium small"><?= smartcms_h($message) ?></div>
    </aside>
  <?php endif; ?>

  <div class="row g-4">
    <!-- 새 게시판 생성 섹션 -->
    <section class="col-12">
      <div class="card border shadow-sm mb-4 border-top border-primary border-4 overflow-hidden">
        <div class="card-body p-4 p-lg-5">
          <div class="d-flex align-items-center gap-2 mb-4">
            <div class="p-2 bg-primary-subtle text-primary rounded-3 shadow-sm"><i class="bi bi-plus-circle-fill fs-5"></i></div>
            <h2 class="h5 mb-0 fw-bold text-dark">새 게시판 생성</h2>
          </div>
          <form class="row g-3" method="post">
            <?= smartcms_csrf_input() ?>
            <input type="hidden" name="action" value="create">
            <div class="col-12 col-md-4">
              <label for="board_key" class="form-label fw-bold small text-dark">게시판 키 <span class="text-primary">*</span></label>
              <input class="form-control py-2" id="board_key" name="board_key" placeholder="영문/숫자 (예: notice)" required>
            </div>
            <div class="col-12 col-md-4">
              <label for="board_name" class="form-label fw-bold small text-dark">게시판 이름 <span class="text-primary">*</span></label>
              <input class="form-control py-2" id="board_name" name="board_name" placeholder="표시될 이름 (예: 공지사항)" required>
            </div>
            <div class="col-12 col-md-4">
              <label for="description" class="form-label fw-bold small text-dark">간략 설명</label>
              <input class="form-control py-2" id="description" name="description" placeholder="게시판 용도 설명">
            </div>
            <div class="col-12 mt-4">
              <button class="btn btn-primary px-4 py-2 fw-bold shadow-sm" type="submit">저장 및 생성</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- 운영 중인 게시판 목록 섹션 -->
    <section class="col-12">
      <article class="card border shadow-sm overflow-hidden">
        <header class="card-header bg-white border-bottom py-4 px-4 d-flex align-items-center justify-content-between">
          <h2 class="h5 mb-0 fw-bold text-dark">운영 중인 게시판</h2>
          <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold">
            총 <?= count($boards) ?>개 활성
          </span>
        </header>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead class="table-light">
              <tr class="small text-uppercase fw-bold text-secondary">
                <th scope="col" class="ps-4 py-3">게시판 정보</th>
                <th scope="col" class="py-3">권한 요약 (Level)</th>
                <th scope="col" class="py-3">상태</th>
                <th scope="col" class="text-end pe-4 py-3">관리</th>
              </tr>
            </thead>
            <tbody class="table-group-divider">
              <?php foreach ($boards as $board): ?>
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="p-2.5 bg-primary-subtle text-primary rounded shadow-sm"><i class="bi bi-chat-dots fs-5"></i></div>
                      <div>
                        <div class="fw-bold text-dark mb-1"><?= smartcms_h($board['board_name']) ?></div>
                        <a class="text-xs text-primary text-decoration-none fw-bold opacity-75" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>" target="_blank">
                          <i class="bi bi-box-arrow-up-right me-1"></i>/board/<?= smartcms_h($board['board_key']) ?>
                        </a>
                      </div>
                    </div>
                  </td>
                  <td class="py-3">
                    <div class="d-flex gap-2">
                      <span class="badge bg-light text-dark border fw-bold px-2 py-1 small">목록 <?= $board['board_list_level'] ?></span>
                      <span class="badge bg-light text-dark border fw-bold px-2 py-1 small">보기 <?= $board['board_view_level'] ?></span>
                      <span class="badge bg-light text-dark border fw-bold px-2 py-1 small">쓰기 <?= $board['board_write_level'] ?></span>
                    </div>
                  </td>
                  <td class="py-3">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-<?= $board['status'] === 'active' ? 'success' : 'secondary' ?> p-1 rounded-circle" style="width:8px; height:8px; display:inline-block;"></span>
                      <span class="small fw-bold text-capitalize text-dark"><?= $board['status'] ?></span>
                    </div>
                  </td>
                  <td class="text-end pe-4 py-3">
                    <form class="d-inline-flex gap-2 align-items-center" method="post">
                      <?= smartcms_csrf_input() ?>
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="board_key" value="<?= smartcms_h($board['board_key']) ?>">
                      <input class="form-control form-control-sm fw-bold" name="board_name" value="<?= smartcms_h($board['board_name']) ?>" style="width:140px;" required>
                      <select class="form-select form-select-sm fw-bold" name="status" style="width:100px;">
                        <?php foreach (['active', 'hidden', 'disabled'] as $status): ?>
                          <option value="<?= smartcms_h($status) ?>" <?= $status === $board['status'] ? 'selected' : '' ?>><?= $status ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-primary btn-sm px-3 fw-bold shadow-none" type="submit">변경</button>
                      <a href="/admin/boards/settings.php?key=<?= urlencode($board['board_key']) ?>" class="btn btn-light border btn-sm shadow-none" title="상세 설정"><i class="bi bi-gear-fill"></i></a>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$boards): ?>
                <tr>
                  <td colspan="4" class="text-center py-5 text-secondary fw-medium opacity-75">생성된 게시판이 없습니다.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </div>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
