<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') {
  json_error('POST 요청만 허용됩니다.', 405);
}

$input = get_json_input();

$db = validate_identifier($input['db'] ?? '', 'DB명');
$table = validate_identifier($input['table'] ?? '', '테이블명');
$mode = $input['mode'] ?? 'insert';
$formData = $input['form_data'] ?? [];
$pkValue = $input['pk_value'] ?? null;

if (!is_assoc_array($formData)) {
  json_error('form_data 형식이 올바르지 않습니다.');
}

$conn = db_connect($db);
$columns = get_table_columns($conn, $table);
$primaryKey = get_primary_key($columns);

if (!$primaryKey) {
  db_close($conn);
  json_error('단일 PK 없는 테이블은 현재 지원하지 않습니다.');
}

$columnMap = [];
foreach ($columns as $col) {
  $columnMap[$col['field']] = $col;
}

if ($mode === 'insert') {
  $fields = [];
  $values = [];

  foreach ($columnMap as $field => $col) {
    if ($col['is_auto_increment']) continue;
    if (!array_key_exists($field, $formData)) continue;

    $value = normalize_value_by_column($col, $formData[$field]);

    $fields[] = db_escape_identifier($field);
    $values[] = db_escape_value($conn, $value);
  }

  if (empty($fields)) {
    db_close($conn);
    json_error('저장할 데이터가 없습니다.');
  }

  $sql = "INSERT INTO " . db_escape_identifier($table)
    . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";

  $result = mysqli_query($conn, $sql);

  if (!$result) {
    db_close($conn);
    json_error('등록 실패: ' . mysqli_error($conn), 500);
  }

  $insertId = mysqli_insert_id($conn);
  db_close($conn);

  json_success([
    'insert_id' => $insertId
  ], '등록 완료');
}

if ($mode === 'update') {
  if ($pkValue === null || $pkValue === '') {
    db_close($conn);
    json_error('PK 값이 필요합니다.');
  }

  $sets = [];

  foreach ($columnMap as $field => $col) {
    if ($field === $primaryKey) continue;
    if (!array_key_exists($field, $formData)) continue;

    $value = normalize_value_by_column($col, $formData[$field]);
    $sets[] = db_escape_identifier($field) . " = " . db_escape_value($conn, $value);
  }

  if (empty($sets)) {
    db_close($conn);
    json_error('수정할 데이터가 없습니다.');
  }

  $sql = "UPDATE " . db_escape_identifier($table)
    . " SET " . implode(', ', $sets)
    . " WHERE " . db_escape_identifier($primaryKey) . " = " . db_escape_value($conn, $pkValue)
    . " LIMIT 1";

  $result = mysqli_query($conn, $sql);

  if (!$result) {
    db_close($conn);
    json_error('수정 실패: ' . mysqli_error($conn), 500);
  }

  db_close($conn);
  json_success([], '수정 완료');
}

db_close($conn);
json_error('지원하지 않는 mode 입니다.');
