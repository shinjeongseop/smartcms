<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';

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

$SMARTCMS_HEAD = ['title' => '회원 관리', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'users'];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<!-- [MESSAGES] 알림 영역 -->
<?php if ($message): ?>
  <aside class="alert alert-<?= $message_type === 'error' ? 'danger' : ( $message_type === 'success' ? 'success' : 'info' ) ?> d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-info-circle-fill fs-5"></i>
    <div class="fw-medium"><?= smartcms_h($message) ?></div>
  </aside>
<?php endif; ?>

<!-- [SEARCH] 검색 영역 -->
<section class="card border shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="get" class="row g-3 align-items-center" role="search">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="이름 또는 이메일 검색" value="<?= smartcms_h($search) ?>">
                    <button class="btn btn-primary px-4 shadow-none" type="submit">검색</button>
                </div>
            </div>
            <?php if ($search !== ''): ?>
                <div class="col-auto">
                    <a href="?" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-none">필터 초기화</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- [LIST] 회원 목록 테이블 영역 -->
<section class="card border shadow-sm overflow-hidden">
    <header class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-dark">전체 회원 목록</h5>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold">
            총 <?= number_format($total_users) ?>명
        </span>
    </header>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead class="table-light small text-uppercase fw-bold text-secondary">
                <tr>
                    <th scope="col" class="ps-4 py-3">ID</th>
                    <th scope="col" class="py-3">회원 정보</th>
                    <th scope="col" class="py-3">권한 설정</th>
                    <th scope="col" class="py-3">상태</th>
                    <th scope="col" class="py-3">가입일</th>
                    <th scope="col" class="text-end pe-4 py-3">액션</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="ps-4 text-secondary small">#<?= (int)$user['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                    <?= smartcms_h(mb_substr((string)$user['name'], 0, 1)) ?>
                                </div>
                                <div class="lh-sm">
                                    <div class="fw-bold text-dark mb-1"><?= smartcms_h($user['name']) ?></div>
                                    <div class="text-xs text-secondary"><?= smartcms_h($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <form class="d-flex gap-2 align-items-center" method="post" id="form-user-<?= (int)$user['id'] ?>">
                                <?= smartcms_csrf_input() ?>
                                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                <select name="role" aria-label="역할" class="form-select form-select-sm" style="width:110px;">
                                    <?php foreach(['admin', 'manager', 'user'] as $r): ?>
                                        <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="level" aria-label="레벨" class="form-select form-select-sm" style="width:70px;">
                                    <?php for($i=1;$i<=10;$i++): ?>
                                        <option value="<?= $i ?>" <?= (int)$user['level'] === $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="status" aria-label="상태" class="form-select form-select-sm" style="width:90px;">
                                    <?php foreach(['active', 'blocked'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $user['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">정상</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">차단</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-secondary small">
                            <time datetime="<?= date('Y-m-d', strtotime($user['created_at'])) ?>">
                                <?= smartcms_h(date('Y-m-d', strtotime($user['created_at']))) ?>
                            </time>
                        </td>
                        <td class="text-end pe-4">
                            <button type="submit" form="form-user-<?= (int)$user['id'] ?>" class="btn btn-primary btn-sm px-3 shadow-none">저장</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- [PAGINATION] 하단 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
    <footer class="card-footer bg-white border-top py-4">
        <nav aria-label="회원 목록 페이지">
            <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link rounded-circle border shadow-sm" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link rounded-circle border shadow-sm mx-1" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link rounded-circle border shadow-sm" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </footer>
    <?php endif; ?>
</section>

<?php if (!$users): ?>
    <div class="text-center py-5">
        <i class="bi bi-person-exclamation fs-1 text-secondary opacity-25"></i>
        <p class="text-secondary mt-3">조회된 회원이 없습니다.</p>
    </div>
<?php endif; ?>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
