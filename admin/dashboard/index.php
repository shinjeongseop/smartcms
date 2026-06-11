<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';

$admin = smartcms_admin_user();

// 통계 데이터 조회
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

$SMARTCMS_HEAD = ['title' => '대시보드', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'dashboard'];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<section class="row g-4 mb-4" aria-label="통계 요약">
  <!-- 요약 카드 세트 -->
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card border shadow-sm h-100 bg-white overflow-hidden">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar flex-shrink-0 me-3">
            <span class="badge bg-primary-subtle text-primary p-3 rounded-3 shadow-sm"><i class="bi bi-people fs-4"></i></span>
          </div>
          <div>
            <small class="d-block text-secondary mb-1 fw-bold text-uppercase letter-spacing-1">전체 회원</small>
            <h2 class="h4 card-title mb-0 fw-bold text-dark"><?= number_format($stats['users']) ?></h2>
          </div>
        </div>
        <div class="mt-3 small fw-medium">
          <span class="text-success"><i class="bi bi-chevron-up me-1"></i>0.0%</span>
          <span class="text-secondary ms-1 opacity-75">지난주 대비</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card border shadow-sm h-100 bg-white overflow-hidden">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar flex-shrink-0 me-3">
            <span class="badge bg-info-subtle text-info p-3 rounded-3 shadow-sm"><i class="bi bi-layout-text-window fs-4"></i></span>
          </div>
          <div>
            <small class="d-block text-secondary mb-1 fw-bold text-uppercase letter-spacing-1">운영 게시판</small>
            <h2 class="h4 card-title mb-0 fw-bold text-dark"><?= number_format($stats['boards']) ?></h2>
          </div>
        </div>
        <div class="mt-3 small fw-medium">
          <span class="text-success"><i class="bi bi-chevron-up me-1"></i>0.0%</span>
          <span class="text-secondary ms-1 opacity-75">지난주 대비</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card border shadow-sm h-100 bg-white overflow-hidden">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar flex-shrink-0 me-3">
            <span class="badge bg-success-subtle text-success p-3 rounded-3 shadow-sm"><i class="bi bi-chat-left-text fs-4"></i></span>
          </div>
          <div>
            <small class="d-block text-secondary mb-1 fw-bold text-uppercase letter-spacing-1">전체 게시물</small>
            <h2 class="h4 card-title mb-0 fw-bold text-dark"><?= number_format($stats['posts']) ?></h2>
          </div>
        </div>
        <div class="mt-3 small fw-medium">
          <span class="text-success"><i class="bi bi-chevron-up me-1"></i>0.0%</span>
          <span class="text-secondary ms-1 opacity-75">지난주 대비</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card border shadow-sm h-100 bg-white overflow-hidden">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar flex-shrink-0 me-3">
            <span class="badge bg-warning-subtle text-warning p-3 rounded-3 shadow-sm"><i class="bi bi-activity fs-4"></i></span>
          </div>
          <div>
            <small class="d-block text-secondary mb-1 fw-bold text-uppercase letter-spacing-1">오늘의 로그</small>
            <h2 class="h4 card-title mb-0 fw-bold text-dark"><?= number_format($stats['today_logs']) ?></h2>
          </div>
        </div>
        <div class="mt-3 small fw-medium">
          <span class="text-danger"><i class="bi bi-chevron-down me-1"></i>0.0%</span>
          <span class="text-secondary ms-1 opacity-75">지난주 대비</span>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="row g-4">
  <div class="col-12 col-lg-6">
    <section class="card border shadow-sm h-100">
      <header class="card-header bg-white border-bottom p-4 d-flex align-items-center justify-content-between">
        <h2 class="h5 mb-0 fw-bold text-dark">최근 가입 회원</h2>
        <a href="/admin/users/" class="btn btn-sm btn-primary-subtle text-primary fw-bold rounded-pill px-3 shadow-none border-0">전체보기</a>
      </header>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light sc-admin-table-head">
              <tr>
                <th scope="col" class="px-4 py-3">회원</th>
                <th scope="col" class="px-4 py-3">가입일</th>
                <th scope="col" class="px-4 py-3 text-end">액션</th>
              </tr>
            </thead>
            <tbody class="table-group-divider">
              <?php foreach ($recent_users as $u): ?>
                <tr>
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm sc-admin-avatar-40">
                        <?= smartcms_h(mb_substr((string)$u['name'], 0, 1)) ?>
                      </div>
                      <div class="lh-sm">
                        <div class="fw-bold text-dark small mb-1"><?= smartcms_h($u['name']) ?></div>
                        <div class="text-xs text-secondary opacity-75 fw-medium"><?= smartcms_h($u['email']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 small text-secondary fw-medium">
                    <time datetime="<?= date('Y-m-d', strtotime($u['created_at'])) ?>"><?= date('Y-m-d', strtotime($u['created_at'])) ?></time>
                  </td>
                  <td class="px-4 py-3 text-end">
                    <button class="btn btn-link btn-sm text-secondary p-0 shadow-none border-0"><i class="bi bi-three-dots-vertical fs-5"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
  <div class="col-12 col-lg-6">
    <section class="card border shadow-sm h-100">
      <header class="card-header bg-white border-bottom p-4 d-flex align-items-center justify-content-between">
        <h2 class="h5 mb-0 fw-bold text-dark">최근 시스템 활동</h2>
        <a href="/admin/logs/" class="btn btn-sm btn-secondary-subtle text-secondary fw-bold rounded-pill px-3 shadow-none border-0">로그 전체보기</a>
      </header>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php foreach ($recent_logs as $log): ?>
            <div class="list-group-item bg-white border-0 px-4 py-3 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <?php
                $log_theme = [
                    'login_success' => 'success',
                    'login_fail' => 'danger',
                    'permission_denied' => 'warning',
                    'page_view' => 'info'
                ][$log['access_type']] ?? 'secondary';
                ?>
                <div class="badge bg-<?= $log_theme ?>-subtle text-<?= $log_theme ?> p-2.5 rounded-3 me-3 shadow-sm border border-<?= $log_theme ?>-subtle">
                  <i class="bi bi-record-circle fs-5"></i>
                </div>
                <div class="lh-sm">
                  <div class="fw-bold small text-dark mb-1"><?= smartcms_h($log['access_type']) ?></div>
                  <div class="text-xs text-secondary fw-medium"><?= smartcms_h($log['target_type']) ?> · Status: <?= (int)$log['status_code'] ?></div>
                </div>
              </div>
              <time class="small text-secondary fw-bold opacity-75" datetime="<?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>"><?= date('H:i', strtotime($log['created_at'])) ?></time>
            </div>
          <?php endforeach; ?>
          <?php if (!$recent_logs): ?>
            <div class="text-center py-5 text-secondary small fw-medium opacity-75">최근 활동 기록이 없습니다.</div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
