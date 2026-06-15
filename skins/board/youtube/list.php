<?php
/* 게시판 글 목록 스킨 - youtube/list.php */
$skin_meta = smartcms_board_skin_meta($board);
$thumb_config = smartcms_board_thumbnail_config($board, 'list');
$board_bulk_can_manage = smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);
$board_bulk_form_id = 'boardBulkForm_' . (int)$board['id'];
$board_bulk_select_all_id = $board_bulk_form_id . '_all';
$board_bulk_targets = [];
if ($board_bulk_can_manage && !empty($boards) && is_array($boards)) {
  foreach ($boards as $candidate) {
    if ((int)($candidate['id'] ?? 0) === (int)$board['id']) {
      continue;
    }
    if ((string)($candidate['status'] ?? '') === 'disabled') {
      continue;
    }
    if (!smartcms_has_level((int)($candidate['board_write_level'] ?? 8), $user)) {
      continue;
    }
    $board_bulk_targets[] = $candidate;
  }
}

if (!isset($SMARTCMS_FOOT['scripts']) || !is_array($SMARTCMS_FOOT['scripts'])) {
  $SMARTCMS_FOOT['scripts'] = [];
}
if (!in_array('/common/js/board-bulk-actions.js', $SMARTCMS_FOOT['scripts'], true)) {
  $SMARTCMS_FOOT['scripts'][] = '/common/js/board-bulk-actions.js';
}
?>
<section class="board-list-container">
  <div class="card border shadow-sm bg-white overflow-hidden">
    <header class="card-header bg-white border-bottom p-4 p-lg-5">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <h2 class="fs-5 fw-bold mb-0 text-dark"><i class="bi bi-youtube text-danger me-2"></i>유튜브 게시판</h2>
        <form class="row g-2 flex-grow-1 justify-content-lg-end mb-0" method="get" role="search" data-search-min-length="2">
          <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
          <div class="col-12 col-lg">
            <div class="input-group">
              <span class="input-group-text bg-white border"><i class="bi bi-search text-muted"></i></span>
              <input type="search" class="form-control" name="q" value="<?= smartcms_h($pagination['keyword']) ?>" placeholder="제목, 설명, 작성자 검색">
            </div>
          </div>
          <div class="col-12 col-lg-auto">
            <button class="btn btn-danger rounded-2 px-4 w-100 shadow-none fw-bold" type="submit">검색</button>
          </div>
          <?php if ($pagination['keyword'] !== ''): ?>
            <div class="col-12 col-lg-auto">
              <a class="btn btn-light border rounded-2 px-4 w-100 shadow-none fw-bold text-secondary"
                 href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </header>

    <?php require SMARTCMS_ROOT . '/skins/board/_bulk_toolbar.php'; ?>

    <div class="p-4 p-lg-5">
      <div class="vstack gap-4">
        <?php foreach ($posts as $post): ?>
          <?php $video = smartcms_board_youtube_link_data($post); ?>
          <?php $thumb_url = $video['thumb_url'] ?? null; ?>
          <?php $excerpt = smartcms_board_excerpt((string)($post['content'] ?? $post['excerpt'] ?? ''), 90); ?>
          <article class="card border shadow-sm bg-white overflow-hidden position-relative">
            <?php if ($board_bulk_can_manage): ?>
              <div class="position-absolute top-0 start-0 p-3 z-3">
                <input class="form-check-input shadow-sm m-0" type="checkbox" name="post_ids[]" value="<?= (int)$post['id'] ?>" form="<?= smartcms_h($board_bulk_form_id) ?>" data-board-bulk-item aria-label="게시글 <?= (int)$post['id'] ?> 선택">
              </div>
            <?php endif; ?>
            <div class="row g-0 align-items-stretch">
              <div class="col-12 col-md-5 col-xl-5">
                <div class="h-100 p-2 p-lg-3 d-flex align-items-center">
                  <a class="d-block ratio ratio-16x9 w-100 bg-light border rounded-4 text-decoration-none overflow-hidden shadow-sm"
                     href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <?php if ($thumb_url): ?>
                      <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($thumb_url) ?>" alt="<?= smartcms_h((string)$post['title']) ?>">
                    <?php else: ?>
                      <div class="d-flex align-items-center justify-content-center text-secondary h-100 w-100">
                        <span class="text-center">
                          <i class="bi bi-youtube fs-1 text-danger d-block mb-2"></i>
                          <span class="small fw-semibold">YouTube</span>
                        </span>
                      </div>
                    <?php endif; ?>
                  </a>
                </div>
              </div>
              <div class="col-12 col-md-7 col-xl-7">
                <div class="card-body p-3 p-lg-4 h-100 d-flex flex-column gap-2">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <?php if ((int)$post['is_notice'] === 1): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 px-2 py-1 fw-bold">공지</span>
                      <?php else: ?>
                        <span class="badge bg-light text-secondary border rounded-2 px-2 py-1 fw-bold">#<?= (int)$post['id'] ?></span>
                      <?php endif; ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 px-2 py-1 fw-bold">YouTube</span>
                    </div>
                    <time class="small text-secondary fw-medium" datetime="<?= date('Y-m-d H:i:s', strtotime((string)$post['created_at'])) ?>">
                      <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                    </time>
                  </div>
                  <a class="text-decoration-none fw-bold text-dark fs-5 lh-sm d-block text-break"
                     href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?>
                    <?= smartcms_h(smartcms_board_truncate_title((string)$post['title'])) ?>
                  </a>
                  <?php if ($excerpt !== ''): ?>
                    <p class="mb-0 text-secondary small lh-base"><?= smartcms_h($excerpt) ?></p>
                  <?php endif; ?>
                  <div class="d-flex flex-wrap gap-2 small text-secondary fw-medium">
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-person"></i><?= smartcms_h(smartcms_board_author_display_name($board, $post)) ?></span>
                    <span class="opacity-25">|</span>
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-eye"></i><?= number_format((int)$post['view_count']) ?></span>
                    <?php if ((int)$post['comment_count'] > 0): ?>
                      <span class="opacity-25">|</span>
                      <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-chat-dots"></i><?= (int)$post['comment_count'] ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="mt-auto">
                    <a class="btn btn-primary btn-sm rounded-2 px-3 py-2 fw-bold shadow-sm"
                       href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                      자세히
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if (!$posts): ?>
          <div class="text-center text-secondary py-5">
            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
            등록된 게시글이 없습니다.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ((int)$pagination['pages'] > 1): ?>
      <footer class="card-footer bg-white p-4 p-lg-5 border-top">
        <nav aria-label="게시글 페이지 목록">
          <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
            <?php foreach (smartcms_pagination_window((int)$pagination['page'], (int)$pagination['pages']) as $i): ?>
              <li class="page-item <?= $i === (int)$pagination['page'] ? 'active' : '' ?>">
                <a class="page-link border-0 rounded-circle px-3 py-2 fw-bold shadow-none"
                   href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])
                       . '&page=' . $i
                       . '&q=' . rawurlencode((string)$pagination['keyword'])) ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>
      </footer>
    <?php endif; ?>

    <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
      <footer class="card-footer bg-white border-top p-4 p-lg-5 text-end">
        <a class="btn btn-primary rounded-2 px-4 fw-bold shadow-sm"
           href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
          새글
        </a>
      </footer>
    <?php endif; ?>
  </div>
</section>
