<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$search = trim((string)($_GET['search'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;

$message = '';
$message_type = 'info';
$total_users = 0;
$total_pages = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $user_id = (int)($_POST['user_id'] ?? 0);
    $role = (string)($_POST['role'] ?? 'user');
    $level = (int)($_POST['level'] ?? 2);
    $status = (string)($_POST['status'] ?? 'active');

    if ($user_id > 0) {
        smartcms_execute(
            "UPDATE " . smartcms_table('users') . " SET role = :role, level = :level, status = :status WHERE id = :id",
            ['role' => $role, 'level' => $level, 'status' => $status, 'id' => $user_id]
        );
        $message = '회원 권한이 성공적으로 수정되었습니다.';
        $message_type = 'success';
    }
}

try {
    $where = " WHERE 1=1";
    $params = [];
    if ($search !== '') {
        $where .= " AND (email LIKE :search OR name LIKE :search)";
        $params['search'] = "%$search%";
    }

    $total_users = (int)smartcms_fetch_value("SELECT COUNT(*) FROM " . smartcms_table('users') . $where, $params);
    $total_pages = (int)ceil($total_users / $limit);
    $query = "SELECT id, email, name, role, level, status, last_login_at, created_at FROM " . smartcms_table('users') . $where . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $users = smartcms_fetch_all($query, $params);
} catch (Throwable $e) {
    $users = [];
    $message = '회원 목록을 불러오는 중 오류 발생: ' . $e->getMessage();
    $message_type = 'error';
}

$SMARTCMS_HEAD = ['title' => '회원 관리', 'body_class' => 'smartcms-admin-page'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_admin_page_header($admin, '회원 관리', 'users');
?>

<?php if ($message): ?>
  <?= smartcms_alert($message, $message_type) ?>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="get" class="row g-3 align-items-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="이름 또는 이메일 검색" value="<?= smartcms_h($search) ?>">
                    <button class="btn btn-primary px-4" type="submit">검색</button>
                </div>
            </div>
            <?php if ($search !== ''): ?>
                <div class="col-auto"><a href="?" class="btn btn-link btn-sm text-secondary">필터 초기화</a></div>
            <?php endif; ?>
        </form>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <header class="card-header bg-white border-bottom py-4 px-4 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold">전체 회원 목록</h5>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold"><?= number_format($total_users) ?>명 조회됨</span>
    </header>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead class="bg-light text-secondary small text-uppercase">
                <tr>
                    <th class="ps-4 py-3">ID</th>
                    <th class="py-3">회원 정보</th>
                    <th class="py-3">역할 / 레벨</th>
                    <th>상태</th>
                    <th>가입일</th>
                    <th class="text-end pe-4 py-3">권한 수정</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="ps-4 text-secondary opacity-75">#<?= $user['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="badge bg-primary-subtle text-primary rounded-circle p-2 lh-1" style="width:32px; height:32px; font-size: 0.75rem;"><?= mb_substr($user['name'],0,1) ?></div>
                                <div class="lh-sm">
                                    <div class="fw-bold text-emphasis small"><?= smartcms_h($user['name']) ?></div>
                                    <div class="text-xs text-secondary opacity-75"><?= smartcms_h($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <span class="badge bg-secondary-subtle text-secondary small text-uppercase" style="font-size:0.65rem;"><?= $user['role'] ?></span>
                                <span class="badge bg-light text-dark border-0 small">LV <?= $user['level'] ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?> p-1 rounded-circle me-1" style="width:6px; height:6px; display:inline-block;"></span>
                            <span class="small text-capitalize"><?= $user['status'] ?></span>
                        </td>
                        <td class="text-secondary small"><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                        <td class="text-end pe-4">
                            <form class="d-inline-flex gap-2 align-items-center" method="post">
                                <?= smartcms_csrf_input() ?>
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="role" class="form-select form-select-sm bg-light border-0" style="width:100px;">
                                    <?php foreach(['admin', 'manager', 'user'] as $r): ?>
                                        <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="level" class="form-select form-select-sm bg-light border-0" style="width:65px;">
                                    <?php for($i=1;$i<=10;$i++): ?>
                                        <option value="<?= $i ?>" <?= $user['level'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-none">변경</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$users): ?>
        <div class="text-center py-5 text-secondary">표시할 회원이 없습니다.</div>
    <?php endif; ?>
</section>

<?php if ($total_pages > 1): ?>
<nav class="mt-4 d-flex justify-content-center" aria-label="Page navigation">
      <ul class="pagination pagination-sm mb-0 gap-1">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link rounded-circle border-0 shadow-sm" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link rounded-circle border-0 shadow-sm mx-1" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
          <a class="page-link rounded-circle border-0 shadow-sm" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
      </ul>
</nav>
<?php endif; ?>

<?= smartcms_admin_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
