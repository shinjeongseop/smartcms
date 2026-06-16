<?php
if (empty($board_bulk_can_manage)) {
    return;
}

$board_bulk_form_id = (string)($board_bulk_form_id ?? 'boardBulkForm');
$board_bulk_select_all_id = (string)($board_bulk_select_all_id ?? ($board_bulk_form_id . '_all'));
$board_bulk_modal_id = $board_bulk_form_id . '_modal';
$board_bulk_modal_title_id = $board_bulk_modal_id . '_title';
$board_bulk_modal_body_id = $board_bulk_modal_id . '_body';
$board_bulk_modal_target_id = $board_bulk_modal_id . '_target';
$board_bulk_action_input_id = $board_bulk_form_id . '_action';
$board_bulk_modal_submit_id = $board_bulk_modal_id . '_submit';
$board_bulk_targets = is_array($board_bulk_targets ?? null) ? $board_bulk_targets : [];
$board_bulk_select_all_location = (string)($board_bulk_select_all_location ?? 'toolbar');
?>
<div class="border-bottom bg-body-tertiary px-4 py-3">
  <form id="<?= smartcms_h($board_bulk_form_id) ?>" class="d-flex flex-wrap justify-content-between align-items-center gap-3" method="post" action="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>" data-board-bulk-form>
    <?= smartcms_csrf_input() ?>
    <input type="hidden" name="bulk_action" value="" data-board-bulk-action-input id="<?= smartcms_h($board_bulk_action_input_id) ?>">
    <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
    <input type="hidden" name="page" value="<?= (int)($pagination['page'] ?? 1) ?>">
    <input type="hidden" name="q" value="<?= smartcms_h((string)($pagination['keyword'] ?? '')) ?>">

    <?php if ($board_bulk_select_all_location !== 'header'): ?>
      <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" id="<?= smartcms_h($board_bulk_select_all_id) ?>" data-board-bulk-select-all form="<?= smartcms_h($board_bulk_form_id) ?>" aria-label="전체 선택">
        <label class="form-check-label fw-bold" for="<?= smartcms_h($board_bulk_select_all_id) ?>">전체 선택</label>
      </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2 ms-auto">
      <button type="button" class="btn btn-danger fw-bold" data-board-bulk-action="delete">
        선택삭제
      </button>
      <button type="button" class="btn btn-primary fw-bold" data-board-bulk-action="move" <?= $board_bulk_targets ? '' : 'disabled' ?>>
        이동
      </button>
      <button type="button" class="btn btn-secondary fw-bold" data-board-bulk-action="copy" <?= $board_bulk_targets ? '' : 'disabled' ?>>
        복사
      </button>
    </div>
  </form>
</div>

<div class="modal fade" id="<?= smartcms_h($board_bulk_modal_id) ?>" tabindex="-1" aria-labelledby="<?= smartcms_h($board_bulk_modal_title_id) ?>" aria-hidden="true" data-board-bulk-modal>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="<?= smartcms_h($board_bulk_modal_title_id) ?>">확인</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="닫기"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3 text-dark fw-medium" id="<?= smartcms_h($board_bulk_modal_body_id) ?>"></p>
        <div class="mb-0">
          <label class="form-label fw-bold" for="<?= smartcms_h($board_bulk_modal_target_id) ?>">대상 게시판</label>
          <select class="form-select fw-semibold" id="<?= smartcms_h($board_bulk_modal_target_id) ?>" name="target_board" form="<?= smartcms_h($board_bulk_form_id) ?>" aria-label="대상 게시판 선택">
            <option value="">대상 게시판을 선택하세요.</option>
            <?php if ($board_bulk_targets): ?>
              <?php foreach ($board_bulk_targets as $target_board): ?>
                <option value="<?= smartcms_h((string)$target_board['board_key']) ?>">
                  <?= smartcms_h((string)$target_board['board_name']) ?>
                </option>
              <?php endforeach; ?>
            <?php else: ?>
              <option value="" disabled>이동/복사 가능한 대상 게시판이 없습니다.</option>
            <?php endif; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn btn-primary fw-bold" id="<?= smartcms_h($board_bulk_modal_submit_id) ?>">확인</button>
      </div>
    </div>
  </div>
</div>
