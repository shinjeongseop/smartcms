<?php
require_once __DIR__ . '/common.php';

if (request_method() !== 'POST') json_error('POST 요청만 허용됩니다.', 405);

$db    = validate_identifier($_POST['db'] ?? '', 'DB명');
$table = validate_identifier($_POST['table'] ?? '', '테이블명');

if (empty($_FILES['csv_file']['tmp_name'])) json_error('CSV 파일이 없습니다.');

$fp = fopen($_FILES['csv_file']['tmp_name'], 'r');
if (!$fp) json_error('파일 읽기 실패.');

// BOM 제거
$bom = fread($fp, 3);
if ($bom !== "\xEF\xBB\xBF") rewind($fp);

$conn        = db_connect($db);
$columnsMeta = get_table_columns($conn, $table);
$colMap      = [];
foreach ($columnsMeta as $c) $colMap[$c['field']] = $c;
$primaryKey  = get_primary_key($columnsMeta);

// 첫 행 = 헤더
$headers = fgetcsv($fp);
if (!$headers) { fclose($fp); db_close($conn); json_error('CSV 헤더를 읽을 수 없습니다.'); }
$headers = array_map('trim', $headers);

// 헤더 검증
$validFields = [];
foreach ($headers as $h) {
  if (isset($colMap[$h])) $validFields[] = $h;
}
if (empty($validFields)) { fclose($fp); db_close($conn); json_error('일치하는 컬럼이 없습니다.'); }

// auto_increment / PK 제외
$insertFields = array_filter($validFields, fn($f) => !($colMap[$f]['is_auto_increment'] ?? false));
if (empty($insertFields)) { fclose($fp); db_close($conn); json_error('삽입할 컬럼이 없습니다.'); }

$headerIndex = array_flip($headers);
$inserted = 0; $skipped = 0;

while (($row = fgetcsv($fp)) !== false) {
  if (count($row) < count($headers)) { $skipped++; continue; }

  $fields = [];
  $values = [];
  foreach ($insertFields as $field) {
    $idx = $headerIndex[$field];
    $raw = $row[$idx] ?? '';
    $value = ($raw === 'NULL' || $raw === '') && ($colMap[$field]['null'] === 'YES') ? null : $raw;
    $value = normalize_value_by_column($colMap[$field], $value);
    $fields[] = db_escape_identifier($field);
    $values[] = db_escape_value($conn, $value);
  }

  $sql = "INSERT INTO " . db_escape_identifier($table)
    . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";

  if (mysqli_query($conn, $sql)) {
    $inserted++;
  } else {
    $skipped++;
  }
}

fclose($fp);
db_close($conn);
json_success(['inserted' => $inserted, 'skipped' => $skipped],
  "{$inserted}건 삽입 완료" . ($skipped ? ", {$skipped}건 건너뜀" : ''));
