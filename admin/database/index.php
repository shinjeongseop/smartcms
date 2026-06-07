<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/database_tools.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';

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
                        $message = 'DB 복구를 완료했습니다. 실행된 SQL 문장 수: ' . $executed;
                        $message_type = 'success';
                    } catch (Throwable $e) {
                        $message = 'DB 복구 중 오류가 발생했습니다: ' . $e->getMessage();
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
                $lockPath = SMARTCMS_ROOT . '/install.lock';
                if (is_file($lockPath)) {
                    unlink($lockPath);
                }

                $message = 'DB 초기화를 완료했습니다. 삭제된 테이블 수: ' . $dropped . '개. 설치 잠금 파일도 해제했습니다.';
                $message_type = 'success';
            } catch (Throwable $e) {
                $message = 'DB 초기화 중 오류가 발생했습니다: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

$tables = smartcms_db_managed_tables();
$prefix = (string)smartcms_config_value('table_prefix', 'sc_');

smartcms_render_head(['title' => 'DB 관리', 'body_class' => 'smartcms-admin-page']);
echo smartcms_admin_page_header($admin, 'DB 관리', 'database');
?>

<?php if ($message !== ''): ?>
  <?= smartcms_alert($message, $message_type) ?>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-2">DB 백업</h2>
        <p class="text-body-secondary mb-3">현재 prefix <strong><?= smartcms_h($prefix) ?></strong>로 시작하는 SmartCMS 테이블을 SQL 파일로 내려받습니다.</p>
        <form method="post">
          <?= smartcms_csrf_input() ?>
          <input type="hidden" name="action" value="backup">
          <?= smartcms_button('SQL 백업 다운로드', 'submit') ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-2">DB 복구</h2>
        <p class="text-body-secondary mb-3">SmartCMS 백업 SQL 파일을 업로드해 복구합니다. 복구 전 현재 DB 백업을 먼저 다운로드하는 것을 권장합니다.</p>
        <form class="row g-3" method="post" enctype="multipart/form-data">
          <?= smartcms_csrf_input() ?>
          <input type="hidden" name="action" value="restore">
          <div class="col-12 col-lg-8">
            <label for="backup_file" class="form-label">SQL 백업 파일</label>
            <input class="form-control" id="backup_file" name="backup_file" type="file" accept=".sql" required>
          </div>
          <div class="col-12 col-lg-4 d-flex align-items-end">
            <?= smartcms_button('SQL 복구 실행', 'submit') ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card border-0 shadow-sm border-danger">
      <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-2 text-danger">DB 초기화</h2>
        <p class="text-body-secondary mb-3">현재 prefix <strong><?= smartcms_h($prefix) ?></strong>로 시작하는 테이블 <?= count($tables) ?>개를 삭제하고 설치 잠금 파일을 해제합니다.</p>
        <form class="row g-3" method="post">
          <?= smartcms_csrf_input() ?>
          <input type="hidden" name="action" value="reset">
          <div class="col-12 col-lg-8">
            <label for="confirm_text" class="form-label">확인 문구</label>
            <input class="form-control" id="confirm_text" name="confirm_text" placeholder="RESET SMARTCMS" required>
          </div>
          <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn btn-danger rounded-pill px-4" type="submit">DB 초기화 실행</button>
            <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>">설치 마법사로 이동</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= smartcms_admin_footer() ?>
<?php smartcms_render_foot(); ?>
