<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') {
  json_error('POST 요청만 허용됩니다.', 405);
}

$input = get_json_input();

$db = validate_identifier($input['db'] ?? '', 'DB명');
$table = validate_identifier($input['table'] ?? '', '테이블명');
$pkValue = $input['pk_value'] ?? null;

if ($pkValue === null || $pkValue === '') {
  json_error('PK 값이 필요합니다.');
}

$conn = db_connect($db);
$columns = get_table_columns($conn, $table);
$primaryKey = get_primary_key($columns);

if (!$primaryKey) {
  db_close($conn);
  json_error('단일 PK 없는 테이블은 현재 지원하지 않습니다.');
}

$sql = "DELETE FROM " . db_escape_identifier($table)
  . " WHERE " . db_escape_identifier($primaryKey) . " = " . db_escape_value($conn, $pkValue)
  . " LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result) {
  db_close($conn);
  json_error('삭제 실패: ' . mysqli_error($conn), 500);
}

db_close($conn);
json_success([], '삭제 완료');
