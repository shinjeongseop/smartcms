<section class="smartcms-panel smartcms-admin-panel">
  <div class="smartcms-section-head">
    <h2 class="smartcms-section-title">글 목록</h2>
    <?php if (smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
      <a class="smartcms-link-btn smartcms-link-btn--primary" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">글쓰기</a>
    <?php endif; ?>
  </div>
  <form class="smartcms-search-form" method="get">
    <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
    <input class="smartcms-input" name="q" value="<?= smartcms_h($pagination['keyword']) ?>" placeholder="제목, 내용, 작성자 검색">
    <button class="smartcms-small-btn" type="submit">검색</button>
    <?php if ($pagination['keyword'] !== ''): ?>
      <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">초기화</a>
    <?php endif; ?>
  </form>
  <div class="smartcms-table-wrap">
    <table class="smartcms-table">
      <thead>
        <tr>
          <th>제목</th>
          <th>작성자</th>
          <th>조회</th>
          <th>작성일</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td>
              <?php if ((int)$post['is_notice'] === 1): ?>
                <span class="smartcms-badge">공지</span>
              <?php endif; ?>
              <a class="smartcms-table-link" href="<?= smartcms_h(smartcms_board_post_url((string)$board['board_key'], (int)$post['id'])) ?>">
                <?= (int)$post['is_secret'] === 1 ? '비밀글 ' : '' ?><?= smartcms_h($post['title']) ?>
                <?php if ((int)$post['comment_count'] > 0): ?>
                  <span class="smartcms-text-muted">(<?= smartcms_h($post['comment_count']) ?>)</span>
                <?php endif; ?>
                <?php if ((int)($post['attachment_count'] ?? 0) > 0): ?>
                  <span class="smartcms-badge smartcms-badge--muted">첨부</span>
                <?php endif; ?>
              </a>
            </td>
            <td><?= smartcms_h($post['author_name']) ?></td>
            <td><?= smartcms_h($post['view_count']) ?></td>
            <td><?= smartcms_h($post['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$posts): ?>
          <tr>
            <td colspan="4">등록된 글이 없습니다.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pagination['pages'] > 1): ?>
    <nav class="smartcms-pagination" aria-label="게시글 페이지">
      <?php for ($i = 1; $i <= (int)$pagination['pages']; $i++): ?>
        <a class="smartcms-page-link <?= $i === (int)$pagination['page'] ? 'is-active' : '' ?>" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key']) . '&page=' . $i . '&q=' . rawurlencode((string)$pagination['keyword'])) ?>"><?= $i ?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
</section>
