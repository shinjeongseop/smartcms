<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') json_error('POST 요청만 허용됩니다.', 405);

$input  = get_json_input();
$action = $input['action'] ?? '';
$db     = validate_identifier($input['db'] ?? '', 'DB명');
$table  = validate_identifier($input['table'] ?? '', '테이블명');

if (!in_array($action, ['add', 'drop'], true)) json_error('허용되지 않는 action입니다.');

$conn = db_connect($db);

if ($action === 'add') {
  $indexName = validate_identifier($input['index_name'] ?? '', '인덱스명');
  $cols      = $input['columns'] ?? [];
  $unique    = !empty($input['unique']);

  if (empty($cols) || !is_array($cols)) { db_close($conn); json_error('컬럼을 선택하세요.'); }

  $escapedCols = [];
  foreach ($cols as $c) {
    $escapedCols[] = db_escape_identifier(validate_identifier($c, '컬럼명'));
  }

  $uniqueKw = $unique ? 'UNIQUE ' : '';
  $sql = "CREATE {$uniqueKw}INDEX " . db_escape_identifier($indexName)
    . " ON " . db_escape_identifier($table)
    . " (" . implode(', ', $escapedCols) . ")";

  $result = mysqli_query($conn, $sql);
  if (!$result) { db_close($conn); json_error('인덱스 생성 실패: ' . mysqli_error($conn), 500); }
  db_close($conn);
  json_success([], '인덱스 생성 완료');
}

if ($action === 'drop') {
  $indexName = validate_identifier($input['index_name'] ?? '', '인덱스명');
  $sql = "DROP INDEX " . db_escape_identifier($indexName)
    . " ON " . db_escape_identifier($table);

  $result = mysqli_query($conn, $sql);
  if (!$result) { db_close($conn); json_error('인덱스 삭제 실패: ' . mysqli_error($conn), 500); }
  db_close($conn);
  json_success([], '인덱스 삭제 완료');
}

db_close($conn);
json_error('처리 중 오류가 발생했습니다.');
