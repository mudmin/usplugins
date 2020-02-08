<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
//PHP Goes Here!
$errors = $successes = [];
$e = $db->query("SELECT * FROM email")->first();
$act = $e->email_act;
$form_valid=TRUE;



// dnd($permOps);
$validation = new Validate();

  //Manually Add User
  if(!empty($_POST['addUser'])) {
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
            'account_owner' => 1,
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
          logger($user->data()->id,"SAAS","Added user $username.");
            Redirect::to('admin.php?view=plugins_config&plugin=saas&v=org');
        } catch (Exception $e) {
          die($e->getMessage());
        }

      }
    }



if(!empty($_POST['createOrg'])){
  $fields = array(
    'org'=>Input::get('org'),
    'owner'=>Input::get('owner'),
    'level'=>Input::get('plan'),
    'active'=>1,
  );
  $db->insert('us_saas_orgs',$fields);
  $id = $db->lastId();
  $db->update('users',Input::get('owner'),['account_owner'=>$id]);
  Redirect::to('admin.php?view=plugins_config&plugin=saas&v=org');
}
$orgs = $db->query("SELECT * FROM us_saas_orgs ORDER BY active DESC, org ASC")->results();
$ones = $db->query("SELECT * FROM users WHERE id > 1 AND account_owner = 1")->results();
$plans = $db->query("SELECT * FROM us_saas_levels ORDER BY users ASC")->results();
$pl = [];
$pl[0]="Reserved";
foreach($plans as $p){
  $pl[$p->id] = $p->level;
}


?>
<div class="row">
  <div class="col-12 col-sm-6">
    <h3>New Org</h3>
    <p>You must already have a user account to be the "owner" of the org.  <a class="pull-right" href="#" data-toggle="modal" data-target="#adduser"><font color="blue"><i class="fa fa-plus"></i> Manually Add User</a></font></p>
    <form class="" action="" method="post">
      <div class="form-group">
        <label>Owner</label>
        <select class="form-control" name="owner" required>
          <option value="" disabled selected>-----------</option>
          <?php foreach($ones as $o){?>
            <option value="<?=$o->id?>"><?php echouser($o->id);?></option>
          <?php } ?>
        </select>
      </div>
      <div class="form-group">
        <label>Org Name</label>
        <input class="form-control" type="text" name="org" value="" required>
      </div>
      <div class="form-group">
        <label>Plan</label>
        <select class="form-control" name="plan" required>
          <option value="" disabled selected>-----------</option>
          <?php foreach($plans as $p){?>
            <option value="<?=$p->id?>"><?=$p->details;?></option>
          <?php } ?>
        </select>
      </div>

        <input type="submit" name="createOrg" value="Create Org" class="btn btn-primary">

    </form>
</div>
  <div class="col-12 col-sm-6">
  </div>
  <div class="col-12">
    <h3>Exising Orgs</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Org</th><th>Owner</th><th>Plan</th><th>Members</th><th>Active?</th><th>Manage</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($orgs as $o){
          $members = $db->query("SELECT id FROM users WHERE account_owner = ?",[$o->id])->count();?>
          <tr>
            <td><?=$o->org?></td>
            <td><?=echouser($o->owner);?></td>
            <td><?=ucfirst($pl[$o->level]);?></td>
            <td><?=$members?></td>
            <td><?=bin($o->active);?></td>
            <td><?php if($o->id > 1){?><a href="admin.php?view=plugins_config&plugin=saas&v=manage_org&o=<?=$o->id?>">Manage</a><?php } ?></td>
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
        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
