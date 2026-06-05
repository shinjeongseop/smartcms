<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/board.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';

$locked = smartcms_install_locked();
$message = '이미 설치 잠금이 적용되어 있습니다.';
$message_type = 'info';
$board_message = '';

if (!$locked) {
    try {
        $admin = smartcms_fetch_one(
            "SELECT id FROM " . smartcms_table('users') . " WHERE level >= :level ORDER BY id ASC LIMIT 1",
            ['level' => (int)smartcms_config_value('super_admin_level', 10)]
        );
        if ($admin) {
            $seed = smartcms_board_seed_defaults((int)$admin['id']);
            $board_message = $seed['message'];
        } else {
            $board_message = '기본 게시판 생성을 위해 먼저 관리자 계정을 생성해야 합니다.';
        }
    } catch (Throwable $e) {
        $board_message = '기본 게시판 생성 중 오류가 발생했습니다: ' . $e->getMessage();
    }

    $written = file_put_contents(SMARTCMS_ROOT . '/install.lock', date('c') . PHP_EOL, LOCK_EX) !== false;
    $message = $written ? '설치가 완료되었고 install.lock을 생성했습니다.' : 'install.lock 생성에 실패했습니다. 파일 권한을 확인하세요.';
    $message_type = $written ? 'success' : 'error';
}

smartcms_render_head([
    'title' => '설치 완료',
    'body_class' => 'smartcms-install',
    'stylesheets' => [smartcms_base_url('/install/style.css')],
]);
?>
<main class="smartcms-panel">
  <h1 class="smartcms-title">설치 완료</h1>
  <p class="smartcms-text-muted">설치 잠금이 적용되면 설치 마법사는 다시 실행되지 않습니다.</p>
  <?= smartcms_alert($message, $message_type) ?>
  <?php if ($board_message !== ''): ?>
    <?= smartcms_alert($board_message, 'info') ?>
  <?php endif; ?>
  <p><a class="btn btn-primary rounded-pill px-4" href="../">홈으로 이동</a></p>
</main>
<?php smartcms_render_foot(['scripts' => [smartcms_base_url('/install/app.js')]]); ?>
