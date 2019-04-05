  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

 <?php
include($abs_us_root.$us_url_root.'usersc/plugins/userman/assets/setup.php');
if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
}
 if(!empty($_POST['plugin_userman'])){
   $fields = [];
   foreach($_POST as $k=>$v){
     if($k != "plugin_userman" && $k != 'csrv'){
       $fields[$k] =  Input::get($k);
     }
   }
   $db->update('userman_settings',1,$fields);
   Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=userman&err=Settings+updated');
 }

 if(!empty($_POST['addSpec'])){
   $uid = Input::get('addSpec');
   $check = $db->query("SELECT id FROM users WHERE id = ?",array($uid))->count();
   if($check > 0){
     $db->update('users',$uid,['userman'=>1]);
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=userman&err=User+added');
   }else{
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=userman&err=User+not+found');
   }
 }

 if(!empty($_POST['removeSpec'])){
   $uid = Input::get('removeSpec');
   $check = $db->query("SELECT id FROM users WHERE id = ?",array($uid))->count();
   if($check > 0){
     $db->update('users',$uid,['userman'=>0]);
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=userman&err=User+removed');
   }else{
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=userman&err=User+not+found');
   }
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
      <h1>Configure the User Manager Plugin</h1>
      The User Manager plugin is a way to allow admins and other users to do some levels of User Management.  Change the settings below to decide who can do what.
      <br><br>
      When you are ready to share your control panel make a link to <a href="<?=$us_url_root?>usersc/plugins/userman/files/index.php">here</a><br><br>
 			<div class="col-sm-6">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<form class="" action="" method="post">
            <input type="hidden" name="csrf" $value=<?=$token;?>" />
            <?php $key = 'create';?>
            <label for="<?=$key?>">Create New Users</label>
            <select class="form-control" name="<?=$key?>">
              <?php foreach($opts as $k=>$v){ ?>
                <option <?php if($k==$userman[$key]){echo "selected";}?> value="<?=$k?>"><?=$v?></option>
              <?php } ?>
            </select>
<br>
            <?php $key = 'delete';?>
            <label for="<?=$key?>">Delete Users (Except Admins and Master Accounts)</label>
            <select class="form-control" name="<?=$key?>">
              <?php foreach($opts as $k=>$v){ ?>
                <option <?php if($k==$userman[$key]){echo "selected";}?> value="<?=$k?>"><?=$v?></option>
              <?php } ?>
            </select>
<br>
            <?php $key = 'perms';?>
            <label for="<?=$key?>">Assign and Remove Non-Admin Permissions</label>
            <select class="form-control" name="<?=$key?>">
              <?php foreach($opts as $k=>$v){ ?>
                <option <?php if($k==$userman[$key]){echo "selected";}?> value="<?=$k?>"><?=$v?></option>
              <?php } ?>
            </select>
<br>
            <?php $key = 'passwords';?>
            <label for="<?=$key?>">Change and Reset Passwords</label>
            <select class="form-control" name="<?=$key?>">
              <?php foreach($opts as $k=>$v){ ?>
                <option <?php if($k==$userman[$key]){echo "selected";}?> value="<?=$k?>"><?=$v?></option>
              <?php } ?>
            </select>
<br>
            <?php $key = 'info';?>
            <label for="<?=$key?>">Change Other User Information</label>
            <select class="form-control" name="<?=$key?>">
              <?php foreach($opts as $k=>$v){ ?>
                <option <?php if($k==$userman[$key]){echo "selected";}?> value="<?=$k?>"><?=$v?></option>
              <?php } ?>
            </select>
            <input type="submit" name="plugin_userman" value="Update Settings">
          </form>
 			</div> <!-- /.col -->
      <div class="col-sm-6">
        <h3>Specified Users</h3>
        Adding a user here allows you to give them User Management Access without making them an admin.<br><br>
        <h5>Add Specified User</h5>
        <form class="" action="" method="post">
          <input type="hidden" name="csrf" $value=<?=$token;?>" />
          Enter the User ID of the User you want to add.<br>
          <input type="number" name="addSpec" value="">
          <input type="submit" name="add" value="Add" class="btn btn-success">
        </form>
        <br>
        <h5>Existing Specified Users</h5>
        <?php
        $specQ = $db->query("SELECT id FROM users WHERE userman = 1");
        $specC = $specQ->count();
        $spec = $specQ->results();
        if($specC > 0){?>

        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th><th>User</th><th>Remove</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($spec as $s){ ?>
              <tr>
                <td><?=$s->id?></td>
                <td><?php echouser($s->id);?></td>
                <td>
                  <form class="" action="" method="post">
                    <input type="hidden" name="csrf" $value=<?=$token;?>" />
                    <input type="hidden" name="removeSpec" value="<?=$s->id?>">
                    <input type="submit" name="remove" value="Remove" class="btn btn-danger">
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php }else{
        echo "none";
      } ?>


      </div>
 		</div> <!-- /.row -->
