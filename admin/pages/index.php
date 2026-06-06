<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $id = (int)($_POST['id'] ?? 0);
    $view_level = max(0, min(10, (int)($_POST['page_view_level'] ?? 0)));
    $write_level = max(0, min(10, (int)($_POST['page_write_level'] ?? 8)));
    $manage_level = max(0, min(10, (int)($_POST['page_manage_level'] ?? 8)));
    $allow_guest = isset($_POST['allow_guest']) ? 1 : 0;
    $status = (string)($_POST['status'] ?? 'active');

    if (!in_array($status, ['active', 'disabled'], true)) {
        $message = '올바르지 않은 페이지 상태입니다.';
        $message_type = 'error';
    } else {
        smartcms_execute(
            "UPDATE " . smartcms_table('page_permissions') . "
             SET page_view_level = :page_view_level,
                 page_write_level = :page_write_level,
                 page_manage_level = :page_manage_level,
                 allow_guest = :allow_guest,
                 status = :status
             WHERE id = :id",
            [
                'id' => $id,
                'page_view_level' => $view_level,
                'page_write_level' => $write_level,
                'page_manage_level' => $manage_level,
                'allow_guest' => $allow_guest,
                'status' => $status,
            ]
        );
        $message = '페이지 권한을 저장했습니다.';
        $message_type = 'success';
    }
}

$pages = [];
try {
    $stmt = smartcms_db()->query(
        "SELECT id, page_key, page_path, title, page_view_level, page_write_level, page_manage_level, allow_guest, status, updated_at
         FROM " . smartcms_table('page_permissions') . "
         ORDER BY id DESC
         LIMIT 100"
    );
    $pages = $stmt->fetchAll();
} catch (Throwable $e) {
    $message = '페이지 권한 목록을 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

smartcms_render_head(['title' => '페이지 권한', 'body_class' => 'smartcms-admin-page']);
echo smartcms_admin_page_header($admin, '페이지 권한', 'pages');
?>

<?php if ($message !== ''): ?>
  <?= smartcms_alert($message, $message_type) ?>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    <h2 class="h5 fw-bold mb-2">등록된 페이지 권한</h2>
    <p class="text-body-secondary mb-4">페이지가 `smartcms_require_page_view()`를 호출하면 여기에 자동 등록됩니다.</p>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>페이지</th>
            <th>경로</th>
            <th>보기</th>
            <th>쓰기</th>
            <th>관리</th>
            <th>게스트</th>
            <th>상태</th>
            <th>저장</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pages as $page): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= smartcms_h($page['title']) ?></div>
                <small class="text-body-secondary"><?= smartcms_h($page['page_key']) ?></small>
              </td>
              <td><?= smartcms_h($page['page_path']) ?></td>
              <td colspan="6">
                <form class="row g-2 align-items-center" method="post">
                  <?= smartcms_csrf_input() ?>
                  <input type="hidden" name="id" value="<?= smartcms_h($page['id']) ?>">
                  <div class="col-6 col-xl-2">
                    <select class="form-select form-select-sm" name="page_view_level">
                      <?php for ($level = 0; $level <= 10; $level++): ?>
                        <option value="<?= $level ?>" <?= $level === (int)$page['page_view_level'] ? 'selected' : '' ?>>보기 <?= $level ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="col-6 col-xl-2">
                    <select class="form-select form-select-sm" name="page_write_level">
                      <?php for ($level = 0; $level <= 10; $level++): ?>
                        <option value="<?= $level ?>" <?= $level === (int)$page['page_write_level'] ? 'selected' : '' ?>>쓰기 <?= $level ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="col-6 col-xl-2">
                    <select class="form-select form-select-sm" name="page_manage_level">
                      <?php for ($level = 0; $level <= 10; $level++): ?>
                        <option value="<?= $level ?>" <?= $level === (int)$page['page_manage_level'] ? 'selected' : '' ?>>관리 <?= $level ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="col-6 col-xl-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="allow_guest" value="1" id="allow_guest_<?= smartcms_h($page['id']) ?>" <?= (int)$page['allow_guest'] === 1 ? 'checked' : '' ?>>
                      <label class="form-check-label" for="allow_guest_<?= smartcms_h($page['id']) ?>">게스트</label>
                    </div>
                  </div>
                  <div class="col-6 col-xl-2">
                    <select class="form-select form-select-sm" name="status">
                      <?php foreach (['active', 'disabled'] as $status): ?>
                        <option value="<?= smartcms_h($status) ?>" <?= $status === $page['status'] ? 'selected' : '' ?>><?= smartcms_h($status) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-6 col-xl-2">
                    <button class="btn btn-primary btn-sm w-100" type="submit">저장</button>
                  </div>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$pages): ?>
            <tr>
              <td colspan="8" class="text-body-secondary">등록된 페이지 권한이 없습니다.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= smartcms_admin_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
