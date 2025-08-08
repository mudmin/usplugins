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
      <h2>Twitch Login Settings</h2>
<strong>Please note:</strong> You must generate Twitch Client Tokens to use Twitch OAuth. More information can be found on the plugin Github README <a href="https://github.com/bangingheads/UserSpiceTwitchOAuth" target="_blank"><font color="blue">here.</font></a><br><br>


<!-- left -->
<div class="form-group">
  <label for="glogin">Enable Twitch Login</label>
  <span style="float:right;" class="form-check form-switch">
    <label class="switch switch-text switch-success">
                <input id="twlogin" type="checkbox" class="switch-input form-check-input toggle" data-desc="Twitch Login" <?php if($settings->twlogin==1) echo 'checked="true"'; ?>>
                <span data-on="Yes" data-off="No" class="switch-label"></span>
                <span class="switch-handle"></span>
              </label>
            </span>
          </div>

          <div class="form-group">
            <label for="fbid">Twitch Client ID</label>
            <input type="password" class="form-control ajxtxt" data-desc="Twitch Client ID" name="twclientid" id="twclientid" value="<?=$settings->twclientid?>">
          </div>

          <div class="form-group">
            <label for="fbsecret">Twitch Client Secret</label>
            <input type="password" class="form-control ajxtxt" data-desc="Twitch Client Secret" name="twclientsecret" id="twclientsecret" value="<?=$settings->twclientsecret?>">
          </div>

          <div class="form-group">
            <label for="fbcallback">Twitch Callback URL</label>
            <input type="text" class="form-control ajxtxt" data-desc="Twitch Callback URL" name="twcallback" id="twcallback" value="<?=$settings->twcallback?>">
          </div>

  		</div>
  		</div>