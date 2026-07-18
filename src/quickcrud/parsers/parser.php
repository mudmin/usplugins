<?php
require_once '../../../../users/init.php';
global $user;
$db = DB::getInstance();
if(!pluginActive('quickcrud',true)){
  die("inactive");
}

if(!function_exists('quickCrudHasPerm') || !quickCrudHasPerm()){
  die();
}

$method = Input::get('method');
$table = Input::get('table');

if(!quickCrudTableExists($table)){
  if($method == 'update'){
    echo "Unknown table."; die;
  }
  echo json_encode(['reload'=>false,'msg'=>'Unknown table']);die;
}

//update, duplicate, and delete address a single row via its key columns,
//detected fresh on this side - the client is never trusted for the key
if($method == 'update'){
  $crudKey = quickCrudKey($table);
  $where = quickCrudRowWhere($table, Input::get('row'));
  if($crudKey === null || $where === null){
    echo "This table has no usable key, so rows cannot be updated."; die;
  }
  if(in_array(Input::get('key'), $crudKey['cols'], true)){
    echo "Cannot update a key column."; die;
  }
  $db->update($table,$where,[Input::get('key')=>Input::get('value')]);
  if($db->errorString() != "ERROR #0: "){
    echo $db->errorString();die;
  }
}

if($method == 'duplicate'){
  $crudKey = quickCrudKey($table);
  $where = quickCrudRowWhere($table, Input::get('row'));
  if($crudKey === null || $where === null){
    echo json_encode(['reload'=>false,'msg'=>'This table has no usable key, so rows cannot be duplicated']);die;
  }
  if($crudKey['auto'] === null){
    echo json_encode(['reload'=>false,'msg'=>'Duplicating needs an auto_increment key column to mint the new row\'s key']);die;
  }
  $rowQ = $db->action('SELECT *',$table,$where);
  if($rowQ !== false && $rowQ->count() > 0){
    $row = $rowQ->first();
    $fields = [];
    foreach($row as $k=>$v){
      if($k !== $crudKey['auto']){
        $fields[$k]=$v;
      }
    }
    $db->insert($table,$fields);
    if($db->error()){
      echo json_encode(['reload'=>false,'msg'=>$db->errorString()]);die;
    }
    echo json_encode(['reload'=>true,'msg'=>""]);die;
  }else{
    echo json_encode(['reload'=>false,'msg'=>'Row not found']);die;
  }
}

if($method == 'delete'){
  $where = quickCrudRowWhere($table, Input::get('row'));
  if($where === null){
    echo json_encode(['reload'=>false,'msg'=>'This table has no usable key, so rows cannot be deleted']);die;
  }
  $db->delete($table,$where);
  if($db->error()){
    echo json_encode(['reload'=>false,'msg'=>$db->errorString()]);die;
  }
  echo json_encode(['reload'=>true,'msg'=>""]);die;

}

if($method == 'insert'){
    $fields = [];
    $row = parse_str($_POST['data'],$fields);
    foreach($fields as $k=>$v){
      if($v == ""){
        unset($fields[$k]);
      }else{
        $fields[$k] = Input::sanitize($fields[$k]);
      }
    }
    $db->insert($table,$fields);
  if(!$db->error()){
    echo json_encode(['reload'=>true,'msg'=>""]);die;
  }else{
    echo json_encode(['reload'=>false,'msg'=>$db->errorString()]);die;
  }
}

 ?>
