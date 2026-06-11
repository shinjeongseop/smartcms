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
    $message = '로그 데이터를 불러오는 중 오류 발생: ' . $e->getMessage();
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
      <header class="card-header bg-white border-bottom-0 pt-4 px-4">
          <ul class="nav nav-tabs card-header-tabs border-bottom-0 gap-2 fw-bold" id="logTab" role="tablist">
              <li class="nav-item">
                  <button class="nav-link active border-0 py-3 px-4 shadow-none" id="access-tab" data-bs-toggle="tab" data-bs-target="#access-panel" type="button">
                    <i class="bi bi-activity me-2"></i>접속 로그
                  </button>
              </li>
              <li class="nav-item">
                  <button class="nav-link border-0 py-3 px-4 shadow-none" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel" type="button">
                    <i class="bi bi-key-fill me-2"></i>로그인 이력
                  </button>
              </li>
              <li class="nav-item">
                  <button class="nav-link border-0 py-3 px-4 shadow-none" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-panel" type="button">
                    <i class="bi bi-shield-check me-2"></i>게시판 감사
                  </button>
              </li>
          </ul>
      </header>
      <div class="tab-content border-top">
          <!-- 접속 로그 패널 -->
          <section class="tab-pane fade show active" id="access-panel" role="tabpanel" aria-labelledby="access-tab">
              <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0 text-nowrap">
                      <thead class="table-light small text-uppercase fw-bold text-secondary">
                          <tr>
                              <th scope="col" class="ps-4">일시</th>
                              <th scope="col">유형</th>
                              <th scope="col">대상 및 경로</th>
                              <th scope="col">IP 식별</th>
                              <th scope="col" class="text-end pe-4">상태</th>
                          </tr>
                      </thead>
                      <tbody class="table-group-divider">
                          <?php foreach ($access_logs as $log): ?>
                              <tr>
                                  <td class="ps-4">
                                      <div class="d-flex flex-column lh-sm">
                                          <time class="fw-bold text-dark small" datetime="<?= smartcms_h((string)$log['created_at']) ?>"><?= date('m.d', strtotime((string)$log['created_at'])) ?></time>
                                          <time class="text-secondary opacity-50 small fw-medium" style="font-size:0.7rem;"><?= date('H:i:s', strtotime((string)$log['created_at'])) ?></time>
                                      </div>
                                  </td>
                                  <td>
                                      <span class="badge bg-secondary-subtle text-secondary small text-uppercase fw-bold" style="font-size:0.6rem; letter-spacing:0.05rem;">
                                          <?= smartcms_h($log['access_type']) ?>
                                      </span>
                                  </td>
                                  <td>
                                      <div class="fw-bold small text-dark mb-1"><?= smartcms_h($log['target_type']) ?>: <?= smartcms_h($log['target_key'] ?? '-') ?></div>
                                      <div class="text-xs text-secondary fw-medium opacity-75"><?= smartcms_h($log['request_path']) ?></div>
                                  </td>
                                  <td><code class="text-xs text-muted fw-bold"><?= smartcms_h(substr((string)($log['ip_hash'] ?? 'N/A'), 0, 12)) ?>...</code></td>
                                  <td class="text-end pe-4">
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
                  <table class="table table-hover align-middle mb-0 text-nowrap">
                      <thead class="table-light small text-uppercase fw-bold text-secondary">
                          <tr>
                              <th scope="col" class="ps-4">일시</th>
                              <th scope="col">로그인 시도 계정 (Email)</th>
                              <th scope="col" class="text-end pe-4">결과</th>
                          </tr>
                      </thead>
                      <tbody class="table-group-divider">
                          <?php foreach ($login_logs as $log): ?>
                              <tr>
                                  <td class="ps-4 fw-medium small"><time datetime="<?= smartcms_h($log['created_at']) ?>"><?= smartcms_h($log['created_at']) ?></time></td>
                                  <td class="fw-bold text-dark small"><?= smartcms_h($log['email']) ?></td>
                                  <td class="text-end pe-4">
                                      <span class="badge bg-<?= $log['result'] === 'success' ? 'success' : 'danger' ?> text-uppercase fw-bold" style="font-size:0.65rem;">
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
                  <table class="table table-hover align-middle mb-0 text-nowrap">
                      <thead class="table-light small text-uppercase fw-bold text-secondary">
                          <tr>
                              <th scope="col" class="ps-4">일시</th>
                              <th scope="col">액션 (Action)</th>
                              <th scope="col" class="pe-4">상세 내역</th>
                          </tr>
                      </thead>
                      <tbody class="table-group-divider">
                          <?php foreach ($board_logs as $log): ?>
                              <tr>
                                  <td class="ps-4 fw-medium small"><time datetime="<?= smartcms_h($log['created_at']) ?>"><?= smartcms_h($log['created_at']) ?></time></td>
                                  <td><span class="badge bg-info-subtle text-info border-0 fw-bold"><?= smartcms_h($log['action']) ?></span></td>
                                  <td class="small text-secondary fw-medium pe-4"><?= smartcms_h($log['message']) ?></td>
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
