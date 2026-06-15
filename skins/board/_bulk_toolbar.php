<?php
if (empty($board_bulk_can_manage)) {
    return;
}

$board_bulk_form_id = (string)($board_bulk_form_id ?? 'boardBulkForm');
$board_bulk_select_all_id = (string)($board_bulk_select_all_id ?? ($board_bulk_form_id . '_all'));
$board_bulk_targets = is_array($board_bulk_targets ?? null) ? $board_bulk_targets : [];
$board_bulk_target_label = '대상 게시판 선택';
?>
<div class="border-bottom bg-body-tertiary px-4 py-3">
  <form id="<?= smartcms_h($board_bulk_form_id) ?>" class="row g-2 align-items-center" method="post" data-board-bulk-form>
    <?= smartcms_csrf_input() ?>
    <input type="hidden" name="board" value="<?= smartcms_h($board['board_key']) ?>">
    <input type="hidden" name="page" value="<?= (int)($pagination['page'] ?? 1) ?>">
    <input type="hidden" name="q" value="<?= smartcms_h((string)($pagination['keyword'] ?? '')) ?>">

    <div class="col-12 col-lg-auto">
      <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" id="<?= smartcms_h($board_bulk_select_all_id) ?>" data-board-bulk-select-all>
        <label class="form-check-label fw-bold" for="<?= smartcms_h($board_bulk_select_all_id) ?>">전체 선택</label>
      </div>
    </div>

    <div class="col-12 col-lg">
      <select class="form-select fw-semibold" name="target_board" aria-label="대상 게시판 선택">
        <option value=""><?= smartcms_h($board_bulk_target_label) ?></option>
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

    <div class="col-12 col-lg-auto">
      <div class="d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-danger fw-bold" name="bulk_action" value="delete" data-board-bulk-action="delete">
          선택삭제
        </button>
        <button type="submit" class="btn btn-primary fw-bold" name="bulk_action" value="move" data-board-bulk-action="move" <?= $board_bulk_targets ? '' : 'disabled' ?>>
          이동
        </button>
        <button type="submit" class="btn btn-secondary fw-bold" name="bulk_action" value="copy" data-board-bulk-action="copy" <?= $board_bulk_targets ? '' : 'disabled' ?>>
          복사
        </button>
      </div>
    </div>
  </form>
</div>
