<?php
/* 글쓰기/수정 폼 스킨 - default/form.php
 * 사용 가능 변수: $board, $user, $form_values, $form_action, $form_enctype,
 *                 $submit_label, $back_url, $back_label, $show_attachments, $show_hide_form
 */
?>
<div class="sc-panel">
  <form class="sc-form-grid" method="post"
        enctype="<?= smartcms_h($form_enctype ?? 'application/x-www-form-urlencoded') ?>">
    <?= smartcms_csrf_input() ?>
    <input type="hidden" name="action" value="<?= smartcms_h($form_action ?? 'update') ?>">

    <!-- 제목 -->
    <div class="sc-field">
      <label for="title">제목 <span class="text-danger">*</span></label>
      <input class="sc-input" id="title" name="title"
             value="<?= smartcms_h($form_values['title'] ?? '') ?>"
             placeholder="제목을 입력하세요." required>
    </div>

    <!-- 옵션 (공지/비밀) -->
    <div class="d-flex gap-3 flex-wrap">
      <?php if (smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)): ?>
        <label class="sc-check-field">
          <input type="checkbox" name="is_notice" value="1"
                 <?= !empty($form_values['is_notice']) ? 'checked' : '' ?>>
          <i class="bi bi-megaphone me-1"></i>공지글
        </label>
      <?php endif; ?>
      <label class="sc-check-field">
        <input type="checkbox" name="is_secret" value="1"
               <?= !empty($form_values['is_secret']) ? 'checked' : '' ?>>
        <i class="bi bi-lock me-1"></i>비밀글
      </label>
    </div>

    <!-- 내용 -->
    <div class="sc-field">
      <label for="content">내용 <span class="text-danger">*</span></label>
      <textarea class="sc-textarea" id="content" name="content" rows="14"
                placeholder="내용을 입력하세요." required><?= smartcms_h($form_values['content'] ?? '') ?></textarea>
    </div>

    <!-- 첨부파일 -->
    <?php if (!empty($show_attachments)): ?>
      <div class="sc-field">
        <label for="attachments">첨부파일</label>
        <input class="sc-input" id="attachments" name="attachments[]" type="file" multiple>
        <p class="sc-field-hint">파일당 <?= smartcms_h(smartcms_setting_int('upload_max_mb', 10)) ?>MB 이하</p>
      </div>
    <?php endif; ?>

    <!-- 액션 버튼 -->
    <div class="d-flex gap-2 flex-wrap">
      <?= smartcms_button($submit_label ?? '저장', 'submit') ?>
      <a class="btn btn-outline-secondary rounded-pill px-4"
         href="<?= smartcms_h($back_url) ?>"><?= smartcms_h($back_label ?? '목록으로') ?></a>
    </div>
  </form>

  <!-- 숨김 처리 (수정 페이지) -->
  <?php if (!empty($show_hide_form)): ?>
    <div class="sc-danger-zone">
      <h3 class="sc-section-title sc-section-title--xs text-danger">위험 영역</h3>
      <form class="sc-inline-form" method="post">
        <?= smartcms_csrf_input() ?>
        <input type="hidden" name="action" value="hide">
        <button class="btn btn-outline-danger rounded-pill px-4" type="submit">글 숨김 처리</button>
        <span class="sc-danger-note">데이터는 삭제하지 않고 목록에서 숨깁니다.</span>
      </form>
    </div>
  <?php endif; ?>
</div>
