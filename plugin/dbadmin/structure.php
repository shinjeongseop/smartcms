<?php
require_once __DIR__ . '/common.php';

$db    = validate_identifier(require_param('db', 'DB명이 필요합니다.'), 'DB명');
$table = validate_identifier(require_param('table', '테이블명이 필요합니다.'), '테이블명');

$conn = db_connect($db);
$columns = get_table_columns($conn, $table);

// ── 인덱스 ─────────────────────────────────────────────────────
$idxResult = mysqli_query($conn, "SHOW INDEX FROM " . db_escape_identifier($table));
$indexes = [];
if ($idxResult) {
  while ($row = mysqli_fetch_assoc($idxResult)) {
    $indexes[$row['Key_name']][] = $row;
  }
  mysqli_free_result($idxResult);
}

// ── 외래키 ─────────────────────────────────────────────────────
$fkSql = "SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = '" . mysqli_real_escape_string($conn, $db) . "'
  AND TABLE_NAME = '" . mysqli_real_escape_string($conn, $table) . "'
  AND REFERENCED_TABLE_NAME IS NOT NULL";
$fkResult = mysqli_query($conn, $fkSql);
$foreignKeys = [];
if ($fkResult) {
  while ($row = mysqli_fetch_assoc($fkResult)) $foreignKeys[] = $row;
  mysqli_free_result($fkResult);
}

db_close($conn);
?>

<div class="section-head">
  <h3>컬럼</h3>
</div>
<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr><th>컬럼명</th><th>타입</th><th>NULL</th><th>KEY</th><th>DEFAULT</th><th>EXTRA</th><th>COMMENT</th></tr>
    </thead>
    <tbody>
      <?php foreach ($columns as $col): ?>
        <tr>
          <td><?= h($col['field']) ?></td>
          <td><?= h($col['type']) ?></td>
          <td><?= h($col['null']) ?></td>
          <td><?= h($col['key']) ?></td>
          <td><?= $col['default'] === null ? '<span class="badge badge--null">NULL</span>' : h($col['default']) ?></td>
          <td><?= h($col['extra']) ?></td>
          <td><?= h($col['comment']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="section-head">
  <h3>인덱스</h3>
</div>
<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr><th>인덱스명</th><th>타입</th><th>컬럼</th></tr>
    </thead>
    <tbody>
      <?php if (empty($indexes)): ?>
        <tr><td colspan="3" class="is-muted text-center">인덱스 없음</td></tr>
      <?php else: ?>
        <?php foreach ($indexes as $idxName => $idxCols): ?>
          <?php
          $isUnique = ($idxCols[0]['Non_unique'] == 0);
          $isPrimary = ($idxName === 'PRIMARY');
          $colNames = implode(', ', array_map(fn($r) => $r['Column_name'], $idxCols));
          ?>
          <tr>
            <td><?= h($idxName) ?></td>
            <td>
              <?php if ($isPrimary): ?>
                <span class="badge badge--primary">PRIMARY</span>
              <?php elseif ($isUnique): ?>
                <span class="badge badge--warning">UNIQUE</span>
              <?php else: ?>
                <span class="badge badge--secondary">INDEX</span>
              <?php endif; ?>
            </td>
            <td><?= h($colNames) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="section-head">
  <h3>외래키 (FK)</h3>
</div>
<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr><th>컬럼</th><th>참조 테이블</th><th>참조 컬럼</th><th>제약명</th></tr>
    </thead>
    <tbody>
      <?php if (empty($foreignKeys)): ?>
        <tr><td colspan="4" class="is-muted text-center">외래키 없음</td></tr>
      <?php else: ?>
        <?php foreach ($foreignKeys as $fk): ?>
          <tr>
            <td><?= h($fk['COLUMN_NAME']) ?></td>
            <td><?= h($fk['REFERENCED_TABLE_NAME']) ?></td>
            <td><?= h($fk['REFERENCED_COLUMN_NAME']) ?></td>
            <td><span class="is-muted"><?= h($fk['CONSTRAINT_NAME']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
