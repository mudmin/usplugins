<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(saasMgr()){

  $planinfo = saasPlanInfo($user->data()->account_owner);
  if($planInfo->perms == ""){
    $z = false;
  }else{
    $z = true;
    $perms = explode(',',$planInfo->perms);
    }


$userId = Input::get('u');
  if(!userIdExists($userId)){
    logger($user->data()->id,"SECURITY","Tried to access user $u who did not belong to them");
      Redirect::to($us_url_root.'users/account.php?err=That user is not yours. This has been logged.'); die();
  }

  $userdetails = fetchUserDetails(NULL, NULL, $userId); //Fetch user details
if($userdetails->account_owner != $user->data()->account_owner){
  logger($user->data()->id,"SECURITY","Tried to access user $u who did not belong to them");
    Redirect::to($us_url_root.'users/account.php?err=That user is not yours. This has been logged.'); die();
}

  //Forms posted
  if(!empty($_POST)) {
    if(saasOwner()){
      if(!empty($_POST['makeMgr'])){
        $db->insert('us_saas_mgrs',['org'=>$user->data()->account_owner,'user'=>$userdetails->id]);
        Redirect::to('account.php?v=manage&u='.$userId);
      }
      if(!empty($_POST['removeMgr'])){
        $db->query("DELETE FROM us_saas_mgrs WHERE org=? AND user = ?",[$user->data()->account_owner,$userdetails->id]);
        Redirect::to('account.php?v=manage&u='.$userId);
      }
    }
    if($z){
      foreach($perms as $p){
        if($p > 2){
        $db->query("DELETE FROM user_permission_matches WHERE permission_id = ? AND user_id=?",[$p,$userdetails->id]);

      }
    }
    $selectedPerms = Input::get('perms');
    if($selectedPerms != ""){
    foreach($selectedPerms  as $p){
      if($p > 2 && in_array($p,$perms)){
        $fields = array(
          'permission_id'=>$p,
          'user_id'=>$userdetails->id,
        );
        $db->insert("user_permission_matches",$fields);
      }
    }
  }
    }
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }else {

      if(!empty($_POST['delete'])){
        $deletions = $_POST['delete'];
        if ($deletion_count = deleteUsers($deletions)){
          logger($user->data()->id,"SAAS Manager","Deleted user named $userdetails->fname.");
          Redirect::to($us_url_root.'account.php?msg='.lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count)));
        }
        else {
          $errors[] = lang("SQL_ERROR");
        }
      }else{

        //Update display name
        $displayname = Input::get("unx");
        if ($userdetails->username != $displayname) {

          $fields=array('username'=>$displayname);
          $validation->check($_POST,array(
            'unx' => array(
              'display' => 'Username',
              'required' => true,
              'unique_update' => 'users,'.$userId,
              'min' => $settings->min_un,
              'max' => $settings->max_un
            )
          ));
          if($validation->passed()){
            $db->update('users',$userId,$fields);
            $successes[] = "Username Updated";
            logger($user->data()->id,"User Manager","Updated username for $userdetails->fname from $userdetails->username to $displayname.");
          }else{

          }
        }

        //Update first name
        $fname = ucfirst(Input::get("fnx"));
        if ($userdetails->fname != $fname) {

          $fields=array('fname'=>$fname);
          $validation->check($_POST,array(
            'fnx' => array(
              'display' => 'First Name',
              'required' => true,
              'min' => 1,
              'max' => 25
            )
          ));
          if($validation->passed()){
            $db->update('users',$userId,$fields);
            $successes[] = "First Name Updated";
            logger($user->data()->id,"User Manager","Updated first name for $userdetails->fname from $userdetails->fname to $fname.");
          }else{
            ?><?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
            <?php
          }
        }

        //Update last name
        $lname = ucfirst(Input::get("lnx"));
        if ($userdetails->lname != $lname){

          $fields=array('lname'=>$lname);
          $validation->check($_POST,array(
            'lnx' => array(
              'display' => 'Last Name',
              'required' => true,
              'min' => 1,
              'max' => 25
            )
          ));
          if($validation->passed()){
            $db->update('users',$userId,$fields);
            $successes[] = "Last Name Updated";
            logger($user->data()->id,"User Manager","Updated last name for $userdetails->fname from $userdetails->lname to $lname.");
          }else{
            ?>
            <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
            <?php
          }
        }

        if(!empty($_POST['pwx'])) {
          $validation->check($_POST,array(
            'pwx' => array(
              'display' => 'New Password',
              'required' => true,
              'min' => $settings->min_pw,
              'max' => $settings->max_pw,
            ),
            'confirm' => array(
              'display' => 'Confirm New Password',
              'required' => true,
              'matches' => 'pwx',
            ),
          ));

          if (empty($errors)) {
            //process
            $new_password_hash = password_hash(Input::get('pwx', true), PASSWORD_BCRYPT, array('cost' => 12));
            $user->update(array('password' => $new_password_hash,),$userId);
            $successes[]='Password updated.';
            logger($user->data()->id,"User Manager","Updated password for $userdetails->fname.");

          }
        }
        $vericode_expiry=date("Y-m-d H:i:s",strtotime("+$settings->reset_vericode_expiry minutes",strtotime(date("Y-m-d H:i:s"))));
        $vericode=randomstring(15);
        $db->update('users',$userdetails->id,['vericode' => $vericode,'vericode_expiry' => $vericode_expiry]);
        if(isset($_POST['sendPwReset'])) {
          $params = array(
            'username' => $userdetails->username,
            'sitename' => $settings->site_name,
            'fname' => $userdetails->fname,
            'email' => rawurlencode($userdetails->email),
            'vericode' => $vericode,
            'reset_vericode_expiry' => $settings->reset_vericode_expiry
          );
          $to = rawurlencode($userdetails->email);
          $subject = 'Password Reset';
          $body = email_body('_email_adminPwReset.php',$params);
          email($to,$subject,$body);
          $successes[] = "Password reset sent.";
          logger($user->data()->id,"User Manager","Sent password reset email to $userdetails->fname, Vericode expires at $vericode_expiry.");
        }

        //Block User
        $active = Input::get("active");
        if ($userdetails->permissions != $active){
          $fields=array('permissions'=>$active);
          $db->update('users',$userId,$fields);
          $successes[] = "Set user access to $active.";
          logger($user->data()->id,"User Manager","Updated active for $userdetails->fname from $userdetails->active to $active.");
        }

        //Force PW User
        $force_pr = Input::get("force_pr");
        if ($userdetails->force_pr != $force_pr){
          $fields=array('force_pr'=>$force_pr);
          $db->update('users',$userId,$fields);
          $successes[] = "Set force_pr to $force_pr.";
          logger($user->data()->id,"User Manager","Updated force_pr for $userdetails->fname from $userdetails->force_pr to $force_pr.");
        }

        //Update email
        $email = Input::get("emx");
        if ($userdetails->email != $email){
          $fields=array('email'=>$email);
          $validation->check($_POST,array(
            'emx' => array(
              'display' => 'Email',
              'required' => true,
              'valid_email' => true,
              'unique_update' => 'users,'.$userId,
              'min' => 3,
              'max' => 75
            )
          ));
          if($validation->passed()){
            $db->update('users',$userId,$fields);
            $successes[] = "Email Updated";
            logger($user->data()->id,"User Manager","Updated email for $userdetails->fname from $userdetails->email to $email.");
          }else{
            ?>
            <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
            <?php
          }

        }

      }
      $userdetails = fetchUserDetails(NULL, NULL, $userId);
    }
}

    if((!in_array($user->data()->id, $master_account) && in_array($userId, $master_account) || !in_array($user->data()->id, $master_account) && $userdetails->protected==1) && $userId != $user->data()->id) $protectedprof = 1;
    else $protectedprof = 0;
    $isMgrC = $db->query("SELECT * FROM us_saas_mgrs WHERE org = ? AND user = ?",[$user->data()->account_owner,$userdetails->id])->count();
    if($isMgrC > 0){
      $isMgr = true;
    }else{
      $isMgr = false;
    }
    ?>

    <div class="content mt-3">
      <?=resultBlock($errors,$successes);?>
      <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
        <form class="form" id='adminUser' name='adminUser' action='' method='post'>
          <div class="row">
            <div class="col-8">
              <h3><?=$userdetails->fname?> <?=$userdetails->lname?> - <?=$userdetails->username?> <div class="btn-group"><a class='btn btn-warning' href="<?=$us_url_root?>users/account.php">Cancel</a></div></h3>
              <?php if(saasOwner()){
                if(!$isMgr){ ?>
                  <form class="" action="" method="post">
                    <input type="submit" name="makeMgr" value="Make Manager" class="btn btn-primary"><br>
                  </form>
                <?php }else{ ?>
                  <form class="" action="" method="post">
                    <input type="submit" name="removeMgr" value="Remove Manager Role" class="btn btn-danger"><br>
                  </form>
              <?php   }
              }
              ?>
              <label>User ID: </label> <?=$userdetails->id?><?php if($act==1) {?> <br>
                <?php if($userdetails->email_verified==1) {?> Email Verified <input type="hidden" name="email_verified" value="1" />
              <?php } elseif($userdetails->email_verified==0) {?> Email Unverified -
                <label class="normal"><br><input type="checkbox" name="email_verified" value="1" />
                  Verify</label><?php } else {?>Error: No Validation<?php } } ?>

                    <br><label>Joined: </label> <?=$userdetails->join_date?>

                    <br><label>Last Login: </label> <?php if($userdetails->last_login != 0) { echo $userdetails->last_login; } else {?> <i>Never</i> <?php }?><br/>
                  </div>

                </div>


                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <label>Username:</label>
                          <input  class='form-control' type='text' name='unx' value='<?=$userdetails->username?>' autocomplete="new-password" />
                        </div>

                        <div class="form-group">
                          <label>Email:</label>
                          <input class='form-control' type='text' name='emx' value='<?=$userdetails->email?>' autocomplete="new-password" />
                        </div>

                        <div class="form-group">
                          <label>First Name:</label>
                          <input  class='form-control' type='text' name='fnx' value='<?=$userdetails->fname?>' autocomplete="new-password" />
                        </div>

                        <div class="form-group">
                          <label>Last Name:</label>
                          <input  class='form-control' type='text' name='lnx' value='<?=$userdetails->lname?>' autocomplete="new-password" />
                        </div>

                        <div class="form-group">
                          <label>New Password (<?=$settings->min_pw?> char min, <?=$settings->max_pw?> max.)</label>
                          <input class='form-control' type='password' autocomplete="new-password" name='pwx' <?php if((!in_array($user->data()->id, $master_account) && in_array($userId, $master_account) || !in_array($user->data()->id, $master_account) && $userdetails->protected==1) && $userId != $user->data()->id) {?>disabled<?php } ?>/>
                        </div>

                        <div class="form-group">
                          <label>Confirm Password</label>
                          <input class='form-control' type='password' autocomplete="new-password" name='confirm' <?php if((!in_array($user->data()->id, $master_account) && in_array($userId, $master_account) || !in_array($user->data()->id, $master_account) && $userdetails->protected==1) && $userId != $user->data()->id) {?>disabled<?php } ?>/>
                        </div>

                        <?php if($act == 1){?>
                        <div class="form-group">
                          <label><input type="checkbox" name="sendPwReset" id="sendPwReset" /> Send Reset Email? Will expire in <?=$settings->reset_vericode_expiry?> minutes.</label><br>
                        </div>
                      <?php } ?>

                        <div class="form-group">
                          <label> Deactivate<a class="nounderline" data-toggle="tooltip" title="Does not remove them from your user count"><font color="blue">?</font></a></label>
                          <select name="active" class="form-control">
                            <option value="1" <?php if ($userdetails->permissions==1){echo "selected='selected'";} else { if(!checkMenu(2,$user->data()->id)){  ?>disabled<?php }} ?>>No</option>
                            <option value="0" <?php if ($userdetails->permissions==0){echo "selected='selected'";} else { if(!checkMenu(2,$user->data()->id)){  ?>disabled<?php }} ?>>Yes</option>
                          </select>
                        </div>

                        <div class="form-group">
                          <label> Force Password Reset<a class="nounderline" data-toggle="tooltip" title="The user will be required to create a new password on next login"><font color="blue">?</font></a></label>
                          <select name="force_pr" class="form-control">
                            <option <?php if ($userdetails->force_pr==0){echo "selected='selected'";} ?> value="0">No</option>
                            <option <?php if ($userdetails->force_pr==1){echo "selected='selected'";} ?>value="1">Yes</option>
                          </select>
                        </div>


                          <div class="form-group">
                            <label>Delete this User<a class="nounderline" data-toggle="tooltip" title="Completely delete a user. This cannot be undone."><font color="blue">?</font></a></label>
                            <select name='delete[<?php echo "$userId"; ?>]' id='delete[<? echo "$userId"; ?>]' class="form-control">
                              <option selected='selected' disabled>No</option>
                              <option value="<?=$userId?>"  <?php if (!checkMenu(2,$user->data()->id) && !in_array($user->data()->id,$master_account)){  echo "disabled";} ?>>Yes - Cannot be undone!</option>
                            </select>
                          </div>
                          <label>Permissions</label>
                          <br>
                          <?php
                          if($z){
                          foreach($perms as $p){
                            $check = $db->query("SELECT * FROM user_permission_matches WHERE permission_id = ? AND user_id = ?",[$p,$userdetails->id])->count();
                            ?>
                            <div class="col-4">

                              <input type="checkbox" name="perms[]" value="<?=$p?>" <?php if($check > 0){echo "checked";}?>><?php echoPerm($p);?>
                            </div>
                              <?php
                             }
                            }
                            ?>

                          <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
                          <div class="pull-right">
                            <div class="btn-group"><input class='btn btn-primary' type='submit' value='Update' class='submit' /></div>
                            <div class="btn-group"><a class='btn btn-warning' href="<?=$us_url_root?>users/account.php">Cancel</a></div><br /><Br />
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>




<?php } //end sassmgr in include
