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
      <h2>Google Login Settings</h2>
<strong>Please note:</strong> Social logins require that you do some configuration on your own with Google and/or Google.It is strongly recommended that you  <a href="http://www.userspice.com/documentation-social-logins/" target="_blank"><font color="blue">check the documentation at UserSpice.com.</font></a><br><br>


<div class="form-group">
      <label for="glogin">Enable Google Login</label>
      <span style="float:right;">
        <label class="switch switch-text switch-success">
          <input id="glogin" type="checkbox" class="switch-input toggle" data-desc="Google Login" <?php if($settings->glogin==1) echo 'checked="true"'; ?>>
          <span data-on="Yes" data-off="No" class="switch-label"></span>
          <span class="switch-handle"></span>
        </label>
      </span>
    </div>

    <div class="form-group">
      <label for="gid">Google Client ID</label>
      <input type="password" autocomplete="off" class="form-control ajxtxt" data-desc="Google Client ID" name="gid" id="gid" value="<?=$settings->gid?>">
    </div>

    <div class="form-group">
      <label for="gsecret">Google Client Secret</label>
      <input type="password" autocomplete="off" class="form-control ajxtxt" data-desc="Google Client Secret"  name="gsecret" id="gsecret" value="<?=$settings->gsecret?>">
    </div>

    <div class="form-group">
      <label for="ghome">Full Home URL of Website - include the final /</label>
      <input type="text" class="form-control ajxtxt" data-desc="Home URL"  name="ghome" id="ghome" value="<?=$settings->ghome?>">
    </div>

    <div class="form-group">
      <label for="gredirect">Google Redirect URL (Path to oauth_success.php)</label>
      <input type="text" class="form-control ajxtxt" data-desc="Redirect URL"  name="gredirect" id="gredirect" value="<?=$settings->gredirect?>">
    </div>

</div>
</div>
