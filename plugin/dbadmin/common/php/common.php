<?php
if (!defined('MYADMIN_ACCESS')) {
    exit;
}
require_once __DIR__ . '/config.php';
function json_response($data = [], $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function json_success($data = [], $message = 'OK') {
    json_response(['success'=>true,'message'=>$message,'data'=>$data]);
}
function json_error($message = '오류가 발생했습니다.', $status = 400, $extra = []) {
    json_response(['success'=>false,'message'=>$message,'data'=>$extra], $status);
}
function request_method(){ return $_SERVER['REQUEST_METHOD'] ?? 'GET'; }
function get_json_input(){ $raw=file_get_contents('php://input'); if(!$raw)return[]; $json=json_decode($raw,true); return is_array($json)?$json:[]; }
function get_param($key,$default=null){ return $_GET[$key] ?? $_POST[$key] ?? $default; }
function require_param($key,$message=null){ $value=get_param($key); if($value===null||$value===''){ json_error($message ?: ($key.' 값이 필요합니다.')); } return $value; }
function validate_identifier($value,$label='식별자'){ if(!preg_match('/^[a-zA-Z0-9_]+$/',$value)){ json_error($label.' 형식이 올바르지 않습니다.'); } return $value; }
function normalize_datetime_local_to_mysql($value){ if($value===null||$value==='') return null; return str_replace('T',' ',$value).(strlen($value)===16?':00':''); }
function mysql_datetime_to_local($value){ if(!$value) return ''; return str_replace(' ','T',substr($value,0,16)); }
function is_assoc_array($arr){ if(!is_array($arr)) return false; return array_keys($arr)!==range(0,count($arr)-1); }
function h($str){ return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
