  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_steam_login'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 ?>
 <div class="content mt-3">
   <div class="row">
     <div class="col-6 offset-3">
       <h2>Steam Login Settings</h2>
     <div class="form-group">
       <label for="gid">Steam API Key (found at <a href="https://steamcommunity.com/dev/apikey">https://steamcommunity.com/dev/apikey</a>)</label>
       <input type="password" autocomplete="off" class="form-control ajxtxt" data-desc="Steam API Key" name="steam_api" id="steam_api" value="<?=$settings->steam_api?>">
     </div>

     <div class="form-group">
       <label for="gsecret">Your Domain with no http(s) or / </label>
       <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="Steam Domain"  name="steam_domain" id="steam_domain" value="<?=$settings->steam_domain?>">
     </div>

 </div>
 </div>
