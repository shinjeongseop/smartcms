<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/head.php';
require_once __DIR__ . '/common/ui/components.php';
require_once __DIR__ . '/foot.php';

function smartcms_home_date(?string $value): string
{
    if (!$value) {
        return '';
    }

    $ts = strtotime($value);
    return $ts ? date('m.d', $ts) : $value;
}

require_once __DIR__ . '/common/board.php';

$user = smartcms_current_user();
$boards = [];
$board_map = [];
$board_counts = [];
$recent_posts = [];
$popular_posts = [];
$notice_posts = [];
$board_widgets = [];
$message = '';

try {
    $boards = smartcms_board_list();
    foreach ($boards as $board) {
        $board_map[(string)$board['board_key']] = $board;
    }
    $board_counts = smartcms_board_post_counts();
    $recent_posts = smartcms_board_recent_posts(8);
    $popular_posts = smartcms_board_popular_posts(5);
    $notice_posts = smartcms_board_recent_posts_by_key('notice', 4);

    foreach (['free' => '자유롭게 이야기를 나누는 공간', 'qna' => '궁금한 점을 남기고 답을 찾는 공간', 'notice' => '운영 소식과 중요한 안내'] as $bk => $summary) {
        if (!isset($board_map[$bk])) {
            continue;
        }
        $board_widgets[] = [
            'board' => $board_map[$bk],
            'summary' => $summary,
            'posts' => smartcms_board_recent_posts_by_key($bk, 5),
        ];
    }
} catch (Throwable $e) {
    $message = '커뮤니티 데이터를 불러오지 못했습니다: ' . $e->getMessage();
}

$SMARTCMS_HEAD = ['title' => 'smartcms', 'body_class' => 'bg-body'];
require SMARTCMS_ROOT . '/head.php';
echo smartcms_site_header('home');
?>

<section class="bg-white border-bottom">
  <div class="container-fluid container-xxl py-4 py-lg-5">
    <div class="row g-4 align-items-stretch">
      <div class="col-12 col-xl-8">
        <div class="card border-0 h-100 bg-primary text-white overflow-hidden position-relative">
          <div class="card-body p-4 p-lg-5 position-relative">
            <span class="badge text-bg-light text-primary fw-bold mb-3">Hot Update</span>
            <h1 class="display-5 fw-bold mb-3">그누보드 감성의 모던 부트스트랩 커뮤니티</h1>
            <p class="lead mb-4 text-white-50">공지, 자유게시판, Q&A와 회원 기능을 하나의 포털 화면으로 정리했습니다. Bootstrap 기본 컴포넌트와 유틸리티만으로 구성한 가벼운 운영형 레이아웃입니다.</p>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-light text-primary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>"><i class="bi bi-list-ul me-1"></i>전체 게시판</a>
              <a class="btn btn-secondary rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url($user ? '/board/write/?board=free' : '/member/login/')) ?>">
                <i class="bi <?= $user ? 'bi-pencil-square' : 'bi-box-arrow-in-right' ?> me-1"></i><?= $user ? '글쓰기' : '로그인' ?>
              </a>
              <a class="btn btn-light rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>"><i class="bi bi-person-plus me-1"></i>회원가입</a>
              <a class="btn btn-light rounded-pill px-4" href="<?= smartcms_h(smartcms_base_url('/admin/')) ?>"><i class="bi bi-speedometer2 me-1"></i>관리자</a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card border-0 h-100">
          <div class="card-body p-4">
            <p class="text-uppercase small fw-semibold text-success mb-2">Community Snapshot</p>
            <?php if ($notice_posts): ?>
              <?php $notice = $notice_posts[0]; ?>
              <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                <div>
                  <p class="text-uppercase small fw-semibold text-primary mb-1">공지</p>
                  <a class="text-decoration-none fw-semibold" href="<?= smartcms_h(smartcms_board_post_url((string)$notice['board_key'], (int)$notice['id'])) ?>"><?= smartcms_h($notice['title']) ?></a>
                </div>
                <time class="text-body-secondary small flex-shrink-0"><?= smartcms_h(smartcms_home_date((string)$notice['created_at'])) ?></time>
              </div>
            <?php endif; ?>
            <div class="list-group list-group-flush">
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="text-body-secondary">총 게시글</span>
                <strong><?= number_format(array_sum($board_counts)) ?></strong>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="text-body-secondary">활성 게시판</span>
                <strong><?= number_format(count($boards)) ?></strong>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="text-body-secondary">최근 7일</span>
                <strong><?= number_format(count($recent_posts)) ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container-fluid container-xxl py-4 py-lg-5">
  <?php if ($message !== ''): ?>
    <?= smartcms_alert($message, 'error') ?>
  <?php endif; ?>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge text-bg-warning"><i class="bi bi-megaphone me-1"></i>공지</span>
        <?php if ($notice_posts): ?>
          <?php $notice = $notice_posts[0]; ?>
          <a class="text-decoration-none fw-semibold" href="<?= smartcms_h(smartcms_board_post_url((string)$notice['board_key'], (int)$notice['id'])) ?>"><?= smartcms_h($notice['title']) ?></a>
        <?php else: ?>
          <a class="text-decoration-none fw-semibold" href="<?= smartcms_h(smartcms_base_url('/board/?board=notice')) ?>">아직 등록된 공지사항이 없습니다.</a>
        <?php endif; ?>
      </div>
      <time class="text-body-secondary small"><?= smartcms_h($notice_posts ? smartcms_home_date((string)$notice_posts[0]['created_at']) : '준비중') ?></time>
    </div>
  </div>

  <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
    <div class="col">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-body-secondary small">총 게시글</div>
          <div class="h2 fw-bold mb-0"><?= number_format(array_sum($board_counts)) ?></div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-body-secondary small">활성 게시판</div>
          <div class="h2 fw-bold mb-0"><?= number_format(count($boards)) ?></div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-body-secondary small">최근 7일</div>
          <div class="h2 fw-bold mb-0"><?= number_format(count($recent_posts)) ?></div>
        </div>
      </div>
    </div>
  </div>

  <?= smartcms_two_column_start() ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
          <div>
            <p class="text-uppercase small fw-semibold text-primary mb-1">Latest</p>
            <h2 class="h5 fw-bold mb-0">전체 최신글</h2>
          </div>
          <a class="btn btn-secondary btn-sm" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">더보기</a>
        </div>
        <div class="list-group list-group-flush">
          <?php foreach ($recent_posts as $post): ?>
            <a class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"
               href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
              <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge text-bg-primary rounded-pill"><?= smartcms_h($post['board_name']) ?></span>
                <strong><?= smartcms_h($post['title']) ?></strong>
              </div>
              <small class="text-body-secondary"><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></small>
            </a>
          <?php endforeach; ?>
          <?php if (!$recent_posts): ?>
            <div class="list-group-item text-body-secondary">아직 게시글이 없습니다. 첫 글을 남겨보세요.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($board_widgets): ?>
      <div class="row g-3">
        <?php foreach ($board_widgets as $widget): ?>
          <?php $wb = $widget['board']; ?>
          <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                  <div>
                    <p class="text-uppercase small fw-semibold text-primary mb-1"><?= smartcms_h($wb['board_key']) ?></p>
                    <h3 class="h5 fw-bold mb-1"><?= smartcms_h($wb['board_name']) ?></h3>
                    <p class="text-body-secondary mb-0"><?= smartcms_h($widget['summary']) ?></p>
                  </div>
                  <a class="btn btn-secondary btn-sm" href="<?= smartcms_h(smartcms_board_url((string)$wb['board_key'])) ?>">더보기</a>
                </div>
                <div class="list-group list-group-flush">
                  <?php foreach ($widget['posts'] as $post): ?>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 px-0"
                       href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                      <span class="text-truncate"><?= smartcms_h($post['title']) ?></span>
                      <small class="text-body-secondary flex-shrink-0"><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></small>
                    </a>
                  <?php endforeach; ?>
                  <?php if (!$widget['posts']): ?>
                    <div class="text-body-secondary">등록된 글이 없습니다.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?= smartcms_two_column_middle() ?>
    <?= smartcms_sidebar_card(
      $user ? 'Member' : 'Welcome',
      $user
        ? '<h2 class="h5 fw-bold mb-2">' . smartcms_h($user['name']) . '님</h2>'
          . '<p class="text-body-secondary mb-3">level ' . smartcms_h($user['level']) . ' 권한으로 이용 중</p>'
          . '<div class="d-flex flex-wrap gap-2">'
          . '<a class="btn btn-primary btn-sm rounded-pill px-3" href="' . smartcms_h(smartcms_base_url('/member/mypage/')) . '">마이페이지</a>'
          . '<a class="btn btn-secondary btn-sm rounded-pill px-3" href="' . smartcms_h(smartcms_base_url('/member/logout/')) . '">로그아웃</a>'
          . '</div>'
        : '<h2 class="h5 fw-bold mb-2">로그인하고 참여하세요</h2>'
          . '<p class="text-body-secondary mb-3">회원가입 후 글쓰기, 댓글, 마이페이지를 이용할 수 있습니다.</p>'
          . '<div class="d-flex flex-wrap gap-2">'
          . '<a class="btn btn-primary btn-sm rounded-pill px-3" href="' . smartcms_h(smartcms_base_url('/member/login/')) . '">로그인</a>'
          . '<a class="btn btn-secondary btn-sm rounded-pill px-3" href="' . smartcms_h(smartcms_base_url('/member/register/')) . '">회원가입</a>'
          . '</div>'
    ) ?>
    <?= smartcms_sidebar_card(
      'Boards',
      '<div class="list-group list-group-flush">'
      . implode('', array_map(static function (array $board) use ($board_counts): string {
          if ((string)$board['status'] === 'hidden') {
              return '';
          }
          return '<a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 px-0" href="'
              . smartcms_h(smartcms_board_url((string)$board['board_key']))
              . '"><strong>'
              . smartcms_h($board['board_name'])
              . '</strong><small class="text-body-secondary">'
              . (int)($board_counts[(string)$board['board_key']] ?? 0)
              . ' posts</small></a>';
      }, $boards))
      . '</div>',
      '게시판 바로가기와 인기글을 한눈에 확인하세요.'
    ) ?>
    <?= smartcms_sidebar_card(
      'Popular',
      '<div class="list-group list-group-flush">'
      . implode('', array_map(static function (array $post, int $idx): string {
          return '<a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-start gap-2" href="'
              . smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id']))
              . '"><span class="d-flex align-items-center gap-2 text-truncate"><span class="badge text-bg-light border text-body-secondary">'
              . ($idx + 1)
              . '</span><strong class="text-truncate">'
              . smartcms_h($post['title'])
              . '</strong></span><small class="text-body-secondary flex-shrink-0">조회 '
              . number_format((int)$post['view_count'])
              . '</small></a>';
      }, $popular_posts, array_keys($popular_posts)))
      . '</div>',
      $popular_posts ? '' : '인기글 집계 전입니다.'
    ) ?>
  <?= smartcms_two_column_end() ?>
</section>

<?= smartcms_site_footer() ?>
<?php $SMARTCMS_FOOT = []; require SMARTCMS_ROOT . '/foot.php'; ?>
