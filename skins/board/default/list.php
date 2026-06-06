<?php
/* 게시판 글 목록 스킨 - default/list.php
 * 사용 가능 변수: $board, $posts, $pagination, $user
 */
?>
<div class="card sc-panel">
  <!-- 헤더: 제목 + 글쓰기 버튼 -->
  <div class="sc-section-head">
    <h2 class="sc-section-title">글 목록
      <span class="badge bg-secondary ms-1 sc-auth-linkline"><?= number_format((int)$pagination['total']) ?></span>
    </h2>
    <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
      <a class="btn btn-primary rounded-pill px-4"
         href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">
        <i class="bi bi-pencil-square me-1"></i>글쓰기
      </a>
    <?php endif; ?>
  </div>

  <!-- 검색 -->
  <form class="sc-search-form" method="get">
    <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
    <input class="form-control sc-input" name="q"
           value="<?= smartcms_h($pagination['keyword']) ?>"
           placeholder="제목, 내용, 작성자 검색">
    <button class="btn btn-primary px-3" type="submit">
      <i class="bi bi-search"></i>
    </button>
    <?php if ($pagination['keyword'] !== ''): ?>
      <a class="btn btn-outline-secondary rounded-pill"
         href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
    <?php endif; ?>
  </form>

  <!-- 테이블 -->
  <div class="table-responsive sc-table-wrap">
    <table class="table table-hover align-middle sc-table">
      <thead>
        <tr>
          <th>제목</th>
          <th class="sc-table-col-author">작성자</th>
          <th class="sc-table-col-views">조회</th>
          <th class="sc-table-col-date">작성일</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td>
              <?php if ((int)$post['is_notice'] === 1): ?>
                <span class="badge text-bg-primary me-1">공지</span>
              <?php endif; ?>
              <a class="sc-table-link"
                 href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                <?= (int)$post['is_secret'] === 1 ? '🔒 ' : '' ?><?= smartcms_h($post['title']) ?>
                <?php if ((int)$post['comment_count'] > 0): ?>
                  <span class="sc-muted ms-1">[<?= (int)$post['comment_count'] ?>]</span>
                <?php endif; ?>
                <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                  <i class="bi bi-paperclip ms-1 sc-muted"></i>
                <?php endif; ?>
              </a>
            </td>
            <td class="sc-table-meta"><?= smartcms_h($post['author_name']) ?></td>
            <td class="sc-table-meta"><?= number_format((int)$post['view_count']) ?></td>
            <td class="sc-table-meta"><?= smartcms_h($post['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$posts): ?>
          <tr>
            <td colspan="4" class="text-center sc-muted py-4">등록된 글이 없습니다.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- 페이지네이션 -->
  <?php if ((int)$pagination['pages'] > 1): ?>
    <nav class="sc-pagination" aria-label="게시글 페이지">
      <?php for ($i = 1; $i <= (int)$pagination['pages']; $i++): ?>
        <a class="sc-page-link <?= $i === (int)$pagination['page'] ? 'is-active' : '' ?>"
           href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])
               . '&page=' . $i
               . '&q=' . rawurlencode((string)$pagination['keyword'])) ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
</div>
