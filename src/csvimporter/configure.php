  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);

 if(!empty($_POST['plugin_importer'])){
   $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
 }

 if (!empty($_FILES)) {
 $temp = explode(".", $_FILES["file"]["name"]);
 $newfilename = "import.csv";
 if(move_uploaded_file($_FILES["file"]["tmp_name"], $newfilename)){
   $added = [];
   $failed = [];

   $arrResult  = array();
   $handle     = fopen($newfilename, "r");
   if(empty($handle) === false) {
       while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
           $arrResult[] = $data;
       }
       fclose($handle);
   }
   foreach($arrResult as $a){
     $email = $a[0];
     $pass = $a[1];
     $un = $a[2];
     $fn = $a[3];
     $ln = $a[4];
     $rest = $a[5];
     $perms = $a[6];

     //lots of checks happening here.
     //does the email exist or is it not in a valid format.
     $emailCheck = $db->query("SELECT * FROM users WHERE email = ?",array($email))->count();
     if(($emailCheck > 0) || (!filter_var($email, FILTER_VALIDATE_EMAIL))) {
       $failed[]="Invalid Email $email - Either bad format or duplicate";
       continue;
     }

     //if the username is not specified, make it the email address
     if($un == ""){$un = $email;}
     //is the username already in the system
     $unCheck = $db->query("SELECT * FROM users WHERE username = ?",array($un))->count();
     if($unCheck > 0){
       $failed[]="Username $un already exists";
       continue;
     }

     //if last name is not specified, make it the username
     if($ln == ""){
       $ln = $un;
     }

     //if no password, create one
     if($pass == ""){$pass = randomstring(15); $rest = 1;}

     //if the password is not already encrypted in bcrypt, encrypt it
     if(substr($pass,0,4) != "$2y$"){
       $prePass = $pass;
       $pass = password_hash($pass, PASSWORD_BCRYPT, array('cost' => 12));
     }else{
       $prePass = "Pre-Encrypted Password Provided";
     }

     //get an array of valid permissions for this user
     if($perms == ""){
       $perms = [];
     }else{
       $permFail = false;
       $permArray = explode("|",$perms);
       foreach($permArray as $pa){
         if($pa == 1){continue;}
         $check = $db->query("SELECT * FROM permissions WHERE id = ?",[$pa])->count();
         if($check < 1){
           $permFail = true;
         }
       }
       if($permFail == true){
         $failed[]="Username $un was given an invalid permission of $perms";
         continue;
       }else{
         $perms = $permArray;
       }
     }

     //if you made it this far, create the user
     $theNewId = $user->create(array(
               'username' => $un,
               'fname' => $fn,
               'lname' => $ln,
               'email' => $email,
               'password' => $pass,
               'permissions' => 1,
               'account_owner' => 1,
               'join_date' => date("Y-m-d H:i:s"),
               'email_verified' => 1,
               'active' => 1,
               'vericode' => randomstring(15),
               'vericode_expiry' => date("Y-m-d H:i:s"),
               'oauth_tos_accepted' => true
       ));
       foreach($perms as $p){
         $db->insert("user_permission_matches",['permission_id'=>$p,'user_id'=>$theNewId]);
       }
       if($rest == 1){$db->update('users',$theNewId,['force_pr'=>1]);}
       include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
       logger($theNewId,"User","Registered via CSV Import");
       $added[] = [$theNewId,$email,$prePass];

   }//end foreach
   unlink($newfilename);
 }else{
   bold("Unable to move file");
 }
}
 $token = Token::generate();
 ?>
 <style media="screen">
   p {color:black;}
 </style>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>CSV Bulk Importer*</h1>
          <form class="" action="" method="post" name="plugin_importer" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?=$token?>" />
            <input type="file" name="file" value="" />
            <input type="submit" name="submit" value="Import" />
          </form>
          <?php if(isset($added)){ ?>
            <h3>Successes (<?php echo count($added);?>)</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th><th>Email</th><th>Password (<input id="chkShowPassword" type="checkbox" />
                Show password</label>)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($added as $add){?>
                  <tr>
                    <td><?=$add[0];?></td>
                    <td><?=$add[1];?></td>
                    <td><span class="pwtoggle" style="display:none;"><?=$add[2];?></span></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
            <h3>Failures (<?php echo count($failed);?>)</h3>
            <table class="table">
              <tbody>
                <?php foreach($failed as $f){?>
                  <tr>
                    <td><?php echo $f;?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>



          <?php } ?>
          <strong>*Important Information</strong><br>
          <p>This plugin does a lot of checks and hashing passwords in particular takes a while. You may want to consider importing users 100 to a few hundred at a time instead of doing thousands all at once.</p>
          <p>This plugin expects a csv with <strong>no headers</strong> and columns in this order...</p>
          <table class="table">
            <thead>
              <tr>
                <th>email(a)</th><th>password(b)</th><th>username(c)</th><th>first name(d)</th><th>last name(e)</th><th>force password reset (1 or null)(f)</th><th>Permissions (Separated by |)(g)</th>
              </tr>
            </thead>
          </table>
          <font >
          <p>a - Email is required</p>
          <p>b - If no password is provided, one will be auto-generated for you with the force-password reset flag set on their first login. The password you provide will AUTOMATICALLY be hashed
          into a format that is compatible with UserSpice UNLESS it is already in a UserSpice (bcrpyt) format.  If it is, it will begin with $2y$.  If it is in some format other than plain text
        or bcrypt, UserSpice will re-encrypt it, essentially making that account inaccessable without a password reset.</p>
          <p>c - If no username is given, the email address will be inserted into the db as the username as well</p>
          <p>e - If no last name is given, the username will be inserted as the last name</p>
          <p>f - If the password reset flag is not set, it will be assumed that the password should not be reset</p>
          <p>g - New users will be given a permission level of 1 if nothing is specified.  If you want to give them something different, that column should look like 1|3|5</p>
          <p>Additional columns will be ignored.</p>
          <p>After import, you will be given a one time list of the users that imported and those that failed.</p>
        </font>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <script type="text/javascript">
        $(function () {
            $("#chkShowPassword").bind("click", function () {
                if ($(this).is(":checked")) {
                  $(".pwtoggle").show();
                } else {
                  $(".pwtoggle").hide();
                }
            });
        });

    </script>
