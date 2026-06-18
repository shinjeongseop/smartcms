<?php
/* 게시판 상세 스킨 - default/view.php
 * 사용 가능 변수: $board, $post, $files, $comments, $user, $can_manage_post, $can_manage_board, $can_comment
 */
$skin_meta = smartcms_board_skin_meta($board);
$accent = (string)$skin_meta['accent'];
$accent_text = $accent === 'dark' ? 'text-dark' : 'text-' . $accent;
$post_links = smartcms_board_post_links($post);
$image_files = smartcms_board_image_files($files);
$attachment_files = array_values(array_filter($files, static fn(array $file): bool => !smartcms_board_file_is_image($file)));
$thumb_config = smartcms_board_thumbnail_config($board, 'view');
$image_columns = max(1, (int)$thumb_config['columns']);
$audit_logs = smartcms_board_post_audit_logs((int)$board['id'], (int)$post['id'], 5);
?>
<article class="card border shadow-sm bg-white overflow-hidden mb-4">
  <div class="card-body p-4 p-lg-5">
    <header class="d-flex align-items-start justify-content-between gap-3 mb-4">
      <div>
        <div class="mb-2">
          <?php if ((int)$post['is_notice'] === 1): ?><span class="badge bg-<?= smartcms_h($accent) ?> rounded-2 me-1 px-3">공지</span><?php endif; ?>
          <?php if ((int)$post['is_secret'] === 1): ?><span class="badge bg-dark rounded-2 me-1 px-3"><i class="bi bi-lock-fill me-1"></i>비밀글</span><?php endif; ?>
        </div>
        <h2 class="fs-5 fw-bold mb-0 text-dark"><?= smartcms_h($post['title']) ?></h2>
      </div>
    </header>

    <div class="d-flex flex-wrap align-items-center gap-3 py-3 border-top border-bottom text-secondary small mb-5 fw-medium">
      <span class="d-flex align-items-center gap-1"><i class="bi bi-person-circle fs-6 <?= $accent_text ?>"></i><?= smartcms_h(smartcms_board_author_display_name($board, $post)) ?></span>
      <span class="opacity-25">|</span>
      <span class="d-flex align-items-center gap-1"><i class="bi bi-clock fs-6 <?= $accent_text ?>"></i><?= smartcms_h($post['created_at']) ?></span>
      <span class="opacity-25">|</span>
      <span class="d-flex align-items-center gap-1"><i class="bi bi-eye fs-6 <?= $accent_text ?>"></i>조회 <?= number_format((int)$post['view_count']) ?></span>
      <span class="opacity-25 d-none d-md-inline">|</span>
      <span class="d-flex align-items-center gap-1"><i class="bi bi-chat-dots fs-6 <?= $accent_text ?>"></i>댓글 <?= count($comments) ?></span>
    </div>

    <?php if ($post_links): ?>
      <div class="mb-5 vstack gap-2">
        <?php foreach ($post_links as $index => $link_url): ?>
          <a class="d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold text-primary text-break"
             href="<?= smartcms_h($link_url) ?>"
             target="_blank"
             rel="noopener noreferrer">
            <span class="badge text-bg-light border text-body-secondary rounded-2">링크 <?= (int)$index + 1 ?></span>
            <span><?= smartcms_h($link_url) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($image_files): ?>
      <section class="mb-5">
        <div class="row g-3">
          <?php foreach ($image_files as $image): ?>
            <?php $thumb_url = smartcms_board_file_thumbnail_url($image, (int)$thumb_config['width'], (int)$thumb_config['height']); ?>
            <?php $public_url = smartcms_board_file_public_url($image); ?>
            <div class="<?= $image_columns > 1 ? 'col-12 col-md-6' : 'col-12' ?>">
              <figure class="card border shadow-sm bg-white h-100 overflow-hidden mb-0">
                <a class="d-block bg-light text-decoration-none p-3 p-md-4"
                   href="<?= smartcms_h($public_url ?? '#') ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                  <img class="img-fluid d-block mx-auto rounded-3" src="<?= smartcms_h($thumb_url ?? (smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$image['id']))) ?>" alt="<?= smartcms_h($image['original_name']) ?>">
                </a>
                <figcaption class="card-body p-3 small">
                  <div class="fw-semibold text-dark text-truncate"><?= smartcms_h($image['original_name']) ?></div>
                  <div class="text-secondary mb-2"><?= number_format((int)$image['file_size']) ?> bytes · 다운로드 <?= (int)$image['download_count'] ?>회</div>
                  <span class="badge text-bg-light border text-body-secondary rounded-2">원본 보기</span>
                </figcaption>
              </figure>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <div class="sc-board-content mb-5 text-break lh-lg fs-6 text-dark">
      <?= smartcms_board_render_content($post) ?>
    </div>

    <?php if ($attachment_files): ?>
      <section class="mb-5">
        <h3 class="fs-5 fw-bold mb-3 text-primary">첨부파일</h3>
        <div class="list-group list-group-flush rounded-3 overflow-hidden border shadow-sm bg-white">
          <?php foreach ($attachment_files as $index => $file): ?>
            <a class="list-group-item list-group-item-action bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3"
               href="<?= smartcms_h(smartcms_base_url('/board/download/') . '?file=' . rawurlencode((string)$file['id'])) ?>">
              <span class="d-flex flex-column gap-1">
                <span class="d-flex flex-wrap align-items-center gap-2">
                  <span class="badge text-bg-light border text-body-secondary rounded-2">첨부 <?= (int)$index + 1 ?></span>
                  <span class="fw-bold text-primary text-break"><?= smartcms_h($file['original_name']) ?></span>
                </span>
                <span class="small text-secondary">클릭하면 다운로드됩니다.</span>
              </span>
              <small class="text-secondary fw-medium text-nowrap"><?= number_format((int)$file['file_size']) ?> bytes · 다운로드 <?= (int)$file['download_count'] ?>회</small>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <footer class="pt-4 border-top">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex flex-wrap gap-2">
          <?php if ($user && smartcms_has_level((int)($board['board_write_level'] ?? 8), $user)): ?>
            <a class="btn btn-primary rounded-2 px-4 fw-bold shadow-sm" href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'], '/board/write/')) ?>">새글</a>
          <?php endif; ?>
          <?php if ($can_manage_post): ?>
            <a class="btn btn-light border rounded-2 px-4 fw-bold shadow-none text-secondary"
               href="<?= smartcms_h(smartcms_base_url('/board/edit/')
                   . '?board=' . rawurlencode((string)$board['board_key'])
                   . '&id=' . rawurlencode((string)$post['id'])) ?>">
              수정
            </a>
            <form class="d-inline" method="post" onsubmit="return confirm('이 게시글을 삭제할까요? 삭제 후에는 복구할 수 없습니다.');">
              <?= smartcms_csrf_input() ?>
              <input type="hidden" name="action" value="post_delete">
              <button class="btn btn-danger rounded-2 px-4 fw-bold shadow-sm" type="submit">삭제</button>
            </form>
          <?php endif; ?>
        </div>
        <a class="btn btn-light border rounded-2 px-4 fw-bold shadow-none text-secondary"
           href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
          목록
        </a>
      </div>
    </footer>

    <?php if ($audit_logs): ?>
      <section class="mt-4">
        <div class="card border shadow-sm bg-white">
          <div class="card-header bg-white border-bottom py-3 px-4">
            <h3 class="fs-6 fw-bold mb-0 text-dark">이동/복사 기록</h3>
          </div>
          <div class="list-group list-group-flush">
            <?php foreach ($audit_logs as $log): ?>
              <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-3 px-4">
                <div class="d-flex flex-column gap-1">
                  <div class="fw-semibold text-dark"><?= smartcms_h(smartcms_board_audit_action_label((string)$log['action'])) ?></div>
                  <div class="text-secondary small"><?= smartcms_h((string)$log['message']) ?></div>
                </div>
                <div class="text-md-end small text-secondary fw-medium">
                  <div><?= smartcms_h(smartcms_board_audit_actor_name($log)) ?></div>
                  <div><?= smartcms_h((string)$log['created_at']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>
  </div>
</article>

<!-- [COMMENTS] 댓글 섹션 -->
<?php require SMARTCMS_ROOT . '/skins/board/_comments.php'; ?>
