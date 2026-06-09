<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../head.php';
require_once __DIR__ . '/../../foot.php';
require_once __DIR__ . '/../../common/ui/components.php';

$admin = smartcms_admin_user();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $site_name = trim((string)($_POST['site_name'] ?? 'smartcms'));
    $default_member_level = max(1, min(10, (int)($_POST['default_member_level'] ?? 2)));
    $admin_level = max(1, min(10, (int)($_POST['admin_level'] ?? 8)));
    $upload_max_mb = max(1, min(100, (int)($_POST['upload_max_mb'] ?? 10)));

    if ($admin_level < 8) {
        $message = '관리자 기준 레벨은 8 이상을 권장하며, 현재는 8 미만으로 저장할 수 없습니다.';
        $message_type = 'error';
    } elseif ($site_name === '') {
        $message = '사이트명을 입력하세요.';
        $message_type = 'error';
    } else {
        smartcms_save_settings([
            'site_name' => $site_name,
            'allow_registration' => isset($_POST['allow_registration']) ? '1' : '0',
            'default_member_level' => (string)$default_member_level,
            'admin_level' => (string)$admin_level,
            'upload_max_mb' => (string)$upload_max_mb,
        ]);
        $message = '환경 설정을 저장했습니다.';
        $message_type = 'success';
    }
}

$settings = smartcms_settings_all();

$SMARTCMS_HEAD = ['title' => '환경 설정', 'body_class' => 'smartcms-admin-page'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_admin_page_header($admin, '환경 설정', 'settings');
?>

<?php if ($message !== ''): ?>
  <?= smartcms_alert($message, $message_type) ?>
<?php endif; ?>

<div class="card border-0 shadow-sm sc-card-settings">
  <div class="card-body p-4 p-lg-5">
    <div class="d-flex align-items-center gap-2 mb-5">
      <div class="p-2 bg-primary text-white rounded-3 lh-1"><i class="bi bi-sliders2 fs-5"></i></div>
      <h5 class="card-title mb-0 fw-bold">시스템 기본 설정</h5>
    </div>
    <form class="row g-3" method="post">
      <?= smartcms_csrf_input() ?>
      <div class="col-12 col-md-6">
        <label for="site_name" class="form-label fw-bold text-secondary small">사이트 이름</label>
        <input class="form-control" id="site_name" name="site_name" value="<?= smartcms_h($settings['site_name'] ?? 'smartcms') ?>" required>
        <div class="form-text text-muted">브라우저 탭 제목 및 사이트 상단 브랜드명으로 노출됩니다.</div>
      </div>
      <div class="col-12 col-md-6">
        <label for="upload_max_mb" class="form-label fw-bold text-secondary small">첨부파일 최대 용량 (MB)</label>
        <input class="form-control" id="upload_max_mb" name="upload_max_mb" type="number" min="1" max="100" value="<?= smartcms_h($settings['upload_max_mb'] ?? '10') ?>" required>
        <div class="form-text text-muted">서버 환경(php.ini) 설정을 초과할 수 없습니다.</div>
      </div>
      <div class="col-12"><hr class="my-2 opacity-10"></div>
      <div class="col-12 col-md-4">
        <label for="default_member_level" class="form-label fw-bold text-secondary small">신규 가입 레벨</label>
        <select class="form-select" id="default_member_level" name="default_member_level">
          <?php for ($level = 1; $level <= 10; $level++): ?>
            <option value="<?= $level ?>" <?= $level === (int)($settings['default_member_level'] ?? 2) ? 'selected' : '' ?>>Level <?= $level ?><?= $level == 2 ? ' (권장)' : '' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-12 col-md-4">
        <label for="admin_level" class="form-label fw-bold text-secondary small">관리자 최소 레벨</label>
        <select class="form-select" id="admin_level" name="admin_level">
          <?php for ($level = 8; $level <= 10; $level++): ?>
            <option value="<?= $level ?>" <?= $level === (int)($settings['admin_level'] ?? 8) ? 'selected' : '' ?>>Level <?= $level ?><?= $level == 8 ? ' (운영자)' : '' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-12 col-md-4 d-flex align-items-end">
  <div class="d-flex justify-content-between align-items-center p-0 border rounded-3 bg-light w-100">
    <!-- label의 cursor 처리를 위해 클래스를 추가하거나 swich 클릭 영역을 넓혀두면 좋습니다 -->
    <label class="fw-bold mb-0" style="cursor: pointer;" for="allow_registration">
      신규 회원가입 허용
    </label>
    <div class="form-check form-switch mb-0 p-2 d-flex align-items-center">
      <!-- 양끝 정렬 시 form-check 내부 패딩을 제거하고 정렬합니다 -->
      <input class="form-check-input m-0" type="checkbox" name="allow_registration" value="1" id="allow_registration" style="cursor: pointer;" <?= (string)($settings['allow_registration'] ?? '1') === '1' ? 'checked' : '' ?>>
    </div>
  </div>
</div>
      <div class="col-12 mt-4 pt-3">
        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm">
          <i class="bi bi-check-circle-fill me-2"></i>시스템 설정 저장하기
        </button>
      </div>
    </form>
  </div>
</div>

<?= smartcms_admin_footer() ?>
<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
