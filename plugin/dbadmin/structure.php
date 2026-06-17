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

<!-- ── 컬럼 ──────────────────────────────────────────────────── -->
<div class="section-head">
  <h3>컬럼</h3>
  <button class="button button--sm button--success" id="btnAddColumn"
    data-db="<?= h($db) ?>" data-table="<?= h($table) ?>">+ 컬럼 추가</button>
</div>

<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr><th>컬럼명</th><th>타입</th><th>NULL</th><th>KEY</th><th>DEFAULT</th><th>EXTRA</th><th>COMMENT</th><th>관리</th></tr>
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
          <td>
            <?php if (!$col['is_primary']): ?>
              <div class="cluster">
                <button type="button" class="button button--xs button--ghost btn-modify-column"
                  data-db="<?= h($db) ?>" data-table="<?= h($table) ?>"
                  data-column="<?= h($col['field']) ?>" data-type="<?= h($col['type']) ?>"
                  data-null="<?= h($col['null']) ?>" data-default="<?= h($col['default'] ?? '') ?>"
                  data-comment="<?= h($col['comment']) ?>">수정</button>
                <button type="button" class="button button--xs button--danger btn-drop-column"
                  data-db="<?= h($db) ?>" data-table="<?= h($table) ?>"
                  data-column="<?= h($col['field']) ?>">삭제</button>
              </div>
            <?php else: ?>
              <span class="is-muted">PK</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ── 인덱스 ─────────────────────────────────────────────────── -->
<div class="section-head">
  <h3>인덱스</h3>
  <button class="button button--sm button--success" id="btnAddIndex"
    data-db="<?= h($db) ?>" data-table="<?= h($table) ?>"
    data-columns="<?= h(json_encode(array_map(fn($c) => $c['field'], $columns))) ?>">+ 인덱스 추가</button>
</div>

<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr><th>인덱스명</th><th>타입</th><th>컬럼</th><th>관리</th></tr>
    </thead>
    <tbody>
      <?php if (empty($indexes)): ?>
        <tr><td colspan="4" class="is-muted text-center">인덱스 없음</td></tr>
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
            <td>
              <?php if (!$isPrimary): ?>
                <button type="button" class="button button--xs button--danger btn-drop-index"
                  data-db="<?= h($db) ?>" data-table="<?= h($table) ?>"
                  data-index="<?= h($idxName) ?>">삭제</button>
              <?php else: ?>
                <span class="is-muted">PK</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ── 외래키 ─────────────────────────────────────────────────── -->
<div class="section-head">
  <h3>외래키 (FK)</h3>
  <button class="button button--sm button--success" id="btnAddFk"
    data-db="<?= h($db) ?>" data-table="<?= h($table) ?>"
    data-columns="<?= h(json_encode(array_map(fn($c) => $c['field'], $columns))) ?>">+ 외래키 추가</button>
</div>

<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr><th>컬럼</th><th>참조 테이블</th><th>참조 컬럼</th><th>제약명</th><th>관리</th></tr>
    </thead>
    <tbody>
      <?php if (empty($foreignKeys)): ?>
        <tr><td colspan="5" class="is-muted text-center">외래키 없음</td></tr>
      <?php else: ?>
        <?php foreach ($foreignKeys as $fk): ?>
          <tr>
            <td><?= h($fk['COLUMN_NAME']) ?></td>
            <td>
              <a href="#" class="fk-table-link"
                data-table="<?= h($fk['REFERENCED_TABLE_NAME']) ?>">
                <?= h($fk['REFERENCED_TABLE_NAME']) ?>
              </a>
            </td>
            <td><?= h($fk['REFERENCED_COLUMN_NAME']) ?></td>
            <td><span class="is-muted"><?= h($fk['CONSTRAINT_NAME']) ?></span></td>
            <td>
              <button type="button" class="button button--xs button--danger btn-drop-fk"
                data-db="<?= h($db) ?>" data-table="<?= h($table) ?>"
                data-constraint="<?= h($fk['CONSTRAINT_NAME']) ?>">삭제</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
