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

    if ($action === 'restore') {
        $upload = $_FILES['backup_file'] ?? null;
        if (!is_array($upload) || (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $message = '복구할 SQL 백업 파일을 선택하세요.';
            $message_type = 'error';
        } else {
            $tmpPath = (string)$upload['tmp_name'];
            $originalName = (string)($upload['name'] ?? '');
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $size = (int)($upload['size'] ?? 0);

            if ($extension !== 'sql') {
                $message = 'SQL 파일만 복구할 수 있습니다.';
                $message_type = 'error';
            } elseif ($size <= 0 || $size > 20 * 1024 * 1024) {
                $message = '복구 파일은 20MB 이하만 허용됩니다.';
                $message_type = 'error';
            } else {
                $sql = file_get_contents($tmpPath);
                if ($sql === false || trim($sql) === '') {
                    $message = '복구 파일 내용을 읽을 수 없습니다.';
                    $message_type = 'error';
                } else {
                    try {
                        $executed = smartcms_db_restore_sql($sql);
                        $message = '데이터베이스 복구를 성공적으로 완료했습니다. (쿼리 ' . $executed . '개 실행됨)';
                        $message_type = 'success';
                    } catch (Throwable $e) {
                        $message = '데이터베이스 복구 중 치명적 오류 발생: ' . $e->getMessage();
                        $message_type = 'error';
                    }
                }
            }
        }
    }

    if ($action === 'reset') {
        $confirmText = trim((string)($_POST['confirm_text'] ?? ''));
        if ($confirmText !== 'RESET SMARTCMS') {
            $message = '초기화 확인 문구를 정확히 입력해야 합니다.';
            $message_type = 'error';
        } else {
            try {
                $dropped = smartcms_db_drop_managed_tables();
                $message = '시스템 테이블이 모두 초기화되었습니다. (삭제된 테이블: ' . $dropped . '개)';
                $message_type = 'success';
            } catch (Throwable $e) {
                $message = '초기화 작업 중 오류가 발생했습니다: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
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
    <!-- DB 백업 섹션 -->
    <div class="col-12 col-lg-6">
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
          <form method="post">
            <?= smartcms_csrf_input() ?>
            <input type="hidden" name="action" value="backup">
            <button class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm py-2.5" type="submit">
              <i class="bi bi-download me-2"></i>SQL 백업 다운로드
            </button>
          </form>
        </div>
      </article>
    </div>

    <!-- DB 복구 섹션 -->
    <div class="col-12 col-lg-6">
      <article class="card border shadow-sm h-100 overflow-hidden">
        <header class="card-header bg-white border-bottom py-3 px-4">
          <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-cloud-upload text-info fs-5"></i>데이터베이스 복구 (Restore)
          </h2>
        </header>
        <div class="card-body p-4 p-lg-5">
          <p class="text-secondary small mb-4 fw-medium">
            이전에 백업된 SQL 파일을 업로드하여 데이터를 복원합니다. <span class="text-danger fw-bold">주의: 현재 데이터가 백업 파일의 내용으로 덮어씌워집니다.</span>
          </p>
          <form class="vstack gap-3" method="post" enctype="multipart/form-data">
            <?= smartcms_csrf_input() ?>
            <input type="hidden" name="action" value="restore">
            <div>
              <label for="backup_file" class="form-label fw-bold small text-dark text-uppercase">백업 SQL 파일 선택</label>
              <input class="form-control py-2 fw-bold" id="backup_file" name="backup_file" type="file" accept=".sql" required>
            </div>
            <div class="pt-2">
              <button class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm py-2.5" type="submit">
                <i class="bi bi-arrow-repeat me-2"></i>SQL 복구 실행
              </button>
            </div>
          </form>
        </div>
      </article>
    </div>

    <!-- DB 초기화 섹션 -->
    <div class="col-12">
      <section class="card border shadow-sm border-start border-danger border-5 overflow-hidden">
        <div class="card-body p-4 p-lg-5">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
            <div class="d-flex align-items-start gap-3">
              <div class="badge bg-danger p-3 rounded-circle shadow-sm"><i class="bi bi-exclamation-octagon fs-4"></i></div>
              <div>
                <h2 class="h5 fw-bold mb-1 text-danger">시스템 데이터 초기화 (Dangerous Area)</h2>
              </div>
            </div>
            <form class="d-flex flex-column flex-md-row gap-2 align-items-md-end" method="post">
              <?= smartcms_csrf_input() ?>
              <input type="hidden" name="action" value="reset">
              <div>
                <label for="confirm_text" class="form-label fw-bold small text-danger text-uppercase">확인 문구 입력</label>
                <input class="form-control border-danger-subtle py-2 px-3 fw-bold" id="confirm_text" name="confirm_text" placeholder="RESET SMARTCMS" required style="width:200px;">
              </div>
              <button class="btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm" type="submit">지금 즉시 초기화</button>
            </form>
          </div>
        </div>
      </section>
    </div>
  </div>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
