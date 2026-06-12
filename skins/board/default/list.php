<?php
/* 게시판 글 목록 스킨 - default/list.php
 * 사용 가능 변수: $board, $posts, $pagination, $user
 */
$skin_meta = smartcms_board_skin_meta($board);
$accent = (string)$skin_meta['accent'];
$accent_text = $accent === 'dark' ? 'text-dark' : 'text-' . $accent;
$accent_bg_subtle = 'bg-' . $accent . '-subtle';
$layout = (string)$skin_meta['layout'];
?>
<section class="board-list-container">
  <div class="card border shadow-sm overflow-hidden">
    <header class="card-header bg-white border-bottom p-4 p-lg-5">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <h2 class="h3 fw-bold mb-0 text-dark">글 목록</h2>
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
            <button class="btn <?= $layout === 'cards' ? 'btn-dark' : 'btn-secondary' ?> rounded-pill px-4 w-100 shadow-none fw-bold" type="submit">검색</button>
          </div>
          <?php if ($pagination['keyword'] !== ''): ?>
            <div class="col-12 col-lg-auto">
              <a class="btn btn-light border rounded-pill px-4 w-100 shadow-none fw-bold text-secondary"
                 href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </header>

    <?php if ($layout === 'cards'): ?>
      <div class="p-4 p-lg-5">
        <div class="row g-4">
          <?php foreach ($posts as $post): ?>
            <div class="col-12 col-md-6">
              <?php $first_image = smartcms_board_first_image_file((int)$post['id']); ?>
              <article class="card h-100 border shadow-sm">
                <?php if ($first_image): ?>
                  <?php $thumb_url = smartcms_board_file_thumbnail_url($first_image, 640, 360); ?>
                  <a class="d-block bg-light overflow-hidden ratio ratio-16x9" href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($thumb_url ?? (smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$first_image['id']))) ?>" alt="<?= smartcms_h($first_image['original_name']) ?>">
                  </a>
                <?php endif; ?>
                <div class="card-body p-4 d-flex flex-column gap-3">
                  <div class="d-flex align-items-start justify-content-between gap-2">
                    <?php if ((int)$post['is_notice'] === 1): ?>
                      <span class="badge <?= $skin_meta['badge_class'] ?> rounded-pill px-3 py-2 fw-bold">공지</span>
                    <?php else: ?>
                      <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 fw-bold">#<?= (int)$post['id'] ?></span>
                    <?php endif; ?>
                    <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                      <i class="bi bi-paperclip <?= $accent_text ?>"></i>
                    <?php endif; ?>
                  </div>
                  <a class="text-decoration-none fw-bold text-dark fs-5 lh-sm stretched-link"
                     href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?>
                    <?= smartcms_h(smartcms_board_truncate_title((string)$post['title'], smartcms_board_title_limit($board))) ?>
                  </a>
                  <div class="d-flex flex-wrap gap-2 small text-secondary fw-medium">
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-person"></i><?= smartcms_h($post['author_name']) ?></span>
                    <span class="opacity-25">|</span>
                    <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-eye"></i><?= number_format((int)$post['view_count']) ?></span>
                    <span class="opacity-25">|</span>
                    <time datetime="<?= date('Y-m-d H:i:s', strtotime($post['created_at'])) ?>">
                      <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                    </time>
                  </div>
                  <?php if ((int)$post['comment_count'] > 0): ?>
                    <div>
                      <span class="badge bg-light text-primary border rounded-pill small"><?= (int)$post['comment_count'] ?>개의 댓글</span>
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
        <table class="table table-hover align-middle mb-0 text-nowrap">
          <thead class="table-light">
            <tr class="text-uppercase small fw-bold text-secondary">
              <th scope="col" class="ps-4 ps-lg-5 py-3 text-nowrap" style="width: 80px;">번호</th>
              <th scope="col" class="py-3 text-nowrap">제목</th>
              <th scope="col" class="d-none d-md-table-cell py-3 text-nowrap" style="width: 150px;">작성자</th>
              <th scope="col" class="d-none d-lg-table-cell py-3 text-center text-nowrap" style="width: 100px;">조회</th>
              <th scope="col" class="d-none d-md-table-cell pe-4 pe-lg-5 py-3 text-end text-nowrap" style="width: 120px;">날짜</th>
            </tr>
          </thead>
          <tbody class="table-group-divider">
            <?php foreach ($posts as $post): ?>
              <tr class="<?= (int)$post['is_notice'] === 1 ? 'table-' . $accent . ' opacity-90' : '' ?>">
                <td class="ps-4 ps-lg-5 text-secondary small">
                  <?php if ((int)$post['is_notice'] === 1): ?>
                    <span class="badge <?= $skin_meta['badge_class'] ?> rounded-pill">공지</span>
                  <?php else: ?>
                    <?= (int)$post['id'] ?>
                  <?php endif; ?>
                </td>
                <td class="min-w-0">
                  <div class="d-flex align-items-center gap-2 min-w-0">
                    <a class="text-decoration-none fw-semibold text-dark text-truncate fs-6 d-block flex-grow-1 min-w-0"
                       href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                      <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?>
                      <?= smartcms_h(smartcms_board_truncate_title((string)$post['title'], smartcms_board_title_limit($board))) ?>
                    </a>
                    <?php if ((int)$post['comment_count'] > 0): ?>
                      <span class="badge bg-light text-primary border rounded-pill small"><?= (int)$post['comment_count'] ?></span>
                    <?php endif; ?>
                    <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                      <i class="bi bi-paperclip text-muted small"></i>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="d-none d-md-table-cell">
                  <span class="small text-secondary"><?= smartcms_h($post['author_name']) ?></span>
                </td>
                <td class="d-none d-lg-table-cell text-center text-muted small">
                  <?= number_format((int)$post['view_count']) ?>
                </td>
                <td class="d-none d-md-table-cell pe-4 pe-lg-5 text-end text-muted small">
                  <time datetime="<?= date('Y-m-d H:i:s', strtotime($post['created_at'])) ?>">
                    <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
                  </time>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$posts): ?>
              <tr>
                <td colspan="5" class="text-center text-secondary py-5">
                  <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                  등록된 게시글이 없습니다.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ((int)$pagination['pages'] > 1): ?>
      <footer class="card-footer bg-white p-4 p-lg-5 border-top">
        <nav aria-label="게시글 페이지 목록">
          <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
            <?php for ($i = 1; $i <= (int)$pagination['pages']; $i++): ?>
              <li class="page-item <?= $i === (int)$pagination['page'] ? 'active' : '' ?>">
                <a class="page-link border-0 rounded-circle px-3 py-2 fw-bold shadow-none"
                   href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])
                       . '&page=' . $i
                       . '&q=' . rawurlencode((string)$pagination['keyword'])) ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </footer>
    <?php endif; ?>

    <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
      <footer class="card-footer bg-white border-top p-4 p-lg-5 text-end">
        <a class="btn <?= smartcms_h((string)$skin_meta['button_class']) ?> rounded-pill px-4 fw-bold shadow-sm <?= smartcms_h((string)$skin_meta['button_text_class']) ?>"
           href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
          <i class="bi bi-pencil-square me-2"></i>글쓰기
        </a>
      </footer>
    <?php endif; ?>
  </div>
</section>
