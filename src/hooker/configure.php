  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$files = scandir($abs_us_root.$us_url_root.'/usersc/plugins/hooker/hooks');
$events = [
'hitBanned',
'forgotPassword',
'forgotPasswordResponse',
'joinAttempt',
'joinAttemptApi',
'joinFail',
'joinFailApi',
'joinSuccessApi',
'loginFail',
'loginFailApi',
'loginSuccess',
'loginSuccessApi',
'logout',
'noAccess',
'passwordResetFail',
'passwordResetSuccess',
'verifyEmailAttempt',
'verifyEmailAttemptPassed',
'verifySuccess',
'verifyFail',
'verifyResend',
'verifyResendSubmit',
];

if(!empty($_POST['addHook'])){
  $pages = ['account.php','admin.php?view=general','admin.php?view=user','admin.php?view=users','forgot_password.php','join.php','login.php','user_settings.php','verify.php'];
  $positions = ['pre','post','body','form','bottom'];
  $combined = array_merge($pages,$events);
  $valid = false;
  $page = Input::get('page');
  $position = Input::get('position');
  $file = Input::get('file');
  if(in_array($page,$combined) && in_array($position,$positions) && in_array($file,$files)){
    $valid = true;
  }else{
    die("invalid data");
  }
  if($valid){
    $hooks = [];
    if(in_array($page,$events)){
      $position = "body";
    }
    $hooks[$page][$position] = 'hooks/'.$file;
    registerHooks($hooks,'hooker');
    Redirect::to('admin.php?view=plugins_config&plugin=hooker&err=Hook+added');
  }
}
if(!empty($_POST['deleteHook'])){

  $hookid = Input::get('hookid');
  $checkQ = $db->query("SELECT * FROM us_plugin_hooks WHERE id = ?",[$hookid]);
  $checkC = $checkQ->count();
  if($checkC > 0){
    $db->query("DELETE FROM us_plugin_hooks WHERE id = ?",[$hookid]);
    Redirect::to('admin.php?view=plugins_config&plugin=hooker&err=Hook+deleted');
  }else{
    Redirect::to('admin.php?view=plugins_config&plugin=hooker&err=Hook+not+found');
  }
}

 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Hooker Plugin!</h1>
          With the Hooker plugin, you can use hooks to inject your code in UserSpice pages, but you don't have
          to create a whole plugin to do it! Simply create a hook in the hooks folder of this plugin and register it below!
          For a table of where plugin hooks show up on the page, see <a href="https://userspice.com/plugin-hooks/">https://userspice.com/plugin-hooks/</a>.
          Please note that uninstalling this plugin will remove all hooks and if you reinstall, you will have to manually add them again.<br><br>
          There is a sample_hook.php file in the hooks folder you can play with to get started. <strong>If you are not on the latest version of UserSpice,
          please check the plugin hooks page to make sure that the hook you want to use is included in your version.

          <form class="" action="" method="post">
            <div class="form-group" id="pages">
              <label for="">Choose a page or event</label>
              <select class="form-control" name="page" id="pageSelect" required>
                  <option class="nada" value="" disabled selected="selected">--Choose Page--</option>
                  <option class="pg" value="account.php">account.php (no post or form)</option>
                  <option class="pg" value="admin.php?view=general">admin.php?view=general (no post,form, or bottom)</option>
                  <option class="pg" value="admin.php?view=user">admin.php?view=user (v5.0.5+)</option>
                  <option class="pg" value="admin.php?view=users">admin.php?view=users (v5.0.5+)</option>
                  <option class="pg" value="forgot_password.php">forgot_password.php (no body or form)</option>
                  <option class="pg" value="join.php">join.php (all positions available)</option>
                  <option class="pg" value="login.php">login.php (all positions available)</option>
                  <option class="pg" value="user_settings.php">user_settings.php (all positions available)</option>
                  <option class="pg" value="verify.php">verify.php (all positions available)(v5.3.8+)</option>
                  <?php foreach($events as $e){?>
                    <option value="<?=$e?>" class="event"><?=$e?> Event</option>
                  <?php } ?>
              </select>
            </div>

            <div class="form-group" id="positions">
              <label for="">Choose a position</label>
              <select class="form-control" name="position" id="pos" required>
                  <option value="" disabled selected="selected">--Choose Position--</option>
                  <option value="pre">pre</option>
                  <option value="post">post</option>
                  <option value="body">body</option>
                  <option value="form">form</option>
                  <option value="bottom">bottom</option>
              </select>
            </div>

            <div class="form-group">
              <label for="">Choose a hook file</label>
              <select class="form-control" name="file" required>
                  <option value="" disabled selected="selected">--Choose Position--</option>
                  <?php
                  foreach ($files as $file) {
                    if($file != "." && $file != ".." && $file != ".htaccess"){?>
                      <option value="<?=$file?>"><?=$file?></option>
                    <?php
                   }
                  }
                  ?>
              </select>
            </div>
            <div class="form-group">
              <input type="submit" name="addHook" value="Add Hook" class="btn btn-success">
            </div>
          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <div class="row">
      <div class="col-12">
        <?php
        $existing = $db->query("SELECT * FROM us_plugin_hooks WHERE folder = ?",['hooker'])->results();
        ?>
        <table class="table table striped">
          <thead>
            <tr>
              <th>Plugin</th><th>Page</th><th>Position</th><th>Hook</th><th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach($existing as $e){?>
              <tr>
                <td><?=ucfirst($e->folder);?></td>
                <td><?=$e->page?></td>
                <td><?=$e->position?></td>
                <td><?=$e->hook?></td>
                <td>
                  <form class="" action="" method="post">
                    <input type="hidden" name="hookid" value="<?=$e->id?>">
                    <input type="submit" name="deleteHook" value="Delete" class="btn btn-danger">
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
        If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>.
        Either way, thanks for using UserSpice!
      </div>
    </div>
    <script type="text/javascript">
    $("#pageSelect").change(function () {
       var cl = $('select[name="page"] :selected').attr('class');
       if(cl == "event"){
         $("#pos").val('body');
       }
   });
    </script>
