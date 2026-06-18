<?php
/* 글쓰기/수정 폼 스킨 - youtube/form.php */
$skin_meta = smartcms_board_skin_meta($board);
$use_editor = (int)($board['use_editor'] ?? 1) === 1;
$show_submit_icon = $show_submit_icon ?? true;
?>
<article class="smartcms-board-form">
  <div class="card border shadow-sm bg-white overflow-hidden">
    <div class="card-body p-4 p-lg-5">
      <form method="post" enctype="<?= smartcms_h($form_enctype ?? 'application/x-www-form-urlencoded') ?>">
        <?= smartcms_csrf_input() ?>
        <input type="hidden" name="action" value="<?= smartcms_h($form_action ?? 'update') ?>">

        <div class="row g-4">
          <div class="col-12">
            <label for="title" class="form-label fw-bold text-dark">제목 <span class="text-primary">*</span></label>
            <input class="form-control py-2" id="title" name="title" value="<?= smartcms_h($form_values['title'] ?? '') ?>" placeholder="게시글 제목을 입력하세요." required>
          </div>

          <div class="col-12">
            <div class="d-flex flex-wrap gap-3">
              <?php if (smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)): ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_notice" value="1" id="is_notice" <?= !empty($form_values['is_notice']) ? 'checked' : '' ?>>
                  <label class="form-check-label fw-medium" for="is_notice"><i class="bi bi-megaphone me-1"></i>공지글로 게시</label>
                </div>
              <?php endif; ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_secret" value="1" id="is_secret" <?= !empty($form_values['is_secret']) ? 'checked' : '' ?>>
                <label class="form-check-label fw-medium" for="is_secret"><i class="bi bi-lock me-1"></i>비밀글 (작성자/관리자만 열람)</label>
              </div>
            </div>
          </div>

          <div class="col-12">
            <label for="content" class="form-label fw-bold text-dark">설명</label>
            <?php if ($use_editor): ?>
              <textarea
                class="form-control"
                id="content"
                name="content"
                rows="12"
                placeholder="영상 설명이나 소개 문구를 작성하세요."
                data-ob-editor="jodit"
                data-ob-upload-url="<?= smartcms_h(smartcms_base_url('/board/editor-upload/')) ?>"
                data-ob-board-code="<?= smartcms_h((string)$board['board_key']) ?>"
                data-ob-csrf="<?= smartcms_h(smartcms_csrf_token()) ?>"
              ><?= smartcms_h($form_values['content'] ?? '') ?></textarea>
              <div class="form-text mt-2">설명은 본문 아래에 표시됩니다.</div>
            <?php else: ?>
              <textarea class="form-control" id="content" name="content" rows="12" placeholder="영상 설명이나 소개 문구를 작성하세요."><?= smartcms_h($form_values['content'] ?? '') ?></textarea>
              <div class="form-text mt-2">설명은 본문 아래에 표시됩니다.</div>
            <?php endif; ?>
          </div>

          <div class="col-12">
            <label for="link_url_1" class="form-label fw-bold text-dark">유튜브 주소 <span class="text-danger">*</span></label>
            <input class="form-control py-2" id="link_url_1" name="link_url_1" type="text" value="<?= smartcms_h($form_values['link_url_1'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
            <div class="form-text">유튜브 공유 링크 또는 youtu.be 단축 링크를 넣어주세요.</div>
            <input type="hidden" name="link_url_2" value="<?= smartcms_h($form_values['link_url_2'] ?? '') ?>">
          </div>

          <?php if (!empty($show_attachments)): ?>
            <div class="col-12">
              <label for="attachments" class="form-label fw-bold text-dark">첨부파일</label>
              <input class="form-control py-2" id="attachments" name="attachments[]" type="file" multiple>
              <div class="form-text small text-secondary">보조 이미지나 파일을 여러 개 첨부할 수 있습니다.</div>
            </div>
          <?php endif; ?>

          <div class="col-12 pt-3">
            <div class="d-flex flex-wrap gap-2">
              <button class="btn <?= $skin_meta['button_class'] ?> rounded-2 px-5 py-2 fw-bold shadow-sm <?= smartcms_h((string)$skin_meta['button_text_class']) ?>" type="submit">
                <?php if ($show_submit_icon): ?><i class="bi bi-check2-circle me-1"></i><?php endif; ?><?= smartcms_h($submit_label ?? '저장하기') ?>
              </button>
              <a class="btn btn-light border rounded-2 px-4 py-2 fw-bold shadow-none text-secondary" href="<?= smartcms_h($back_url) ?>">
                <?= smartcms_h($back_label ?? '취소하고 목록으로') ?>
              </a>
            </div>
          </div>
        </div>
      </form>

      <?php if (!empty($show_hide_form)): ?>
        <section class="mt-5 pt-5 border-top">
          <div class="alert alert-danger border shadow-sm bg-danger-subtle p-4 mb-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
              <div>
                <h3 class="h6 fw-bold mb-1 text-danger">게시글 관리 옵션</h3>
                <p class="mb-0 text-dark small opacity-75 fw-medium">이 게시글을 목록에서 즉시 숨김 처리합니다. 데이터는 삭제되지 않습니다.</p>
              </div>
              <form method="post" class="m-0">
                <?= smartcms_csrf_input() ?>
                <input type="hidden" name="action" value="hide">
                <button class="btn btn-danger rounded-2 px-4 py-2 fw-bold shadow-sm" type="submit">지금 숨기기</button>
              </form>
            </div>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </div>
</article>
