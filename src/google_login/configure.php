<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if (!empty($_POST['plugin_google_login'])) {
  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
  // Redirect::to('admin.php?err=I+agree!!!');
}
$googleSettings = $db->query('SELECT * FROM plg_google_login')->first();
if(strtolower($user->data()->fname) == "pete" || strtolower($user->data()->fname) == "peter"){
  //Pete complains he can never see the switch to turn the feature on.
  $buttonClass = "huge-control";
}else{
  $buttonClass = "";
}
$token = Token::generate();
?>
<div class="content mt-3">
  <div class="row">
    <div class="col-12 col-md-6 offset-md-3">
      <h2>Google Login Settings</h2>
      <strong>Please note:</strong> Social logins require that you do some configuration on your own with Google and/or Google.It is strongly recommended that you <a href="http://www.userspice.com/documentation-social-logins/" target="_blank">
        <font color="blue">check the documentation at UserSpice.com.</font>
      </a><br><br>
      <div class="row">
        <div class="col-12">
        <div class="form-group">
    <label for="glogin">Enable Google Login</label>
    <span id="glogin-status"><?php echo ($settings->glogin == 1) ? "(Currently Enabled)" : "(Currently Disabled)"; ?></span>
    <span style="float:right;" class="form-check form-switch">
        <label class="switch switch-text switch-success">
            <input id="glogin" type="checkbox" class="switch-input form-check-input toggle <?=$buttonClass?>" data-desc="Google Login" <?php if ($settings->glogin == 1) echo 'checked="true"'; ?>>
            <span data-on="Yes" data-off="No" data-table="settings" class="switch-label"></span>
            <span class="switch-handle"></span>
        </label>
    </span>
    
</div>
        </div>
      </div>


      <div class="form-group">
      <br>
        <label for="gid">Google Client ID</label>
        <input type="password" autocomplete="off" class="form-control ajxtxt" data-table="plg_google_login" data-desc="Google Client ID" name="gid" id="gid" value="<?= $googleSettings->gid ?>">
      </div>

      <div class="form-group">
        <label for="gsecret">Google Client Secret</label>
        <input type="password" autocomplete="off" class="form-control ajxtxt" data-table="plg_google_login" data-desc="Google Client Secret" name="gsecret" id="gsecret" value="<?= $googleSettings->gsecret ?>">
      </div>

      <div class="form-group">
        <label for="ghome">Full Home URL of Website - include the final /</label>
        <input type="text" class="form-control ajxtxt" data-table="plg_google_login" data-desc="Home URL" name="ghome" id="ghome" value="<?= $googleSettings->ghome ?>">
      </div>

      <div class="form-group">
        <label for="gredirect">Google Redirect URL (Path to oauth_success.php)</label>
        <input type="text" class="form-control ajxtxt" data-table="plg_google_login" data-desc="Redirect URL" name="gredirect" id="gredirect" value="<?= $googleSettings->gredirect ?>">
      </div>


      <p>If you would like to change the text on the login/join pages you can set the following keys in usersc/langs/your_language.php</p><br>
      <p><code>"OR_SIGN_IN_WITH"</code> Default: "Or sign in with:"</p>
      <p><code>"SOCIAL_PROVIDER_{NAME}"</code> For example <code>"SOCIAL_PROVIDER_GOOGLE"</code> Defaults to provider name.</p>

      <br>
      If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate" style="color:blue;">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
   
    </div>
  </div>
<style>
  .huge-control{
    width: 30rem !important;   
    height: 15rem !important;
    margin-bottom: 2rem;
  }
</style>
  <script>
$(document).ready(function() {
    $('#glogin').change(function() {
        var statusText = $(this).is(':checked') ? "(Currently Enabled)" : "(Currently Disabled)";
        $('#glogin-status').text(statusText);
    });
});
</script>