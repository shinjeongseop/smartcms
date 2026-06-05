<?php
declare(strict_types=1);

require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/ui/layout.php';

$user = smartcms_current_user();

smartcms_render_head([
    'title' => 'smartcms',
    'body_class' => 'smartcms-site-home',
]);
?>
<main class="smartcms-content-shell">
  <section class="smartcms-home-hero">
    <p class="smartcms-eyebrow">smartcms</p>
    <h1 class="smartcms-title">회원, 권한, 게시판을 재사용하는 PHP CMS 코어</h1>
    <p class="smartcms-text-muted">설치 마법사부터 관리자, 회원, 게시판 모듈까지 한 프로젝트 안에서 관리합니다.</p>
    <div class="smartcms-actions">
      <a class="smartcms-link-btn smartcms-link-btn--primary" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">게시판 보기</a>
      <?php if ($user): ?>
        <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지</a>
      <?php else: ?>
        <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a>
      <?php endif; ?>
      <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>">관리자</a>
    </div>
  </section>

  <section class="smartcms-card-grid">
    <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/member/')) ?>">
      <strong>회원 기능</strong>
      <span>로그인, 회원가입, 마이페이지, 비밀번호 변경</span>
    </a>
    <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">
      <strong>게시판 기능</strong>
      <span>글쓰기, 댓글, 첨부파일, 검색과 페이징</span>
    </a>
    <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>">
      <strong>관리자 기능</strong>
      <span>회원 권한, 페이지 권한, 게시판 관리, 로그 조회</span>
    </a>
  </section>
</main>
<?php smartcms_render_foot(); ?>
