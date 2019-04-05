<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
include "plugin_info.php";
pluginActive($plugin_name);
  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
  }
  if(!empty($_POST['plugin_meekro'])){
    $meekro = Input::get('meekro');
    if($meekro == 0 || $meekro == 1){
      $db->update('settings',1,['meekro'=>$meekro]);
    }
  }
  $token = Token::generate();
  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>Configure the meekro Plugin!</h1>


        <form>
          <input type="hidden" name="csrf" value="<?=$token?>" />
          <fieldset>
            <legend>Which style would you like to use for Meekro?</legend>
            <p>
              <input type = "radio"
              name = "meekro"
              value = "0"
              <?php if($settings->meekro == 0){ echo "checked = 'checked'";}?> />
              <label for = "oop">OOP - $users = $mdb->query("SELECT username FROM users");</label>
              <br>
              <input disabled type = "radio"
              name = "meekro"
              value = "1"
              <?php if($settings->meekro == 1){ echo "checked = 'checked'";}?> />
              <label for = "static">(unavailable) Static - $users = MDB::query("SELECT * FROM users");</label>
            </p>
          </fieldset>
          <input type="submit" name="plugin_meekro" value="Update">
        </form>

        Please note that Meekro is free only for personal use and they ask you to purchase a license for commercial projects.<br>
        Please purchase the appropriate license at the link on their homepage at <a href="https://meekro.com/">https://meekro.com/</a>

        When this plugin is enabled, you automatically have access to the $mdb variable which is connected to your core UserSpice DB. You can use other databases with...<br>
        $mdb = new MeekroDB($host, $user, $pass, $dbName);<br>
        You can also add , $port, $encoding to the above if you use non-standard ports or encoding.<br><br>
        <strong>
          Note: Due to a naming conflict, whenever you see DB:: in the Meekro documentation, you must use MDB::<br><br>
        </strong>
        Here is a link to the <a href="https://meekro.com/quickstart.php">Quick Start Guide</a> and the <a href="https://meekro.com/docs.php">Complete documentation.</a>
      </div> <!-- /.col -->
    </div> <!-- /.row -->
  </div>
