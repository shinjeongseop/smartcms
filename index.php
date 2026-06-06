<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/ui/layout.php';
require_once __DIR__ . '/common/ui/components.php';
require_once __DIR__ . '/common/ui/navigation.php';

function smartcms_home_date(?string $value): string
{
    if (!$value) return '';
    $ts = strtotime($value);
    return $ts ? date('m.d', $ts) : $value;
}

$installed    = is_file(smartcms_config_local_path()) && smartcms_install_locked();
$user         = null;
$boards       = [];
$board_map    = [];
$board_counts = [];
$recent_posts = [];
$popular_posts = [];
$notice_posts = [];
$board_widgets = [];
$message = '';

if ($installed) {
    require_once __DIR__ . '/common/board.php';
    $user = smartcms_current_user();

    try {
        $boards = smartcms_board_list();
        foreach ($boards as $board) {
            $board_map[(string)$board['board_key']] = $board;
        }
        $board_counts  = smartcms_board_post_counts();
        $recent_posts  = smartcms_board_recent_posts(8);
        $popular_posts = smartcms_board_popular_posts(5);
        $notice_posts  = smartcms_board_recent_posts_by_key('notice', 4);

        foreach (['free' => '자유롭게 이야기를 나누는 공간', 'qna' => '궁금한 점을 남기고 답을 찾는 공간', 'notice' => '운영 소식과 중요한 안내'] as $bk => $summary) {
            if (!isset($board_map[$bk])) continue;
            $board_widgets[] = [
                'board'   => $board_map[$bk],
                'summary' => $summary,
                'posts'   => smartcms_board_recent_posts_by_key($bk, 5),
            ];
        }
    } catch (Throwable $e) {
        $message = '커뮤니티 데이터를 불러오지 못했습니다: ' . $e->getMessage();
    }
}

smartcms_render_head(['title' => 'smartcms', 'body_class' => 'sc-home-page']);
echo smartcms_site_header('home');
?>

<?php if (!$installed): ?>
  <!-- 미설치 히어로 -->
  <section class="sc-hero sc-hero--setup">
    <div>
      <p class="sc-eyebrow">Setup Required</p>
      <h1 class="sc-title">설치 마법사로 smartcms를 시작하세요</h1>
      <p class="sc-subtitle">DB 설정, 테이블 생성, 최초 관리자 계정 생성을 순서대로 완료하면 커뮤니티 기능을 사용할 수 있습니다.</p>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>">
          <i class="bi bi-magic me-1"></i>설치 마법사 시작
        </a>
        <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/install/check.php')) ?>">
          <i class="bi bi-server me-1"></i>서버 환경 확인
        </a>
      </div>
    </div>
    <div class="sc-hero-stats">
      <div class="sc-hero-stat--light"><span>STEP 01</span><strong class="sc-hero-step-title">DB 연결</strong></div>
      <div class="sc-hero-stat--light"><span>STEP 02</span><strong class="sc-hero-step-title">스키마 생성</strong></div>
      <div class="sc-hero-stat--light"><span>STEP 03</span><strong class="sc-hero-step-title">관리자 생성</strong></div>
    </div>
  </section>

<?php else: ?>
  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, 'error') ?>
  <?php endif; ?>

  <!-- 설치 완료 히어로 -->
  <section class="sc-hero sc-hero--portal">
    <div class="sc-hero-content">
      <p class="sc-eyebrow">Smart Community OS</p>
      <h1 class="sc-title">가볍게 설치하고, 바로 운영하는 커뮤니티 CMS</h1>
      <p class="sc-subtitle">공지, 자유게시판, Q&A와 회원 기능을 하나의 포털 화면으로 정리했습니다.</p>
      <div class="sc-hero-actions">
        <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">
          <i class="bi bi-list-ul me-1"></i>전체 게시판
        </a>
        <a class="btn btn-outline-secondary rounded-pill px-4"
           href="<?= smartcms_h(smartcms_base_url($user ? '/board/write/?board=free' : '/member/login/')) ?>">
          <i class="bi <?= $user ? 'bi-pencil-square' : 'bi-box-arrow-in-right' ?> me-1"></i>
          <?= $user ? '글쓰기' : '로그인' ?>
        </a>
      </div>
      <div class="sc-hero-points">
        <span><i class="bi bi-check2-circle"></i> 회원 레벨 권한</span>
        <span><i class="bi bi-check2-circle"></i> 게시판 스킨</span>
        <span><i class="bi bi-check2-circle"></i> 관리자 대시보드</span>
      </div>
    </div>
    <div class="sc-hero-stats sc-hero-stats--float">
      <div class="sc-hero-stat"><span>Boards</span><strong><?= count($boards) ?></strong></div>
      <div class="sc-hero-stat"><span>Recent</span><strong><?= count($recent_posts) ?></strong></div>
      <div class="sc-hero-stat"><span>Status</span><strong class="sc-hero-status"><?= $user ? 'ON' : 'Guest' ?></strong></div>
    </div>
  </section>

  <!-- 공지 배너 -->
  <div class="sc-notice-bar">
    <span class="badge text-bg-warning"><i class="bi bi-megaphone me-1"></i>공지</span>
    <?php if ($notice_posts): ?>
      <?php $notice = $notice_posts[0]; ?>
      <a href="<?= smartcms_h(smartcms_board_post_url((string)$notice['board_key'], (int)$notice['id'])) ?>">
        <?= smartcms_h($notice['title']) ?>
      </a>
      <time><?= smartcms_h(smartcms_home_date((string)$notice['created_at'])) ?></time>
    <?php else: ?>
      <a href="<?= smartcms_h(smartcms_base_url('/board/?board=notice')) ?>">아직 등록된 공지사항이 없습니다.</a>
      <time>준비중</time>
    <?php endif; ?>
  </div>

  <section class="sc-home-strip" aria-label="커뮤니티 요약">
    <div class="sc-strip-card">
      <span>총 게시글</span>
      <strong><?= number_format(array_sum($board_counts)) ?></strong>
    </div>
    <div class="sc-strip-card">
      <span>활성 게시판</span>
      <strong><?= number_format(count($boards)) ?></strong>
    </div>
    <div class="sc-strip-card">
      <span>최근 7일</span>
      <strong><?= number_format(count($recent_posts)) ?></strong>
    </div>
  </section>

  <!-- 메인 레이아웃 -->
  <div class="sc-home-layout">
    <!-- 왼쪽: 게시판 위젯 -->
    <div class="sc-home-main">

      <!-- 전체 최신글 -->
      <section class="card sc-widget">
        <div class="sc-widget-head">
          <div>
            <p class="sc-eyebrow">Latest</p>
            <h2>전체 최신글</h2>
          </div>
          <a class="sc-widget-more" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">더보기</a>
        </div>
        <div class="sc-post-list sc-post-list--featured">
          <?php foreach ($recent_posts as $post): ?>
            <a class="sc-post-list-item" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
              <span class="badge text-bg-primary rounded-pill sc-board-badge"><?= smartcms_h($post['board_name']) ?></span>
              <strong><?= smartcms_h($post['title']) ?></strong>
              <em><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></em>
            </a>
          <?php endforeach; ?>
          <?php if (!$recent_posts): ?>
            <p class="sc-empty">아직 게시글이 없습니다. 첫 글을 남겨보세요.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- 게시판별 위젯 2열 -->
      <?php if ($board_widgets): ?>
        <div class="sc-widget-grid">
          <?php foreach ($board_widgets as $widget): ?>
            <?php $wb = $widget['board']; ?>
            <section class="card sc-widget">
              <div class="sc-widget-head">
                <div>
                  <p class="sc-eyebrow"><?= smartcms_h($wb['board_key']) ?></p>
                  <h2><?= smartcms_h($wb['board_name']) ?></h2>
                  <p class="sc-widget-summary"><?= smartcms_h($widget['summary']) ?></p>
                </div>
                <a class="sc-widget-more" href="<?= smartcms_h(smartcms_board_url((string)$wb['board_key'])) ?>">더보기</a>
              </div>
              <div class="sc-post-list">
                <?php foreach ($widget['posts'] as $post): ?>
                  <a class="sc-post-list-item" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                    <strong><?= smartcms_h($post['title']) ?></strong>
                    <em><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></em>
                  </a>
                <?php endforeach; ?>
                <?php if (!$widget['posts']): ?>
                  <p class="sc-empty">등록된 글이 없습니다.</p>
                <?php endif; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- 오른쪽: 사이드바 -->
    <aside class="sc-home-side">

      <!-- 멤버 카드 -->
      <section class="card sc-member-card">
        <?php if ($user): ?>
          <p class="sc-eyebrow">Member</p>
          <h2><?= smartcms_h($user['name']) ?>님</h2>
          <p>level <?= smartcms_h($user['level']) ?> 권한으로 이용 중</p>
          <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지</a>
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
          </div>
        <?php else: ?>
          <p class="sc-eyebrow">Welcome</p>
          <h2>로그인하고 참여하세요</h2>
          <p>회원가입 후 글쓰기, 댓글, 마이페이지를 이용할 수 있습니다.</p>
          <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary btn-sm rounded-pill px-3" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a>
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>">회원가입</a>
          </div>
        <?php endif; ?>
      </section>

      <!-- 인기글 -->
      <section class="card sc-widget">
        <div class="sc-widget-head">
          <div><p class="sc-eyebrow">Popular</p><h2>인기글</h2></div>
        </div>
        <div class="sc-rank-list">
          <?php foreach ($popular_posts as $idx => $post): ?>
            <a class="sc-rank-list-item" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
              <span class="sc-rank-num"><?= $idx + 1 ?></span>
              <strong><?= smartcms_h($post['title']) ?></strong>
              <em>조회 <?= number_format((int)$post['view_count']) ?></em>
            </a>
          <?php endforeach; ?>
          <?php if (!$popular_posts): ?>
            <p class="sc-empty">인기글 집계 전입니다.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- 게시판 바로가기 -->
      <section class="card sc-widget">
        <div class="sc-widget-head">
          <div><p class="sc-eyebrow">Boards</p><h2>바로가기</h2></div>
        </div>
        <div class="sc-shortcut-list">
          <?php foreach ($boards as $board): ?>
            <?php if ((string)$board['status'] === 'hidden') continue; ?>
            <a class="sc-shortcut-item" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
              <strong><?= smartcms_h($board['board_name']) ?></strong>
              <span><?= (int)($board_counts[(string)$board['board_key']] ?? 0) ?> posts</span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>

    </aside>
  </div>
<?php endif; ?>

<?= smartcms_site_footer() ?>
<?php smartcms_render_foot(); ?>
