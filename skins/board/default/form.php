<?php
/* 글쓰기/수정 폼 스킨 - default/form.php
 * 사용 가능 변수: $board, $user, $form_values, $form_action, $form_enctype,
 *                 $submit_label, $back_url, $back_label, $show_attachments, $show_hide_form
 */
$skin_meta = smartcms_board_skin_meta($board);
$accent = (string)$skin_meta['accent'];
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
            <input class="form-control py-2.5" id="title" name="title" value="<?= smartcms_h($form_values['title'] ?? '') ?>" placeholder="게시글 제목을 입력하세요." required>
          </div>

          <div class="col-12">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="link_url_1" class="form-label fw-bold text-dark">링크 1</label>
                <input class="form-control py-2.5" id="link_url_1" name="link_url_1" type="text" value="<?= smartcms_h($form_values['link_url_1'] ?? '') ?>" placeholder="https://example.com">
              </div>
              <div class="col-12 col-md-6">
                <label for="link_url_2" class="form-label fw-bold text-dark">링크 2</label>
                <input class="form-control py-2.5" id="link_url_2" name="link_url_2" type="text" value="<?= smartcms_h($form_values['link_url_2'] ?? '') ?>" placeholder="https://example.com">
              </div>
            </div>
            <div class="form-text">그누보드처럼 링크를 2개까지 입력할 수 있습니다.</div>
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
            <label for="content" class="form-label fw-bold text-dark">내용 <span class="text-primary">*</span></label>
            <?php if ($use_editor): ?>
              <textarea
                class="form-control"
                id="content"
                name="content"
                rows="16"
                placeholder="자유롭게 내용을 작성해주세요."
                required
                data-ob-editor="jodit"
                data-ob-upload-url="<?= smartcms_h(smartcms_base_url('/board/editor-upload/')) ?>"
                data-ob-board-code="<?= smartcms_h((string)$board['board_key']) ?>"
                data-ob-csrf="<?= smartcms_h(smartcms_csrf_token()) ?>"
              ><?= smartcms_h($form_values['content'] ?? '') ?></textarea>
              <div class="form-text mt-2">에디터가 활성화된 게시판입니다. 본문 이미지는 첨부파일 영역에서 따로 관리됩니다.</div>
            <?php else: ?>
              <textarea class="form-control" id="content" name="content" rows="16" placeholder="자유롭게 내용을 작성해주세요." required><?= smartcms_h($form_values['content'] ?? '') ?></textarea>
              <div class="form-text mt-2">이 게시판은 일반 텍스트 모드로 작성됩니다.</div>
            <?php endif; ?>
          </div>

          <?php if (!empty($existing_files)): ?>
            <div class="col-12">
              <section class="rounded-3 bg-white p-3 p-md-4 border shadow-sm">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                  <h3 class="h6 fw-bold mb-0 text-dark"><i class="bi bi-paperclip me-1"></i>현재 첨부파일</h3>
                  <span class="badge text-bg-light border text-body-secondary">삭제 선택 가능</span>
                </div>
                <div class="list-group">
                  <?php foreach ($existing_files as $file): ?>
                    <label class="list-group-item d-flex align-items-start justify-content-between gap-3">
                      <span class="d-flex align-items-start gap-2 min-w-0">
                        <input class="form-check-input mt-1 flex-shrink-0" type="checkbox" name="remove_files[]" value="<?= smartcms_h($file['id']) ?>" id="remove_file_<?= smartcms_h($file['id']) ?>">
                        <span class="min-w-0">
                          <span class="fw-semibold text-dark d-block text-truncate">
                            <span class="visually-hidden">파일 선택:</span><?= smartcms_h($file['original_name']) ?>
                          </span>
                          <small class="text-secondary d-block"><?= number_format((int)$file['file_size']) ?> bytes · 다운로드 <?= (int)$file['download_count'] ?>회</small>
                        </span>
                      </span>
                      <span class="small text-body-secondary flex-shrink-0">삭제</span>
                    </label>
                  <?php endforeach; ?>
                </div>
                <p class="form-text mt-3 mb-0">체크한 파일은 저장 시 삭제됩니다.</p>
              </section>
            </div>
          <?php endif; ?>

          <?php if (!empty($show_attachments)): ?>
            <div class="col-12">
              <label for="attachments" class="form-label fw-bold text-dark">첨부파일</label>
              <input class="form-control py-2" id="attachments" name="attachments[]" type="file" multiple>
              <div class="form-text small text-secondary">파일당 최대 <?= smartcms_h(smartcms_setting_int('upload_max_mb', 10)) ?>MB까지, 여러 파일을 한 번에 업로드할 수 있습니다.</div>
            </div>
          <?php endif; ?>

          <div class="col-12 pt-3">
            <div class="d-flex flex-wrap gap-2">
              <button class="btn <?= $skin_meta['button_class'] ?> rounded-pill px-5 py-2 fw-bold shadow-sm <?= smartcms_h((string)$skin_meta['button_text_class']) ?>" type="submit">
                <?php if ($show_submit_icon): ?><i class="bi bi-check2-circle me-1"></i><?php endif; ?><?= smartcms_h($submit_label ?? '저장하기') ?>
              </button>
              <a class="btn btn-light border rounded-pill px-4 py-2 fw-bold shadow-none text-secondary" href="<?= smartcms_h($back_url) ?>">
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
                <button class="btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm" type="submit">지금 숨기기</button>
              </form>
            </div>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </div>
</article>
