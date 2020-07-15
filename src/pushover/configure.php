<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  $hooks = ['A new user registered'=>'join.php','Someone tried to login unsuccessfully'=>'loginFail','Someone tried to access a page without permission'=>'noAccess','A blocked IP tried to access the site'=>'hitBanned','Someone is resetting their password'=>'forgotPassword'];
  $myHooks = $db->query("SELECT * FROM us_plugin_hooks WHERE folder = ?",['pushover'])->results();
  $installed = [];
  foreach($myHooks as $m){
    $installed[] = $m->page;
  }
  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    if(!empty($_POST['subKey'])){
      $fields = [
        'plg_po_token'=>Input::get('plg_po_token'),
        'plg_po_key'=>Input::get('plg_po_key'),
      ];
      $db->update("settings",1,$fields);
      Redirect::to("admin.php?view=plugins_config&plugin=pushover&err=Saved");
    }
    if(!empty($_POST['testKey'])){
      pushoverNotification($settings->plg_po_key,"This is a test");
      Redirect::to("admin.php?view=plugins_config&plugin=pushover&err=Test Sent");
    }

    if(!empty($_POST['regHooks'])){
      $db->query("DELETE FROM us_plugin_hooks WHERE folder = ?",['pushover']);
      $hk = $_POST['hook'];
      foreach($hk as $h){
        $h = Input::sanitize($h);
        if(in_array($h,$hooks)){
          if($h == "join.php"){
            $position = "post";
            $page = "hooks/join.php";
          }else{
            $position = "body";
            $page = 'hooks/'.$h.'.php';
          }
          $fields = [
            'page'=>$h,
            'folder'=>'pushover',
            'position'=>$position,
            'hook'=>$page,
          ];
          $db->insert("us_plugin_hooks",$fields);
        }
      }
      Redirect::to("admin.php?view=plugins_config&plugin=pushover&err=Hooks Saved");
    }
  }
  $token = Token::generate();
  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h3>Configure the Pushover Plugin!</h3>
        Pushover is a free service for 7,500 push notifications per month.
        Their Android, iOS, and Desktop apps are $4.99 each (7 day free trial) or you can use a Node-Red to process Pushover notifications
        for free.  You must create an account on https://pushover.net.  You will also need to create an "application"
        on their dashboard.  Once you do that, you can enter your token and key here and you're all set.

        <form class="" action="" method="post">
          <input type="hidden" name="csrf" value="<?=$token?>">
          <div class="row">
            <div class="col-12 col-sm-3">
              <label for="">App Token</label>
              <input type="password" name="plg_po_token" value="<?=$settings->plg_po_token?>" class="form-control">
            </div>
            <div class="col-12 col-sm-3">
              <label for="">Your User Key</label>
              <input type="password" name="plg_po_key" value="<?=$settings->plg_po_key?>" class="form-control">
            </div>
            <div class="col-12 col-sm-3">
              <br>
              <input type="submit" name="subKey" value="Save" class="btn btn-primary">
            </div>
          </form>
          <div class="col-12 col-sm-3">
            <form class="" action="" method="post">
              <br>
              <input type="hidden" name="csrf" value="<?=$token?>">
              <input type="submit" name="testKey" value="Test Settings" class="btn btn-info">
            </form>
          </div>
        </div>
      </div> <!-- /.col -->
    </div> <!-- /.row -->
    <div class="row">
      <div class="col-12">
        <h3>Setup Notifications</h3>
        You can send a notification on any page using this format <strong>pushoverNotification($settings->plg_po_key,"This is a test");</strong><br>
        See <strong><a href="https://pushover.net/api#html"><font color="blue">this page</font></a></strong> to learn how to style your messages.<br>
        You can also trigger notifications when certain events happen.  These can be put in any of our scripts.  For instance, you can put
        <code>
          <br>
          if(hasPerm([2],$user->data()->id)){<br>
            pushoverNotification($settings->plg_po_key,"There was an admin login");<br>
          }<br>
        </code>
        in <strong>usersc/scripts/custom_login_script.php</strong> to be notified every time an admin logs in.
        <br><br>
        <form class="" action="" method="post">
          <h3>Register Hooks</h3>
          You can register to be notified when any of these events happen. These events are available in 5.1.4 or later.<br><br>
          <div class="row">
            <?php foreach($hooks as $k=>$v){?>
              <div class="col-4">
                <input type="checkbox" name="hook[]" value="<?=$v?>" <?php if(in_array($v,$installed)){echo "checked";}?>> <strong><?=$k?></strong>
              </div>
            <?php } ?>
          </div>
          <input type="hidden" name="csrf" value="<?=$token?>">
          <input type="submit" name="regHooks" value="Register Hooks" class="btn btn-primary">
        </form>
      </div>
    </div>
    If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>.
    Either way, thanks for using UserSpice!
