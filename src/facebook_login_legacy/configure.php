<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if(!empty($_POST['plugin_google_login'])){
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
      <h2>Facebook Login Settings</h2>
<strong>Please note:</strong> Social logins require that you do some configuration on your own with Facebook. It is strongly recommended that you  <a href="http://www.userspice.com/documentation-social-logins/" target="_blank"><font color="blue">check the documentation at UserSpice.com.</font></a><br><br>


<!-- left -->
<div class="form-group">
  <label for="glogin">Enable Facebook Login</label>
  <span style="float:right;">
    <label class="switch switch-text switch-success">
                <input id="fblogin" type="checkbox" class="switch-input toggle" data-desc="Facebook Login" <?php if($settings->fblogin==1) echo 'checked="true"'; ?>>
                <span data-on="Yes" data-off="No" class="switch-label"></span>
                <span class="switch-handle"></span>
              </label>
            </span>
          </div>

          <div class="form-group">
            <label for="fbid">Facebook App ID</label>
            <input type="password" class="form-control ajxtxt" data-desc="Facebook App ID" name="fbid" id="fbid" value="<?=$settings->fbid?>">
          </div>

          <div class="form-group">
            <label for="fbsecret">Facebook Secret</label>
            <input type="password" class="form-control ajxtxt" data-desc="Facebook Secret" name="fbsecret" id="fbsecret" value="<?=$settings->fbsecret?>">
          </div>

          <div class="form-group">
            <label for="fbcallback">Facebook Callback URL</label>
            <input type="text" class="form-control ajxtxt" data-desc="Facebook Callback URL" name="fbcallback" id="fbcallback" value="<?=$settings->fbcallback?>">
          </div>

  		<div class="form-group">
            <label for="graph_ver">Facebook Graph Version - Formatted as v3.2</label>
            <input type="text" class="form-control ajxtxt" data-desc="Facebook Graph Version" name="graph_ver" id="graph_ver" value="<?=$settings->graph_ver?>">
          </div>

  		<div class="form-group">
            <label for="finalredir">Redirect After Facebook Login</label>
            <input type="text" class="form-control ajxtxt" data-desc="Facebook Redirect" name="finalredir" id="finalredir" value="<?=$settings->finalredir?>">
          </div>

  		</div>
  		</div>
      <br><br>
      If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
<br><br>
