<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  require_once $abs_us_root . $us_url_root . "usersc/plugins/html_purifier/purifier.php";
  if(!empty($_POST)){
    if(!Token::check(Input::get('csrf'))){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    // Redirect::to('admin.php?err=I+agree!!!');
  }
  $test_string = "<b>This is bold</b><script>alert('Secrurity Breach!');</script><br><u>This is underline</u>";
  ?>

  <div class="content mt-3">
    <div class="row">
      <div class="col-12 p-3 mb-2" style="background-color:lightgray;">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>HTML Purifier Plugin</h1>
        There is nothing to configure in the plugin itself.  However there are many usage options.  This page serves as a test to demonstrate usage. Enter some malicous html or javascript in the box to see the output. This plugin also fixes illegal nesting, unclosed, and depecated html tags.
      </div>
      <form class="" action="" method="post">
        <div class="row">
          <div class="col-6">
            <div class="row">
              <div class="col-6">
                <h3>Input</h3>
              </div>
              <div class="col-6 text-right text-end">
                <input type="submit" name="go" value="Submit" class="btn btn-primary">
              </div>
            </div>

            <form class="" action="" method="post">
              <?=tokenHere();?>
              <textarea name="bad" rows="8" class="form-control"><?php if(!isset($_POST['bad'])){echo $test_string;}else{ echo $_POST['bad'];}?></textarea>
            </form>


          </div>
        </form>
        <div class="col-6">
          <h3>Output</h3>
          <?php if(!empty($_POST['bad'])){
            $conf = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($conf);
            $clean_html = $purifier->purify($_POST['bad']);
            echo html_entity_decode($clean_html);

          }
          ?>
        </div>
      </div>
    </div>



    <!-- Do not close the content mt-3 div in this file -->
