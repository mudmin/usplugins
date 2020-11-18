<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$e = $db->query("SELECT * FROM plg_userinfo")->first();
// global $form_valid;
// $check = $db->query("SELECT id FROM users_form")->count();
// if($check > 0){
//   if(pluginActive("forms",true)){
//   if(!empty($_POST)){
//
//     $response = preProcessForm();
//     if($response['form_valid'] == true){
//       //do something here after the form has been validated
//       postProcessForm($response);
//       $form_valid=TRUE;
//     }else{
//
//     }
//   }
// }
// }

if(!empty($_POST)){

if($e->uname == 1){
  $_POST['email'] = Input::get('username')."@".$e->domain;

}

if($e->uname == 2){
  $_POST['username'] = Input::get('email');
}

if($e->fname == 1){
  $_POST['fname'] = Input::get('username');
}

if($e->lname == 1){
  $_POST['lname'] = Input::get('username');
}



}
