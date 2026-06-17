<?php
require_once __DIR__ . '/common.php';

$db    = validate_identifier(require_param('db', 'DB명이 필요합니다.'), 'DB명');
$table = validate_identifier(require_param('table', '테이블명이 필요합니다.'), '테이블명');
$searchCol = get_param('search_col', '__all__');
$searchKw  = get_param('search_keyword', '');
$orderCol  = get_param('order_col', '');
$orderDir  = strtoupper(get_param('order_dir', 'DESC'));
if (!in_array($orderDir, ['ASC', 'DESC'], true)) $orderDir = 'DESC';

$conn        = db_connect($db);
$columnsMeta = get_table_columns($conn, $table);
$primaryKey  = get_primary_key($columnsMeta);
$validCols   = array_map(fn($c) => $c['field'], $columnsMeta);

$whereSql = '';
if ($searchKw !== '') {
  if ($searchCol === '__all__') {
    $conds = [];
    foreach ($columnsMeta as $col) {
      if (!preg_match('/^(int|bigint|tinyint|smallint|mediumint|float|double|decimal)/i', $col['type'])) {
        $conds[] = db_escape_identifier($col['field']) . " LIKE '%" . mysqli_real_escape_string($conn, $searchKw) . "%'";
      }
    }
    if ($conds) $whereSql = ' WHERE ' . implode(' OR ', $conds);
  } elseif (in_array($searchCol, $validCols, true)) {
    $whereSql = ' WHERE ' . db_escape_identifier($searchCol) . " LIKE '%" . mysqli_real_escape_string($conn, $searchKw) . "%'";
  }
}

$orderSql = '';
if ($orderCol && in_array($orderCol, $validCols, true)) {
  $orderSql = ' ORDER BY ' . db_escape_identifier($orderCol) . ' ' . $orderDir;
} elseif ($primaryKey) {
  $orderSql = ' ORDER BY ' . db_escape_identifier($primaryKey) . ' DESC';
}

$sql    = "SELECT * FROM " . db_escape_identifier($table) . $whereSql . $orderSql;
$result = mysqli_query($conn, $sql);
if (!$result) { db_close($conn); die('조회 실패: ' . mysqli_error($conn)); }

$filename = $db . '_' . $table . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache');

$fp = fopen('php://output', 'w');
fputs($fp, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

fputcsv($fp, $validCols);
while ($row = mysqli_fetch_assoc($result)) {
  fputcsv($fp, array_map(fn($v) => $v ?? 'NULL', $row));
}

fclose($fp);
mysqli_free_result($result);
db_close($conn);
