<article class="smartcms-panel smartcms-admin-panel smartcms-post-view">
  <?php if ((int)$post['is_notice'] === 1): ?>
    <span class="smartcms-badge">공지</span>
  <?php endif; ?>
  <?php if ((int)$post['is_secret'] === 1): ?>
    <span class="smartcms-badge smartcms-badge--muted">비밀글</span>
  <?php endif; ?>
  <?php if ($can_manage_post): ?>
    <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_base_url('/board/edit/') . '?board=' . rawurlencode((string)$board['board_key']) . '&id=' . rawurlencode((string)$post['id'])) ?>">수정</a>
  <?php endif; ?>
  <div class="smartcms-post-content"><?= nl2br(smartcms_h($post['content'])) ?></div>
  <?php if ($files): ?>
    <div class="smartcms-file-list">
      <h2 class="smartcms-section-title">첨부파일</h2>
      <?php foreach ($files as $file): ?>
        <a class="smartcms-file-link" href="<?= smartcms_h(smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$file['id'])) ?>">
          <?= smartcms_h($file['original_name']) ?>
          <span><?= number_format((int)$file['file_size']) ?> bytes · 다운로드 <?= smartcms_h($file['download_count']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</article>

<section class="smartcms-panel smartcms-admin-panel smartcms-stack-panel">
  <div class="smartcms-section-head">
    <h2 class="smartcms-section-title">댓글 <?= smartcms_h(count($comments)) ?></h2>
    <a class="smartcms-link-btn" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">목록으로</a>
  </div>

  <div class="smartcms-comment-list">
    <?php foreach ($comments as $comment): ?>
      <article class="smartcms-comment">
        <strong><?= smartcms_h($comment['author_name']) ?></strong>
        <span class="smartcms-text-muted"><?= smartcms_h($comment['created_at']) ?></span>
        <p><?= nl2br(smartcms_h((int)$comment['is_hidden'] === 1 ? '숨김 처리된 댓글입니다.' : $comment['content'])) ?></p>
        <?php if ($can_manage_board && (int)$comment['is_hidden'] !== 1): ?>
          <form class="smartcms-inline-form" method="post">
            <?= smartcms_csrf_input() ?>
            <input type="hidden" name="action" value="comment_hide">
            <input type="hidden" name="comment_id" value="<?= smartcms_h($comment['id']) ?>">
            <button class="smartcms-small-muted-btn" type="submit">댓글 숨김</button>
          </form>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
    <?php if (!$comments): ?>
      <p class="smartcms-text-muted">등록된 댓글이 없습니다.</p>
    <?php endif; ?>
  </div>

  <?php if ($can_comment && $user): ?>
    <form class="smartcms-grid smartcms-comment-form" method="post">
      <?= smartcms_csrf_input() ?>
      <input type="hidden" name="action" value="comment_create">
      <div class="smartcms-field">
        <label for="content">댓글 작성</label>
        <textarea class="smartcms-textarea" id="content" name="content" rows="4" required></textarea>
      </div>
      <?= smartcms_button('댓글 등록', 'submit') ?>
    </form>
  <?php else: ?>
    <?= smartcms_alert('댓글 작성 권한이 없습니다.', 'info') ?>
  <?php endif; ?>
</section>
