<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/schema.php';
require_once __DIR__ . '/../head.php';
require_once __DIR__ . '/../foot.php';
require_once __DIR__ . '/../common/ui/components.php';

if (smartcms_install_locked()) {
    http_response_code(403);
    echo 'Installation is locked.';
    exit;
}

$message = '';
$message_type = 'info';

try {
    smartcms_create_schema();
    $message = '기본 테이블을 생성했습니다.';
    $message_type = 'success';
} catch (Throwable $e) {
    $message = '테이블 생성에 실패했습니다: ' . $e->getMessage();
    $message_type = 'error';
}

smartcms_render_head([
    'title' => '테이블 생성',
    'body_class' => 'smartcms-install',
    'stylesheets' => ['/install/style.css'],
]);
?>
<div class="container py-4 py-md-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-8">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 fw-bold mb-2">테이블 생성</h1>
      <p class="text-body-secondary">회원, 권한, 게시판, 로그 저장에 필요한 기본 테이블을 준비합니다.</p>
      <?= smartcms_alert($message, $message_type) ?>
      <?php if ($message_type === 'success'): ?>
        <p class="mb-0"><a class="btn btn-primary rounded-pill px-4" href="./create_admin.php">다음: 최초 관리자 생성</a></p>
      <?php else: ?>
        <p class="mb-0"><a class="btn btn-outline-secondary rounded-pill px-4" href="./">DB 설정 다시 확인</a></p>
      <?php endif; ?>
    </div>
  </div>
    </div>
  </div>
</div>
<?php smartcms_render_foot(['scripts' => ['/install/app.js']]); ?>
