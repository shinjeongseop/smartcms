<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/ui/layout.php';
require_once __DIR__ . '/common/ui/components.php';

$installed = is_file(smartcms_config_local_path()) && smartcms_install_locked();
$user = null;
$boards = [];
$recent_posts = [];
$message = '';

if ($installed) {
    require_once __DIR__ . '/common/board.php';
    $user = smartcms_current_user();

    try {
        $boards = smartcms_board_list();
        $recent_posts = smartcms_board_recent_posts(12);
    } catch (Throwable $e) {
        $message = '커뮤니티 데이터를 불러오지 못했습니다: ' . $e->getMessage();
    }
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
      <h1 class="smartcms-title">스마트 커뮤니티</h1>
      <p class="smartcms-text-muted">공지사항, 자유게시판, Q&A를 중심으로 운영되는 기본 커뮤니티 홈입니다.</p>
      <div class="smartcms-actions">
        <a class="smartcms-link-btn smartcms-link-btn--primary" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">전체 게시판</a>
        <?php if ($user): ?>
          <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지</a>
        <?php else: ?>
          <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a>
        <?php endif; ?>
        <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>">관리자</a>
      </div>
    <?php endif; ?>
  </section>

  <?php if (!$installed): ?>
    <section class="smartcms-card-grid">
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
    </section>
  <?php else: ?>
    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, 'error') ?>
    <?php endif; ?>

    <section class="smartcms-community-grid">
      <article class="smartcms-panel smartcms-admin-panel">
        <h2 class="smartcms-section-title">게시판</h2>
        <div class="smartcms-card-grid smartcms-card-grid--compact">
          <?php foreach ($boards as $board): ?>
            <?php if ((string)$board['status'] !== 'hidden'): ?>
              <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
                <strong><?= smartcms_h($board['board_name']) ?></strong>
                <span><?= smartcms_h($board['description'] ?? '게시판으로 이동') ?></span>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if (!$boards): ?>
            <p class="smartcms-text-muted">생성된 게시판이 없습니다.</p>
          <?php endif; ?>
        </div>
      </article>

      <article class="smartcms-panel smartcms-admin-panel">
        <h2 class="smartcms-section-title">최근글</h2>
        <div class="smartcms-mini-list">
          <?php foreach ($recent_posts as $post): ?>
            <a href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
              <strong><?= smartcms_h($post['title']) ?></strong>
              <span><?= smartcms_h($post['board_name']) ?> · <?= smartcms_h($post['author_name']) ?> · <?= smartcms_h($post['created_at']) ?></span>
            </a>
          <?php endforeach; ?>
          <?php if (!$recent_posts): ?>
            <p class="smartcms-text-muted">최근 게시글이 없습니다.</p>
          <?php endif; ?>
        </div>
      </article>
    </section>
  <?php endif; ?>
</main>
<?php smartcms_render_foot(); ?>
