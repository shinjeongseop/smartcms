<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
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
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-primary-subtle text-primary p-3 rounded"><i class="bi bi-people fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">전체 회원</small>
                        <h4 class="card-title mb-0 fw-bold"><?= number_format($stats['users']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-info-subtle text-info p-3 rounded"><i class="bi bi-layout-text-window fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">운영 게시판</small>
                        <h4 class="card-title mb-0 fw-bold"><?= number_format($stats['boards']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-success-subtle text-success p-3 rounded"><i class="bi bi-chat-left-text fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">전체 게시물</small>
                        <h4 class="card-title mb-0 fw-bold"><?= number_format($stats['posts']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="badge bg-warning-subtle text-warning p-3 rounded"><i class="bi bi-activity fs-4"></i></span>
                    </div>
                    <div>
                        <small class="d-block text-secondary mb-1">오늘의 로그</small>
                        <h4 class="card-title mb-0 fw-bold"><?= number_format($stats['today_logs']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">최근 가입 회원</h5>
                <a href="/admin/users/" class="btn btn-sm btn-light border">전체보기</a>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="list-group list-group-flush">
                    <?php foreach ($recent_users as $u): ?>
                        <div class="list-group-item border-0 px-4 py-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-primary rounded-circle p-2 me-3" style="width:32px; height:32px; line-height:16px;">
                                    <?= smartcms_admin_initial($u['name']) ?>
                                </div>
                                <div>
                                    <div class="fw-medium"><?= smartcms_h($u['name']) ?></div>
                                    <small class="text-secondary opacity-75"><?= smartcms_h($u['email']) ?></small>
                                </div>
                            </div>
                            <small class="text-secondary"><?= date('m-d H:i', strtotime($u['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold">최근 시스템 활동</h5>
                <a href="/admin/logs/" class="btn btn-sm btn-light border">로그 전체보기</a>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="list-group list-group-flush">
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
                                ?>
                                <div class="p-2 bg-<?= $log_theme ?>-subtle text-<?= $log_theme ?> rounded me-3">
                                    <i class="bi bi-dot fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-medium small"><?= smartcms_h($log['access_type']) ?></div>
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
