<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/board.php';
require_once __DIR__ . '/../../common/ui/layout.php';
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

smartcms_render_head([
    'title' => '게시판 관리',
    'body_class' => 'smartcms-admin-page',
]);
?>
<?= smartcms_admin_page_header($admin, '게시판 관리', 'boards') ?>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <section class="card smartcms-panel smartcms-admin-panel">
    <h2 class="smartcms-section-title">게시판 생성</h2>
    <form class="smartcms-grid smartcms-form-grid" method="post">
      <?= smartcms_csrf_input() ?>
      <input type="hidden" name="action" value="create">
      <div class="smartcms-field">
        <label for="board_key">게시판 키</label>
        <input class="form-control smartcms-input" id="board_key" name="board_key" placeholder="notice" required>
      </div>
      <div class="smartcms-field">
        <label for="board_name">게시판 이름</label>
        <input class="form-control smartcms-input" id="board_name" name="board_name" placeholder="공지사항" required>
      </div>
      <div class="smartcms-field">
        <label for="description">설명</label>
        <input class="form-control smartcms-input" id="description" name="description">
      </div>
      <?= smartcms_button('게시판 생성', 'submit') ?>
    </form>
  </section>

  <section class="card smartcms-panel smartcms-admin-panel smartcms-stack-panel">
    <h2 class="smartcms-section-title">게시판 목록</h2>
    <div class="table-responsive smartcms-table-wrap">
      <table class="table table-hover align-middle smartcms-table">
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
                <strong><?= smartcms_h($board['board_name']) ?></strong><br>
                <a class="smartcms-text-muted" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>"><?= smartcms_h($board['board_key']) ?></a>
              </td>
              <td>
                목록 <?= smartcms_h($board['board_list_level'] ?? 0) ?> /
                보기 <?= smartcms_h($board['board_view_level'] ?? 0) ?> /
                쓰기 <?= smartcms_h($board['board_write_level'] ?? 8) ?>
              </td>
              <td><?= smartcms_h($board['status']) ?></td>
              <td>
                <form class="smartcms-inline-form" method="post">
                  <?= smartcms_csrf_input() ?>
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="board_key" value="<?= smartcms_h($board['board_key']) ?>">
                  <input class="form-control form-control-sm smartcms-compact-input" name="board_name" value="<?= smartcms_h($board['board_name']) ?>" required>
                  <input class="form-control form-control-sm smartcms-compact-input" name="description" value="<?= smartcms_h($board['description'] ?? '') ?>" placeholder="설명">
                  <?php foreach (['board_list_level' => '목록', 'board_view_level' => '보기', 'board_write_level' => '쓰기', 'board_comment_level' => '댓글'] as $field => $label): ?>
                    <select class="form-select form-select-sm smartcms-select" name="<?= smartcms_h($field) ?>">
                      <?php for ($level = 0; $level <= 10; $level++): ?>
                        <option value="<?= $level ?>" <?= $level === (int)($board[$field] ?? 0) ? 'selected' : '' ?>><?= smartcms_h($label) ?> <?= $level ?></option>
                      <?php endfor; ?>
                    </select>
                  <?php endforeach; ?>
                  <label class="smartcms-check-field">
                    <input type="checkbox" name="allow_guest_list" value="1" <?= (int)($board['allow_guest_list'] ?? 0) === 1 ? 'checked' : '' ?>>
                    목록 게스트
                  </label>
                  <label class="smartcms-check-field">
                    <input type="checkbox" name="allow_guest_view" value="1" <?= (int)($board['allow_guest_view'] ?? 0) === 1 ? 'checked' : '' ?>>
                    보기 게스트
                  </label>
                  <select class="form-select form-select-sm smartcms-select" name="status">
                    <?php foreach (['active', 'hidden', 'disabled'] as $status): ?>
                      <option value="<?= smartcms_h($status) ?>" <?= $status === $board['status'] ? 'selected' : '' ?>><?= smartcms_h($status) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select class="form-select form-select-sm smartcms-select" name="permission_status">
                    <?php foreach (['active', 'disabled'] as $status): ?>
                      <option value="<?= smartcms_h($status) ?>" <?= $status === ($board['permission_status'] ?? 'active') ? 'selected' : '' ?>>권한 <?= smartcms_h($status) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-primary btn-sm" type="submit">저장</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$boards): ?>
            <tr>
              <td colspan="4">생성된 게시판이 없습니다.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  <?= smartcms_admin_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
