<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/ui/layout.php';
require_once __DIR__ . '/common/ui/components.php';
require_once __DIR__ . '/common/ui/navigation.php';

function smartcms_home_date(?string $value): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('m.d', $timestamp) : $value;
}

$installed = is_file(smartcms_config_local_path()) && smartcms_install_locked();
$user = null;
$boards = [];
$board_map = [];
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

        $board_counts = smartcms_board_post_counts();
        $recent_posts = smartcms_board_recent_posts(8);
        $popular_posts = smartcms_board_popular_posts(5);
        $notice_posts = smartcms_board_recent_posts_by_key('notice', 4);

        foreach ([
            'free' => '자유롭게 이야기를 나누는 공간',
            'qna' => '궁금한 점을 남기고 답을 찾는 공간',
            'notice' => '운영 소식과 중요한 안내',
        ] as $board_key => $summary) {
            if (!isset($board_map[$board_key])) {
                continue;
            }

            $board_widgets[] = [
                'board' => $board_map[$board_key],
                'summary' => $summary,
                'posts' => smartcms_board_recent_posts_by_key($board_key, 5),
            ];
        }
    } catch (Throwable $e) {
        $message = '커뮤니티 데이터를 불러오지 못했습니다: ' . $e->getMessage();
    }
}

smartcms_render_head([
    'title' => 'smartcms',
    'body_class' => 'smartcms-site-home',
]);
?>
<?= smartcms_site_header('home', 'smartcms-home-shell') ?>

  <?php if (!$installed): ?>
    <section class="smartcms-home-hero smartcms-home-hero--setup">
      <div>
        <p class="smartcms-eyebrow">Setup Required</p>
        <h1 class="smartcms-title">설치 마법사로 smartcms를 시작하세요</h1>
        <p class="smartcms-text-muted">DB 설정, 테이블 생성, 최초 관리자 계정 생성을 순서대로 완료하면 커뮤니티 홈과 관리자 기능을 사용할 수 있습니다.</p>
        <div class="smartcms-actions">
          <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/install/')) ?>"><i class="bi bi-magic me-1"></i>설치 마법사 시작</a>
          <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/install/check.php')) ?>"><i class="bi bi-server me-1"></i>서버 환경 확인</a>
        </div>
      </div>
      <div class="smartcms-home-setup-card">
        <span>01</span>
        <strong>DB 연결</strong>
        <span>02</span>
        <strong>스키마 생성</strong>
        <span>03</span>
        <strong>관리자 생성</strong>
      </div>
    </section>
  <?php else: ?>
    <?php if ($message !== ''): ?>
      <?= smartcms_alert($message, 'error') ?>
    <?php endif; ?>

    <section class="smartcms-home-hero smartcms-home-hero--portal">
      <div class="smartcms-home-hero-copy">
        <p class="smartcms-eyebrow">Community Builder</p>
        <h1 class="smartcms-title">게시판과 회원 기능을 한 화면에 모은 커뮤니티 홈</h1>
        <p class="smartcms-text-muted">공지, 자유게시판, Q&A를 위젯처럼 조합해 사이트 첫 화면을 빠르게 구성합니다.</p>
        <div class="smartcms-actions">
          <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>"><i class="bi bi-list-ul me-1"></i>전체 게시판 보기</a>
          <a class="btn btn-outline-light rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url($user ? '/board/write/?board=free' : '/member/login/')) ?>"><i class="bi <?= $user ? 'bi-pencil-square' : 'bi-box-arrow-in-right' ?> me-1"></i><?= $user ? '글쓰기' : '로그인' ?></a>
        </div>
      </div>
      <div class="smartcms-home-hero-board">
        <div>
          <span>Boards</span>
          <strong><?= count($boards) ?></strong>
        </div>
        <div>
          <span>Recent</span>
          <strong><?= count($recent_posts) ?></strong>
        </div>
        <div>
          <span>Members</span>
          <strong><?= $user ? 'ON' : 'Guest' ?></strong>
        </div>
      </div>
    </section>

    <section class="smartcms-home-notice">
      <strong><i class="bi bi-megaphone me-1"></i>공지</strong>
      <?php if ($notice_posts): ?>
        <?php $notice = $notice_posts[0]; ?>
        <a href="<?= smartcms_h(smartcms_board_post_url((string)$notice['board_key'], (int)$notice['id'])) ?>">
          <?= smartcms_h($notice['title']) ?>
        </a>
        <span><?= smartcms_h(smartcms_home_date((string)$notice['created_at'])) ?></span>
      <?php else: ?>
        <a href="<?= smartcms_h(smartcms_base_url('/board/?board=notice')) ?>">아직 등록된 공지사항이 없습니다.</a>
        <span>준비중</span>
      <?php endif; ?>
    </section>

    <section class="smartcms-home-layout">
      <div class="smartcms-home-main">
        <section class="smartcms-home-widget smartcms-home-widget--wide">
          <div class="smartcms-home-widget-head">
            <div>
              <p class="smartcms-eyebrow">Latest</p>
              <h2>전체 최신글</h2>
            </div>
            <a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">더보기</a>
          </div>
          <div class="smartcms-home-list smartcms-home-list--featured">
            <?php foreach ($recent_posts as $post): ?>
              <a href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                <span class="badge rounded-pill text-bg-primary"><?= smartcms_h($post['board_name']) ?></span>
                <strong><?= smartcms_h($post['title']) ?></strong>
                <em><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></em>
              </a>
            <?php endforeach; ?>
            <?php if (!$recent_posts): ?>
              <p class="smartcms-home-empty">아직 게시글이 없습니다. 첫 글을 남겨보세요.</p>
            <?php endif; ?>
          </div>
        </section>

        <div class="smartcms-home-widget-grid">
          <?php foreach ($board_widgets as $widget): ?>
            <?php $board = $widget['board']; ?>
            <section class="smartcms-home-widget">
              <div class="smartcms-home-widget-head">
                <div>
                  <p class="smartcms-eyebrow"><?= smartcms_h($board['board_key']) ?></p>
                  <h2><?= smartcms_h($board['board_name']) ?></h2>
                  <span><?= smartcms_h($widget['summary']) ?></span>
                </div>
                <a href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">더보기</a>
              </div>
              <div class="smartcms-home-list">
                <?php foreach ($widget['posts'] as $post): ?>
                  <a href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                    <strong><?= smartcms_h($post['title']) ?></strong>
                    <em><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></em>
                  </a>
                <?php endforeach; ?>
                <?php if (!$widget['posts']): ?>
                  <p class="smartcms-home-empty">등록된 글이 없습니다.</p>
                <?php endif; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="smartcms-home-side">
        <section class="smartcms-home-member-card">
          <?php if ($user): ?>
            <p class="smartcms-eyebrow">Member</p>
            <h2><?= smartcms_h($user['name']) ?>님</h2>
            <p>현재 level <?= smartcms_h($user['level']) ?> 권한으로 이용 중입니다.</p>
            <div class="smartcms-actions">
              <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지</a>
              <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
            </div>
          <?php else: ?>
            <p class="smartcms-eyebrow">Welcome</p>
            <h2>로그인하고 커뮤니티에 참여하세요</h2>
            <p>회원가입 후 글쓰기, 댓글, 마이페이지 기능을 사용할 수 있습니다.</p>
            <div class="smartcms-actions">
              <a class="btn btn-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a>
              <a class="btn btn-outline-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>">회원가입</a>
            </div>
          <?php endif; ?>
        </section>

        <section class="smartcms-home-widget">
          <div class="smartcms-home-widget-head">
            <div>
              <p class="smartcms-eyebrow">Popular</p>
              <h2>인기글</h2>
            </div>
          </div>
          <div class="smartcms-home-rank-list">
            <?php foreach ($popular_posts as $index => $post): ?>
              <a href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                <span><?= $index + 1 ?></span>
                <strong><?= smartcms_h($post['title']) ?></strong>
                <em>조회 <?= smartcms_h($post['view_count']) ?></em>
              </a>
            <?php endforeach; ?>
            <?php if (!$popular_posts): ?>
              <p class="smartcms-home-empty">인기글 집계 전입니다.</p>
            <?php endif; ?>
          </div>
        </section>

        <section class="smartcms-home-widget">
          <div class="smartcms-home-widget-head">
            <div>
              <p class="smartcms-eyebrow">Boards</p>
              <h2>바로가기</h2>
            </div>
          </div>
          <div class="smartcms-home-shortcuts">
            <?php foreach ($boards as $board): ?>
              <?php if ((string)$board['status'] === 'hidden'): ?>
                <?php continue; ?>
              <?php endif; ?>
              <a href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
                <strong><?= smartcms_h($board['board_name']) ?></strong>
                <span><?= (int)($board_counts[(string)$board['board_key']] ?? 0) ?> posts</span>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      </aside>
    </section>
  <?php endif; ?>
  <?= smartcms_site_footer() ?>
</main>
<?php smartcms_render_foot(); ?>
