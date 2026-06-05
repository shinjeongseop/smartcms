<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';

require_once __DIR__ . '/common/ui/layout.php';

$installed = is_file(smartcms_config_local_path()) && smartcms_install_locked();
$user = null;

if ($installed) {
    require_once __DIR__ . '/common/auth.php';
    $user = smartcms_current_user();
}

smartcms_render_head([
    'title' => 'smartcms',
    'body_class' => 'smartcms-site-home',
]);
?>
<main class="smartcms-content-shell">
  <section class="smartcms-home-hero">
    <p class="smartcms-eyebrow">smartcms</p>
    <?php if (!$installed): ?>
      <h1 class="smartcms-title">설치가 필요합니다</h1>
      <p class="smartcms-text-muted">DB 설정과 최초 관리자 계정 생성을 마치면 smartcms를 사용할 수 있습니다.</p>
      <div class="smartcms-actions">
        <a class="smartcms-link-btn smartcms-link-btn--primary" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>">설치 마법사 시작</a>
        <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/install/check.php')) ?>">서버 환경 확인</a>
      </div>
    <?php else: ?>
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
    <?php endif; ?>
  </section>

  <section class="smartcms-card-grid">
    <?php if (!$installed): ?>
    <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>">
      <strong>1 설치 마법사</strong>
      <span>DB 연결 정보를 입력하고 설정 파일을 생성합니다.</span>
    </a>
    <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>">
      <strong>2 테이블 생성</strong>
      <span>DB 설정 후 회원, 권한, 게시판, 로그 테이블을 준비합니다.</span>
    </a>
    <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>">
      <strong>3 관리자 계정 생성</strong>
      <span>테이블 생성 후 최초 level 10 관리자 계정을 생성합니다.</span>
    </a>
    <?php else: ?>
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
    <?php endif; ?>
  </section>
</main>
<?php smartcms_render_foot(); ?>
