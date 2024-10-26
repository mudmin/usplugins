  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_payments'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
}
 $token = Token::generate();
 $dirs = glob($abs_us_root . $us_url_root . 'usersc/plugins/payments/assets/*', GLOB_ONLYDIR);
 if(!isset($keys)){$keys = $db->query("SELECT * FROM `keys`")->first();}
 if(!empty($_POST['updateGlobal'])){
   $options = $_POST['options'];
   $db->query("TRUNCATE TABLE plg_payments_options");
   foreach($options as $k=>$v){
     if($v == 1 || $v == 0){
       $fields = array(
         'option'=>Input::sanitize($k),
         'enabled'=>$v,
       );
       $db->insert('plg_payments_options',$fields);
     }
   }
   if(strlen(Input::get('currency')) > 3 || strlen(Input::get('currency')) < 3){
     Redirect::to('admin.php?view=plugins_config&plugin=payments&err=Invalid+currency+code');
   }
   $db->update('`keys`',$keys->id,['currency'=>strtoupper(Input::get('currency'))]);
   Redirect::to('admin.php?view=plugins_config&plugin=payments&err=Globals+saved');
 }
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Payments Plugin</h1><br>
          <h3>Global Settings</h3>
          <form class="" action="" method="post">
            <div class="row">
              <div class="col-6">
                <label for="">Set currency for site (3 letter code)</label>
                <div class="form-group">
                  <input type="text" name="currency" value="<?=$keys->currency?>" required>
                </div>
              </div>
              <div class="col-6">
                <label for="">Enable/Disable Payment Options</label>
                <?php
                foreach($dirs as $d){

                	$asset = str_replace($abs_us_root . $us_url_root . 'usersc/plugins/payments/assets/','',$d);
                	$checkQ = $db->query("SELECT * FROM plg_payments_options WHERE option = ?",[$asset]);
                  $checkC = $checkQ->count();
                  ?>
                  <div class="form-group">
                    <label for=""><?=ucfirst($asset);?></label>
                  <?php if($checkC < 1){ ?>
                    <select class="" name="options[<?=$asset?>]">
                      <option value="0" selected>Disabled</option>
                      <option value="1" enabled>Enabled</option>
                    </select>
                  <?php }else{
                    $check = $checkQ->first();
                    ?>
                    <select class="" name="options[<?=$asset?>]">
                      <option value="0" <?php if($check->enabled == 0){echo "selected";}?>>Disabled</option>
                      <option value="1" <?php if($check->enabled == 1){echo "selected";}?>>Enabled</option>
                    </select>
                <?php } ?>
              </div>
              <?php } ?>
              </div>
            </div>

            <input type="hidden" name="csrf" value="<?=$token?>" />
            <input type="submit" name="updateGlobal" value="Update Global Settings" class="btn btn-primary" style="margin-bottom: 1.25em;">

          </form>
          <?php foreach ($dirs as $d) {
            if(file_exists($d.'/configure.php')){
              include $d.'/configure.php';
            }
          }

          ?>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
<div class="row">
  <div class="col-12"><br>
    <?php
    require_once 'functions.php';
    $payments = $db->query("SELECT * FROM plg_payments ORDER BY id DESC")->results();
    $opts['nodata'] = "<p align='center'>You have not received any payments</p>";
    paymentTableFromData($payments,$opts);
    ?>
  </div>
</div>
<p>
  If appreciate this work and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
</p>
