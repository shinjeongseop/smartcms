<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/ui/layout.php';
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

smartcms_render_head([
    'title' => '환경 설정',
    'body_class' => 'smartcms-admin-page',
]);
?>
<?= smartcms_admin_page_header($admin, '환경 설정', 'settings') ?>

  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, $message_type) ?>
  <?php endif; ?>

  <section class="card smartcms-panel smartcms-admin-panel">
    <h2 class="smartcms-section-title">기본 설정</h2>
    <form class="smartcms-grid smartcms-form-grid" method="post">
      <?= smartcms_csrf_input() ?>
      <div class="smartcms-field">
        <label for="site_name">사이트명</label>
        <input class="form-control smartcms-input" id="site_name" name="site_name" value="<?= smartcms_h($settings['site_name'] ?? 'smartcms') ?>" required>
      </div>
      <div class="smartcms-field">
        <label for="default_member_level">기본 회원 레벨</label>
        <select class="form-select smartcms-select" id="default_member_level" name="default_member_level">
          <?php for ($level = 1; $level <= 10; $level++): ?>
            <option value="<?= $level ?>" <?= $level === (int)($settings['default_member_level'] ?? 2) ? 'selected' : '' ?>><?= $level ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="smartcms-field">
        <label for="admin_level">관리자 기준 레벨</label>
        <select class="form-select smartcms-select" id="admin_level" name="admin_level">
          <?php for ($level = 8; $level <= 10; $level++): ?>
            <option value="<?= $level ?>" <?= $level === (int)($settings['admin_level'] ?? 8) ? 'selected' : '' ?>><?= $level ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="smartcms-field">
        <label for="upload_max_mb">첨부파일 최대 용량 MB</label>
        <input class="form-control smartcms-input" id="upload_max_mb" name="upload_max_mb" type="number" min="1" max="100" value="<?= smartcms_h($settings['upload_max_mb'] ?? '10') ?>" required>
      </div>
      <label class="smartcms-check-field smartcms-form-wide">
        <input type="checkbox" name="allow_registration" value="1" <?= (string)($settings['allow_registration'] ?? '1') === '1' ? 'checked' : '' ?>>
        회원가입 허용
      </label>
      <div class="smartcms-actions smartcms-form-wide">
        <?= smartcms_button('설정 저장', 'submit') ?>
      </div>
    </form>
  </section>
  <?= smartcms_admin_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
