<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$page = max(1, (int)($_GET['page'] ?? 1));
$type = (string)($_GET['type'] ?? '');
$limit = 30;
$offset = ($page - 1) * $limit;

try {
    $where = "";
    $params = [];
    if ($type !== '') {
        $where = " WHERE access_type = :type";
        $params['type'] = $type;
    }

    $count_sql = "SELECT COUNT(*) FROM " . smartcms_table('access_logs') . $where;
    $total_logs = $type !== '' ? (int)smartcms_fetch_value($count_sql, $params) : (int)smartcms_db()->query($count_sql)->fetchColumn();
    $total_pages = (int)ceil($total_logs / $limit);

    $query = "SELECT l.*, u.name as user_name, u.email as user_email
              FROM " . smartcms_table('access_logs') . " l
              LEFT JOIN " . smartcms_table('users') . " u ON l.user_id = u.id
              $where ORDER BY l.id DESC LIMIT $limit OFFSET $offset";

    $logs = $type !== '' ? smartcms_fetch_all($query, $params) : smartcms_db()->query($query)->fetchAll();
} catch (Throwable $e) {
    $logs = [];
    $error = $e->getMessage();
}

$SMARTCMS_HEAD = ['title' => '접속 로그', 'body_class' => 'smartcms-admin-page'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_admin_page_header($admin, '접속 로그', 'logs');
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 d-flex align-items-center gap-3">
        <span class="text-secondary fw-medium">유형 필터:</span>
        <div class="btn-group btn-group-sm shadow-none">
            <a href="?" class="btn btn-<?= $type === '' ? 'primary' : 'outline-secondary' ?> px-3">전체</a>
            <?php foreach (['page_view', 'login_success', 'login_fail', 'permission_denied'] as $t): ?>
                <a href="?type=<?= $t ?>" class="btn btn-<?= $type === $t ? 'primary' : 'outline-secondary' ?>"><?= smartcms_h($t) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold">시스템 접속 기록</h5>
        <span class="text-secondary small">총 <?= number_format($total_logs) ?>건</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead class="table-light text-secondary small text-uppercase">
                <tr>
                    <th class="ps-4">일시</th>
                    <th>사용자</th>
                    <th>유형</th>
                    <th>대상</th>
                    <th>경로 / 메소드</th>
                    <th>상태</th>
                    <th class="pe-4">IP (Hash)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex flex-column small">
                                <span><?= date('Y-m-d', strtotime($log['created_at'])) ?></span>
                                <span class="text-secondary opacity-50"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if ($log['user_id']): ?>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium"><?= smartcms_h($log['user_name']) ?></span>
                                    <span class="text-xs text-secondary opacity-75"><?= smartcms_h($log['user_email']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-secondary opacity-50 small">Guest</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $type_theme = [
                                'login_success' => 'success',
                                'login_fail' => 'danger',
                                'permission_denied' => 'warning',
                                'page_view' => 'info'
                            ][$log['access_type']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $type_theme ?>-subtle text-<?= $type_theme ?> small border-0"><?= smartcms_h($log['access_type']) ?></span>
                        </td>
                        <td><span class="small text-secondary"><?= smartcms_h($log['target_type']) ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border fw-normal"><?= smartcms_h($log['method']) ?></span>
                                <span class="text-xs text-secondary text-truncate" style="max-width: 200px;"><?= smartcms_h($log['request_path']) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold <?= $log['status_code'] >= 400 ? 'text-danger' : 'text-success' ?>">
                                <?= (int)$log['status_code'] ?>
                            </span>
                        </td>
                        <td class="pe-4"><code class="text-xs text-muted"><?= substr($log['ip_hash'] ?? 'N/A', 0, 12) ?>...</code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$logs): ?>
            <div class="text-center py-5 text-secondary">기록된 로그가 없습니다.</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($total_pages > 1): ?>
    <div class="mt-4 d-flex justify-content-center">
        <ul class="pagination pagination-sm gap-1">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link rounded-circle border-0 shadow-sm" href="?type=<?= $type ?>&page=<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link rounded-circle border-0 shadow-sm mx-1" href="?type=<?= $type ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link rounded-circle border-0 shadow-sm" href="?type=<?= $type ?>&page=<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a>
            </li>
        </ul>
    </div>
<?php endif; ?>

<?= smartcms_admin_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
