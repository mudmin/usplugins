<?php
require_once '../../../../users/init.php';
global $user;
$db = DB::getInstance();
if(!pluginActive('quickcrud',true)){
  die("inactive");
}

if(!isset($user) || !$user->isLoggedIn() || !hasPerm([2],$user->data()->id)){
  die();
}

$method = Input::get('method');
if($method == 'update'){
  if(Input::get('key') == 'id'){
    echo "Cannot update id."; die;
  }
  $db->update(Input::get('table'),Input::get('row'),[Input::get('key')=>Input::get('value')]);
  if($db->errorString() != "ERROR #0: "){
    echo $db->errorString();die;
  }
}

if($method == 'duplicate'){
  $table = Input::get('table');
  $rowQ = $db->query("SELECT * FROM $table WHERE id = ?",[Input::get('row')]);
  $rowC = $rowQ->count();
  if($rowC > 0){
    $row = $rowQ->first();
    $fields = [];
    foreach($row as $k=>$v){
      if($k != 'id'){
        $fields[$k]=$v;
      }
    }
    $db->insert($table,$fields);
    echo json_encode(['reload'=>true,'msg'=>""]);die;
  }else{
    echo json_encode(['reload'=>false,'msg'=>'Row not found']);die;
  }
}

if($method == 'delete'){
  $table = Input::get('table');
  $db->query("DELETE FROM $table WHERE id = ?",[Input::get('row')]);
  echo json_encode(['reload'=>true,'msg'=>""]);die;

}

if($method == 'insert'){
  $table = Input::get('table');
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
