<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') {
  json_error('POST 요청만 허용됩니다.', 405);
}

$input = get_json_input();
$action = $input['action'] ?? '';
$db = validate_identifier($input['db'] ?? '', 'DB명');
$table = validate_identifier($input['table'] ?? '', '테이블명');

$allowedActions = ['create', 'rename', 'drop'];
if (!in_array($action, $allowedActions, true)) {
  json_error('허용되지 않는 action입니다.');
}

$conn = db_connect(null);

if ($action === 'create') {
  $allowedEngines = ['InnoDB', 'MyISAM', 'MEMORY', 'ARCHIVE'];
  $engine = in_array($input['engine'] ?? '', $allowedEngines, true) ? $input['engine'] : 'InnoDB';
  $comment = $input['comment'] ?? '';
  $commentSql = $comment ? " COMMENT='" . mysqli_real_escape_string($conn, $comment) . "'" : '';

  $sql = "CREATE TABLE " . db_escape_identifier($db) . "." . db_escape_identifier($table)
    . " (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE={$engine}{$commentSql}";

  $result = mysqli_query($conn, $sql);
  if (!$result) {
    db_close($conn);
    json_error('테이블 생성 실패: ' . mysqli_error($conn), 500);
  }
  db_close($conn);
  json_success([], '테이블 생성 완료');
}

if ($action === 'rename') {
  $newName = validate_identifier($input['new_name'] ?? '', '새 테이블명');

  $sql = "RENAME TABLE " . db_escape_identifier($db) . "." . db_escape_identifier($table)
    . " TO " . db_escape_identifier($db) . "." . db_escape_identifier($newName);

  $result = mysqli_query($conn, $sql);
  if (!$result) {
    db_close($conn);
    json_error('테이블 이름 변경 실패: ' . mysqli_error($conn), 500);
  }
  db_close($conn);
  json_success([], '이름 변경 완료');
}

if ($action === 'drop') {
  $sql = "DROP TABLE " . db_escape_identifier($db) . "." . db_escape_identifier($table);

  $result = mysqli_query($conn, $sql);
  if (!$result) {
    db_close($conn);
    json_error('테이블 삭제 실패: ' . mysqli_error($conn), 500);
  }
  db_close($conn);
  json_success([], '테이블 삭제 완료');
}

db_close($conn);
json_error('처리 중 오류가 발생했습니다.');
