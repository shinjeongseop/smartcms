<?php
require_once __DIR__ . '/common.php';

$db = validate_identifier(require_param('db', 'DB명이 필요합니다.'), 'DB명');
$table = validate_identifier(require_param('table', '테이블명이 필요합니다.'), '테이블명');

$conn = db_connect($db);
$columns = get_table_columns($conn, $table);
$primaryKey = get_primary_key($columns);
db_close($conn);
?>
<div class="modal__header">
  <h2 class="modal__title">행 추가</h2>
  <button type="button" class="icon-btn" data-modal-close aria-label="닫기">×</button>
</div>

<form id="rowForm" data-mode="insert">
  <div class="modal__body">
    <div class="form-grid">
      <?php foreach ($columns as $col): ?>
        <?php
        if ($col['is_auto_increment']) continue;
        $field = $col['field'];
        $inputType = get_field_input_type($field, $col['type']);
        $isPk = $col['is_primary'];
        $value = $col['default'] ?? '';
        if ($inputType === 'datetime-local' && $value) {
          $value = mysql_datetime_to_local($value);
        }
        ?>
        <div class="field">
          <label class="field__label">
            <?= h($field) ?>
            <?php if ($isPk): ?>
              <span class="badge badge--primary">PK</span>
            <?php endif; ?>
          </label>

          <?php if ($inputType === 'textarea'): ?>
            <textarea class="textarea" name="<?= h($field) ?>" rows="4"><?= h($value) ?></textarea>
          <?php elseif ($inputType === 'yn'): ?>
            <select class="select" name="<?= h($field) ?>">
              <option value="Y">Y</option>
              <option value="N" selected>N</option>
            </select>
          <?php else: ?>
            <input type="<?= h($inputType) ?>" class="input" name="<?= h($field) ?>" value="<?= h($value) ?>">
          <?php endif; ?>

          <div class="help-text"><?= h($col['type']) ?><?= $col['comment'] ? ' / ' . h($col['comment']) : '' ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="modal__footer">
    <div class="modal__footer-note">테이블: <?= h($table) ?></div>
    <button type="button" class="button button--ghost" data-modal-close>닫기</button>
    <button type="submit" class="button button--primary">저장</button>
  </div>
</form>
