<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../../common/board.php';
$admin = smartcms_admin_user();
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    smartcms_verify_csrf_or_fail();
    $site_name = trim((string)($_POST['site_name'] ?? 'smartcms'));
    $default_member_level = max(1, min(10, (int)($_POST['default_member_level'] ?? 2)));
    $admin_level = max(1, min(10, (int)($_POST['admin_level'] ?? 8)));
    $upload_max_mb = max(1, min(100, (int)($_POST['upload_max_mb'] ?? 10)));
    $author_display_mode = smartcms_board_normalize_author_display_mode((string)($_POST['author_display_mode'] ?? 'name'));

    if ($admin_level < 8) {
        $message = '관리자 기준 레벨은 8 이상을 권장하며, 현재는 8 미만으로 저장할 수 없습니다.';
        $message_type = 'error';
    } elseif ($site_name === '') {
        $message = '사이트 이름을 정확히 입력하세요.';
        $message_type = 'error';
    } else {
        smartcms_save_settings([
            'site_name' => $site_name,
            'allow_registration' => isset($_POST['allow_registration']) ? '1' : '0',
            'default_member_level' => (string)$default_member_level,
            'admin_level' => (string)$admin_level,
            'upload_max_mb' => (string)$upload_max_mb,
            'author_display_mode' => $author_display_mode,
        ]);
        $message = '시스템 환경 설정이 성공적으로 저장되었습니다.';
        $message_type = 'success';
    }
}

$settings = smartcms_settings_all();

$SMARTCMS_HEAD = ['title' => '전체 환경 설정', 'page_heading' => '운영 설정', 'body_class' => 'smartcms-admin-page', 'active_menu' => 'settings'];
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
    <header class="card-header bg-white border-bottom py-4 px-4 p-lg-5">
      <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-primary-subtle text-primary rounded-4 shadow-sm lh-1"><i class="bi bi-gear-wide-connected fs-4"></i></div>
        <h2 class="h5 mb-0 fw-bold text-dark">시스템 운영 및 정책 설정</h2>
      </div>
    </header>

    <div class="card-body p-4 p-lg-5">
      <form class="row g-4" method="post">
        <?= smartcms_csrf_input() ?>

        <div class="col-12">
          <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-sliders text-primary"></i>
            <h3 class="h6 mb-0 fw-bold text-dark">사이트 정책</h3>
          </div>
          <p class="small text-muted mb-0">사이트 전체에 공통 적용되는 기본 정책입니다.</p>
        </div>

        <div class="col-12 col-md-6">
          <label for="author_display_mode" class="form-label fw-bold small text-secondary text-uppercase mb-2">게시판 글쓴이 표시 정책</label>
          <select class="form-select py-2 fw-bold" id="author_display_mode" name="author_display_mode">
            <?php foreach (smartcms_board_author_display_options() as $mode_key => $mode_label): ?>
              <option value="<?= smartcms_h($mode_key) ?>" <?= (string)($settings['author_display_mode'] ?? 'name') === $mode_key ? 'selected' : '' ?>><?= smartcms_h($mode_label) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text text-muted small mt-2 fw-medium">사이트 전체 게시판과 최신글, 검색 결과의 작성자 표시 방식에 적용됩니다.</div>
        </div>

        <div class="col-12 col-md-6">
          <label for="site_name" class="form-label fw-bold small text-secondary text-uppercase mb-2">공식 사이트 이름</label>
          <input class="form-control py-2 fw-bold" id="site_name" name="site_name" value="<?= smartcms_h($settings['site_name'] ?? 'smartcms') ?>" required>
          <div class="form-text text-muted small mt-2 fw-medium">브라우저 탭 타이틀과 로고 영역에 표시될 프로젝트 이름입니다.</div>
        </div>

        <div class="col-12 col-md-6">
          <label for="upload_max_mb" class="form-label fw-bold small text-secondary text-uppercase mb-2">첨부파일 단일 최대 용량 (MB)</label>
          <input class="form-control py-2 fw-bold" id="upload_max_mb" name="upload_max_mb" type="number" min="1" max="100" value="<?= smartcms_h($settings['upload_max_mb'] ?? '10') ?>" required>
          <div class="form-text text-muted small mt-2 fw-medium">게시판 파일 업로드 시 적용되는 용량 제한입니다. (서버 설정 범위 내)</div>
        </div>

        <div class="col-12 my-2"><hr class="opacity-10"></div>

        <div class="col-12">
          <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-people text-primary"></i>
            <h3 class="h6 mb-0 fw-bold text-dark">회원 정책</h3>
          </div>
          <p class="small text-muted mb-0">가입과 기본 권한에 관련된 운영 기준입니다.</p>
        </div>

        <div class="col-12 col-md-4">
          <label for="default_member_level" class="form-label fw-bold small text-secondary text-uppercase mb-2">신규 회원 가입 레벨</label>
          <select class="form-select py-2 fw-bold" id="default_member_level" name="default_member_level">
            <?php for ($level = 1; $level <= 10; $level++): ?>
              <option value="<?= $level ?>" <?= $level === (int)($settings['default_member_level'] ?? 2) ? 'selected' : '' ?>>Level <?= $level ?><?= $level == 2 ? ' (System Default)' : '' ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col-12 col-md-4">
          <label for="admin_level" class="form-label fw-bold small text-secondary text-uppercase mb-2">관리자 최소 인증 레벨</label>
          <select class="form-select py-2 fw-bold" id="admin_level" name="admin_level">
            <?php for ($level = 8; $level <= 10; $level++): ?>
              <option value="<?= $level ?>" <?= $level === (int)($settings['admin_level'] ?? 8) ? 'selected' : '' ?>>Level <?= $level ?><?= $level == 8 ? ' (Admin Base)' : '' ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label fw-bold small text-secondary text-uppercase mb-2">회원 가입 정책</label>
          <div class="p-2 bg-light rounded-3 border-0">
            <div class="form-check form-switch py-1 ms-2">
              <input class="form-check-input ms-0" type="checkbox" name="allow_registration" value="1" id="allow_registration" <?= (string)($settings['allow_registration'] ?? '1') === '1' ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold text-dark ms-3" for="allow_registration">신규 자유 가입 허용</label>
            </div>
          </div>
        </div>

        <footer class="col-12 mt-5 pt-3">
          <button type="submit" class="btn btn-primary btn-lg rounded-2 px-5 fw-bold shadow-sm py-3 w-100 w-auto">
            <i class="bi bi-cloud-check me-2"></i>모든 시스템 설정 저장
          </button>
        </footer>
      </form>
    </div>
  </article>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/admin/foot.php';
?>
