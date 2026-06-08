<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
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
        $permission_status = (string)($_POST['permission_status'] ?? 'active');

        if ($board_key === '' || $board_name === '' || !in_array($status, ['active', 'hidden', 'disabled'], true) || !in_array($permission_status, ['active', 'disabled'], true)) {
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
            smartcms_execute(
                "UPDATE " . smartcms_table('board_permissions') . "
                 SET board_name = :board_name,
                     board_list_level = :board_list_level,
                     board_view_level = :board_view_level,
                     board_write_level = :board_write_level,
                     board_comment_level = :board_comment_level,
                     allow_guest_list = :allow_guest_list,
                     allow_guest_view = :allow_guest_view,
                     status = :status
                 WHERE board_key = :board_key",
                [
                    'board_key' => $board_key,
                    'board_name' => $board_name,
                    'board_list_level' => max(0, min(10, (int)($_POST['board_list_level'] ?? 0))),
                    'board_view_level' => max(0, min(10, (int)($_POST['board_view_level'] ?? 0))),
                    'board_write_level' => max(0, min(10, (int)($_POST['board_write_level'] ?? 8))),
                    'board_comment_level' => max(0, min(10, (int)($_POST['board_comment_level'] ?? 2))),
                    'allow_guest_list' => isset($_POST['allow_guest_list']) ? 1 : 0,
                    'allow_guest_view' => isset($_POST['allow_guest_view']) ? 1 : 0,
                    'status' => $permission_status,
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

$SMARTCMS_HEAD = ['title' => '게시판 관리', 'body_class' => 'smartcms-admin-page'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_admin_page_header($admin, '게시판 관리', 'boards');
?>

<?php if ($message !== ''): ?>
  <?= smartcms_alert($message, $message_type) ?>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-4">게시판 생성</h2>
        <form class="row g-3" method="post">
          <?= smartcms_csrf_input() ?>
          <input type="hidden" name="action" value="create">
          <div class="col-12 col-lg-4">
            <label for="board_key" class="form-label">게시판 키</label>
            <input class="form-control" id="board_key" name="board_key" placeholder="notice" required>
          </div>
          <div class="col-12 col-lg-4">
            <label for="board_name" class="form-label">게시판 이름</label>
            <input class="form-control" id="board_name" name="board_name" placeholder="공지사항" required>
          </div>
          <div class="col-12 col-lg-4">
            <label for="description" class="form-label">설명</label>
            <input class="form-control" id="description" name="description">
          </div>
          <div class="col-12">
            <?= smartcms_button('게시판 생성', 'submit') ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-4">게시판 목록</h2>
        <div class="table-responsive border rounded bg-white">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>게시판</th>
                <th>권한</th>
                <th>상태</th>
                <th>관리</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($boards as $board): ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= smartcms_h($board['board_name']) ?></div>
                    <a class="text-body-secondary text-decoration-none" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>"><?= smartcms_h($board['board_key']) ?></a>
                  </td>
                  <td class="text-body-secondary">
                    목록 <?= smartcms_h($board['board_list_level'] ?? 0) ?> /
                    보기 <?= smartcms_h($board['board_view_level'] ?? 0) ?> /
                    쓰기 <?= smartcms_h($board['board_write_level'] ?? 8) ?>
                  </td>
                  <td><?= smartcms_h($board['status']) ?></td>
                  <td>
                    <form class="row g-2" method="post">
                      <?= smartcms_csrf_input() ?>
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="board_key" value="<?= smartcms_h($board['board_key']) ?>">
                      <div class="col-12 col-xl-3">
                        <input class="form-control form-control-sm" name="board_name" value="<?= smartcms_h($board['board_name']) ?>" required>
                      </div>
                      <div class="col-12 col-xl-3">
                        <input class="form-control form-control-sm" name="description" value="<?= smartcms_h($board['description'] ?? '') ?>" placeholder="설명">
                      </div>
                      <?php foreach (['board_list_level' => '목록', 'board_view_level' => '보기', 'board_write_level' => '쓰기', 'board_comment_level' => '댓글'] as $field => $label): ?>
                        <div class="col-6 col-xl-2">
                          <select class="form-select form-select-sm" name="<?= smartcms_h($field) ?>">
                            <?php for ($level = 0; $level <= 10; $level++): ?>
                              <option value="<?= $level ?>" <?= $level === (int)($board[$field] ?? 0) ? 'selected' : '' ?>><?= smartcms_h($label) ?> <?= $level ?></option>
                            <?php endfor; ?>
                          </select>
                        </div>
                      <?php endforeach; ?>
                      <div class="col-12 col-xl-2">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="allow_guest_list" value="1" id="guest_list_<?= smartcms_h($board['board_key']) ?>" <?= (int)($board['allow_guest_list'] ?? 0) === 1 ? 'checked' : '' ?>>
                          <label class="form-check-label" for="guest_list_<?= smartcms_h($board['board_key']) ?>">목록 게스트</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="allow_guest_view" value="1" id="guest_view_<?= smartcms_h($board['board_key']) ?>" <?= (int)($board['allow_guest_view'] ?? 0) === 1 ? 'checked' : '' ?>>
                          <label class="form-check-label" for="guest_view_<?= smartcms_h($board['board_key']) ?>">보기 게스트</label>
                        </div>
                      </div>
                      <div class="col-6 col-xl-2">
                        <select class="form-select form-select-sm" name="status">
                          <?php foreach (['active', 'hidden', 'disabled'] as $status): ?>
                            <option value="<?= smartcms_h($status) ?>" <?= $status === $board['status'] ? 'selected' : '' ?>><?= smartcms_h($status) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-6 col-xl-2">
                        <select class="form-select form-select-sm" name="permission_status">
                          <?php foreach (['active', 'disabled'] as $status): ?>
                            <option value="<?= smartcms_h($status) ?>" <?= $status === ($board['permission_status'] ?? 'active') ? 'selected' : '' ?>>권한 <?= smartcms_h($status) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-12 col-xl-2">
                        <button class="btn btn-primary btn-sm w-100" type="submit">저장</button>
                      </div>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$boards): ?>
                <tr>
                  <td colspan="4" class="text-body-secondary">생성된 게시판이 없습니다.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= smartcms_admin_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
