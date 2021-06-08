<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user,$userId,$usFormUpdate,$form_valid;
if(currentPage() == "user_settings.php"){
  $usFormUpdate = $user->data()->id;
}else{
  $usFormUpdate = $userId;
}

$check = $db->query("SELECT id FROM users_form")->count();
if($check > 0){
  if(pluginActive("forms",true)){
  if(!empty($_POST)){

    $response = preProcessForm();
    if($response['form_valid'] == true){
      //do something here after the form has been validated
      postProcessForm($response,['update'=>$usFormUpdate]);
      $form_valid=TRUE;
    }else{

    }
  }
}
}
