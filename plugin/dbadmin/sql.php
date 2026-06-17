<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') {
  json_error('POST 요청만 허용됩니다.', 405);
}

$input = get_json_input();
$db = validate_identifier($input['db'] ?? '', 'DB명');
$sql = trim($input['sql'] ?? '');

if (!$sql) {
  json_error('SQL을 입력하세요.');
}

foreach ($GLOBALS['BLOCKED_SQL_PATTERNS'] as $pattern) {
  if (preg_match($pattern, $sql)) {
    json_error('허용되지 않는 SQL 패턴입니다.');
  }
}

$conn = db_connect($db);
$result = mysqli_query($conn, $sql);

if ($result === false) {
  db_close($conn);
  json_error('SQL 실행 실패: ' . mysqli_error($conn), 500);
}

if ($result === true) {
  $affectedRows = mysqli_affected_rows($conn);
  db_close($conn);
  json_success(['type' => 'execute', 'affected_rows' => $affectedRows]);
}

$columns = [];
$numFields = mysqli_num_fields($result);
for ($i = 0; $i < $numFields; $i++) {
  $columns[] = mysqli_fetch_field_direct($result, $i)->name;
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
  $rows[] = $row;
}
mysqli_free_result($result);
db_close($conn);

json_success(['type' => 'select', 'columns' => $columns, 'rows' => $rows]);
