<?php
if (!defined('MYADMIN_ACCESS')) {
    exit;
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/common.php';
function db_connect($database = null) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, $database, DB_PORT);
    if (!$conn) { json_error('DB 연결 실패: ' . mysqli_connect_error(), 500); }
    mysqli_set_charset($conn, DB_CHARSET);
    return $conn;
}
function db_close($conn){ if($conn){ mysqli_close($conn); } }
function db_escape_identifier($name){ return '`' . str_replace('`', '``', $name) . '`'; }
function db_escape_value($conn,$value){ if($value===null){ return 'NULL'; } return "'" . mysqli_real_escape_string($conn,(string)$value) . "'"; }
