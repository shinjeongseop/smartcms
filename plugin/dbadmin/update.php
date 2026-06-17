<?php
require_once __DIR__ . '/common.php';

$db = validate_identifier(require_param('db', 'DB명이 필요합니다.'), 'DB명');
$table = validate_identifier(require_param('table', '테이블명이 필요합니다.'), '테이블명');
$pkValue = require_param('pk', 'PK 값이 필요합니다.');

$conn = db_connect($db);
$columns = get_table_columns($conn, $table);
$primaryKey = get_primary_key($columns);

if (!$primaryKey) {
  db_close($conn);
  exit('<div class="alert alert--danger">단일 PK 없는 테이블은 현재 지원하지 않습니다.</div>');
}

$sql = "SELECT * FROM " . db_escape_identifier($table)
  . " WHERE " . db_escape_identifier($primaryKey) . " = " . db_escape_value($conn, $pkValue)
  . " LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
  db_close($conn);
  exit('<div class="alert alert--danger">데이터 조회 실패</div>');
}

$row = mysqli_fetch_assoc($result);
mysqli_free_result($result);
db_close($conn);

if (!$row) {
  exit('<div class="alert alert--danger">데이터가 존재하지 않습니다.</div>');
}
?>
<div class="modal__header">
  <h2 class="modal__title">행 수정</h2>
  <button type="button" class="icon-btn" data-modal-close aria-label="닫기">×</button>
</div>

<form id="rowForm" data-mode="update" data-pk="<?= h($pkValue) ?>">
  <div class="modal__body">
    <div class="form-grid">
      <?php foreach ($columns as $col): ?>
        <?php
        $field = $col['field'];
        $inputType = get_field_input_type($field, $col['type']);
        $isPk = $col['is_primary'];
        $readonly = $isPk ? 'readonly disabled' : '';
        $value = $row[$field] ?? '';

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
            <?php if ($col['is_auto_increment']): ?>
              <span class="badge badge--secondary">AUTO</span>
            <?php endif; ?>
          </label>

          <?php if ($inputType === 'textarea'): ?>
            <textarea class="textarea <?= $isPk ? 'readonly-field' : '' ?>" name="<?= h($field) ?>" rows="4" <?= $readonly ?>><?= h($value) ?></textarea>
          <?php elseif ($inputType === 'yn'): ?>
            <select class="select <?= $isPk ? 'readonly-field' : '' ?>" name="<?= h($field) ?>" <?= $readonly ?>>
              <option value="Y" <?= $value === 'Y' ? 'selected' : '' ?>>Y</option>
              <option value="N" <?= $value === 'N' ? 'selected' : '' ?>>N</option>
            </select>
          <?php else: ?>
            <input type="<?= h($inputType) ?>" class="input <?= $isPk ? 'readonly-field' : '' ?>" name="<?= h($field) ?>" value="<?= h($value) ?>" <?= $readonly ?>>
          <?php endif; ?>

          <div class="help-text"><?= h($col['type']) ?><?= $col['comment'] ? ' / ' . h($col['comment']) : '' ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="modal__footer">
    <div class="modal__footer-note">테이블: <?= h($table) ?> / PK: <?= h($primaryKey ?? '-') ?></div>
    <button type="button" class="button button--ghost" data-modal-close>닫기</button>
    <button type="submit" class="button button--primary">저장</button>
  </div>
</form>
