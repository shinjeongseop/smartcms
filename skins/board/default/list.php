<?php
/* 게시판 글 목록 스킨 - default/list.php
 * 사용 가능 변수: $board, $posts, $pagination, $user
 */
?>
<article class="smartcms-board-list">
<div class="card border-0 shadow-sm overflow-hidden">
  <section class="card-body p-0">
    <!-- List Header -->
    <header class="p-4 p-lg-5 pb-0">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <p class="text-xs text-uppercase fw-bold text-primary mb-1">Board Community</p>
          <h2 class="h3 fw-bold mb-0">글 목록 <span class="badge bg-primary-subtle text-primary align-middle ms-2"><?= number_format((int)$pagination['total']) ?></span></h2>
        </div>
        <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
          <a class="btn btn-primary rounded-pill px-4 fw-bold"
             href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
            <i class="bi bi-pencil-square me-2"></i>글쓰기
          </a>
        <?php endif; ?>
      </div>

      <!-- Search Form -->
      <form class="row g-2 mb-4" method="get">
        <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
        <div class="col-12 col-lg">
          <div class="input-group">
            <span class="input-group-text bg-body border-0"><i class="bi bi-search text-muted"></i></span>
            <input class="form-control bg-body border-0" name="q" value="<?= smartcms_h($pagination['keyword']) ?>" placeholder="제목, 내용, 작성자 검색">
          </div>
        </div>
        <div class="col-12 col-lg-auto">
          <button class="btn btn-dark rounded-pill px-4 w-100" type="submit">검색</button>
        </div>
        <?php if ($pagination['keyword'] !== ''): ?>
          <div class="col-12 col-lg-auto">
            <a class="btn btn-light border rounded-pill px-4 w-100"
               href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
          </div>
        <?php endif; ?>
      </form>
    </header>

    <!-- List Table -->
    <section class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-body-secondary bg-opacity-10">
          <tr class="text-uppercase text-xs fw-bold text-muted border-top">
            <th class="ps-4 ps-lg-5 py-3" style="width: 60px;">ID</th>
            <th class="py-3">제목</th>
            <th class="d-none d-md-table-cell py-3" style="width: 150px;">작성자</th>
            <th class="d-none d-lg-table-cell py-3" style="width: 100px;">조회</th>
            <th class="d-none d-md-table-cell pe-4 pe-lg-5 py-3" style="width: 120px;">날짜</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
            <tr class="<?= (int)$post['is_notice'] === 1 ? 'bg-primary-subtle bg-opacity-10' : '' ?>">
              <td class="ps-4 ps-lg-5 text-muted small">
                <?php if ((int)$post['is_notice'] === 1): ?>
                  <span class="badge bg-primary rounded-pill">공지</span>
                <?php else: ?>
                  <?= (int)$post['id'] ?>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <a class="text-decoration-none fw-semibold text-dark text-truncate"
                     href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?><?= smartcms_h($post['title']) ?>
                  </a>
                  <?php if ((int)$post['comment_count'] > 0): ?>
                    <span class="badge bg-light text-primary border rounded-pill text-xs"><?= (int)$post['comment_count'] ?></span>
                  <?php endif; ?>
                  <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                    <i class="bi bi-paperclip text-muted small"></i>
                  <?php endif; ?>
                </div>
              </td>
              <td class="d-none d-md-table-cell">
                <span class="text-sm text-body-secondary"><?= smartcms_h($post['author_name']) ?></span>
              </td>
              <td class="d-none d-lg-table-cell text-muted text-sm">
                <?= number_format((int)$post['view_count']) ?>
              </td>
              <td class="d-none d-md-table-cell pe-4 pe-lg-5 text-muted text-sm">
                <?= smartcms_h(smartcms_home_date((string)$post['created_at'])) ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$posts): ?>
            <tr>
              <td colspan="5" class="text-center text-body-secondary py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                등록된 게시글이 없습니다. 첫 글의 주인공이 되어보세요!
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

    <!-- Pagination -->
    <?php if ((int)$pagination['pages'] > 1): ?>
      <nav class="p-4 p-lg-5 border-top" aria-label="게시글 페이지">
        <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
          <?php for ($i = 1; $i <= (int)$pagination['pages']; $i++): ?>
            <li class="page-item <?= $i === (int)$pagination['page'] ? 'active' : '' ?>">
              <a class="page-link border-0 rounded-circle px-3 py-2 fw-bold"
                 href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])
                     . '&page=' . $i
                     . '&q=' . rawurlencode((string)$pagination['keyword'])) ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </section>
</div>
</article>
