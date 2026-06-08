<?php
/* 게시판 글 목록 스킨 - default/list.php
 * 사용 가능 변수: $board, $posts, $pagination, $user
 */
?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-4 p-lg-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <p class="text-uppercase small fw-semibold text-primary mb-2">Board List</p>
        <h2 class="h4 fw-bold mb-0">글 목록 <span class="badge text-bg-secondary align-middle ms-1"><?= number_format((int)$pagination['total']) ?></span></h2>
      </div>
      <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
        <a class="btn btn-primary px-4"
           href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
          <i class="bi bi-pencil-square me-1"></i>글쓰기
        </a>
      <?php endif; ?>
    </div>

    <form class="row g-2 align-items-center mb-4" method="get">
      <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
      <div class="col-12 col-lg">
        <input class="form-control" name="q" value="<?= smartcms_h($pagination['keyword']) ?>" placeholder="제목, 내용, 작성자 검색">
      </div>
      <div class="col-12 col-lg-auto">
        <button class="btn btn-primary w-100" type="submit">
          <i class="bi bi-search me-1"></i>검색
        </button>
      </div>
      <?php if ($pagination['keyword'] !== ''): ?>
        <div class="col-12 col-lg-auto">
          <a class="btn btn-secondary w-100"
             href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
        </div>
      <?php endif; ?>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>제목</th>
          <th class="d-none d-md-table-cell">작성자</th>
          <th class="d-none d-lg-table-cell">조회</th>
          <th class="d-none d-md-table-cell">작성일</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td>
              <?php if ((int)$post['is_notice'] === 1): ?>
                <span class="badge text-bg-primary me-1">공지</span>
              <?php endif; ?>
              <a class="text-decoration-none fw-semibold text-body"
                 href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                <?= (int)$post['is_secret'] === 1 ? '🔒 ' : '' ?><?= smartcms_h($post['title']) ?>
                <?php if ((int)$post['comment_count'] > 0): ?>
                  <span class="text-body-secondary ms-1">[<?= (int)$post['comment_count'] ?>]</span>
                <?php endif; ?>
                <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                  <i class="bi bi-paperclip ms-1 text-body-secondary"></i>
                <?php endif; ?>
              </a>
            </td>
            <td class="d-none d-md-table-cell text-body-secondary"><?= smartcms_h($post['author_name']) ?></td>
            <td class="d-none d-lg-table-cell text-body-secondary"><?= number_format((int)$post['view_count']) ?></td>
            <td class="d-none d-md-table-cell text-body-secondary"><?= smartcms_h($post['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$posts): ?>
          <tr>
            <td colspan="4" class="text-center text-body-secondary py-4">등록된 글이 없습니다.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>

    <?php if ((int)$pagination['pages'] > 1): ?>
      <nav class="mt-4" aria-label="게시글 페이지">
        <ul class="pagination justify-content-center flex-wrap mb-0">
          <?php for ($i = 1; $i <= (int)$pagination['pages']; $i++): ?>
            <li class="page-item <?= $i === (int)$pagination['page'] ? 'active' : '' ?>">
              <a class="page-link"
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
  </div>
</div>
