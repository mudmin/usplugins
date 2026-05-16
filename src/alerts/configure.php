  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_alerts'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}

 }
 $token = Token::generate();
 $alerts = scandir($abs_us_root.$us_url_root.'usersc/plugins/alerts/assets/');
 foreach ($alerts as $k => $v) {
     if ($v == '.' || $v == '..' ) {
         unset($alerts[$k]);
         continue;
     }
 }
 ?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <div class="alert alert-light border py-2 px-3 small text-muted mt-3" role="note">
            <i class="fa fa-info-circle mr-1"></i>
            <strong>CSP note:</strong> the SweetAlert2 styles load from <code>https://cdnjs.cloudflare.com</code>, and the Shoelace and dark-theme styles from <code>https://cdn.jsdelivr.net</code>. If your site sends a <em>Content-Security-Policy</em> header, add those origins to <code>script-src</code> (and <code>style-src</code>) or those alerts will not load.
          </div>
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Alerts Plugin!</h1>
          <p>The alert styles in this plugin are just the beginning!  You can easily use these as a sample to create your own styles.  Simply copy a folder in usersc/plugins/alerts/assets and rename it and modify to your heart's content. </p>


          <div class="form-group">
            <label for="">Set your alert style</label>

            <select id="alerts" class="form-control ajxtxt" data-desc="Alert styling" name="alerts">
              <option value="<?=$settings->alerts; ?>"><?=$settings->alerts; ?></option>
              <?php foreach ($alerts as $l) {
              if ($l != false && $l != $settings->alerts) {?>
                  <option value="<?=$l; ?>"><?=$l; ?></option>
                <?php }
                    }?>
            </select>
          </div>

          <label>Error Message Timeout (seconds)</label>
          <div class="input-group">
            <input type="number" step="1" min="0"  class="form-control ajxnum" data-desc="Error message timeout time" name="err_tim" id="err_time" value="<?=$settings->err_time; ?>">
            <span class="input-group-addon">seconds</span>
          </div>

        <br>
        <div class="input-group">
            <a target="_blank" class="btn btn-primary" href="<?=$us_url_root?>usersc/plugins/alerts/sample.php?err=This+is+err+in+the+address+bar&msg=Msg+in+address+bar">View Sample Alerts In Your Theme</a>
        </div>
        <br><br>
        If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!




 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
