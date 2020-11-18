<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $theNewId,$form_valid;

$check = $db->query("SELECT id FROM users_form")->count();
if($check > 0){
  if(pluginActive("forms",true)){
  if(!empty($_POST)){

    $response = preProcessForm();
    if($response['form_valid'] == true){
      //do something here after the form has been validated
       $db->update("users",$theNewId,$response['fields']);
      //temporary compatibility fix to clean up old bad data
      $db->query("DELETE FROM users WHERE password = ? AND email = ? AND username = ?",["","",""]);
      $form_valid=TRUE;
    }else{
      $db->query("DELETE FROM users WHERE id = ?",[$theNewId]);
      $db->query("DELETE FROM user_permission_matches WHERE user_id = ?",[$theNewId]);
      $form_valid=FALSE;
    }
  }
 }
}
