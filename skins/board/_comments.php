<?php
$skin_meta = smartcms_board_skin_meta($board);
$accent = (string)$skin_meta['accent'];
$accent_text = $accent === 'dark' ? 'text-dark' : 'text-' . $accent;
$comment_tree = smartcms_board_comment_tree(is_array($comments ?? null) ? $comments : []);

$render_comments = static function (array $items, int $depth = 0) use (&$render_comments, $board, $user, $can_manage_board, $can_comment): void {
    foreach ($items as $comment):
        $comment_id = (int)$comment['id'];
        $reply_form_id = 'commentReplyForm_' . $comment_id;
        $is_hidden = (int)($comment['is_hidden'] ?? 0) === 1;
        $can_reply = $can_comment && $user && (!$is_hidden || $can_manage_board);
        $item_class = $depth === 0
            ? 'p-3 bg-light rounded-3 border-start border-primary border-4 shadow-none'
            : 'p-3 bg-white rounded-3 border border-primary-subtle shadow-sm ms-4 ms-md-5';
        ?>
        <article class="<?= smartcms_h($item_class) ?>">
          <header class="d-flex justify-content-between align-items-center gap-2 mb-2">
            <span class="fw-bold text-dark">
              <i class="bi bi-person me-1"></i><?= smartcms_h(smartcms_board_author_display_name($board, $comment)) ?>
            </span>
            <time class="text-secondary small fw-medium" datetime="<?= smartcms_h((string)$comment['created_at']) ?>"><?= smartcms_h($comment['created_at']) ?></time>
          </header>
          <div class="mb-0 text-dark fw-medium lh-base fs-6">
            <?= nl2br(smartcms_h($is_hidden ? '⚠️ 관리자에 의해 숨김 처리된 댓글입니다.' : $comment['content'])) ?>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
            <?php if ($can_reply): ?>
              <button class="btn btn-link p-0 text-decoration-none shadow-none"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#<?= smartcms_h($reply_form_id) ?>"
                      aria-expanded="false"
                      aria-controls="<?= smartcms_h($reply_form_id) ?>">
                <span class="badge text-bg-<?= smartcms_h($accent === 'dark' ? 'secondary' : $accent) ?> rounded-2 px-2 py-1 fw-semibold">댓글</span>
              </button>
            <?php endif; ?>
            <?php if ($can_manage_board && !$is_hidden): ?>
              <form method="post" class="mb-0">
                <?= smartcms_csrf_input() ?>
                <input type="hidden" name="action" value="comment_hide">
                <input type="hidden" name="comment_id" value="<?= smartcms_h($comment['id']) ?>">
                <button class="btn btn-danger btn-sm rounded-2 px-3 shadow-none fw-bold" type="submit">댓글 숨김</button>
              </form>
            <?php endif; ?>
          </div>
          <?php if ($can_reply): ?>
            <div class="collapse mt-3" id="<?= smartcms_h($reply_form_id) ?>">
              <form class="vstack gap-3" method="post">
                <?= smartcms_csrf_input() ?>
                <input type="hidden" name="action" value="comment_create">
                <input type="hidden" name="parent_id" value="<?= smartcms_h($comment_id) ?>">
                <div>
                  <textarea class="form-control py-3" name="content" rows="3" required placeholder="댓글을 입력하세요."></textarea>
                </div>
                <div class="text-end">
                  <button type="submit" class="btn btn-primary rounded-2 px-4 py-2 fw-bold shadow-sm">댓글 등록</button>
                </div>
              </form>
            </div>
          <?php endif; ?>
          <?php if (!empty($comment['children'])): ?>
            <div class="vstack gap-3 mt-3">
              <?php $render_comments($comment['children'], $depth + 1); ?>
            </div>
          <?php endif; ?>
        </article>
    <?php
    endforeach;
};
?>

<!-- [COMMENTS] 댓글 섹션 -->
<section class="card border shadow-sm bg-white mt-4 overflow-hidden">
  <div class="card-body p-4 p-lg-5">
    <div class="d-flex align-items-center gap-2 mb-5">
      <i class="bi bi-chat-left-text-fill fs-4 text-primary"></i>
      <h2 class="fs-5 fw-bold mb-0 text-dark">전체 댓글 <span class="text-primary ms-1"><?= count($comments) ?></span></h2>
    </div>

    <?php if ($comment_tree): ?>
      <div class="vstack gap-4">
        <?php $render_comments($comment_tree, 0); ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5 border rounded-3 bg-light opacity-75">
        <i class="bi bi-chat-dots fs-1 d-block mb-2 text-secondary"></i>
        <p class="text-secondary mb-0 fw-medium">첫 번째 댓글을 남겨보세요.</p>
      </div>
    <?php endif; ?>

    <?php if ($can_comment && $user): ?>
      <section class="mt-5 pt-5 border-top">
        <h3 class="fs-5 fw-bold mb-3 text-dark">댓글 작성하기</h3>
        <form class="vstack gap-3" method="post">
          <?= smartcms_csrf_input() ?>
          <input type="hidden" name="action" value="comment_create">
          <div>
            <textarea class="form-control py-3" id="content" name="content" rows="4" required placeholder="상대방을 존중하는 따뜻한 댓글을 남겨주세요."></textarea>
          </div>
          <div class="text-end">
            <button type="submit" class="btn btn-primary rounded-2 px-5 py-2 fw-bold shadow-sm">댓글 등록</button>
          </div>
        </form>
      </section>
    <?php elseif (!$user): ?>
      <aside class="alert alert-info border shadow-sm bg-info-subtle p-4 mt-5 d-flex align-items-center gap-3">
        <i class="bi bi-info-circle-fill fs-4"></i>
        <div class="fw-bold">로그인 후 댓글을 작성할 수 있습니다. <a href="/member/login/" class="alert-link ms-2">지금 로그인하기 <i class="bi bi-chevron-right small"></i></a></div>
      </aside>
    <?php else: ?>
      <aside class="alert alert-warning border shadow-sm p-4 mt-5 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-circle-fill fs-4"></i>
        <div class="fw-bold text-dark">현재 댓글을 작성할 권한이 없습니다.</div>
      </aside>
    <?php endif; ?>
  </div>
</section>
