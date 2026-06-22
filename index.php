<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/auth.php';
require_once __DIR__ . '/common/board.php';

$user = smartcms_current_user();
$boards = [];
$board_counts = [];
$recent_posts = [];
$popular_posts = [];
$notice_posts = [];
$board_widgets = [];
$message = '';

try {
    $boards = smartcms_board_list();
    $board_counts = smartcms_board_post_counts();
    $recent_posts = smartcms_board_recent_posts(5);
    $popular_posts = smartcms_board_popular_posts(5);
    $notice_posts = smartcms_board_recent_posts_by_key('notice', 4);

    foreach ($boards as $board) {
        if ((string)($board['status'] ?? '') !== 'active') {
            continue;
        }

        $skin_meta = smartcms_board_skin_meta($board);
        $thumb_config = smartcms_board_thumbnail_config($board, 'latest');
        $summary = trim((string)($board['description'] ?? ''));
        if ($summary === '') {
            $summary = (string)($skin_meta['skin'] ?? '') === 'gallery'
                ? '최신 이미지를 모아 보여주는 공간입니다.'
                : '최신 글을 확인해보세요.';
        }

        $limit = (string)($skin_meta['skin'] ?? '') === 'gallery' ? 3 : 4;
        $board_widgets[] = [
            'board' => $board,
            'skin_meta' => $skin_meta,
            'summary' => $summary,
            'thumb_config' => $thumb_config,
            'posts' => smartcms_board_recent_posts_by_key((string)$board['board_key'], $limit),
        ];
    }
} catch (Throwable $e) {
    $message = '커뮤니티 데이터를 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.';
}

$post_groups = [
    'recent' => ['label' => '최신순', 'posts' => $recent_posts],
    'popular' => ['label' => '인기순', 'posts' => $popular_posts],
];

$SMARTCMS_HEAD = [
    'title' => smartcms_site_name() . ' Community',
    'body_class' => 'bg-light',
    'active_menu' => 'home',
    'main_class' => 'flex-grow-1 pb-5',
];

require SMARTCMS_ROOT . '/head.php';
?>

<section class="bg-white border-bottom sc-home-hero" aria-labelledby="home-heading">
  <div class="container-xxl py-5">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-12 col-lg-7">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 px-3 py-2 mb-3 fw-bold"><?= smartcms_h(smartcms_version_tag()) ?> Community</span>
        <h1 class="display-5 fw-bold lh-sm text-dark mb-3" id="home-heading">정보가 모이고, 사람과 경험이 이어지는 커뮤니티</h1>
        <p class="lead text-body-secondary mb-4">공지사항부터 프로젝트 노하우, 자유로운 의견까지 한곳에서 빠르게 찾고 나눌 수 있습니다.</p>
        <div class="d-flex flex-wrap gap-3">
          <a class="btn btn-primary rounded-2 px-4 py-2 fw-bold" href="#latest-posts"><i class="bi bi-arrow-down me-2"></i>최신글 보기</a>
          <a class="btn btn-light border rounded-2 px-4 py-2 fw-bold" href="#board-widgets">게시판 둘러보기</a>
        </div>
      </div>
      <div class="col-12 col-lg-5">
        <aside class="card border shadow-sm">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-start justify-content-between gap-3 pb-3 border-bottom">
              <div>
                <p class="small text-uppercase fw-bold text-body-secondary mb-1">Community pulse</p>
                <h2 class="h5 fw-bold text-dark mb-0">오늘도 활발하게 운영 중입니다</h2>
              </div>
              <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary-subtle text-primary sc-home-icon"><i class="bi bi-activity fs-5"></i></span>
            </div>
            <dl class="row row-cols-3 g-0 text-center mb-0 pt-3">
              <div class="col border-end"><dt class="small text-body-secondary fw-semibold">게시물</dt><dd class="h4 fw-bold text-dark mb-0 mt-1"><?= number_format(array_sum($board_counts)) ?></dd></div>
              <div class="col border-end"><dt class="small text-body-secondary fw-semibold">게시판</dt><dd class="h4 fw-bold text-dark mb-0 mt-1"><?= number_format(count($boards)) ?></dd></div>
              <div class="col"><dt class="small text-body-secondary fw-semibold">최근글</dt><dd class="h4 fw-bold text-primary mb-0 mt-1"><?= number_format(count($recent_posts)) ?></dd></div>
            </dl>
          </div>
        </aside>
      </div>
    </div>
  </div>
</section>

<div class="container-xxl py-4 py-lg-5">
  <?php if ($message !== ''): ?>
    <aside class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      <div class="fw-medium"><?= smartcms_h($message) ?></div>
    </aside>
  <?php endif; ?>

  <section class="card border shadow-sm mb-4" aria-label="공지사항">
    <div class="card-body p-3 p-lg-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary-subtle text-primary flex-shrink-0 sc-home-icon"><i class="bi bi-megaphone"></i></span>
        <div class="min-w-0">
          <p class="small text-uppercase fw-bold text-primary mb-1">Notice</p>
          <?php if ($notice_posts): ?>
            <?php $notice = $notice_posts[0]; ?>
            <a class="text-decoration-none fw-bold text-dark d-block text-truncate" href="<?= smartcms_h(smartcms_board_post_url((string)$notice['board_key'], (int)$notice['id'])) ?>"><?= smartcms_h(smartcms_board_truncate_title((string)$notice['title'])) ?></a>
          <?php else: ?>
            <span class="text-body-secondary small">등록된 공지사항이 없습니다.</span>
          <?php endif; ?>
        </div>
      </div>
      <a class="btn btn-light border rounded-2 px-3 fw-bold flex-shrink-0" href="<?= smartcms_h(smartcms_base_url('/board/?board=notice')) ?>">전체 공지</a>
    </div>
  </section>

  <section class="row g-4" aria-label="게시글과 커뮤니티 정보">
    <div class="col-12 col-lg-8">
      <article class="card border shadow-sm mb-4" id="latest-posts">
        <header class="card-header bg-white border-bottom p-3 p-lg-4 d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-3">
          <div>
            <p class="small text-uppercase fw-bold text-primary mb-1">Latest updates</p>
            <h2 class="h5 fw-bold text-dark mb-0">전체 최신글</h2>
          </div>
          <ul class="nav nav-underline" role="tablist" aria-label="게시글 정렬">
            <?php foreach ($post_groups as $group_key => $group): ?>
              <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold <?= $group_key === 'recent' ? 'active' : '' ?>" id="<?= smartcms_h($group_key) ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= smartcms_h($group_key) ?>-panel" type="button" role="tab" aria-controls="<?= smartcms_h($group_key) ?>-panel" aria-selected="<?= $group_key === 'recent' ? 'true' : 'false' ?>"><?= smartcms_h($group['label']) ?></button>
              </li>
            <?php endforeach; ?>
          </ul>
        </header>
        <div class="card-body p-0 tab-content">
          <?php foreach ($post_groups as $group_key => $group): ?>
            <div class="tab-pane fade <?= $group_key === 'recent' ? 'show active' : '' ?>" id="<?= smartcms_h($group_key) ?>-panel" role="tabpanel" aria-labelledby="<?= smartcms_h($group_key) ?>-tab" tabindex="0">
              <div class="list-group list-group-flush">
                <?php if ($group['posts']): ?>
                  <?php foreach ($group['posts'] as $post): ?>
                    <a class="list-group-item list-group-item-action p-3 p-lg-4" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                      <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-light text-secondary border rounded-2"><?= smartcms_h($post['board_name']) ?></span>
                        <time class="small text-body-secondary" datetime="<?= date('Y-m-d', strtotime((string)$post['created_at'])) ?>"><?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?></time>
                      </div>
                      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                        <strong class="text-dark text-truncate min-w-0"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></strong>
                        <span class="small text-body-secondary flex-shrink-0">
                          <?= smartcms_h(smartcms_board_author_display_name(null, $post)) ?> · 댓글 <?= number_format((int)$post['comment_count']) ?>
                          <?php if (array_key_exists('view_count', $post)): ?> · 조회 <?= number_format((int)$post['view_count']) ?><?php endif; ?>
                        </span>
                      </div>
                    </a>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="p-5 text-center text-body-secondary"><i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>등록된 글이 없습니다.</div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <footer class="card-footer bg-white border-top p-3 p-lg-4 text-end"><a class="text-decoration-none fw-bold text-primary" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">전체글 보기</a></footer>
      </article>

      <?php if ($board_widgets): ?>
        <section class="row g-4" id="board-widgets" aria-label="게시판별 최신글">
          <?php foreach ($board_widgets as $widget): ?>
            <?php
              $board = $widget['board'];
              $is_gallery_widget = (string)($widget['skin_meta']['skin'] ?? '') === 'gallery';
              $widget_thumb_config = $widget['thumb_config'] ?? ['width' => 320, 'height' => 240];
            ?>
            <div class="col-12 <?= $is_gallery_widget ? '' : 'col-md-6' ?>">
              <article class="card border shadow-sm h-100">
                <header class="card-header bg-white border-bottom p-3 p-lg-4 d-flex align-items-start justify-content-between gap-3">
                  <div class="min-w-0">
                    <p class="small text-uppercase fw-bold text-primary mb-1"><?= smartcms_h((string)($widget['skin_meta']['skin'] ?? 'Board')) ?></p>
                    <h2 class="h5 fw-bold text-dark mb-1"><?= smartcms_h($board['board_name']) ?></h2>
                    <p class="small text-body-secondary mb-0 text-truncate"><?= smartcms_h($widget['summary']) ?></p>
                  </div>
                  <a class="btn btn-light border btn-sm rounded-2 fw-bold flex-shrink-0" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">전체보기</a>
                </header>
                <?php if ($is_gallery_widget): ?>
                  <div class="card-body p-3 p-lg-4">
                    <?php if ($widget['posts']): ?>
                      <div class="row row-cols-1 row-cols-sm-3 g-3">
                        <?php foreach ($widget['posts'] as $post): ?>
                          <?php $gallery_image = smartcms_board_first_image_file((int)$post['id']); ?>
                          <div class="col">
                            <a class="d-block text-decoration-none" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                              <div class="ratio ratio-4x3 rounded-2 overflow-hidden bg-light border mb-3">
                                <?php if ($gallery_image): ?>
                                  <?php $gallery_thumb = smartcms_board_file_thumbnail_url($gallery_image, (int)$widget_thumb_config['width'], (int)$widget_thumb_config['height']); ?>
                                  <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($gallery_thumb ?? '') ?>" alt="<?= smartcms_h($gallery_image['original_name']) ?>">
                                <?php else: ?>
                                  <span class="d-flex align-items-center justify-content-center text-secondary"><i class="bi bi-image fs-2 opacity-25"></i></span>
                                <?php endif; ?>
                              </div>
                              <strong class="small text-dark d-block text-truncate"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></strong>
                            </a>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <p class="small text-body-secondary mb-0">등록된 이미지가 없습니다.</p>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="list-group list-group-flush small">
                    <?php if ($widget['posts']): ?>
                      <?php foreach ($widget['posts'] as $post): ?>
                        <a class="list-group-item list-group-item-action px-3 px-lg-4 py-3 d-flex align-items-center justify-content-between gap-3" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                          <span class="text-dark fw-semibold text-truncate"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></span>
                          <time class="text-body-secondary flex-shrink-0" datetime="<?= date('Y-m-d', strtotime((string)$post['created_at'])) ?>"><?= date('m.d', strtotime((string)$post['created_at'])) ?></time>
                        </a>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="p-3 p-lg-4 text-body-secondary">등록된 글이 없습니다.</div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </article>
            </div>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
    </div>

    <aside class="col-12 col-lg-4">
      <article class="card border shadow-sm mb-4">
        <div class="card-body p-3 p-lg-4">
          <?php if ($user): ?>
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3 min-w-0">
                <?= smartcms_user_avatar_markup($user, 'sc-avatar-72', 'fs-2') ?>
                <div class="min-w-0"><h2 class="h5 fw-bold text-dark mb-1 text-truncate"><?= smartcms_h(smartcms_user_display_name($user)) ?>님</h2><p class="small text-body-secondary mb-0 text-truncate">level <?= smartcms_h($user['level']) ?> · <?= smartcms_h($user['email']) ?></p></div>
              </div>
              <a class="btn btn-light border rounded-2 flex-shrink-0" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>" title="마이페이지" aria-label="마이페이지"><i class="bi bi-person-gear"></i></a>
            </div>
          <?php else: ?>
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3"><div><p class="small text-uppercase fw-bold text-body-secondary mb-1">Member</p><h2 class="h5 fw-bold text-dark mb-0">커뮤니티에 참여하세요</h2></div><span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-primary-subtle text-primary sc-home-icon"><i class="bi bi-people"></i></span></div>
            <p class="small text-body-secondary mb-4">로그인하면 글쓰기, 댓글, 마이페이지 기능을 사용할 수 있습니다.</p>
            <div class="row g-2"><div class="col-6"><a class="btn btn-primary w-100 rounded-2 fw-bold" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a></div><div class="col-6"><a class="btn btn-light border w-100 rounded-2 fw-bold" href="<?= smartcms_h(smartcms_base_url('/member/register/')) ?>">회원가입</a></div></div>
          <?php endif; ?>
        </div>
      </article>

      <article class="card border shadow-sm mb-4">
        <header class="card-header bg-white border-bottom p-3 p-lg-4"><p class="small text-uppercase fw-bold text-primary mb-1">Trending</p><h2 class="h5 fw-bold text-dark mb-0">인기글</h2></header>
        <ol class="list-group list-group-flush list-unstyled mb-0">
          <?php if ($popular_posts): ?>
            <?php foreach (array_slice($popular_posts, 0, 3) as $idx => $post): ?>
              <li class="list-group-item p-3 p-lg-4 d-flex gap-3"><span class="h5 fw-bold <?= $idx === 0 ? 'text-primary' : 'text-secondary opacity-50' ?> mb-0"><?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?></span><a class="text-decoration-none fw-bold text-dark lh-sm" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></a></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item p-4 text-body-secondary small">인기글이 없습니다.</li>
          <?php endif; ?>
        </ol>
      </article>

      <section class="card border shadow-sm">
        <header class="card-header bg-white border-bottom p-3 p-lg-4"><p class="small text-uppercase fw-bold text-primary mb-1">Boards</p><h2 class="h5 fw-bold text-dark mb-0">전체 게시판</h2></header>
        <div class="card-body p-3 p-lg-4 d-flex flex-wrap gap-2">
          <?php foreach ($boards as $board_item): ?>
            <?php if ((string)$board_item['status'] === 'hidden') continue; ?>
            <a class="btn btn-light border btn-sm rounded-2 fw-bold" href="<?= smartcms_h(smartcms_board_url((string)$board_item['board_key'])) ?>"><?= smartcms_h($board_item['board_name']) ?></a>
          <?php endforeach; ?>
        </div>
      </section>
    </aside>
  </section>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
