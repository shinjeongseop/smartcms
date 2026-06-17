<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') json_error('POST 요청만 허용됩니다.', 405);

$input  = get_json_input();
$action = $input['action'] ?? '';
$db     = validate_identifier($input['db'] ?? '', 'DB명');
$table  = validate_identifier($input['table'] ?? '', '테이블명');

if (!in_array($action, ['add', 'drop'], true)) json_error('허용되지 않는 action입니다.');

$allowedRefActions = ['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION'];

$conn = db_connect($db);

if ($action === 'add') {
  $constraintName = validate_identifier($input['constraint_name'] ?? '', '제약명');
  $column         = validate_identifier($input['column'] ?? '', '컬럼명');
  $refTable       = validate_identifier($input['ref_table'] ?? '', '참조 테이블명');
  $refColumn      = validate_identifier($input['ref_column'] ?? '', '참조 컬럼명');
  $onDelete       = in_array($input['on_delete'] ?? '', $allowedRefActions, true) ? $input['on_delete'] : 'RESTRICT';
  $onUpdate       = in_array($input['on_update'] ?? '', $allowedRefActions, true) ? $input['on_update'] : 'RESTRICT';

  $sql = "ALTER TABLE " . db_escape_identifier($table)
    . " ADD CONSTRAINT " . db_escape_identifier($constraintName)
    . " FOREIGN KEY (" . db_escape_identifier($column) . ")"
    . " REFERENCES " . db_escape_identifier($refTable) . " (" . db_escape_identifier($refColumn) . ")"
    . " ON DELETE {$onDelete} ON UPDATE {$onUpdate}";

  $result = mysqli_query($conn, $sql);
  if (!$result) { db_close($conn); json_error('외래키 추가 실패: ' . mysqli_error($conn), 500); }
  db_close($conn);
  json_success([], '외래키 추가 완료');
}

if ($action === 'drop') {
  $constraintName = validate_identifier($input['constraint_name'] ?? '', '제약명');

  $sql = "ALTER TABLE " . db_escape_identifier($table)
    . " DROP FOREIGN KEY " . db_escape_identifier($constraintName);

  $result = mysqli_query($conn, $sql);
  if (!$result) { db_close($conn); json_error('외래키 삭제 실패: ' . mysqli_error($conn), 500); }
  db_close($conn);
  json_success([], '외래키 삭제 완료');
}

db_close($conn);
json_error('처리 중 오류가 발생했습니다.');
