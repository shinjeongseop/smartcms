<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();

// 통계 데이터 조회 (실제 운영 시 캐싱 고려 가능)
try {
    $stats = [
        'users' => (int)smartcms_db()->query("SELECT COUNT(*) FROM " . smartcms_table('users'))->fetchColumn(),
        'boards' => (int)smartcms_db()->query("SELECT COUNT(*) FROM " . smartcms_table('boards'))->fetchColumn(),
        'posts' => (int)smartcms_db()->query("SELECT COUNT(*) FROM " . smartcms_table('board_posts'))->fetchColumn(),
        'today_logs' => (int)smartcms_db()->query("SELECT COUNT(*) FROM " . smartcms_table('access_logs') . " WHERE created_at >= CURDATE()")->fetchColumn(),
    ];

    // 최근 가입 회원 5명
    $recent_users = smartcms_db()->query("SELECT id, name, email, created_at FROM " . smartcms_table('users') . " ORDER BY id DESC LIMIT 5")->fetchAll();

    // 최근 시스템 로그 5건
    $recent_logs = smartcms_db()->query("SELECT access_type, target_type, created_at, status_code FROM " . smartcms_table('access_logs') . " ORDER BY id DESC LIMIT 5")->fetchAll();
} catch (Throwable $e) {
    $stats = ['users' => 0, 'boards' => 0, 'posts' => 0, 'today_logs' => 0];
    $recent_users = [];
    $recent_logs = [];
}

$SMARTCMS_HEAD = ['title' => '대시보드', 'body_class' => 'smartcms-admin-page'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_admin_page_header($admin, '대시보드', 'dashboard');
?>

<div class="row g-4 mb-4">
    <!-- 요약 카드 세트 -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card sc-stat-card h-100 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-label-primary p-2 rounded" style="background-color: rgba(105, 108, 255, 0.1); color: #696cff;"><i class="bi bi-people fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">전체 회원</small>
                        <h4 class="card-title mb-0 fw-bold text-dark"><?= number_format($stats['users']) ?></h4>
                    </div>
                </div>
                <div class="mt-3 small">
                    <span class="text-success fw-medium"><i class="bi bi-chevron-up me-1"></i>0.0%</span>
                    <span class="text-secondary ms-1">지난주 대비</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card sc-stat-card h-100 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-label-info p-2 rounded" style="background-color: rgba(3, 195, 236, 0.1); color: #03c3ec;"><i class="bi bi-layout-text-window fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">운영 게시판</small>
                        <h4 class="card-title mb-0 fw-bold text-dark"><?= number_format($stats['boards']) ?></h4>
                    </div>
                </div>
                <div class="mt-3 small">
                    <span class="text-success fw-medium"><i class="bi bi-chevron-up me-1"></i>0.0%</span>
                    <span class="text-secondary ms-1">지난주 대비</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card sc-stat-card h-100 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-label-success p-2 rounded" style="background-color: rgba(113, 221, 55, 0.1); color: #71dd37;"><i class="bi bi-chat-left-text fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">전체 게시물</small>
                        <h4 class="card-title mb-0 fw-bold text-dark"><?= number_format($stats['posts']) ?></h4>
                    </div>
                </div>
                <div class="mt-3 small">
                    <span class="text-success fw-medium"><i class="bi bi-chevron-up me-1"></i>0.0%</span>
                    <span class="text-secondary ms-1">지난주 대비</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card sc-stat-card h-100 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-label-warning p-2 rounded" style="background-color: rgba(255, 171, 0, 0.1); color: #ffab00;"><i class="bi bi-activity fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">오늘의 로그</small>
                        <h4 class="card-title mb-0 fw-bold text-dark"><?= number_format($stats['today_logs']) ?></h4>
                    </div>
                </div>
                <div class="mt-3 small">
                    <span class="text-danger fw-medium"><i class="bi bi-chevron-down me-1"></i>0.0%</span>
                    <span class="text-secondary ms-1">지난주 대비</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card sc-admin-card h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">최근 가입 회원</h5>
                <a href="/admin/users/" class="btn btn-sm btn-label-primary" style="background-color: rgba(105, 108, 255, 0.1); color: #696cff;">전체보기</a>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive">
                    <table class="table table-hover border-top mb-0">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 small text-uppercase text-secondary">회원</th>
                                <th class="px-4 py-3 small text-uppercase text-secondary">가입일</th>
                                <th class="px-4 py-3 small text-uppercase text-secondary text-end">액션</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $u): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="badge bg-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                                <?= smartcms_admin_initial($u['name']) ?>
                                            </div>
                                            <div>
                                                <div class="fw-medium text-dark"><?= smartcms_h($u['name']) ?></div>
                                                <small class="text-secondary"><?= smartcms_h($u['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 small text-secondary">
                                        <?= date('Y-m-d', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <button class="btn btn-icon btn-sm"><i class="bi bi-three-dots-vertical"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card sc-admin-card h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">최근 시스템 활동</h5>
                <a href="/admin/logs/" class="btn btn-sm btn-label-secondary" style="background-color: rgba(133, 146, 163, 0.1); color: #8592a3;">로그 전체보기</a>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="list-group list-group-flush border-top">
                    <?php foreach ($recent_logs as $log): ?>
                        <div class="list-group-item border-0 px-4 py-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <?php
                                $log_theme = [
                                    'login_success' => 'success',
                                    'login_fail' => 'danger',
                                    'permission_denied' => 'warning',
                                    'page_view' => 'info'
                                ][$log['access_type']] ?? 'secondary';
                                $log_color = [
                                    'success' => '#71dd37',
                                    'danger' => '#ff3e1d',
                                    'warning' => '#ffab00',
                                    'info' => '#03c3ec',
                                    'secondary' => '#8592a3'
                                ][$log_theme];
                                ?>
                                <div class="p-2 rounded me-3" style="background-color: <?= $log_color ?>20; color: <?= $log_color ?>;">
                                    <i class="bi bi-dot fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-medium small text-dark"><?= smartcms_h($log['access_type']) ?></div>
                                    <small class="text-secondary opacity-75"><?= smartcms_h($log['target_type']) ?> · Status: <?= (int)$log['status_code'] ?></small>
                                </div>
                            </div>
                            <small class="text-secondary"><?= date('H:i', strtotime($log['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$recent_logs): ?>
                        <div class="text-center py-5 text-secondary small">활동 기록이 없습니다.</div>
                    <?php endif; ?>
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
