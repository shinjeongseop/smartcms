<?php
/* 게시판 글 목록 스킨 - default/list.php
 * 사용 가능 변수: $board, $posts, $pagination, $user
 */
$skin_meta = smartcms_board_skin_meta($board);
$accent = (string)$skin_meta['accent'];
$accent_text = $accent === 'dark' ? 'text-dark' : 'text-' . $accent;
$accent_bg_subtle = 'bg-' . $accent . '-subtle';
$layout = (string)$skin_meta['layout'];
$gallery_mode = (string)($skin_meta['skin'] ?? '') === 'gallery';
$webzine_mode = $layout === 'webzine';
$thumb_config = smartcms_board_thumbnail_config($board, 'list');
$board_bulk_can_manage = smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);
$board_bulk_form_id = 'boardBulkForm_' . (int)$board['id'];
$board_bulk_select_all_id = $board_bulk_form_id . '_all';
$board_bulk_select_all_location = $layout === 'table' ? 'header' : 'toolbar';
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
$board_list_keyword = (string)($pagination['keyword'] ?? '');

if (!isset($SMARTCMS_FOOT['scripts']) || !is_array($SMARTCMS_FOOT['scripts'])) {
  $SMARTCMS_FOOT['scripts'] = [];
}
$board_bulk_actions_js = '/common/js/board-bulk-actions.js?v=' . filemtime(SMARTCMS_ROOT . '/common/js/board-bulk-actions.js');
if (!in_array($board_bulk_actions_js, $SMARTCMS_FOOT['scripts'], true)) {
  $SMARTCMS_FOOT['scripts'][] = $board_bulk_actions_js;
}
?>
<section class="board-list-container">
  <div class="card border shadow-sm bg-white overflow-hidden">
    <header class="card-header bg-white border-bottom p-4 p-lg-5">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <h2 class="fs-5 fw-bold mb-0 text-dark">글 목록</h2>
        <form class="row g-2 flex-grow-1 justify-content-lg-end mb-0" method="get" role="search" data-search-min-length="2">
          <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
          <div class="col-12 col-lg">
            <div class="input-group">
              <span class="input-group-text bg-white border"><i class="bi bi-search text-muted"></i></span>
              <input type="search" class="form-control" name="q"
                  value="<?= smartcms_h($pagination['keyword']) ?>" placeholder="제목, 내용, 작성자 검색">
            </div>
          </div>
          <div class="col-12 col-lg-auto">
            <button class="btn <?= $layout === 'cards' ? 'btn-dark' : 'btn-secondary' ?> rounded-2 px-4 w-100 shadow-none fw-bold" type="submit">검색</button>
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

    <?php if ($webzine_mode): ?>
      <div class="p-4 p-lg-5">
        <div class="vstack gap-5">
          <?php foreach ($posts as $post): ?>
            <?php $first_image = smartcms_board_first_image_file((int)$post['id']); ?>
            <?php $excerpt_source = (string)($post['content'] ?? $post['excerpt'] ?? ''); ?>
            <?php $excerpt = smartcms_board_excerpt($excerpt_source, 140); ?>
            <?php $excerpt_highlight = smartcms_board_highlight_text($excerpt, $board_list_keyword); ?>
            <article class="card h-100 border shadow-sm bg-white overflow-hidden position-relative">
              <?php if ($board_bulk_can_manage): ?>
                <div class="position-absolute top-0 start-0 p-3 z-3">
                  <input class="form-check-input sc-bulk-checkbox" type="checkbox" name="post_ids[]" value="<?= (int)$post['id'] ?>" form="<?= smartcms_h($board_bulk_form_id) ?>" data-board-bulk-item aria-label="게시글 <?= (int)$post['id'] ?> 선택">
                </div>
              <?php endif; ?>
              <div class="row g-0 h-100">
                <div class="col-12 col-md-5 col-lg-4 d-flex align-items-center ps-md-3 ps-lg-4">
                  <div class="w-100">
                    <?php if ($first_image): ?>
                      <?php $thumb_url = smartcms_board_file_thumbnail_url($first_image, (int)$thumb_config['width'], (int)$thumb_config['height']); ?>
                      <a class="d-block ratio ratio-16x9 bg-light overflow-hidden" href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                        <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($thumb_url ?? (smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$first_image['id']))) ?>" alt="<?= smartcms_h($first_image['original_name']) ?>">
                      </a>
                    <?php else: ?>
                      <a class="d-flex align-items-center justify-content-center ratio ratio-16x9 bg-light text-decoration-none text-secondary" href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                        <span class="text-center px-3">
                          <i class="bi bi-image fs-1 opacity-25 d-block mb-2"></i>
                          <span class="small fw-semibold">이미지 없음</span>
                        </span>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-12 col-md-7 col-lg-8">
                  <div class="card-body p-4 p-lg-5 h-100 d-flex flex-column gap-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                      <div class="d-flex flex-wrap align-items-center gap-2">
                        <?php if ((int)$post['is_notice'] === 1): ?>
                          <span class="badge <?= $skin_meta['badge_class'] ?> rounded-2 px-2 py-1 fw-bold">공지</span>
                        <?php else: ?>
                          <span class="badge bg-light text-secondary border rounded-2 px-2 py-1 fw-bold">#<?= (int)$post['id'] ?></span>
                        <?php endif; ?>
                        <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                          <span class="badge bg-light text-secondary border rounded-2 px-2 py-1 fw-bold">첨부</span>
                        <?php endif; ?>
                        <?php if ((int)$post['comment_count'] > 0): ?>
                          <span class="badge bg-light text-primary border rounded-2 px-2 py-1 fw-bold"><?= (int)$post['comment_count'] ?></span>
                        <?php endif; ?>
                      </div>
                      <time class="small text-secondary fw-medium" datetime="<?= date('Y-m-d H:i:s', strtotime((string)$post['created_at'])) ?>">
                        <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                      </time>
                    </div>
                    <a class="text-decoration-none fw-bold text-dark fs-5 lh-sm d-block mb-1"
                       href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                      <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?>
                      <?= smartcms_board_highlight_text(smartcms_board_truncate_title((string)$post['title']), $board_list_keyword) ?>
                    </a>
                    <?php if ($excerpt !== ''): ?>
                      <p class="mb-0 text-secondary fs-6 lh-base"><?= $excerpt_highlight ?></p>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-2 small text-secondary fw-medium mb-1">
                      <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-person"></i><?= smartcms_h(smartcms_board_author_display_name($board, $post)) ?></span>
                      <span class="opacity-25">|</span>
                      <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-eye"></i><?= number_format((int)$post['view_count']) ?></span>
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
    <?php elseif ($layout === 'cards'): ?>
      <div class="<?= $gallery_mode ? 'p-3 p-lg-4' : 'p-4 p-lg-5' ?>">
        <div class="row <?= $gallery_mode ? 'g-3 g-lg-4' : 'g-4' ?>">
          <?php foreach ($posts as $post): ?>
            <div class="<?= $gallery_mode ? 'col-6 col-md-4 col-xl-3' : 'col-12 col-md-6 col-xl-4' ?>">
              <?php $first_image = smartcms_board_first_image_file((int)$post['id']); ?>
              <article class="card h-100 border shadow-sm bg-white <?= $gallery_mode ? 'rounded-3 overflow-hidden' : 'overflow-hidden' ?> position-relative">
                <?php if ($board_bulk_can_manage): ?>
                  <div class="position-absolute top-0 start-0 p-3 z-3">
                    <input class="form-check-input sc-bulk-checkbox" type="checkbox" name="post_ids[]" value="<?= (int)$post['id'] ?>" form="<?= smartcms_h($board_bulk_form_id) ?>" data-board-bulk-item aria-label="게시글 <?= (int)$post['id'] ?> 선택">
                  </div>
                <?php endif; ?>
                <?php if ($first_image): ?>
                  <?php $thumb_url = smartcms_board_file_thumbnail_url($first_image, (int)$thumb_config['width'], (int)$thumb_config['height']); ?>
                  <a class="d-block bg-light overflow-hidden <?= $gallery_mode ? 'ratio ratio-1x1' : 'ratio ratio-4x3' ?>" href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($thumb_url ?? (smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$first_image['id']))) ?>" alt="<?= smartcms_h($first_image['original_name']) ?>">
                  </a>
                <?php elseif ($gallery_mode): ?>
                  <a class="d-flex align-items-center justify-content-center bg-light text-secondary ratio ratio-1x1 text-decoration-none"
                     href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <span class="d-flex flex-column align-items-center gap-2">
                      <i class="bi bi-image fs-1 opacity-50"></i>
                      <span class="small fw-semibold">이미지 없음</span>
                    </span>
                  </a>
                <?php endif; ?>
                <div class="card-body <?= $gallery_mode ? 'p-2 p-lg-3' : 'p-4' ?> d-flex flex-column gap-2">
                  <div class="d-flex align-items-center justify-content-between gap-2">
                    <?php if ((int)$post['is_notice'] === 1): ?>
                      <span class="badge <?= $skin_meta['badge_class'] ?> rounded-2 px-2 py-1 fw-bold">공지</span>
                    <?php else: ?>
                      <span class="badge bg-light text-secondary border rounded-2 px-2 py-1 fw-bold">#<?= (int)$post['id'] ?></span>
                    <?php endif; ?>
                    <div class="d-flex align-items-center gap-2 text-secondary small">
                      <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?><i class="bi bi-paperclip <?= $accent_text ?>"></i><?php endif; ?>
                      <?php if ($gallery_mode && (int)$post['comment_count'] > 0): ?><span class="badge bg-light text-primary border rounded-2"><?= (int)$post['comment_count'] ?></span><?php endif; ?>
                    </div>
                  </div>
                    <a class="text-decoration-none fw-bold text-dark fs-5 lh-sm stretched-link d-block text-truncate"
                       href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?>
                    <?= smartcms_board_highlight_text(smartcms_board_truncate_title((string)$post['title']), $board_list_keyword) ?>
                  </a>
                  <div class="d-flex flex-wrap gap-2 small text-secondary fw-medium">
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-person"></i><?= smartcms_h(smartcms_board_author_display_name($board, $post)) ?></span>
                    <span class="opacity-25">|</span>
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-eye"></i><?= number_format((int)$post['view_count']) ?></span>
                    <span class="opacity-25">|</span>
                    <time datetime="<?= date('Y-m-d H:i:s', strtotime($post['created_at'])) ?>">
                      <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                    </time>
                  </div>
                  <?php if (!$gallery_mode && (int)$post['comment_count'] > 0): ?>
                    <div>
                      <span class="badge bg-light text-primary border rounded-2 small"><?= (int)$post['comment_count'] ?>개의 댓글</span>
                    </div>
                  <?php endif; ?>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
          <?php if (!$posts): ?>
            <div class="col-12">
              <div class="text-center text-secondary py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                등록된 게시글이 없습니다.
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="table-responsive pb-4 pb-lg-5">
        <table class="table table-hover align-middle mb-0 text-nowrap sc-board-table">
          <thead class="table-light">
            <tr class="text-uppercase fw-bold text-secondary">
              <?php if ($board_bulk_can_manage): ?>
                <th scope="col" class="ps-2 ps-lg-3 py-3 text-nowrap sc-col-check text-center align-middle">
                  <div class="d-flex align-items-center justify-content-center h-100">
                    <input class="form-check-input sc-bulk-checkbox" type="checkbox" id="<?= smartcms_h($board_bulk_select_all_id) ?>" data-board-bulk-select-all form="<?= smartcms_h($board_bulk_form_id) ?>" aria-label="전체 선택">
                  </div>
                </th>
              <?php endif; ?>
              <th scope="col" class="ps-2 ps-lg-3 py-3 text-nowrap sc-col-no">번호</th>
              <th scope="col" class="ps-0 py-3 text-nowrap">제목</th>
              <th scope="col" class="d-none d-md-table-cell py-3 text-nowrap sc-col-author">작성자</th>
              <th scope="col" class="d-none d-lg-table-cell py-3 text-center text-nowrap sc-col-views">조회</th>
              <th scope="col" class="d-none d-md-table-cell pe-4 pe-lg-5 py-3 text-end text-nowrap sc-col-date">날짜</th>
            </tr>
          </thead>
          <tbody class="table-group-divider">
            <?php foreach ($posts as $post): ?>
              <tr class="<?= (int)$post['is_notice'] === 1 ? 'table-' . $accent . ' opacity-90' : '' ?>">
                <?php if ($board_bulk_can_manage): ?>
                  <td class="ps-2 ps-lg-3 align-middle text-center sc-col-check">
                    <div class="d-flex align-items-center justify-content-center h-100">
                      <input class="form-check-input sc-bulk-checkbox" type="checkbox" name="post_ids[]" value="<?= (int)$post['id'] ?>" form="<?= smartcms_h($board_bulk_form_id) ?>" data-board-bulk-item aria-label="게시글 <?= (int)$post['id'] ?> 선택">
                    </div>
                  </td>
                <?php endif; ?>
                <td class="ps-2 ps-lg-3 text-secondary small sc-col-no">
                  <?php if ((int)$post['is_notice'] === 1): ?>
                    <span class="badge <?= $skin_meta['badge_class'] ?> rounded-2">공지</span>
                  <?php else: ?>
                    <?= (int)$post['id'] ?>
                  <?php endif; ?>
                </td>
                <td class="min-w-0">
                  <div class="d-flex align-items-center gap-2 min-w-0">
                    <a class="text-decoration-none fw-semibold text-dark text-truncate fs-6 d-block flex-grow-1 min-w-0"
                       href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                      <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?>
                      <?= smartcms_board_highlight_text(smartcms_board_truncate_title((string)$post['title']), $board_list_keyword) ?>
                    </a>
                    <?php if ((int)$post['comment_count'] > 0): ?>
                      <span class="badge bg-light text-primary border rounded-2 small"><?= (int)$post['comment_count'] ?></span>
                    <?php endif; ?>
                    <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                      <i class="bi bi-paperclip text-muted small"></i>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="d-none d-md-table-cell sc-col-author">
                  <span class="small text-secondary"><?= smartcms_h(smartcms_board_author_display_name($board, $post)) ?></span>
                </td>
                <td class="d-none d-lg-table-cell text-center text-muted small sc-col-views">
                  <?= number_format((int)$post['view_count']) ?>
                </td>
                <td class="d-none d-md-table-cell pe-4 pe-lg-5 text-end text-muted small sc-col-date">
                  <time datetime="<?= date('Y-m-d H:i:s', strtotime($post['created_at'])) ?>">
                    <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                  </time>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$posts): ?>
              <tr>
                <td colspan="<?= $board_bulk_can_manage ? 6 : 5 ?>" class="text-center text-secondary py-5">
                  <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                  등록된 게시글이 없습니다.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ((int)$pagination['pages'] > 1 || smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
      <footer class="card-footer bg-white p-4 p-lg-5 border-top">
        <?php if ((int)$pagination['pages'] > 1): ?>
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
        <?php endif; ?>
      </footer>
    <?php endif; ?>

    <?php if ($board_bulk_can_manage || smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
      <footer class="card-footer bg-white border-top p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
          <?php if ($board_bulk_can_manage): ?>
            <div class="w-100">
              <?php $board_bulk_toolbar_inline = true; require SMARTCMS_ROOT . '/skins/board/_bulk_toolbar.php'; ?>
            </div>
          <?php endif; ?>
          <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
            <div class="w-100 d-flex justify-content-lg-end">
              <a class="btn <?= smartcms_h((string)$skin_meta['button_class']) ?> rounded-2 px-4 fw-bold shadow-sm <?= smartcms_h((string)$skin_meta['button_text_class']) ?>"
                 href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
                새글
              </a>
            </div>
          <?php endif; ?>
        </div>
      </footer>
    <?php endif; ?>
  </div>
</section>
