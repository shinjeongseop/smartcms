<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
$admin = smartcms_admin_user();
$access_logs = [];
$login_logs = [];
$board_logs = [];
$message = '';
$message_type = 'info';

try {
    $stmt = smartcms_db()->query(
        "SELECT id, user_id, access_type, target_type, target_key, request_path, method, result, status_code, created_at
         FROM " . smartcms_table('access_logs') . "
         ORDER BY id DESC
         LIMIT 50"
    );
    $access_logs = $stmt->fetchAll();

    $stmt = smartcms_db()->query(
        "SELECT id, user_id, email, result, created_at
         FROM " . smartcms_table('login_logs') . "
         ORDER BY id DESC
         LIMIT 50"
    );
    $login_logs = $stmt->fetchAll();

    $stmt = smartcms_db()->query(
        "SELECT id, board_id, post_id, user_id, action, message, created_at
         FROM " . smartcms_table('board_audit_logs') . "
         ORDER BY id DESC
         LIMIT 50"
    );
    $board_logs = $stmt->fetchAll();
} catch (Throwable $e) {
    $message = '로그를 불러오지 못했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

$SMARTCMS_HEAD = ['title' => '접속 로그', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'logs'];
require SMARTCMS_ROOT . '/head.php'; // head.php는 이미 관리자 레이아웃을 처리합니다.
?>

<?php if ($message !== ''): ?>
  <div class="alert alert-<?= $message_type === 'error' ? 'danger' : ( $message_type === 'success' ? 'success' : 'info' ) ?> d-flex align-items-start gap-2 mb-4" role="alert">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div><?= smartcms_h($message) ?></div>
  </div>
<?php endif; ?>

<section class="card border-0 shadow-sm">
    <header class="card-header bg-white border-bottom-0 pt-4 px-4">
        <ul class="nav nav-tabs card-header-tabs border-bottom-0 gap-2" id="logTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold border-0 py-3" id="access-tab" data-bs-toggle="tab" data-bs-target="#access-panel" type="button">접속 로그</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold border-0 py-3" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel" type="button">로그인 이력</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold border-0 py-3" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-panel" type="button">게시판 감사</button>
            </li>
        </ul>
    </header>
    <div class="tab-content">
        <!-- 접속 로그 패널 -->
        <div class="tab-pane fade show active" id="access-panel" role="tabpanel" aria-labelledby="access-tab">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4">일시</th>
                            <th>유형</th>
                            <th>대상/경로</th>
                            <th>IP (Hash)</th>
                            <th class="text-end pe-4">상태</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($access_logs as $log): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column small">
                                        <span><?= smartcms_h(date('Y-m-d', strtotime((string)$log['created_at']))) ?></span>
                                        <span class="text-secondary opacity-50"><?= smartcms_h(date('H:i:s', strtotime((string)$log['created_at']))) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary small text-uppercase" style="font-size:0.65rem;">
                                        <?= smartcms_h($log['access_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium small"><?= smartcms_h($log['target_type']) ?>: <?= smartcms_h($log['target_key'] ?? '-') ?></div>
                                    <div class="text-xs text-secondary opacity-75"><?= smartcms_h($log['request_path']) ?></div>
                                </td>
                                <td><code class="text-xs text-muted"><?= smartcms_h(substr((string)($log['ip_hash'] ?? 'N/A'), 0, 10)) ?>...</code></td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-<?= $log['status_code'] >= 400 ? 'danger' : 'success' ?>-subtle text-<?= $log['status_code'] >= 400 ? 'danger' : 'success' ?>">
                                        <?= (int)$log['status_code'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 로그인 로그 패널 -->
        <div class="tab-pane fade" id="login-panel" role="tabpanel" aria-labelledby="login-tab">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4">일시</th>
                            <th>이메일</th>
                            <th>결과</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($login_logs as $log): ?>
                            <tr>
                                <td class="ps-4"><?= smartcms_h($log['created_at']) ?></td>
                                <td><?= smartcms_h($log['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $log['result'] === 'success' ? 'success' : 'danger' ?> text-uppercase" style="font-size:0.7rem;">
                                        <?= smartcms_h($log['result']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 게시판 감사 로그 패널 -->
        <div class="tab-pane fade" id="audit-panel" role="tabpanel" aria-labelledby="audit-tab">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4">일시</th>
                            <th>액션</th>
                            <th>내용</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($board_logs as $log): ?>
                            <tr>
                                <td class="ps-4"><?= smartcms_h($log['created_at']) ?></td>
                                <td><span class="badge bg-info-subtle text-info border-0"><?= smartcms_h($log['action']) ?></span></td>
                                <td class="small text-secondary"><?= smartcms_h($log['message']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
