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
        "SELECT id, user_id, access_type, target_type, target_key, request_path, method, ip_hash, result, status_code, created_at
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
    $message = '로그 데이터를 불러오는 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.';
    $message_type = 'error';
}

$SMARTCMS_HEAD = ['title' => '시스템 로그 관리', 'page_heading' => '활동 로그', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'logs'];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<section>
  <?php if ($message !== ''): ?>
    <aside class="alert alert-<?= $message_type === 'error' ? 'danger' : ( $message_type === 'success' ? 'success' : 'info' ) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill fs-5"></i>
      <div class="fw-medium small"><?= smartcms_h($message) ?></div>
    </aside>
  <?php endif; ?>

  <article class="card border shadow-sm overflow-hidden">
      <header class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <h2 class="h5 mb-0 fw-bold text-dark">활동 로그</h2>
          <ul class="nav nav-pills flex-nowrap overflow-auto gap-2 fw-bold pb-1" id="logTab" role="tablist">
              <li class="nav-item">
                  <button class="nav-link active border-0 shadow-none" id="access-tab" data-bs-toggle="tab" data-bs-target="#access-panel" type="button" role="tab" aria-controls="access-panel" aria-selected="true">
                    <i class="bi bi-activity me-2"></i>접속 로그
                  </button>
              </li>
              <li class="nav-item">
                  <button class="nav-link border-0 shadow-none" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel" type="button" role="tab" aria-controls="login-panel" aria-selected="false">
                    <i class="bi bi-key-fill me-2"></i>로그인 이력
                  </button>
              </li>
              <li class="nav-item">
                  <button class="nav-link border-0 shadow-none" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-panel" type="button" role="tab" aria-controls="audit-panel" aria-selected="false">
                    <i class="bi bi-shield-check me-2"></i>게시판 감사
                  </button>
              </li>
          </ul>
      </header>
      <div class="tab-content">
          <!-- 접속 로그 패널 -->
          <section class="tab-pane fade show active" id="access-panel" role="tabpanel" aria-labelledby="access-tab">
              <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0 text-nowrap sc-admin-stack-table">
                      <thead class="table-light">
                          <tr class="small text-uppercase fw-bold text-secondary">
                              <th scope="col" class="ps-4 py-3">일시</th>
                              <th scope="col" class="py-3">유형</th>
                              <th scope="col" class="py-3">대상 및 경로</th>
                              <th scope="col" class="py-3">IP 식별</th>
                              <th scope="col" class="text-end pe-4 py-3">상태</th>
                          </tr>
                      </thead>
                      <tbody class="table-group-divider">
                          <?php foreach ($access_logs as $log): ?>
                              <tr>
                                  <td class="ps-4 py-3" data-label="일시">
                                      <div class="d-flex flex-column lh-sm">
                                          <time class="fw-bold text-dark small" datetime="<?= smartcms_h((string)$log['created_at']) ?>"><?= date('m.d', strtotime((string)$log['created_at'])) ?></time>
                                          <time class="text-secondary opacity-50 small fw-medium sc-admin-time-xs"><?= date('H:i:s', strtotime((string)$log['created_at'])) ?></time>
                                      </div>
                                  </td>
                                  <td class="py-3" data-label="유형">
                                      <span class="badge bg-secondary-subtle text-secondary small text-uppercase fw-bold sc-admin-badge-xs">
                                          <?= smartcms_h($log['access_type']) ?>
                                      </span>
                                  </td>
                                  <td class="py-3" data-label="대상 및 경로">
                                      <div class="fw-bold small text-dark mb-1"><?= smartcms_h($log['target_type']) ?>: <?= smartcms_h($log['target_key'] ?? '-') ?></div>
                                      <div class="small text-secondary fw-medium opacity-75"><?= smartcms_h($log['request_path']) ?></div>
                                  </td>
                                  <td class="py-3" data-label="IP 식별"><code class="small text-muted fw-bold"><?= smartcms_h(substr((string)($log['ip_hash'] ?? 'N/A'), 0, 12)) ?>...</code></td>
                                  <td class="text-end pe-4 py-3" data-label="상태">
                                      <?php $status_theme = $log['status_code'] >= 400 ? 'danger' : 'success'; ?>
                                      <span class="badge bg-<?= $status_theme ?>-subtle text-<?= $status_theme ?> fw-bold border border-<?= $status_theme ?>-subtle">
                                          <?= (int)$log['status_code'] ?>
                                      </span>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </div>
          </section>

          <!-- 로그인 로그 패널 -->
          <section class="tab-pane fade" id="login-panel" role="tabpanel" aria-labelledby="login-tab">
              <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0 text-nowrap sc-admin-stack-table">
                      <thead class="table-light">
                          <tr class="small text-uppercase fw-bold text-secondary">
                              <th scope="col" class="ps-4 py-3">일시</th>
                              <th scope="col" class="py-3">로그인 시도 계정 (Email)</th>
                              <th scope="col" class="text-end pe-4 py-3">결과</th>
                          </tr>
                      </thead>
                      <tbody class="table-group-divider">
                          <?php foreach ($login_logs as $log): ?>
                              <tr>
                                  <td class="ps-4 py-3 fw-medium small" data-label="일시"><time datetime="<?= smartcms_h($log['created_at']) ?>"><?= smartcms_h($log['created_at']) ?></time></td>
                                  <td class="py-3 fw-bold text-dark small" data-label="계정"><?= smartcms_h($log['email']) ?></td>
                                  <td class="text-end pe-4 py-3" data-label="결과">
                                      <span class="badge bg-<?= $log['result'] === 'success' ? 'success' : 'danger' ?> text-uppercase fw-bold sc-admin-badge-sm">
                                          <?= smartcms_h($log['result']) ?>
                                      </span>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </div>
          </section>

          <!-- 게시판 감사 로그 패널 -->
          <section class="tab-pane fade" id="audit-panel" role="tabpanel" aria-labelledby="audit-tab">
              <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0 text-nowrap sc-admin-stack-table">
                      <thead class="table-light">
                          <tr class="small text-uppercase fw-bold text-secondary">
                              <th scope="col" class="ps-4 py-3">일시</th>
                              <th scope="col" class="py-3">액션 (Action)</th>
                              <th scope="col" class="pe-4 py-3">상세 내역</th>
                          </tr>
                      </thead>
                      <tbody class="table-group-divider">
                          <?php foreach ($board_logs as $log): ?>
                              <tr>
                                  <td class="ps-4 py-3 fw-medium small" data-label="일시"><time datetime="<?= smartcms_h($log['created_at']) ?>"><?= smartcms_h($log['created_at']) ?></time></td>
                                  <td class="py-3" data-label="액션"><span class="badge bg-info-subtle text-info border-0 fw-bold"><?= smartcms_h($log['action']) ?></span></td>
                                  <td class="py-3 small text-secondary fw-medium pe-4" data-label="상세 내역"><?= smartcms_h($log['message']) ?></td>
                              </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </div>
          </section>
      </div>
  </article>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
