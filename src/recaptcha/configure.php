  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_demo'])){
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
              <h2>reCAPTCHA Settings</h2>
              <strong>Please Note:</strong> reCAPTCHA requires keys generated from Google.<br>
              You can generate these keys on Google <a href="https://www.google.com/recaptcha/admin">here</a>.<br><br>
              Do not enable reCAPTCHA on public facing pages without setting valid keys first. This can lock you out
              from logging in.
              <br><br>
              <div class="form-group">
                  <label for="recaptcha">Enable reCAPTCHA on public UserSpice pages (Join, Login, Forgot
                      Password)</label>
                  <span style="float:right;">
                      <label class="switch switch-text switch-success">
                          <input id="recaptcha" type="checkbox" class="switch-input toggle" data-desc="reCAPTCHA"
                              <?php if($settings->recaptcha==1) echo 'checked="true"'; ?>>
                          <span data-on="Yes" data-off="No" class="switch-label"></span>
                          <span class="switch-handle"></span>
                      </label>
                  </span>
              </div>
              <div class="form-group">
                  <label for="recap_public">reCAPTCHA Site Key</label>
                  <input type="password" class="form-control ajxtxt" data-desc="reCAPTCHA Site Key" name="recap_public"
                      id="recap_public" value="<?=$settings->recap_public?>">
              </div>
              <div class="form-group">
                  <label for="recap_public">reCAPTCHA Secret Key</label>
                  <input type="password" class="form-control ajxtxt" data-desc="reCAPTCHA Secret Key"
                      name="recap_private" id="recap_private" value="<?=$settings->recap_private?>">
              </div>
              <div class="form-group">
                  <label>reCAPTCHA Version (Must match Google reCAPTCHA Settings)</label>
                  <select name="recap_version" id="recap_version" class="form-control ajxnum"
                      data-desc="reCAPTCHA Version">
                      <option value="2" <?php if ($settings->recap_version == 2) {echo 'selected';}?>>V2</option>
                      <option value="3" <?php if ($settings->recap_version == 3) {echo 'selected';}?>>V3</option>
                  </select>
              </div>
              <div class="form-group">
                  <label>reCAPTCHA Type (Must match Google reCAPTCHA Settings)</label>
                  <select name="recap_type" id="recap_type" class="form-control ajxnum" data-desc="reCAPTCHA Type">
                      <option value="1" <?php if ($settings->recap_type == 1) {echo 'selected';}?>>Checkbox (V2 Only)
                      </option>
                      <option value="2" <?php if ($settings->recap_type == 2) {echo 'selected';}?>>Invisible</option>
                  </select>
              </div>
              <br><br>
              <div class="form-class">
                  You can add a reCAPTCHA to your own form using the following functions:<br>
                  <b>addCaptcha</b><br>
                  <code>
                &lt;form id="testForm"&gt;<br>
                &lt;input type="text" id="fname" name="fname"&gt;<br>
                &lt;input type="submit" value="Submit"&gt;<br>
                &lt;/form&gt;<br>
                &lt;?php addCaptcha("testForm") ?&gt;
                </code><br><br>
                  Simply use addCaptcha with the form id to add captcha to your form.
                  <br><br>
                  <b>verifyCaptcha</b><br>
                  <code>
                if(verifyCaptcha()) {<br>
                    // Process Form<br>
                }
                </code><br>
                  verifyCaptcha returns a boolean of whether it has passed captcha validation. This uses the default
                  threshold set in your reCAPTCHA dashboard.
                  <br>
                  You can optionally pass true to the function to get the full detail array.
                  <code><br>
                array(7) {<br>
                    ["success"]=><br>
                    bool(true)<br>
                    ["hostname"]=><br>
                    string(20) "www.userspice.com"<br>
                    ["challenge_ts"]=><br>
                    string(20) "2021-05-13T13:35:04Z"<br>
                    ["apk_package_name"]=><br>
                    NULL<br>
                    ["score"]=><br>
                    float(0.9)<br>
                    ["action"]=><br>
                    NULL<br>
                    ["error-codes"]=><br>
                    array(0) {<br>
                    }<br>
                }
                </code>
                  <br><br>
                  You can use the full detail array to execute different actions based on score, etc.:<br>
                  <code>if (verifyCaptcha(true)['score'] > 0.7) {//execute}</code>
              </div>
              <br><br><br>

          </div>
      </div>
  </div>