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
        $message = '페이지 접근 권한 설정이 저장되었습니다.';
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

$SMARTCMS_HEAD = ['title' => '페이지 권한 관리', 'page_heading' => 'ACL 설정', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'pages'];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<section class="container-fluid py-2">
  <header class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
      총 <?= count($pages) ?>개 등록됨
    </span>
  </header>

  <?php if ($message !== ''): ?>
    <aside class="alert alert-<?= $message_type === 'error' ? 'danger' : ( $message_type === 'success' ? 'success' : 'info' ) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill fs-5"></i>
      <div class="fw-medium small"><?= smartcms_h($message) ?></div>
    </aside>
  <?php endif; ?>

  <article class="card border shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
          <thead class="table-light">
            <tr class="small text-uppercase fw-bold text-secondary">
              <th scope="col" class="ps-4 py-3">페이지 식별 정보</th>
              <th scope="col" class="py-3">URL 경로</th>
              <th scope="col" class="py-3">권한 제어 (View/Write/Manage)</th>
              <th scope="col" class="py-3">옵션 및 상태</th>
              <th scope="col" class="text-end pe-4 py-3">수정</th>
            </tr>
          </thead>
          <tbody class="table-group-divider">
            <?php foreach ($pages as $page): ?>
              <tr>
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-light rounded text-primary border shadow-sm"><i class="bi bi-shield-lock fs-5"></i></div>
                    <div>
                      <div class="fw-bold text-dark small mb-1"><?= smartcms_h($page['title']) ?></div>
                      <div class="text-xs text-secondary opacity-75 fw-medium"><?= smartcms_h($page['page_key']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="py-3">
                  <code class="text-primary small fw-bold"><?= smartcms_h($page['page_path']) ?></code>
                </td>
                <td class="py-3">
                  <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark border fw-bold px-2 py-1 small">V LV <?= (int)$page['page_view_level'] ?></span>
                    <span class="badge bg-light text-dark border fw-bold px-2 py-1 small">W LV <?= (int)$page['page_write_level'] ?></span>
                    <span class="badge bg-light text-dark border fw-bold px-2 py-1 small">M LV <?= (int)$page['page_manage_level'] ?></span>
                  </div>
                </td>
                <td class="py-3">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-<?= (int)$page['allow_guest'] === 1 ? 'info' : 'secondary' ?>-subtle text-<?= (int)$page['allow_guest'] === 1 ? 'info' : 'secondary' ?> text-uppercase fw-bold shadow-none" style="font-size:0.65rem;">
                      <?= (int)$page['allow_guest'] === 1 ? 'Guest Allowed' : 'Auth Required' ?>
                    </span>
                    <div class="d-flex align-items-center gap-1">
                      <span class="badge bg-<?= $page['status'] === 'active' ? 'success' : 'danger' ?> p-1 rounded-circle" style="width:6px; height:6px;"></span>
                      <span class="text-capitalize small fw-bold text-secondary"><?= smartcms_h($page['status']) ?></span>
                    </div>
                  </div>
                </td>
                <td class="text-end pe-4 py-3">
                  <form class="d-inline-flex gap-2 align-items-center" method="post">
                    <?= smartcms_csrf_input() ?>
                    <input type="hidden" name="id" value="<?= smartcms_h($page['id']) ?>">
                    <select class="form-select form-select-sm fw-bold" name="page_view_level" style="width:100px;">
                      <?php for ($level = 0; $level <= 10; $level++): ?>
                        <option value="<?= $level ?>" <?= $level === (int)$page['page_view_level'] ? 'selected' : '' ?>>LV <?= $level ?></option>
                      <?php endfor; ?>
                    </select>
                    <div class="form-check form-switch small mb-0 px-2 ms-2">
                      <input class="form-check-input ms-0" type="checkbox" name="allow_guest" value="1" id="allow_guest_<?= smartcms_h($page['id']) ?>" <?= (int)$page['allow_guest'] === 1 ? 'checked' : '' ?>>
                      <label class="form-check-label text-secondary fw-bold ms-1" style="font-size:0.7rem;" for="allow_guest_<?= smartcms_h($page['id']) ?>">GUEST</label>
                    </div>
                    <button class="btn btn-primary btn-sm px-3 fw-bold shadow-none" type="submit">저장</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if (!$pages): ?>
        <div class="text-center py-5 text-secondary fw-medium opacity-75">등록된 페이지 권한 데이터가 없습니다.</div>
      <?php endif; ?>
  </article>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
