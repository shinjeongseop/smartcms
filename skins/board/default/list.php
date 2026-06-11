<?php
/* 게시판 글 목록 스킨 - default/list.php
 * 사용 가능 변수: $board, $posts, $pagination, $user
 */
?>
<section class="board-list-container">
  <div class="card border shadow-sm overflow-hidden">
    <!-- [LIST HEADER] 목록 상단 영역 -->
    <header class="card-header bg-white border-bottom p-4 p-lg-5">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <p class="text-xs text-uppercase fw-bold text-primary mb-1">Board Community</p>
          <h2 class="h3 fw-bold mb-0">
            글 목록 <span class="badge bg-primary-subtle text-primary align-middle ms-2"><?= number_format((int)$pagination['total']) ?></span>
          </h2>
        </div>
        <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
          <a class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
             href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
            <i class="bi bi-pencil-square me-2"></i>글쓰기
          </a>
        <?php endif; ?>
      </div>

      <!-- [SEARCH] 검색 폼 -->
      <form class="row g-2 mb-0" method="get" role="search">
        <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
        <div class="col-12 col-lg">
          <div class="input-group">
            <span class="input-group-text bg-white border"><i class="bi bi-search text-muted"></i></span>
            <input type="search" class="form-control" name="q" 
                   value="<?= smartcms_h($pagination['keyword']) ?>" placeholder="제목, 내용, 작성자 검색">
          </div>
        </div>
        <div class="col-12 col-lg-auto">
          <button class="btn btn-dark rounded-pill px-4 w-100 shadow-none" type="submit">검색</button>
        </div>
        <?php if ($pagination['keyword'] !== ''): ?>
          <div class="col-12 col-lg-auto">
            <a class="btn btn-outline-secondary rounded-pill px-4 w-100 shadow-none"
               href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
          </div>
        <?php endif; ?>
      </form>
    </header>

    <!-- [TABLE] 게시글 테이블 -->
    <div class="table-responsive">
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
            <tr class="<?= (int)$post['is_notice'] === 1 ? 'table-primary opacity-90' : '' ?>">
              <td class="ps-4 ps-lg-5 text-secondary small">
                <?php if ((int)$post['is_notice'] === 1): ?>
                  <span class="badge bg-primary rounded-pill">공지</span>
                <?php else: ?>
                  <?= (int)$post['id'] ?>
                <?php endif; ?>
              </td>
              <td class="min-w-0">
                <div class="d-flex align-items-center gap-2 min-w-0">
                  <a class="text-decoration-none fw-semibold text-dark text-truncate fs-6 d-block flex-grow-1 min-w-0"
                     href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                    <?php if ((int)$post['is_secret'] === 1): ?><i class="bi bi-lock-fill small me-1"></i><?php endif; ?>
                    <?= smartcms_h($post['title']) ?>
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

    <!-- [PAGINATION] 페이지네이션 -->
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
  </div>
</section>
