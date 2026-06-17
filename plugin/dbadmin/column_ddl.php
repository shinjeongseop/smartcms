<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') {
  json_error('POST 요청만 허용됩니다.', 405);
}

$input = get_json_input();
$action = $input['action'] ?? '';
$db = validate_identifier($input['db'] ?? '', 'DB명');
$table = validate_identifier($input['table'] ?? '', '테이블명');
$column = validate_identifier($input['column'] ?? '', '컬럼명');

$allowedActions = ['add', 'modify', 'drop'];
if (!in_array($action, $allowedActions, true)) {
  json_error('허용되지 않는 action입니다.');
}

function validate_column_type($type) {
  $simpleTypes = [
    'INT', 'BIGINT', 'TINYINT', 'SMALLINT', 'MEDIUMINT',
    'TEXT', 'MEDIUMTEXT', 'LONGTEXT',
    'DATE', 'DATETIME', 'TIMESTAMP', 'TIME',
    'BOOLEAN', 'JSON', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB',
    'FLOAT', 'DOUBLE',
  ];
  $typeUpper = strtoupper(trim($type));
  if (in_array($typeUpper, $simpleTypes, true)) return $typeUpper;
  if (preg_match('/^(VARCHAR|CHAR|DECIMAL|FLOAT|DOUBLE|TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)\(\d+(?:,\d+)?\)$/i', $typeUpper)) {
    return $typeUpper;
  }
  return false;
}

function is_datetime_like_type($type) {
  $type = strtoupper(trim($type));
  return strpos($type, 'DATETIME') !== false || strpos($type, 'TIMESTAMP') !== false;
}

function build_column_def($conn, $colName, $type, $nullAllow, $default, $onUpdateCurrentTimestamp, $comment) {
  $def = db_escape_identifier($colName) . ' ' . $type;
  $def .= $nullAllow ? ' NULL' : ' NOT NULL';

  if ($default !== null && $default !== '') {
    $defaultUpper = strtoupper(trim((string)$default));
    if (preg_match('/^CURRENT_TIMESTAMP(?:\(\d+\))?$/i', $defaultUpper) || $defaultUpper === 'NOW()') {
      $def .= ' DEFAULT ' . $defaultUpper;
    } else {
      $def .= " DEFAULT '" . mysqli_real_escape_string($conn, $default) . "'";
    }
  } elseif ($nullAllow) {
    $def .= ' DEFAULT NULL';
  }

  if ($onUpdateCurrentTimestamp) {
    $def .= ' ON UPDATE CURRENT_TIMESTAMP';
  }

  if ($comment) {
    $def .= " COMMENT '" . mysqli_real_escape_string($conn, $comment) . "'";
  }
  return $def;
}

$conn = db_connect($db);

if ($action === 'add') {
  $type = validate_column_type($input['type'] ?? '');
  if (!$type) { db_close($conn); json_error('허용되지 않는 컬럼 타입입니다.'); }

  $nullAllow = !empty($input['null_allow']);
  $default = $input['default'] ?? null;
  $onUpdateCurrentTimestamp = !empty($input['on_update_current_timestamp']);
  $comment = $input['comment'] ?? '';
  if ($onUpdateCurrentTimestamp && !is_datetime_like_type($type)) {
    db_close($conn);
    json_error('ON UPDATE CURRENT_TIMESTAMP는 DATETIME 또는 TIMESTAMP 컬럼에서만 사용할 수 있습니다.');
  }
  if ($onUpdateCurrentTimestamp && ($default === null || trim((string)$default) === '')) {
    $default = 'CURRENT_TIMESTAMP';
  }

  $colDef = build_column_def($conn, $column, $type, $nullAllow, $default, $onUpdateCurrentTimestamp, $comment);
  $sql = "ALTER TABLE " . db_escape_identifier($table) . " ADD COLUMN " . $colDef;

  $result = mysqli_query($conn, $sql);
  if (!$result) { db_close($conn); json_error('컬럼 추가 실패: ' . mysqli_error($conn), 500); }
  db_close($conn);
  json_success([], '컬럼 추가 완료');
}

if ($action === 'modify') {
  $newName = validate_identifier($input['new_name'] ?? $column, '새 컬럼명');
  $type = validate_column_type($input['type'] ?? '');
  if (!$type) { db_close($conn); json_error('허용되지 않는 컬럼 타입입니다.'); }

  $nullAllow = !empty($input['null_allow']);
  $default = $input['default'] ?? null;
  $onUpdateCurrentTimestamp = !empty($input['on_update_current_timestamp']);
  $comment = $input['comment'] ?? '';
  if ($onUpdateCurrentTimestamp && !is_datetime_like_type($type)) {
    db_close($conn);
    json_error('ON UPDATE CURRENT_TIMESTAMP는 DATETIME 또는 TIMESTAMP 컬럼에서만 사용할 수 있습니다.');
  }
  if ($onUpdateCurrentTimestamp && ($default === null || trim((string)$default) === '')) {
    $default = 'CURRENT_TIMESTAMP';
  }

  $colDef = build_column_def($conn, $newName, $type, $nullAllow, $default, $onUpdateCurrentTimestamp, $comment);
  $sql = "ALTER TABLE " . db_escape_identifier($table)
    . " CHANGE COLUMN " . db_escape_identifier($column) . " " . $colDef;

  $result = mysqli_query($conn, $sql);
  if (!$result) { db_close($conn); json_error('컬럼 수정 실패: ' . mysqli_error($conn), 500); }
  db_close($conn);
  json_success([], '컬럼 수정 완료');
}

if ($action === 'drop') {
  $sql = "ALTER TABLE " . db_escape_identifier($table) . " DROP COLUMN " . db_escape_identifier($column);

  $result = mysqli_query($conn, $sql);
  if (!$result) { db_close($conn); json_error('컬럼 삭제 실패: ' . mysqli_error($conn), 500); }
  db_close($conn);
  json_success([], '컬럼 삭제 완료');
}

db_close($conn);
json_error('처리 중 오류가 발생했습니다.');
