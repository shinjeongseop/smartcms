<?php
/* 게시글 상세 스킨 - default/view.php
 * 사용 가능 변수: $board, $post, $files, $comments, $user, $can_manage_post, $can_manage_board, $can_comment
 */
?>
<!-- 게시글 본문 -->
<article class="sc-panel">
  <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
    <div>
      <?php if ((int)$post['is_notice'] === 1): ?>
        <span class="badge text-bg-primary me-1">공지</span>
      <?php endif; ?>
      <?php if ((int)$post['is_secret'] === 1): ?>
        <span class="badge text-bg-secondary me-1">비밀글</span>
      <?php endif; ?>
      <h2 class="sc-section-title mb-0 d-inline"><?= smartcms_h($post['title']) ?></h2>
    </div>
    <?php if ($can_manage_post): ?>
      <a class="btn btn-outline-secondary btn-sm rounded-pill flex-shrink-0"
         href="<?= smartcms_h(smartcms_base_url('/board/edit/')
             . '?board=' . rawurlencode((string)$board['board_key'])
             . '&id=' . rawurlencode((string)$post['id'])) ?>">
        <i class="bi bi-pencil me-1"></i>수정
      </a>
    <?php endif; ?>
  </div>

  <!-- 메타 -->
  <div class="sc-post-meta">
    <span><i class="bi bi-person me-1"></i><?= smartcms_h($post['author_name']) ?></span>
    <span><i class="bi bi-clock me-1"></i><?= smartcms_h($post['created_at']) ?></span>
    <span><i class="bi bi-eye me-1"></i><?= number_format((int)$post['view_count']) ?></span>
    <span><i class="bi bi-chat me-1"></i><?= count($comments) ?></span>
  </div>

  <!-- 본문 -->
  <div class="sc-post-content"><?= nl2br(smartcms_h($post['content'])) ?></div>

  <!-- 첨부파일 -->
  <?php if ($files): ?>
    <div class="sc-file-list">
      <h3 class="sc-section-title" style="font-size:16px;">첨부파일</h3>
      <?php foreach ($files as $file): ?>
        <a class="sc-file-item"
           href="<?= smartcms_h(smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$file['id'])) ?>">
          <i class="bi bi-paperclip"></i>
          <span class="fw-bold"><?= smartcms_h($file['original_name']) ?></span>
          <span><?= number_format((int)$file['file_size']) ?> bytes · 다운로드 <?= (int)$file['download_count'] ?>회</span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- 하단 액션 -->
  <div class="sc-post-actions">
    <a class="btn btn-outline-secondary rounded-pill"
       href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
      <i class="bi bi-list-ul me-1"></i>목록으로
    </a>
  </div>
</article>

<!-- 댓글 -->
<section class="sc-panel mt-4">
  <h2 class="sc-section-title">댓글 <span class="text-muted fw-normal"><?= count($comments) ?></span></h2>

  <?php if ($comments): ?>
    <div class="sc-comment-list">
      <?php foreach ($comments as $comment): ?>
        <article class="sc-comment">
          <div class="sc-comment-header">
            <span class="sc-comment-author"><?= smartcms_h($comment['author_name']) ?></span>
            <span class="sc-comment-date"><?= smartcms_h($comment['created_at']) ?></span>
          </div>
          <p><?= nl2br(smartcms_h((int)$comment['is_hidden'] === 1 ? '숨김 처리된 댓글입니다.' : $comment['content'])) ?></p>
          <?php if ($can_manage_board && (int)$comment['is_hidden'] !== 1): ?>
            <form class="sc-inline-form mt-2" method="post">
              <?= smartcms_csrf_input() ?>
              <input type="hidden" name="action" value="comment_hide">
              <input type="hidden" name="comment_id" value="<?= smartcms_h($comment['id']) ?>">
              <button class="btn btn-outline-danger btn-sm rounded-pill" type="submit">댓글 숨김</button>
            </form>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="sc-empty">등록된 댓글이 없습니다.</p>
  <?php endif; ?>

  <!-- 댓글 작성 -->
  <?php if ($can_comment && $user): ?>
    <div class="sc-comment-form">
      <h3 class="sc-section-title" style="font-size:16px;">댓글 작성</h3>
      <form class="sc-form-grid" method="post">
        <?= smartcms_csrf_input() ?>
        <input type="hidden" name="action" value="comment_create">
        <div class="sc-field">
          <textarea class="sc-textarea" id="content" name="content" rows="4" required
                    placeholder="댓글을 입력하세요."></textarea>
        </div>
        <?= smartcms_button('댓글 등록', 'submit') ?>
      </form>
    </div>
  <?php elseif (!$user): ?>
    <?= smartcms_alert('로그인 후 댓글을 작성할 수 있습니다.', 'info') ?>
  <?php else: ?>
    <?= smartcms_alert('댓글 작성 권한이 없습니다.', 'info') ?>
  <?php endif; ?>
</section>
