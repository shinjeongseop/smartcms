<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/database_tools.php';

$admin = smartcms_admin_user();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'backup') {
        $filename = 'smartcms-backup-' . date('Ymd-His') . '.sql';
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo smartcms_db_backup_sql();
        exit;
    }
}

$tables = smartcms_db_managed_tables();
$prefix = (string)smartcms_config_value('table_prefix', 'sc_');

$SMARTCMS_HEAD = ['title' => '데이터베이스 관리', 'page_heading' => 'DB 도구', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'database'];
require SMARTCMS_ROOT . '/admin/head.php';
?>

<section>
  <?php if ($message !== ''): ?>
    <aside class="alert alert-<?= $message_type === 'error' ? 'danger' : ( $message_type === 'success' ? 'success' : 'info' ) ?> d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill fs-5"></i>
      <div class="fw-medium small"><?= smartcms_h($message) ?></div>
    </aside>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-12">
      <article class="card border shadow-sm h-100 overflow-hidden">
        <header class="card-header bg-white border-bottom py-3 px-4">
          <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-cloud-download text-primary fs-5"></i>데이터베이스 백업 (Dump)
          </h2>
        </header>
        <div class="card-body p-4 p-lg-5">
          <p class="text-secondary small mb-4 fw-medium">
            현재 사용 중인 테이블 Prefix <strong><?= smartcms_h($prefix) ?></strong>로 시작하는 모든 구조와 데이터를 SQL 파일로 생성하여 내려받습니다. 정기적인 백업을 권장합니다.
          </p>
          <form method="post" class="d-grid d-sm-block">
            <?= smartcms_csrf_input() ?>
            <input type="hidden" name="action" value="backup">
            <button class="btn btn-primary rounded-2 px-5 fw-bold shadow-sm py-2" type="submit">
              <i class="bi bi-download me-2"></i>SQL 백업 다운로드
            </button>
          </form>
        </div>
      </article>
    </div>
  </div>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
