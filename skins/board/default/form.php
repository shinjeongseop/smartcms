<?php
/* 글쓰기/수정 폼 스킨 - default/form.php
 * 사용 가능 변수: $board, $user, $form_values, $form_action, $form_enctype,
 *                 $submit_label, $back_url, $back_label, $show_attachments, $show_hide_form
 */
?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-4 p-lg-5">
    <form method="post" enctype="<?= smartcms_h($form_enctype ?? 'application/x-www-form-urlencoded') ?>">
      <?= smartcms_csrf_input() ?>
      <input type="hidden" name="action" value="<?= smartcms_h($form_action ?? 'update') ?>">

      <div class="row g-3">
        <div class="col-12">
          <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
          <input class="form-control" id="title" name="title" value="<?= smartcms_h($form_values['title'] ?? '') ?>" placeholder="제목을 입력하세요." required>
        </div>

        <div class="col-12">
          <div class="d-flex flex-wrap gap-3">
            <?php if (smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="is_notice" value="1" id="is_notice" <?= !empty($form_values['is_notice']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_notice"><i class="bi bi-megaphone me-1"></i>공지글</label>
              </div>
            <?php endif; ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" name="is_secret" value="1" id="is_secret" <?= !empty($form_values['is_secret']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_secret"><i class="bi bi-lock me-1"></i>비밀글</label>
            </div>
          </div>
        </div>

        <div class="col-12">
          <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
          <textarea class="form-control" id="content" name="content" rows="14" placeholder="내용을 입력하세요." required><?= smartcms_h($form_values['content'] ?? '') ?></textarea>
        </div>

        <?php if (!empty($show_attachments)): ?>
          <div class="col-12">
            <label for="attachments" class="form-label">첨부파일</label>
            <input class="form-control" id="attachments" name="attachments[]" type="file" multiple>
            <div class="form-text">파일당 <?= smartcms_h(smartcms_setting_int('upload_max_mb', 10)) ?>MB 이하</div>
          </div>
        <?php endif; ?>

        <div class="col-12 d-flex flex-wrap gap-2">
          <?= smartcms_button($submit_label ?? '저장', 'submit') ?>
          <a class="btn btn-outline-secondary" href="<?= smartcms_h($back_url) ?>"><?= smartcms_h($back_label ?? '목록으로') ?></a>
        </div>
      </div>
    </form>

    <?php if (!empty($show_hide_form)): ?>
      <div class="mt-4 pt-4 border-top">
        <div class="alert alert-danger mb-0">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
              <h3 class="h6 fw-semibold mb-1">위험 영역</h3>
              <p class="mb-0">데이터는 삭제하지 않고 목록에서 숨깁니다.</p>
            </div>
            <form method="post">
              <?= smartcms_csrf_input() ?>
              <input type="hidden" name="action" value="hide">
              <button class="btn btn-outline-danger rounded-pill px-4" type="submit">글 숨김 처리</button>
            </form>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
