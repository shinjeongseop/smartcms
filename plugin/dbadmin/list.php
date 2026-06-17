<?php
require_once __DIR__ . '/common.php';

$db = validate_identifier(require_param('db', 'DB명이 필요합니다.'), 'DB명');
$table = validate_identifier(require_param('table', '테이블명이 필요합니다.'), '테이블명');
$page = max(1, (int)get_param('page', 1));
$pageSize = max(1, min(500, (int)get_param('page_size', 50)));
$offset = ($page - 1) * $pageSize;
$searchCol = get_param('search_col', '__all__');
$searchKw  = get_param('search_keyword', '');
$orderCol  = get_param('order_col', '');
$orderDir  = strtoupper(get_param('order_dir', 'DESC'));
if (!in_array($orderDir, ['ASC', 'DESC'], true)) $orderDir = 'DESC';

$conn = db_connect($db);
$columnsMeta = get_table_columns($conn, $table);
$primaryKey  = get_primary_key($columnsMeta);
$columns     = array_map(fn($c) => $c['field'], $columnsMeta);
$validCols   = $columns;

// ── WHERE (search) ────────────────────────────────────────────
$whereSql = '';
if ($searchKw !== '') {
  if ($searchCol === '__all__') {
    $conditions = [];
    foreach ($columnsMeta as $col) {
      $t = strtolower($col['type']);
      // skip pure numeric types
      if (!preg_match('/^(int|bigint|tinyint|smallint|mediumint|float|double|decimal)/i', $t)) {
        $conditions[] = db_escape_identifier($col['field'])
          . " LIKE '%" . mysqli_real_escape_string($conn, $searchKw) . "%'";
      }
    }
    if ($conditions) $whereSql = ' WHERE ' . implode(' OR ', $conditions);
  } elseif (in_array($searchCol, $validCols, true)) {
    $whereSql = ' WHERE ' . db_escape_identifier($searchCol)
      . " LIKE '%" . mysqli_real_escape_string($conn, $searchKw) . "%'";
  }
}

// ── ORDER BY ──────────────────────────────────────────────────
if ($orderCol && in_array($orderCol, $validCols, true)) {
  $orderSql = ' ORDER BY ' . db_escape_identifier($orderCol) . ' ' . $orderDir;
} elseif ($primaryKey) {
  $orderCol = $primaryKey;
  $orderSql = ' ORDER BY ' . db_escape_identifier($primaryKey) . ' DESC';
  $orderDir = 'DESC';
} else {
  $orderSql = '';
}

// ── COUNT ─────────────────────────────────────────────────────
$countSql = "SELECT COUNT(*) AS cnt FROM " . db_escape_identifier($table) . $whereSql;
$countResult = mysqli_query($conn, $countSql);
if (!$countResult) { db_close($conn); exit('<div class="alert alert--danger">건수 조회 실패</div>'); }
$total = (int)mysqli_fetch_assoc($countResult)['cnt'];
mysqli_free_result($countResult);

// ── DATA ──────────────────────────────────────────────────────
$dataSql = "SELECT * FROM " . db_escape_identifier($table) . $whereSql . $orderSql
  . " LIMIT {$offset}, {$pageSize}";
$dataResult = mysqli_query($conn, $dataSql);
if (!$dataResult) { db_close($conn); exit('<div class="alert alert--danger">데이터 조회 실패</div>'); }

$rows = [];
while ($row = mysqli_fetch_assoc($dataResult)) $rows[] = $row;
mysqli_free_result($dataResult);
db_close($conn);

$totalPages = (int)ceil($total / $pageSize);

function sortIcon($col, $orderCol, $orderDir) {
  if ($col !== $orderCol) return '<span class="sort-icon">↕</span>';
  return $orderDir === 'ASC' ? '↑' : '↓';
}
?>

<div class="data-table-wrap">
  <table class="data-table" id="dataTable">
    <thead>
      <tr>
        <?php foreach ($columns as $col): ?>
          <th>
            <a href="#" class="sort-header"
              data-col="<?= h($col) ?>" data-dir="<?= ($orderCol === $col && $orderDir === 'ASC') ? 'DESC' : 'ASC' ?>">
              <?= h($col) ?> <?= sortIcon($col, $orderCol, $orderDir) ?>
            </a>
          </th>
        <?php endforeach; ?>
        <th class="sticky-action-col">관리</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="<?= count($columns) + 1 ?>" class="text-center is-muted">데이터 없음</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($columns as $col): ?>
              <td class="cell-pre">
                <?php if ($row[$col] === null): ?>
                  <span class="badge badge--null">NULL</span>
                <?php else: ?>
                  <?= h($row[$col]) ?>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
            <td class="sticky-action-col">
              <div class="cluster">
                <button type="button" class="button button--xs button--ghost btn-edit-row"
                  data-pk="<?= h($row[$primaryKey] ?? '') ?>">수정</button>
                <button type="button" class="button button--xs button--danger btn-delete-row"
                  data-pk="<?= h($row[$primaryKey] ?? '') ?>">삭제</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="section-head">
  <div class="is-muted">
    총 <?= number_format($total) ?>건 / 페이지 <?= $page ?> / <?= max(1, $totalPages) ?>
    <?php if ($searchKw): ?>
      <span class="badge badge--info">검색: <?= h($searchKw) ?></span>
    <?php endif; ?>
  </div>
  <div class="cluster">
    <button class="button button--sm button--ghost" id="btnPrevPage" <?= $page <= 1 ? 'disabled' : '' ?>>이전</button>
    <button class="button button--sm button--ghost" id="btnNextPage" <?= $page >= $totalPages ? 'disabled' : '' ?>>다음</button>
  </div>
</div>
