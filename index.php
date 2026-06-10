<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/board.php';

function smartcms_home_date(?string $value): string
{
    if (!$value) {
        return '';
    }

    $ts = strtotime($value);
    return $ts ? date('m.d', $ts) : $value;
}

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

$SMARTCMS_HEAD = [
    'title' => 'smartcms Community',
    'body_class' => 'bg-body',
    'active_menu' => 'home',
];

require SMARTCMS_ROOT . '/head.php';
?>

<header class="bg-primary text-white py-5 py-lg-5">
  <div class="container-xxl">
    <div class="row align-items-center g-5 m-0">
      <div class="col-lg-7">
        <span class="badge text-bg-light text-primary rounded-pill px-3 py-2 mb-3 fw-semibold">v2.0 Bootstrap-Native</span>
        <h1 class="display-4 fw-bold mb-3">더 가볍고, 더 똑똑한<br>차세대 커뮤니티 CMS</h1>
        <p class="lead mb-4 text-white-50">
          부트스트랩 5의 강력한 유틸리티 엔진으로 구축된 smartcms 2.0입니다.
          커스텀 CSS를 최소화하고 웹 표준을 지켜 유지보수성을 높였습니다.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a class="btn btn-light rounded-pill px-4 fw-semibold text-primary" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">시작하기</a>
          <a class="btn btn-dark rounded-pill px-4 fw-semibold" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>">회원가입</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card border shadow-sm bg-white">
          <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-start gap-3 mb-4">
              <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 p-3">
                <i class="bi bi-lightning-charge-fill fs-4"></i>
              </div>
              <div>
                <h2 class="h5 fw-bold mb-1">압도적 성능</h2>
                <p class="text-body-secondary mb-0">Pure Bootstrap & Optimized PHP</p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 p-3">
                <i class="bi bi-shield-check-fill fs-4"></i>
              </div>
              <div>
                <h2 class="h5 fw-bold mb-1">강력한 보안</h2>
                <p class="text-body-secondary mb-0">CSRF/XSS Protection Built-in</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container-xxl py-5">
  <?php if ($message !== ''): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
      <i class="bi bi-exclamation-triangle-fill mt-1"></i>
      <div><?= smartcms_h($message) ?></div>
    </div>
  <?php endif; ?>

  <section class="row g-4 mb-5">
    <div class="col-12 col-lg-8">
      <article class="card border shadow-sm h-100">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div class="d-flex align-items-start gap-3">
            <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-megaphone-fill fs-5"></i>
            </span>
            <div class="flex-grow-1 overflow-hidden">
              <p class="small text-uppercase fw-semibold text-primary mb-1">Notice</p>
              <?php if ($notice_posts): ?>
                <?php $notice = $notice_posts[0]; ?>
                <a class="text-decoration-none fw-semibold text-dark d-block text-truncate"
                   href="<?= smartcms_h(smartcms_board_post_url((string)$notice['board_key'], (int)$notice['id'])) ?>">
                  <?= smartcms_h($notice['title']) ?>
                </a>
              <?php else: ?>
                <span class="text-body-secondary">등록된 공지사항이 없습니다.</span>
              <?php endif; ?>
            </div>
          </div>
          <a href="<?= smartcms_h(smartcms_base_url('/board/?board=notice')) ?>" class="btn btn-light btn-sm rounded-pill px-3 flex-shrink-0">더보기</a>
        </div>
      </article>
    </div>

    <div class="col-12 col-lg-4">
      <article class="card border shadow-sm h-100 bg-dark text-white">
        <div class="card-body p-4">
          <div class="row row-cols-3 g-2 text-center">
            <div>
              <div class="small text-white-50 text-uppercase mb-1">Posts</div>
              <div class="h5 fw-bold mb-0"><?= number_format(array_sum($board_counts)) ?></div>
            </div>
            <div>
              <div class="small text-white-50 text-uppercase mb-1">Boards</div>
              <div class="h5 fw-bold mb-0"><?= number_format(count($boards)) ?></div>
            </div>
            <div>
              <div class="small text-white-50 text-uppercase mb-1">7 Days</div>
              <div class="h5 fw-bold mb-0"><?= number_format(count($recent_posts)) ?></div>
            </div>
          </div>
        </div>
      </article>
    </div>
  </section>

  <section class="row g-4">
    <div class="col-12 col-lg-8">
      <article class="card border shadow-sm mb-4">
        <div class="card-header bg-white border-0 p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-clock-history text-primary lh-1"></i>
              <span>전체 최신글</span>
            </h2>
            <a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>" class="text-decoration-none small text-body-secondary">전체보기 <i class="bi bi-chevron-right"></i></a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="list-group list-group-flush mt-3">
            <?php if ($recent_posts): ?>
              <?php foreach ($recent_posts as $post): ?>
                <a class="list-group-item list-group-item-action p-4 border-0 border-bottom d-flex align-items-center gap-3"
                   href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                  <span class="badge text-bg-light text-secondary rounded-pill small flex-shrink-0"><?= smartcms_h($post['board_name']) ?></span>
                  <span class="text-dark fw-medium text-truncate flex-grow-1"><?= smartcms_h($post['title']) ?></span>
                  <time class="small text-body-secondary flex-shrink-0"><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></time>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="p-4 text-body-secondary">아직 최신글이 없습니다.</div>
            <?php endif; ?>
          </div>
        </div>
      </article>

      <?php if ($board_widgets): ?>
        <div class="row g-4">
          <?php foreach ($board_widgets as $widget): ?>
            <?php $board = $widget['board']; ?>
            <div class="col-12 col-md-6">
              <article class="card border shadow-sm h-100">
                <div class="card-header bg-white border-0 p-4">
                  <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                      <h3 class="h6 fw-bold mb-1 text-primary text-uppercase"><?= smartcms_h($board['board_name']) ?></h3>
                      <p class="small text-body-secondary mb-0"><?= smartcms_h($widget['summary']) ?></p>
                    </div>
                    <a href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>" class="btn btn-link btn-sm p-0 text-body-secondary">
                      <i class="bi bi-plus-lg"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body p-4 pt-3">
                  <div class="list-group list-group-flush">
                    <?php if ($widget['posts']): ?>
                      <?php foreach ($widget['posts'] as $post): ?>
                        <a class="list-group-item list-group-item-action px-0 py-2 border-0 d-flex justify-content-between align-items-center gap-2"
                           href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                          <span class="text-truncate"><?= smartcms_h($post['title']) ?></span>
                          <small class="text-body-secondary flex-shrink-0"><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></small>
                        </a>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="text-body-secondary small">등록된 글이 없습니다.</div>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <aside class="col-12 col-lg-4">
      <?php if ($user): ?>
        <article class="card border shadow-sm mb-4 overflow-hidden">
          <div class="card-body p-4 text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white shadow-sm mb-3 p-3">
              <i class="bi bi-person-fill fs-3"></i>
            </div>
            <h2 class="h5 fw-bold mb-1"><?= smartcms_h($user['name']) ?>님</h2>
            <p class="small text-body-secondary mb-3">level <?= smartcms_h($user['level']) ?> · <?= smartcms_h($user['email']) ?></p>
            <div class="row g-2">
              <div class="col-6">
                <a class="btn btn-light btn-sm w-100 rounded-pill border" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지</a>
              </div>
              <div class="col-6">
                <a class="btn btn-secondary btn-sm w-100 rounded-pill" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
              </div>
            </div>
          </div>
        </article>
      <?php else: ?>
        <article class="card border shadow-sm mb-4 bg-primary text-white">
          <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-2">커뮤니티 로그인</h2>
            <p class="small text-white-50 mb-4">가입 후 글쓰기와 댓글 참여가 가능합니다. 지금 시작하세요!</p>
            <div class="d-grid gap-2">
              <a class="btn btn-light text-primary fw-semibold rounded-pill" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a>
              <a class="btn btn-dark rounded-pill" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>">회원가입</a>
            </div>
          </div>
        </article>
      <?php endif; ?>

      <article class="card border shadow-sm mb-4">
        <div class="card-header bg-white border-0 p-4">
          <h3 class="h6 fw-bold mb-0 d-flex align-items-center gap-2 text-uppercase">
            <i class="bi bi-fire text-danger lh-1"></i>
            <span>실시간 인기글</span>
          </h3>
        </div>
        <div class="card-body p-4 pt-3">
          <div class="list-group list-group-flush">
            <?php if ($popular_posts): ?>
              <?php foreach ($popular_posts as $idx => $post): ?>
                <a class="list-group-item list-group-item-action px-0 py-3 border-0 border-bottom d-flex align-items-start gap-3"
                   href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                  <span class="text-primary fw-bold fs-5 lh-1"><?= (int)$idx + 1 ?></span>
                  <div class="flex-grow-1 overflow-hidden">
                    <strong class="d-block text-truncate mb-1"><?= smartcms_h($post['title']) ?></strong>
                    <div class="d-flex flex-wrap gap-2 small text-body-secondary">
                      <span>조회 <?= number_format((int)$post['view_count']) ?></span>
                      <span><?= smartcms_h($post['board_name']) ?></span>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-body-secondary small">인기글이 없습니다.</div>
            <?php endif; ?>
          </div>
        </div>
      </article>

      <article class="card border shadow-sm">
        <div class="card-body p-4">
          <h3 class="h6 fw-bold mb-3 text-uppercase text-body-secondary">전체 게시판</h3>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($boards as $board_item): ?>
              <?php if ((string)$board_item['status'] === 'hidden') continue; ?>
              <a href="<?= smartcms_h(smartcms_board_url((string)$board_item['board_key'])) ?>" class="btn btn-light btn-sm rounded-pill border">
                <?= smartcms_h($board_item['board_name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </article>
    </aside>
  </section>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
