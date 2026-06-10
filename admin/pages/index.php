<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
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

$SMARTCMS_HEAD = ['title' => '페이지 권한', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'pages'];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<?php if ($message !== ''): ?>
  <div class="alert alert-<?= $message_type === 'error' ? 'danger' : ( $message_type === 'success' ? 'success' : 'info' ) ?> d-flex align-items-start gap-2 mb-4" role="alert">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div><?= smartcms_h($message) ?></div>
  </div>
<?php endif; ?>

<section class="card border-0 shadow-sm">
  <header class="card-header bg-white border-bottom py-4 px-4 d-flex align-items-center justify-content-between">
    <div>
      <h5 class="card-title mb-1 fw-bold">등록된 페이지 권한</h5>
      <p class="text-secondary small mb-0">페이지가 호출될 때 자동으로 등록되는 접근 제어 목록입니다.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold"><?= count($pages) ?>개</span>
  </header>
  <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 text-nowrap">
        <thead class="bg-light text-secondary small text-uppercase">
          <tr>
            <th class="ps-4 py-3">페이지 정보</th>
            <th class="py-3">경로</th>
            <th class="py-3">권한 (V/W/M)</th>
            <th class="py-3">옵션 및 상태</th>
            <th class="text-end pe-4 py-3">빠른 설정</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pages as $page): ?>
            <tr>
              <td class="ps-4">
                <div class="fw-bold text-emphasis small"><?= smartcms_h($page['title']) ?></div>
                <div class="text-xs text-secondary opacity-75"><?= smartcms_h($page['page_key']) ?></div>
              </td>
              <td class="text-secondary small"><?= smartcms_h($page['page_path']) ?></td>
              <td>
                <div class="d-flex gap-2">
                  <span class="badge bg-light text-dark border-0 fw-medium px-2 py-1">V LV <?= (int)$page['page_view_level'] ?></span>
                  <span class="badge bg-light text-dark border-0 fw-medium px-2 py-1">W LV <?= (int)$page['page_write_level'] ?></span>
                  <span class="badge bg-light text-dark border-0 fw-medium px-2 py-1">M LV <?= (int)$page['page_manage_level'] ?></span>
                </div>
              </td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <span class="badge bg-<?= (int)$page['allow_guest'] === 1 ? 'info' : 'secondary' ?>-subtle text-<?= (int)$page['allow_guest'] === 1 ? 'info' : 'secondary' ?> text-uppercase fw-semibold" style="font-size:0.65rem;">
                    <?= (int)$page['allow_guest'] === 1 ? 'Guest Allowed' : 'Member Only' ?>
                  </span>
                  <span class="small d-flex align-items-center">
                    <span class="badge bg-<?= $page['status'] === 'active' ? 'success' : 'danger' ?> p-1 rounded-circle me-2" style="width:6px; height:6px;"></span>
                    <span class="text-capitalize text-secondary"><?= smartcms_h($page['status']) ?></span>
                  </span>
                </div>
              </td>
              <td class="text-end pe-4">
                <form class="d-inline-flex gap-2 align-items-center" method="post">
                  <?= smartcms_csrf_input() ?>
                  <input type="hidden" name="id" value="<?= smartcms_h($page['id']) ?>">
                  <select class="form-select form-select-sm bg-light border-0" name="page_view_level" style="width:100px;">
                    <?php for ($level = 0; $level <= 10; $level++): ?>
                      <option value="<?= $level ?>" <?= $level === (int)$page['page_view_level'] ? 'selected' : '' ?>>LV <?= $level ?></option>
                    <?php endfor; ?>
                  </select>
                  <div class="form-check form-switch small mb-0 px-2 ms-2">
                    <input class="form-check-input" type="checkbox" name="allow_guest" value="1" id="allow_guest_<?= smartcms_h($page['id']) ?>" <?= (int)$page['allow_guest'] === 1 ? 'checked' : '' ?>>
                    <label class="form-check-label text-secondary text-xs" for="allow_guest_<?= smartcms_h($page['id']) ?>">Guest</label>
                  </div>
                  <button class="btn btn-primary btn-sm px-3 shadow-none" type="submit">변경</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (!$pages): ?>
      <div class="text-center py-5 text-secondary">등록된 페이지 권한이 없습니다.</div>
    <?php endif; ?>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
