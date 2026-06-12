<?php
/* 게시판 상세 스킨 - default/view.php
 * 사용 가능 변수: $board, $post, $files, $comments, $user, $can_manage_post, $can_manage_board, $can_comment
 */
$skin_meta = smartcms_board_skin_meta($board);
$accent = (string)$skin_meta['accent'];
$accent_text = $accent === 'dark' ? 'text-dark' : 'text-' . $accent;
$post_link_url = smartcms_board_normalize_link_url((string)($post['link_url'] ?? ''));
$image_files = smartcms_board_image_files($files);
$attachment_files = array_values(array_filter($files, static fn(array $file): bool => !smartcms_board_file_is_image($file)));
$thumb_config = smartcms_board_thumbnail_config($board, 'view');
$image_columns = max(1, (int)$thumb_config['columns']);
?>
<article class="card border shadow-sm bg-white overflow-hidden mb-4">
  <div class="card-body p-4 p-lg-5">
    <header class="d-flex align-items-start justify-content-between gap-3 mb-4">
      <div>
        <div class="mb-2">
          <?php if ((int)$post['is_notice'] === 1): ?><span class="badge bg-<?= smartcms_h($accent) ?> rounded-pill me-1 px-3">공지</span><?php endif; ?>
          <?php if ((int)$post['is_secret'] === 1): ?><span class="badge bg-dark rounded-pill me-1 px-3"><i class="bi bi-lock-fill me-1"></i>비밀글</span><?php endif; ?>
        </div>
        <h2 class="fs-5 fw-bold mb-0 text-dark"><?= smartcms_h($post['title']) ?></h2>
      </div>
    </header>

    <div class="d-flex flex-wrap align-items-center gap-3 py-3 border-top border-bottom text-secondary small mb-5 fw-medium">
      <span class="d-flex align-items-center gap-1"><i class="bi bi-person-circle fs-6 <?= $accent_text ?>"></i><?= smartcms_h($post['author_name']) ?></span>
      <span class="opacity-25">|</span>
      <span class="d-flex align-items-center gap-1"><i class="bi bi-clock fs-6 <?= $accent_text ?>"></i><?= smartcms_h($post['created_at']) ?></span>
      <span class="opacity-25">|</span>
      <span class="d-flex align-items-center gap-1"><i class="bi bi-eye fs-6 <?= $accent_text ?>"></i>조회 <?= number_format((int)$post['view_count']) ?></span>
      <span class="opacity-25 d-none d-md-inline">|</span>
      <span class="d-flex align-items-center gap-1"><i class="bi bi-chat-dots fs-6 <?= $accent_text ?>"></i>댓글 <?= count($comments) ?></span>
    </div>

    <?php if ($post_link_url !== ''): ?>
      <div class="mb-5">
        <a class="btn btn-light border rounded-pill px-4 py-2 fw-bold shadow-none text-secondary"
           href="<?= smartcms_h($post_link_url) ?>"
           target="_blank"
           rel="noopener noreferrer">
          <i class="bi bi-link-45deg me-1"></i>링크 열기
        </a>
      </div>
    <?php endif; ?>

    <?php if ($image_files): ?>
      <section class="mb-5">
        <div class="row g-3">
          <?php foreach ($image_files as $image): ?>
            <?php $thumb_url = smartcms_board_file_thumbnail_url($image, (int)$thumb_config['width'], (int)$thumb_config['height']); ?>
            <div class="<?= $image_columns > 1 ? 'col-12 col-md-6' : 'col-12' ?>">
              <figure class="card border shadow-sm bg-white h-100 overflow-hidden mb-0">
                <a class="d-block ratio ratio-16x9 bg-light text-decoration-none"
                   href="<?= smartcms_h(smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$image['id'])) ?>"
                   target="_blank" rel="noopener">
                  <img class="w-100 h-100 object-fit-cover" src="<?= smartcms_h($thumb_url ?? (smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$image['id']))) ?>" alt="<?= smartcms_h($image['original_name']) ?>">
                </a>
                <figcaption class="card-body p-3 small">
                  <div class="fw-semibold text-dark text-truncate"><?= smartcms_h($image['original_name']) ?></div>
                  <div class="text-secondary"><?= number_format((int)$image['file_size']) ?> bytes · 다운로드 <?= (int)$image['download_count'] ?>회</div>
                </figcaption>
              </figure>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <div class="mb-5 text-break lh-lg fs-6 text-dark">
      <?= smartcms_board_render_content($post) ?>
    </div>

    <?php if ($attachment_files): ?>
      <section class="mb-5">
        <h3 class="fs-5 fw-bold mb-3 text-primary">첨부파일</h3>
        <div class="list-group list-group-flush rounded-3 overflow-hidden border shadow-sm bg-white">
          <?php foreach ($attachment_files as $file): ?>
            <a class="list-group-item list-group-item-action bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3"
               href="<?= smartcms_h(smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$file['id'])) ?>">
              <span class="fw-bold text-dark"><i class="bi bi-file-earmark-arrow-down me-2"></i><?= smartcms_h($file['original_name']) ?></span>
              <small class="text-secondary fw-medium bg-light px-2 py-1 rounded"><?= number_format((int)$file['file_size']) ?> bytes · 다운로드 <?= (int)$file['download_count'] ?>회</small>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <footer class="pt-4 border-top">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex flex-wrap gap-2">
          <?php if ($user && smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
            <a class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">새글</a>
          <?php endif; ?>
          <?php if ($can_manage_post): ?>
            <a class="btn btn-light border rounded-pill px-4 fw-bold shadow-none text-secondary"
               href="<?= smartcms_h(smartcms_base_url('/board/edit/')
                   . '?board=' . rawurlencode((string)$board['board_key'])
                   . '&id=' . rawurlencode((string)$post['id'])) ?>">
              수정
            </a>
            <form class="d-inline" method="post">
              <?= smartcms_csrf_input() ?>
              <input type="hidden" name="action" value="post_delete">
              <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" type="submit">삭제</button>
            </form>
          <?php endif; ?>
        </div>
        <a class="btn btn-light border rounded-pill px-4 fw-bold shadow-none text-secondary"
           href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
          목록
        </a>
      </div>
    </footer>
  </div>
</article>

<!-- [COMMENTS] 댓글 섹션 -->
<section class="card border shadow-sm bg-white mt-4 overflow-hidden">
  <div class="card-body p-4 p-lg-5">
    <div class="d-flex align-items-center gap-2 mb-5">
      <i class="bi bi-chat-left-text-fill fs-4 text-primary"></i>
      <h2 class="fs-5 fw-bold mb-0 text-dark">전체 댓글 <span class="text-primary ms-1"><?= count($comments) ?></span></h2>
    </div>

    <?php if ($comments): ?>
      <div class="vstack gap-4">
        <?php foreach ($comments as $comment): ?>
          <article class="p-3 bg-light rounded-3 border-start border-primary border-4 shadow-none">
            <header class="d-flex justify-content-between align-items-center gap-2 mb-2">
              <span class="fw-bold text-dark"><i class="bi bi-person me-1"></i><?= smartcms_h($comment['author_name']) ?></span>
              <time class="text-secondary small fw-medium"><?= smartcms_h($comment['created_at']) ?></time>
            </header>
            <div class="mb-0 text-dark fw-medium lh-base fs-6">
              <?= nl2br(smartcms_h((int)$comment['is_hidden'] === 1 ? '⚠️ 관리자에 의해 숨김 처리된 댓글입니다.' : $comment['content'])) ?>
            </div>
            <?php if ($can_manage_board && (int)$comment['is_hidden'] !== 1): ?>
              <form class="mt-3" method="post">
                <?= smartcms_csrf_input() ?>
                <input type="hidden" name="action" value="comment_hide">
                <input type="hidden" name="comment_id" value="<?= smartcms_h($comment['id']) ?>">
                <button class="btn btn-danger btn-sm rounded-pill px-3 shadow-none fw-bold" type="submit">댓글 숨김</button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
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
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">댓글 등록</button>
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
