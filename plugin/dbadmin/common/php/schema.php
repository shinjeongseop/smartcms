<?php
if (!defined('MYADMIN_ACCESS')) {
    exit;
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/common.php';
function get_table_columns($conn,$table){
    $sql = "SHOW FULL COLUMNS FROM " . db_escape_identifier($table);
    $result = mysqli_query($conn,$sql);
    if(!$result){ json_error('테이블 구조 조회 실패: ' . mysqli_error($conn), 500); }
    $columns = [];
    while($row = mysqli_fetch_assoc($result)){
        $columns[] = [
            'field'=>$row['Field'],'type'=>$row['Type'],'null'=>$row['Null'],'key'=>$row['Key'],
            'default'=>$row['Default'],'extra'=>$row['Extra'],'comment'=>$row['Comment'],
            'is_primary'=>$row['Key']==='PRI','is_auto_increment'=>stripos($row['Extra'],'auto_increment')!==false
        ];
    }
    mysqli_free_result($result);
    return $columns;
}
function get_primary_key($columns){ foreach($columns as $col){ if(!empty($col['is_primary'])) return $col['field']; } return null; }
function get_field_input_type($field,$type){
    $field = strtolower($field); $type = strtolower($type);
    if(in_array($field,['use_yn','del_yn','view_yn'],true)) return 'yn';
    if(strpos($type,'text')!==false) return 'textarea';
    if(strpos($type,'datetime')!==false || strpos($type,'timestamp')!==false) return 'datetime-local';
    if(preg_match('/^date\b/',$type)) return 'date';
    if(strpos($type,'int')!==false || strpos($type,'decimal')!==false || strpos($type,'float')!==false || strpos($type,'double')!==false) return 'number';
    return 'text';
}
function normalize_value_by_column($column,$value){
    $type = strtolower($column['type']);
    if($value==='' && $column['null']==='YES') return null;
    if(strpos($type,'datetime')!==false || strpos($type,'timestamp')!==false) return normalize_datetime_local_to_mysql($value);
    return $value;
}
