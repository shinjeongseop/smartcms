<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/ui/layout.php';
require_once __DIR__ . '/../common/ui/navigation.php';

$user = smartcms_current_user();

smartcms_render_head([
    'title' => '회원 센터',
    'body_class' => 'smartcms-board-page',
]);
?>
<?= smartcms_site_header('') ?>
  <header class="smartcms-page-hero">
    <p class="smartcms-eyebrow">Member</p>
    <h1 class="smartcms-title">회원 센터</h1>
    <p class="smartcms-text-muted">로그인 상태와 회원 관련 기능을 확인합니다.</p>
  </header>

  <section class="smartcms-card-grid">
    <?php if ($user): ?>
      <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">
        <strong>마이페이지</strong>
        <span><?= smartcms_h($user['name']) ?>님의 회원 정보를 확인합니다.</span>
      </a>
      <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/member/password/')) ?>">
        <strong>비밀번호 변경</strong>
        <span>현재 비밀번호 확인 후 새 비밀번호로 변경합니다.</span>
      </a>
      <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">
        <strong>로그아웃</strong>
        <span>현재 세션을 종료합니다.</span>
      </a>
    <?php else: ?>
      <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">
        <strong>로그인</strong>
        <span>가입한 계정으로 로그인합니다.</span>
      </a>
      <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>">
        <strong>회원가입</strong>
        <span>기본 회원 계정을 생성합니다.</span>
      </a>
    <?php endif; ?>
  </section>
  <?= smartcms_site_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
