<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/auth.php';
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
    $board_counts = smartcms_board_post_counts();
    $recent_posts = smartcms_board_recent_posts(5);
    $popular_posts = smartcms_board_popular_posts(5);
    $notice_posts = smartcms_board_recent_posts_by_key('notice', 4);

    foreach ($boards as $board) {
        if ((string)($board['status'] ?? '') !== 'active') {
            continue;
        }

        $skin_meta = smartcms_board_skin_meta($board);
        $summary = trim((string)($board['description'] ?? ''));
        if ($summary === '') {
            $summary = (string)($skin_meta['skin'] ?? '') === 'gallery'
                ? '최신 이미지를 모아 보여주는 공간입니다.'
                : '최신 글을 확인해보세요.';
        }

        $limit = (string)($skin_meta['skin'] ?? '') === 'gallery' ? 2 : 5;
        $board_widgets[] = [
            'board' => $board,
            'skin_meta' => $skin_meta,
            'summary' => $summary,
            'posts' => smartcms_board_recent_posts_by_key((string)$board['board_key'], $limit),
        ];
    }
} catch (Throwable $e) {
    $message = '커뮤니티 데이터를 불러오지 못했습니다: ' . $e->getMessage();
}

$SMARTCMS_HEAD = [
    'title' => 'smartcms Community',
    'body_class' => 'bg-light',
    'active_menu' => 'home',
    'main_class' => 'flex-grow-1 pb-5',
];

require SMARTCMS_ROOT . '/head.php';
?>

<header class="bg-white border-bottom py-5 py-lg-5">
  <div class="container-xxl">
    <div class="row align-items-center g-5 m-0">
      <div class="col-lg-7 text-center text-lg-start">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-3 fw-semibold">v2.1 Semantic & Bootstrap</span>
        <h1 class="display-4 fw-bold mb-3 lh-sm text-emphasis">더 가볍고, 더 똑똑한<br>차세대 커뮤니티 CMS</h1>
        <p class="lead mb-4 text-body-secondary">
          HTML5 시맨틱 마크업과 부트스트랩 5의 표준 컴포넌트를 결합했습니다.<br class="d-none d-md-block">
          유지보수가 쉽고 구조가 명확한 현대적인 CMS를 경험해보세요.
        </p>
        <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
          <a class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" href="<?= smartcms_h(smartcms_base_url('/board/')) ?>">시작하기</a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block">
        <div class="card border shadow-sm bg-white overflow-hidden">
          <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-start gap-3 mb-4">
              <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 p-3 shadow-sm">
                <i class="bi bi-lightning-charge-fill fs-4"></i>
              </div>
              <div>
                <h2 class="h5 fw-bold mb-1">압도적 성능</h2>
                <p class="text-body-secondary mb-0">Pure Bootstrap & Optimized PHP Logic</p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 p-3 shadow-sm">
                <i class="bi bi-shield-check-fill fs-4"></i>
              </div>
              <div>
                <h2 class="h5 fw-bold mb-1">강력한 보안</h2>
                <p class="text-body-secondary mb-0">CSRF/XSS Protection Built-in by Default</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="container-xxl pt-5">
  <?php if ($message !== ''): ?>
    <aside class="alert alert-danger d-flex align-items-center gap-2 mb-5 shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      <div class="fw-medium"><?= smartcms_h($message) ?></div>
    </aside>
  <?php endif; ?>

  <!-- [SUB HEADER] 공지사항 및 통계 -->
  <section class="row g-4 mb-5" aria-label="요약 정보">
    <div class="col-12 col-lg-8">
      <article class="card border shadow-sm h-100 bg-white">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary-subtle text-primary p-2 rounded-3 shadow-sm">
              <i class="bi bi-megaphone-fill fs-5"></i>
            </span>
            <div class="flex-grow-1 overflow-hidden">
              <p class="small text-uppercase fw-bold text-primary mb-1">Notice</p>
              <?php if ($notice_posts): ?>
                <?php $notice = $notice_posts[0]; ?>
                <a class="text-decoration-none fw-bold text-dark d-block text-truncate"
                   href="<?= smartcms_h(smartcms_board_post_url((string)$notice['board_key'], (int)$notice['id'])) ?>">
                  <?= smartcms_h(smartcms_board_truncate_title((string)$notice['title'])) ?>
                </a>
              <?php else: ?>
                <span class="text-body-secondary small">등록된 공지사항이 없습니다.</span>
              <?php endif; ?>
            </div>
          </div>
          <a href="<?= smartcms_h(smartcms_base_url('/board/?board=notice')) ?>" class="btn btn-light btn-sm rounded-pill px-3 flex-shrink-0 fw-bold shadow-none border text-primary">전체보기</a>
        </div>
      </article>
    </div>

    <div class="col-12 col-lg-4">
      <section class="card border shadow-sm h-100 bg-white overflow-hidden">
        <div class="card-body p-4 d-flex align-items-center">
          <div class="row row-cols-3 g-2 text-center w-100">
            <div>
              <div class="text-xs text-body-secondary text-uppercase mb-1 fw-bold">Posts</div>
              <div class="h5 fw-bold mb-0"><?= number_format(array_sum($board_counts)) ?></div>
            </div>
            <div>
              <div class="text-xs text-body-secondary text-uppercase mb-1 fw-bold">Boards</div>
              <div class="h5 fw-bold mb-0"><?= number_format(count($boards)) ?></div>
            </div>
            <div>
              <div class="text-xs text-body-secondary text-uppercase mb-1 fw-bold">7 Days</div>
              <div class="h5 fw-bold mb-0"><?= number_format(count($recent_posts)) ?></div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </section>

  <!-- [CONTENT] 메인 콘텐츠 섹션 -->
  <section class="row g-4" aria-label="게시글 목록 및 사이드바">
    <div class="col-12 col-lg-8">
      <!-- 최신글 카드 -->
      <article class="card border shadow-sm mb-4 bg-white">
        <header class="card-header bg-white border-bottom p-4">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-clock-history text-primary lh-1"></i>
              <span>전체 최신글</span>
            </h2>
            <a href="<?= smartcms_h(smartcms_base_url('/board/')) ?>" class="text-decoration-none small text-body-secondary fw-medium">전체보기</a>
          </div>
        </header>
        <div class="card-body p-0">
          <div class="list-group list-group-flush">
            <?php if ($recent_posts): ?>
              <?php foreach ($recent_posts as $post): ?>
                <?php $recent_image = smartcms_board_first_image_file((int)$post['id']); ?>
                <?php $recent_youtube = $recent_image ? null : smartcms_board_youtube_link_data($post); ?>
                <a class="list-group-item list-group-item-action p-4"
                   href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                  <div class="row g-3 align-items-center">
                    <div class="col-4 col-sm-3 col-md-2">
                      <?php if ($recent_image): ?>
                        <?php $recent_thumb = smartcms_board_file_thumbnail_url($recent_image, 480, 360); ?>
                        <div class="ratio ratio-4x3 rounded-3 overflow-hidden bg-light border shadow-sm">
                          <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($recent_thumb ?? (smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$recent_image['id']))) ?>" alt="<?= smartcms_h($recent_image['original_name']) ?>">
                        </div>
                      <?php elseif (!empty($recent_youtube['thumb_url'])): ?>
                        <div class="ratio ratio-4x3 rounded-3 overflow-hidden bg-light border shadow-sm">
                          <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h((string)$recent_youtube['thumb_url']) ?>" alt="<?= smartcms_h((string)$post['title']) ?>">
                        </div>
                      <?php else: ?>
                        <div class="ratio ratio-4x3 rounded-3 overflow-hidden bg-light border d-flex align-items-center justify-content-center shadow-sm">
                          <i class="bi bi-file-earmark-text text-secondary fs-1 opacity-25"></i>
                        </div>
                      <?php endif; ?>
                      </div>
                      <div class="col-8 col-sm-9 col-md-10 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                          <span class="badge text-bg-light text-secondary rounded-pill small border"><?= smartcms_h($post['board_name']) ?></span>
                          <time class="small text-body-secondary fw-medium" datetime="<?= date('Y-m-d', strtotime((string)$post['created_at'])) ?>">
                            <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                          </time>
                        </div>
                        <div class="text-dark fw-semibold fs-6 text-truncate mb-1"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></div>
                      <div class="small text-body-secondary">
                        <span class="me-2"><i class="bi bi-person me-1"></i><?= smartcms_h(smartcms_board_author_display_name(null, $post)) ?></span>
                        <span class="me-2"><i class="bi bi-chat-dots me-1"></i><?= number_format((int)$post['comment_count']) ?></span>
                        <span><i class="bi bi-paperclip me-1"></i><?= number_format((int)$post['attachment_count']) ?></span>
                      </div>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="p-5 text-center text-body-secondary">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                아직 최신글이 없습니다.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </article>

      <!-- 게시판별 요약 위젯 -->
      <?php if ($board_widgets): ?>
        <div class="row g-4">
          <?php foreach ($board_widgets as $widget): ?>
            <?php $board = $widget['board']; ?>
            <div class="col-12 col-md-6">
              <article class="card border shadow-sm h-100 bg-white">
                <header class="card-header bg-white border-bottom p-4">
                  <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                      <h3 class="h6 fw-bold mb-1 text-primary text-uppercase"><?= smartcms_h($board['board_name']) ?></h3>
                      <p class="text-xs text-body-secondary mb-0"><?= smartcms_h($widget['summary']) ?></p>
                    </div>
                    <a href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>" class="btn btn-light btn-sm rounded-pill px-3 flex-shrink-0 fw-bold shadow-none border text-primary">전체보기</a>
                  </div>
                </header>
                <?php $is_gallery_widget = (string)($widget['skin_meta']['skin'] ?? '') === 'gallery'; ?>
                <?php if ($is_gallery_widget): ?>
                  <div class="card-body p-4">
                    <?php if ($widget['posts']): ?>
                      <div class="row row-cols-2 g-3">
                        <?php foreach ($widget['posts'] as $post): ?>
                          <?php $gallery_image = smartcms_board_first_image_file((int)$post['id']); ?>
                          <?php $gallery_thumb = $gallery_image ? smartcms_board_file_thumbnail_url($gallery_image, 640, 640) : null; ?>
                          <div class="col">
                            <a class="card border-0 shadow-sm h-100 text-decoration-none overflow-hidden rounded-4" href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                              <div class="ratio ratio-1x1 bg-light">
                                <?php if ($gallery_thumb): ?>
                                  <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($gallery_thumb ?? '') ?>" alt="<?= smartcms_h($gallery_image['original_name']) ?>">
                                <?php else: ?>
                                  <div class="d-flex align-items-center justify-content-center text-secondary">
                                    <span class="text-center">
                                      <i class="bi bi-image fs-1 d-block mb-1 opacity-50"></i>
                                      <span class="small fw-semibold">이미지 없음</span>
                                    </span>
                                  </div>
                                <?php endif; ?>
                              </div>
                              <span class="visually-hidden"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></span>
                            </a>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <div class="text-body-secondary small opacity-75">등록된 이미지가 없습니다.</div>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="card-body p-4">
                    <div class="list-group list-group-flush small">
                      <?php if ($widget['posts']): ?>
                        <?php foreach ($widget['posts'] as $post): ?>
                          <?php $widget_image = smartcms_board_first_image_file((int)$post['id']); ?>
                          <a class="list-group-item list-group-item-action bg-white px-0 py-2"
                            href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                            <?php if ($widget_image): ?>
                              <?php $widget_thumb = smartcms_board_file_thumbnail_url($widget_image, 480, 360); ?>
                              <div class="row g-2 align-items-center">
                                <div class="col-3">
                                  <div class="ratio ratio-4x3 rounded-2 overflow-hidden bg-light border">
                                    <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($widget_thumb ?? (smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$widget_image['id']))) ?>" alt="<?= smartcms_h($widget_image['original_name']) ?>">
                                  </div>
                                </div>
                                <div class="col-9 min-w-0">
                                  <span class="text-truncate fw-medium d-block"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></span>
                                  <time class="text-xs text-body-secondary" datetime="<?= date('Y-m-d', strtotime((string)$post['created_at'])) ?>">
                                    <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                                  </time>
                                </div>
                              </div>
                            <?php else: ?>
                              <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-truncate fw-medium"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></span>
                                <time class="text-xs text-body-secondary flex-shrink-0" datetime="<?= date('Y-m-d', strtotime((string)$post['created_at'])) ?>">
                                  <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                                </time>
                              </div>
                            <?php endif; ?>
                          </a>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="text-body-secondary small opacity-75">등록된 글이 없습니다.</div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- 사이드바 -->
    <aside class="col-12 col-lg-4">
      <?php if ($user): ?>
        <article class="card border shadow-sm mb-4 overflow-hidden bg-white">
          <div class="card-body p-4 text-center">
            <div class="d-flex justify-content-center mb-3">
              <?= smartcms_user_avatar_markup($user, 'sc-avatar-72', 'fs-2') ?>
            </div>
            <h2 class="h5 fw-bold mb-1"><?= smartcms_h(smartcms_user_display_name($user)) ?>님</h2>
            <p class="small text-body-secondary mb-3">level <?= smartcms_h($user['level']) ?> · <?= smartcms_h($user['email']) ?></p>
            <div class="row g-2">
              <div class="col-6">
                <a class="btn btn-light btn-sm w-100 rounded-pill border shadow-none" href="<?= smartcms_h(smartcms_base_url('/member/mypage/')) ?>">마이페이지</a>
              </div>
              <div class="col-6">
                <a class="btn btn-secondary btn-sm w-100 rounded-pill shadow-none" href="<?= smartcms_h(smartcms_base_url('/member/logout/')) ?>">로그아웃</a>
              </div>
            </div>
          </div>
        </article>
      <?php else: ?>
        <article class="card border shadow-sm mb-4 bg-primary text-white overflow-hidden">
          <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-2">커뮤니티 로그인</h2>
            <p class="small text-white-50 mb-4">가입 후 글쓰기와 댓글 참여가 가능합니다. 지금 시작하세요!</p>
            <div class="d-grid gap-2">
              <a class="btn btn-light text-primary fw-bold rounded-pill shadow-sm" href="<?= smartcms_h(smartcms_base_url('/member/login/')) ?>">로그인</a>
            </div>
          </div>
        </article>
      <?php endif; ?>

      <article class="card border shadow-sm mb-4 bg-white">
        <header class="card-header bg-white border-bottom p-4">
          <h3 class="h6 fw-bold mb-0 d-flex align-items-center gap-2 text-uppercase">
            <i class="bi bi-fire text-primary lh-1"></i>
            <span>실시간 인기글</span>
          </h3>
        </header>
        <div class="card-body p-4">
          <div class="list-group list-group-flush small">
            <?php if ($popular_posts): ?>
              <?php foreach ($popular_posts as $idx => $post): ?>
                <a class="list-group-item list-group-item-action bg-white px-0 py-3 d-flex align-items-start gap-3"
                   href="<?= smartcms_h(smartcms_board_post_url((string)$post['board_key'], (int)$post['id'])) ?>">
                  <span class="text-primary fw-bold fs-5 lh-1 mt-1"><?= (int)$idx + 1 ?></span>
                  <div class="flex-grow-1 overflow-hidden">
                    <strong class="d-block text-truncate mb-1 text-dark"><?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?></strong>
                    <div class="d-flex flex-wrap gap-2 text-xs text-body-secondary fw-medium">
                      <span>조회 <?= number_format((int)$post['view_count']) ?></span>
                      <span class="opacity-50">|</span>
                      <span><?= smartcms_h($post['board_name']) ?></span>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-body-secondary small text-center py-4 opacity-75">인기글이 없습니다.</div>
            <?php endif; ?>
          </div>
        </div>
      </article>

      <section class="card border shadow-sm">
        <div class="card-body p-4 text-center">
          <h3 class="text-xs fw-bold mb-3 text-uppercase text-body-secondary">전체 게시판</h3>
          <div class="d-flex flex-wrap justify-content-center gap-2">
            <?php foreach ($boards as $board_item): ?>
              <?php if ((string)$board_item['status'] === 'hidden') continue; ?>
              <a href="<?= smartcms_h(smartcms_board_url((string)$board_item['board_key'])) ?>" class="btn btn-light btn-sm rounded-pill border text-xs fw-bold py-1 px-3 shadow-none">
                <?= smartcms_h($board_item['board_name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    </aside>
  </section>
</div>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
