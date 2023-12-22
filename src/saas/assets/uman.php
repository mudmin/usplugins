<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $lang;
if(saasMgr()){
  //Manually Add User
  if(!empty($_POST['addUser'])) {
    
    if(!$usersLeft){Redirect::to('account.php?err=You+are+out+of+user+space.');}
    $vericode_expiry=date("Y-m-d H:i:s",strtotime("+$settings->join_vericode_expiry hours",strtotime(date("Y-m-d H:i:s"))));
    $join_date = date("Y-m-d H:i:s");
    $fname = Input::get('fname');
    $lname = Input::get('lname');
    $email = Input::get('email');
    if($settings->auto_assign_un==1) {
      $username=username_helper($fname,$lname,$email);
      if(!$username) $username=NULL;
    } else {
      $username=Input::get('username');
    }
    $token = $_POST['csrf'];

    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }

    $form_valid=FALSE; // assume the worst
    if($settings->auto_assign_un==0) {
      $validation->check($_POST,array(
        'username' => array(
          'display' => 'Username',
          'required' => true,
          'min' => $settings->min_un,
          'max' => $settings->max_un,
          'unique' => 'users',
        ),
        'fname' => array(
          'display' => 'First Name',
          'required' => true,
          'min' => 1,
          'max' => 100,
        ),
        'lname' => array(
          'display' => 'Last Name',
          'required' => true,
          'min' => 1,
          'max' => 100,
        ),
        'email' => array(
          'display' => 'Email',
          'required' => true,
          'valid_email' => true,
          'unique' => 'users',
          'min' => 5,
          'max' => 100,
        ),

        'password' => array(
          'display' => 'Password',
          'required' => true,
          'min' => $settings->min_pw,
          'max' => $settings->max_pw,
        ),
        'confirm' => array(
          'display' => 'Confirm Password',
          'required' => true,
          'matches' => 'password',
        ),
      )); }
      if($settings->auto_assign_un==1) {
        $validation->check($_POST,array(
          'fname' => array(
            'display' => 'First Name',
            'required' => true,
            'min' => 1,
            'max' => 60,
          ),
          'lname' => array(
            'display' => 'Last Name',
            'required' => true,
            'min' => 1,
            'max' => 60,
          ),
          'email' => array(
            'display' => 'Email',
            'required' => true,
            'valid_email' => true,
            'unique' => 'users',
          ),

          'password' => array(
            'display' => 'Password',
            'required' => true,
            'min' => $settings->min_pw,
            'max' => $settings->max_pw,
          ),
          'confirm' => array(
            'display' => 'Confirm Password',
            'required' => true,
            'matches' => 'password',
          ),
        ));
      }
    
      if($validation->passed()) {
        $form_valid=TRUE;
        try {
          // echo "Trying to create user";
          $fields=array(
            'username' => $username,
            'fname' => ucfirst(Input::get('fname')),
            'lname' => ucfirst(Input::get('lname')),
            'email' => Input::get('email'),
            'password' =>
            password_hash(Input::get('password'), PASSWORD_BCRYPT, array('cost' => 12)),
            'permissions' => 1,
            'account_owner' => $user->data()->account_owner,
            'join_date' => $join_date,
            'email_verified' => 1,
            'active' => 1,
            'vericode' => randomstring(15),
            'force_pr' => $settings->force_pr,
            'vericode_expiry' => $vericode_expiry,
            'oauth_tos_accepted' => true
          );
          $db->insert('users',$fields);
      
          $theNewId=$db->lastId();
          // bold($theNewId);
          $perm = Input::get('perm');
          $addNewPermission = array('user_id' => $theNewId, 'permission_id' => 1);
          $db->insert('user_permission_matches',$addNewPermission);

          include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
          if(isset($_POST['sendEmail'])) {
            $userDetails = fetchUserDetails(NULL, NULL, $theNewId);
            $params = array(
              'username' => $username,
              'password' => Input::get('password'),
              'sitename' => $settings->site_name,
              'force_pr' => $settings->force_pr,
              'fname' => Input::get('fname'),
              'email' => rawurlencode($userDetails->email),
              'vericode' => $userDetails->vericode,
              'join_vericode_expiry' => $settings->join_vericode_expiry
            );
            $to = rawurlencode($email);
            $subject = 'Welcome to '.$settings->site_name;
            $body = email_body('_email_adminUser.php',$params);
            email($to,$subject,$body);
          }
          logger($user->data()->id,"SAAS Manager","Added user $username.");
          usSuccess("User Added");
          Redirect::to('account.php');
        } catch (Exception $e) {
          die($e->getMessage());
        }

      }else{
        display_errors($validation->errors());
      }
    }
    $planinfo = saasPlanInfo($user->data()->account_owner);
    if($planInfo->perms == ""){
      $z = false;
    }else{
      $z = true;
    }
    ?>
    <div class="card mt-2">
      <div class="card-header">Your Users</div>
      <div class="card-body">
        <?php if ($usersLeft) { ?>
          <a class="pull-right" href="#" data-toggle="modal" data-target="#adduser" data-bs-toggle="modal" data-bs-target="#adduser">
            <font color="blue"><i class="fa fa-plus"></i> Manually Add User</a></font>
          <?php } ?>
          <table class="table table-striped paginate">
            <?php if($z){
              $perms = explode(',',$planInfo->perms);
              $string = '';
              foreach($perms as $p){
                $q = $db->query("SELECT * FROM permissions WHERE id = ?",[$p]);
                $c = $q->count();
                if($c > 0){
                  $f = $q->first();
                  $string .= $p." - ".$f->name.", ";
                }
              }
              echo "<br>Available Permissions: ".substr($string,0,-2);
            }
            ?>
            <thead>
              <tr>
                <th>Name</th><th>Email</th><th>Manager?</th>
                <?php if($z){?><th>Permissions</th><?php } ?>
                <th>Manage</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($users as $u){?>
                <tr>
                  <td><?=$u->fname?> <?=$u->lname?>  (<?=$u->username?>) </td>
                  <td><?=$u->email?></td>
                  <td><?php if(in_array($u->id,$mgrs)){bin(1);}else{bin(0);}?></td>
                  <?php if($z){
                    $yours = $db->query("SELECT * FROM user_permission_matches WHERE permission_id > 2 AND user_id = ?",[$u->id])->results();
                    $string = "";
                    foreach($yours as $y){$string .= $y->permission_id.',';}
                    echo "<td>";
                    echo substr($string,0,-1);
                    echo "</td>";
                  } ?>
                  <td><a href="account.php?v=manage&u=<?=$u->id?>">Manage</a></td>
                </tr>

              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="adduser" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal">&times;</button>
              <h4 class="modal-title">User Addition</h4>
            </div>
            <div class="modal-body">
              <form class="form-signup" action="" method="POST">
                <div class="panel-body">
                  <?php if($settings->auto_assign_un==0) {?><label>Username: </label>&nbsp;&nbsp;<span id="usernameCheck" class="small"></span><input type="text" class="form-control" id="username" name="username" placeholder="Username" autocomplete="new-password" value="<?php if (!$form_valid && !empty($_POST)){ echo $username;} ?>" required><?php } ?>
                    <label>First Name: </label><input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" value="<?php if (!$form_valid && !empty($_POST)){ echo $fname;} ?>" required autocomplete="new-password">
                    <label>Last Name: </label><input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" value="<?php if (!$form_valid && !empty($_POST)){ echo $lname;} ?>" required autocomplete="new-password">
                    <label>Email: </label><input  class="form-control" type="text" name="email" id="email" placeholder="Email Address" value="<?php if (!$form_valid && !empty($_POST)){ echo $email;} ?>" required autocomplete="new-password">
                    <label>Password: </label>
                    <div class="input-group" data-container="body">
                      <span class="input-group-addon password_view_control" id="addon1"><span class="fa fa-eye"></span></span>
                      <input  class="form-control" type="password" name="password" id="password" <?php if($settings->force_pr==1) { ?>value="<?=$random_password?>" readonly<?php } ?> placeholder="Password" required autocomplete="new-password" aria-describedby="passwordhelp">
                      <?php if($settings->force_pr==1) { ?>
                        <span class="input-group-addon" id="addon2"><a class="nounderline pwpopover" data-container="body" data-toggle="popover" data-placement="top" data-content="The Administrator has manual creation password resets enabled. If you choose to send an email to this user, it will supply them with the password reset link and let them know they have an account. If you choose to not, you should manually supply them with this password (discouraged).">Why can't I edit this?</a></span>
                      <?php } ?>
                    </div>
                    <label>Confirm Password: </label>
                    <div class="input-group" data-container="body">
                      <span class="input-group-addon password_view_control" id="addon1"><span class="fa fa-eye"></span></span>
                      <input  type="password" id="confirm" name="confirm" <?php if($settings->force_pr==1) { ?>value="<?=$random_password?>" readonly<?php } ?> class="form-control" autocomplete="new-password" placeholder="Confirm Password" required >
                      <?php if($settings->force_pr==1) { ?>
                        <span class="input-group-addon" id="addon2"><a class="nounderline pwpopover" data-container="body" data-toggle="popover" data-placement="top" data-content="The Administrator has manual creation password resets enabled. If you choose to send an email to this user, it will supply them with the password reset link and let them know they have an account. If you choose to not, you should manually supply them with this password (discouraged).">Why can't I edit this?</a></span>
                      <?php } ?>
                    </div>

                    <?php include($abs_us_root.$us_url_root.'usersc/scripts/additional_join_form_fields.php'); ?>
                    <?php if($act==1){?>
                      <label><input type="checkbox" name="sendEmail" id="sendEmail" checked /> Send Email?</label>
                    <?php } ?>
                    <br />
                  </div>
                  <div class="modal-footer">
                    <div class="btn-group">
                      <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
                      <input class='btn btn-primary' type='submit' id="addUser" name="addUser" value='Add User' class='submit' /></div>
                      <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>


          <script type="text/javascript" src="js/pagination/datatables.min.js"></script>
          <script src="js/jwerty.js"></script>

          <script>
          $(document).ready(function() {
            jwerty.key('esc', function(){
              $('.modal').modal('hide');
            });
            $('#paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});

            $('.password_view_control').hover(function () {
              $('#password').attr('type', 'text');
              $('#confirm').attr('type', 'text');
            }, function () {
              $('#password').attr('type', 'password');
              $('#confirm').attr('type', 'password');
            });


            $('[data-toggle="popover"], .pwpopover').popover();
            $('.pwpopover').on('click', function (e) {
              $('.pwpopover').not(this).popover('hide');
            });
            $('.modal').on('hidden.bs.modal', function () {
              $('.pwpopover').popover('hide');
            });
          });
        </script>

        <?php if($settings->auto_assign_un==0) { ?>
          <script type="text/javascript">
          $(document).ready(function(){
            var x_timer;
            $("#username").keyup(function (e){
              clearTimeout(x_timer);
              var username = $(this).val();
              if (username.length > 0) {
                x_timer = setTimeout(function(){
                  check_username_ajax(username);
                }, 500);
              }
              else $('#usernameCheck').text('');
            });

            function check_username_ajax(username){
              $("#usernameCheck").html('Checking...');
              $.post('parsers/existingUsernameCheck.php', {'username': username}, function(response) {
                if (response == 'error') $('#usernameCheck').html('There was an error while checking the username.');
                else if (response == 'taken') { $('#usernameCheck').html('<i class="fa fa-times" style="color: red; font-size: 12px"></i> This username is taken.');
                $('#addUser').prop('disabled', true); }
                else if (response == 'valid') { $('#usernameCheck').html('<i class="fa fa-thumbs-o-up" style="color: green; font-size: 12px"></i> This username is not taken.');
                $('#addUser').prop('disabled', false); }
                else { $('#usernameCheck').html('');
                $('#addUser').prop('disabled', false); }
              });
            }
          });
        </script>
      <?php } ?>

      <script>
      $(document).ready(function() {
        $('.paginate').DataTable(
          {  searching: true,
            "pageLength": 10,"stateSave": true,
            "ordering": false,
            "aLengthMenu": [[10, 25, 50, 100,-1], [10, 25, 50,100,"All"]],
          }
        );
      } );
    </script>

  <?php } //end sassmgr in include
