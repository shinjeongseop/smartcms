<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/components.php';

$locked = smartcms_install_locked();
$message = '이미 설치 잠금이 적용되어 있습니다.';
$message_type = 'info';

if (!$locked) {
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
  <p class="smartcms-text-muted">다음 단계에서는 로그인, 권한 검사, 관리자 화면을 연결합니다.</p>
</main>
<?php smartcms_render_foot(['scripts' => [smartcms_base_url('/install/app.js')]]); ?>
