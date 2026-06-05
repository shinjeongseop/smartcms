<section class="smartcms-panel smartcms-admin-panel">
  <form class="smartcms-grid" method="post" enctype="<?= smartcms_h($form_enctype ?? 'application/x-www-form-urlencoded') ?>">
    <?= smartcms_csrf_input() ?>
    <input type="hidden" name="action" value="<?= smartcms_h($form_action ?? 'update') ?>">
    <div class="smartcms-field">
      <label for="title">제목</label>
      <input class="smartcms-input" id="title" name="title" value="<?= smartcms_h($form_values['title'] ?? '') ?>" required>
    </div>
    <div class="smartcms-field">
      <label for="content">내용</label>
      <textarea class="smartcms-textarea" id="content" name="content" rows="12" required><?= smartcms_h($form_values['content'] ?? '') ?></textarea>
    </div>
    <div class="smartcms-actions">
      <?php if (smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)): ?>
        <label class="smartcms-check-field">
          <input type="checkbox" name="is_notice" value="1" <?= !empty($form_values['is_notice']) ? 'checked' : '' ?>>
          공지글
        </label>
      <?php endif; ?>
      <label class="smartcms-check-field">
        <input type="checkbox" name="is_secret" value="1" <?= !empty($form_values['is_secret']) ? 'checked' : '' ?>>
        비밀글
      </label>
    </div>
    <?php if (!empty($show_attachments)): ?>
      <div class="smartcms-field">
        <label for="attachments">첨부파일</label>
        <input class="smartcms-input" id="attachments" name="attachments[]" type="file" multiple>
        <p class="smartcms-text-muted">파일당 <?= smartcms_h(smartcms_setting_int('upload_max_mb', 10)) ?>MB 이하로 업로드할 수 있습니다.</p>
      </div>
    <?php endif; ?>
    <div class="smartcms-actions">
      <?= smartcms_button($submit_label ?? '저장', 'submit') ?>
      <a class="smartcms-link-btn" href="<?= smartcms_h($back_url) ?>"><?= smartcms_h($back_label ?? '목록으로') ?></a>
    </div>
  </form>

  <?php if (!empty($show_hide_form)): ?>
    <form class="smartcms-danger-form" method="post">
      <?= smartcms_csrf_input() ?>
      <input type="hidden" name="action" value="hide">
      <button class="smartcms-danger-btn" type="submit">글 숨김 처리</button>
      <p class="smartcms-text-muted">데이터는 삭제하지 않고 목록에서 숨깁니다.</p>
    </form>
  <?php endif; ?>
</section>
