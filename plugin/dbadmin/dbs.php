<?php
require_once __DIR__ . '/common.php';

$mode = get_param('mode', 'dbs');

if ($mode === 'columns') {
  $db = validate_identifier(require_param('db', 'DB명이 필요합니다.'), 'DB명');
  $table = validate_identifier(require_param('table', '테이블명이 필요합니다.'), '테이블명');
  $conn = db_connect($db);
    $cols = get_table_columns($conn, $table);
  db_close($conn);
  json_success(['columns' => array_map(fn($c) => ['field' => $c['field'], 'type' => $c['type']], $cols)]);
}

if ($mode === 'tables') {
  $db = validate_identifier(require_param('db', 'DB명이 필요합니다.'), 'DB명');
  $conn = db_connect($db);

  $sql = "SHOW TABLE STATUS";
  $result = mysqli_query($conn, $sql);

  if (!$result) {
    db_close($conn);
    json_error('테이블 목록 조회 실패: ' . mysqli_error($conn), 500);
  }

  $tables = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = [
      'name' => $row['Name'],
      'rows' => -1,
      'engine' => $row['Engine'],
      'collation' => $row['Collation'],
      'comment' => $row['Comment']
    ];
  }
  mysqli_free_result($result);

  foreach ($tables as &$t) {
    $cntResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM `" . str_replace('`', '``', $t['name']) . "`");
    if ($cntResult) {
      $t['rows'] = (int)mysqli_fetch_assoc($cntResult)['cnt'];
      mysqli_free_result($cntResult);
    }
  }
  unset($t);

  db_close($conn);

  json_success([
    'tables' => $tables
  ], '테이블 목록 조회 완료');
}

$conn = db_connect();
$sql = "SHOW DATABASES";
$result = mysqli_query($conn, $sql);

if (!$result) {
  db_close($conn);
  json_error('DB 목록 조회 실패: ' . mysqli_error($conn), 500);
}

$list = [];
while ($row = mysqli_fetch_row($result)) {
  $dbName = $row[0];
  if (in_array($dbName, $GLOBALS['EXCLUDE_DATABASES'], true)) {
    continue;
  }
  $list[] = $dbName;
}

mysqli_free_result($result);
db_close($conn);

json_success([
  'databases' => $list
], 'DB 목록 조회 완료');
