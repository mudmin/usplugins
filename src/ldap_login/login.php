<?php
// This is a user-facing page
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set("allow_url_fopen", 1);
if(isset($_SESSION)){session_destroy();}
require_once '../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
include "plugin_info.php";
pluginActive($plugin_name);
?>
<?php
if(ipCheckBan()){Redirect::to($us_url_root.'usersc/scripts/banned.php');die();}
$errors = [];
$successes = [];
if (@$_REQUEST['err']) $errors[] = $_REQUEST['err']; // allow redirects to display a message
$reCaptchaValid=FALSE;
if($user->isLoggedIn()) Redirect::to($us_url_root.'index.php');

if (!empty($_POST['login_hook'])) {
  $token = Input::get('csrf');
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  //Check to see if recaptcha is enabled
  if($settings->recaptcha == 1){
    //require_once $abs_us_root.$us_url_root.'users/includes/recaptcha.config.php';

    //reCAPTCHA 2.0 check
    $response = null;

    // check secret key
    $reCaptcha = new \ReCaptcha\ReCaptcha($settings->recap_private);

    // if submitted check response
    if ($_POST["g-recaptcha-response"]) {
      $response = $reCaptcha->verify($_POST["g-recaptcha-response"],$_SERVER["REMOTE_ADDR"]);
    }
    if ($response != null && $response->isSuccess()) {
      $reCaptchaValid=TRUE;
    }else{
      $reCaptchaValid=FALSE;
      $errors[] = lang("CAPTCHA_ERROR");
      $reCapErrors = $response->getErrorCodes();
      foreach($reCapErrors as $error) {
        logger(1,"Recapatcha","Error with reCaptcha: ".$error);
      }
    }
  }else{
    $reCaptchaValid=TRUE;
  }

  if($reCaptchaValid || $settings->recaptcha == 0){ //if recaptcha valid or recaptcha disabled

    //We are going to allow 1 non-ldap user to login with id 1
    $username = Input::get('username');         // from login form
    $password = Input::get('password');       // from login form

    //is the person trying to login with the admin username?
    $q = $db->query("SELECT * FROM users WHERE id = 1 AND username = ?",[$username]);
    $c = $q->count();
    if($c > 0){
      $f = $q->first();
      if (password_verify($password,$f->password)) {
        $user = new User();
        $_SESSION['user'] = 1;
        $date = date("Y-m-d H:i:s");
        $db->query("UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?",[$date,1]);
        $_SESSION['last_confirm']=date("Y-m-d H:i:s");
        $db->insert('logs',['logdate' => $date,'user_id' => 1,'logtype' => "User",'lognote' => "User logged in."]);
        $ip = ipCheck();
        $q = $db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
        $c = $q->count();
        if($c < 1){
          $db->insert('us_ip_list', array(
            'user_id' => 1,
            'ip' => $ip,
          ));
        }else{
          $f = $q->first();
          $db->update('us_ip_list',$f->id, array(
            'user_id' => 1,
            'ip' => $ip,
          ));
        }
        if (file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php')) {
          require_once $abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php';
        }
        Redirect::to($us_url_root.'users/admin.php');
      }else{
        Redirect::to("login.php?err=error");
      }
    }else{
    $ldapserver = $settings->ldap_server;          // from LDAP configuration page inside US
    $ldapAdmin_user = $settings->ldap_admin;     // if user has admin search priviliges on all LDAP, then no need to define the whole CN. just the username will be enough // from LDAP configuration page inside US
    $ldapAdmin_pass  = $settings->ldap_admin_pw;  // from LDAP configuration page inside US
    $ldaptree   = $settings->ldap_tree;       // from LDAP configuration page inside US
    $ldapPort = $settings->ldap_port;
    $ldapVersion =  $settings->ldap_version;

    $ldap_search_entry = "(|(cn=$username)(uid=$username))";        // or we can add "mail" if we want to allow users to login with email
    $attribute_mapping_definition = "email:mail,username:uid,fname:givenname,lname:sn";     // map userSpice DB field with LDAP attribute

    // prepare attribte mapping array
    $attribute_mapping = array();
    foreach(explode(',',$attribute_mapping_definition) as $am) {
        $fields_mapping = explode(':',$am);
        if(count($fields_mapping) == 2)
            $attribute_mapping[] = array( "us" => $fields_mapping[0], "ldap" => $fields_mapping[1]);
    }

    // connect
    //ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    $ldapconn = ldap_connect($ldapserver, $ldapPort) or die("Could not connect to LDAP server.");
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, $ldapVersion);

    if($ldapconn) {
        // binding to ldap server
        $ldapbindAdmin = ldap_bind($ldapconn, $ldapAdmin_user, $ldapAdmin_pass) or die ("Error trying to bind: ".ldap_error($ldapconn));
        // verify binding
        if ($ldapbindAdmin) {
            echo "LDAP admin bind successful...<br /><br />";

            // now we need to search for the logged in username.

            $result = ldap_search($ldapconn,$ldaptree, $ldap_search_entry) or die ("Error in search query: ".ldap_error($ldapconn));
            $data = ldap_get_entries($ldapconn, $result);
            $userDN = $data[0]["dn"];
            if(!empty($userDN)) {
              $email = Input::sanitize($data[0]["mail"][0]);
                // echo "userDN = $userDN <br>";
                //Now we need to bind the logged in user
                $ldapBindUser = ldap_bind($ldapconn, $userDN, $password);
                if($ldapBindUser){
                  //user has been authenticated. Let's get their User Account or Create one.
                  $lookupQ = $db->query("SELECT * FROM users WHERE email = ? AND id > 1",[$email]);
                  $lookupC = $lookupQ->count();
            if($lookupC > 0){
              $lookup = $lookupQ->first();
              $_SESSION['user'] = $lookup->id;
              $fields = array(
                'fname' => Input::sanitize($data[0]["givenname"][0]),
                'lname' => Input::sanitize($data[0]["sn"][0])
              );
              $db->update('users',$lookup->id,$fields);
              $db->update('users',$lookup->id, ['logins'=>$lookup->logins+1]);
              if(file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir')){
                include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir');
              }
              if(file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script')){
                include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script');
              }
              Redirect::to($us_url_root.'users/account.php');
            }else {
              $checkUn = $db->query("SELECT id FROM users WHERE email = ?",[$email])->count();
              if($checkUn < 1){
                $username = $email;
              }else{
                $username = $email.randomstring(6); //close enough
              }
              $fields = array(
                'username'=>$email,
                'fname' => Input::sanitize($data[0]["givenname"][0]),
                'lname' => Input::sanitize($data[0]["sn"][0]),
                'email' => $email,
                'password' => password_hash(randomstring(20), PASSWORD_BCRYPT, array('cost' => 12)),
                'permissions' => 1,
                'account_owner' => 1,
                'join_date' => date("Y-m-d H:i:s"),
                'email_verified' => 1,
                'active' => 1,
                'logins' => 1,
                'vericode' => randomstring(12),
                'vericode_expiry' => "2016-01-01 00:00:00"
              );
              $db->insert('users',$fields);
              $theNewId = $db->lastId();
              $_SESSION['user'] = $theNewId;
              $fields = array(
                'user_id'=>$theNewId,
                'permission_id'=>1,
              );
              $db->insert('user_permission_matches',$fields);
              include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
              if (file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php')) {
                require_once $abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php';
              }
              Redirect::to($us_url_root.'users/joinThankYou.php');
            }

          }

        }


                } else {
                    echo "LDAP user bind failed... (not athenticated)";
                }
                //echo '<h1>Dump all data</h1><pre>';
                //print_r($data);
                //echo '</pre>';
            } else {
                echo "LDAP user bind failed... (user not found)";
            }
        }

    }

    // all done? clean up
    ldap_close($ldapconn);
    //if you made it this far, login failed.

      Redirect::to('login.php?err=Error');


  }

    $token = Token::generate();
    ?>
    <div id="page-wrapper">
      <div class="container">
        <?=resultBlock($errors,$successes);?>
        <div class="row">
          <div class="col-sm-12">
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <form name="login" id="login-form" class="form-signin" action="login.php" method="post">
              <input type="hidden" name="dest" value="<?= $dest ?>" />

              <div class="form-group">
                <label for="username"><?=lang("GEN_UNAME")?></label>
                <input  class="form-control" type="text" name="username" id="username" placeholder="<?=lang("GEN_UNAME")?>" required autofocus autocomplete="username">
              </div>

              <div class="form-group">
                <label for="password"><?=lang("SIGNIN_PASS")?></label>
                <input type="password" class="form-control"  name="password" id="password"  placeholder="<?=lang("SIGNIN_PASS")?>" required autocomplete="current-password">
              </div>
              <div class="form-group">
                <input type="hidden" name="login_hook" value="1">
                <input type="hidden" name="csrf" value="<?=$token?>">
                <input type="hidden" name="redirect" value="<?=Input::get('redirect')?>" />
                <button class="submit  btn  btn-primary" id="next_button" type="submit"><i class="fa fa-sign-in"></i> <?=lang("SIGNIN_BUTTONTEXT","");?></button>
                <?php
                if($settings->recaptcha == 1){
                  ?>
                  <div class="g-recaptcha" data-sitekey="<?=$settings->recap_public; ?>" data-bind="next_button" data-callback="submitForm"></div>
                <?php } ?>
              </form>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
                <p align="center"><?php languageSwitcher();?></p>
            </div>
          </div>
        </div>

        <?php require_once $abs_us_root.$us_url_root.'usersc/templates/'.$settings->template.'/container_close.php'; //custom template container ?>

        <!-- footers -->
        <?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

        <!-- Place any per-page javascript here -->

        <?php   if($settings->recaptcha == 1){ ?>
          <script src="https://www.google.com/recaptcha/api.js" async defer></script>
          <script>
          function submitForm() {
            document.getElementById("login-form").submit();
          }
          </script>
        <?php } ?>
        <?php require_once $abs_us_root.$us_url_root.'usersc/templates/'.$settings->template.'/footer.php'; //custom template footer?>
