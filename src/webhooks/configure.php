  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$edit = Input::get('edit');
$method = Input::get("method");
$webhooks = $db->query("SELECT * FROM plg_webhooks ORDER BY id DESC")->results();

 if(!empty($_POST['createWebhook']) || !empty($_POST['editWebhook'])){
   $token = $_POST['csrf'];
   if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   $fields = [
     'hook'=>Input::get('hook'),
     'auth'=>Input::get('auth'),
     'action_type'=>Input::get('action_type'),
     'action'=>$_POST['action'],
     'twofa_key'=>Input::get('twofa_key'),
     'twofa_value'=>Input::get('twofa_value'),
     'log'=>Input::get('log'),
     'disabled'=>Input::get('disabled'),
   ];
   if(!empty($_POST['createWebhook'])){
     $db->insert("plg_webhooks",$fields);
     Redirect::to("admin.php?view=plugins_config&plugin=webhooks&err=Webhook created");
   }elseif(!empty($_POST['editWebhook'])){
     $db->update("plg_webhooks",$edit,$fields);
     Redirect::to("admin.php?view=plugins_config&plugin=webhooks&err=Webhook updated");
   }
}
 $token = Token::generate();
 if(is_numeric($edit)){
   $q = $db->query("SELECT * FROM plg_webhooks WHERE id = ?",[$edit]);
   $c = $q->count();
   if($c < 1){
     Redirect::to("admin.php?view=plugins_config&plugin=webhooks&method=mod&err=Webhook not found");
   }else{
     $h = $q->first();
   }
 }
 ?>
<div class="content mt-3">
 		<div class="row">

 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <br>
    <?php
    require_once $abs_us_root.$us_url_root."usersc/plugins/webhooks/views/_menu.php";

    if($method == "activity"){
      require_once $abs_us_root.$us_url_root."usersc/plugins/webhooks/views/_activity.php";
    }elseif($method == "hooklogs"){
      require_once $abs_us_root.$us_url_root."usersc/plugins/webhooks/views/_hooklogs.php";
    }elseif($method == "docs"){
      require_once $abs_us_root.$us_url_root."usersc/plugins/webhooks/views/_docs.php";
    }else{
      require_once $abs_us_root.$us_url_root."usersc/plugins/webhooks/views/_default.php";
    }
    ?>
