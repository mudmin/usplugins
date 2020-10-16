<?php
if(count(get_included_files()) == 1) die(); //Direct Access Not Permitted

global $settings;
$username = Input::get('username');
$password = Input::get('password');
$sessionName = Config::get('session/session_name');

$ldapserver = $settings->ldap_server;          // from LDAP configuration page inside US
$ldapAdmin_user = $settings->ldap_admin;     // if user has admin search priviliges on all LDAP, then no need to define the whole CN. just the username will be enough // from LDAP configuration page inside US
$ldapAdmin_pass  = $settings->ldap_admin_pw;  // from LDAP configuration page inside US
$ldaptree   = $settings->ldap_tree;       // from LDAP configuration page inside US
$ldapPort = $settings->ldap_port;
$ldapVersion =  $settings->ldap_version;

$ldap_search_entry = "(|(cn=$username)(uid=$username)(sAMAccountName=$username))";
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
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

if($ldapconn) {
    // binding to ldap server
    $ldapbindAdmin = ldap_bind($ldapconn, $ldapAdmin_user, $ldapAdmin_pass) or die ("Error trying to bind: ".ldap_error($ldapconn));
    // verify binding
    if ($ldapbindAdmin) {
        // echo "LDAP admin bind successful...<br /><br />";

        // now we need to search for the logged in username.

        $result = ldap_search($ldapconn,$ldaptree, $ldap_search_entry) or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);
        if(isset($data[0]["dn"]) && !empty($data[0]["dn"])) {
            $userDN = $data[0]["dn"];
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
            $user = new User;
            Session::put($sessionName, $lookup->id);
            $fields = array(
            'fname' => Input::sanitize($data[0]["givenname"][0]),
            'lname' => Input::sanitize($data[0]["sn"][0])
            );
            $db->update('users',$lookup->id,$fields);
            $db->update('users',$lookup->id, ['logins'=>$lookup->logins+1]);
            $_SESSION['ldaplogin'] = 1;
            $hooks = getMyHooks(['page'=>'loginSuccess']);
            includeHook($hooks,'body'); //Allow for other hooks like 2 Factor Authentication
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
            $user = new User;
            Session::put($sessionName, $theNewId);
            
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
        echo "LDAP user bind failed... (not authenticated)";
    }
} else {
    echo "LDAP user bind failed... (user not found)";
}

// all done? clean up
ldap_close($ldapconn);
//if you made it this far, login failed.

//Redirect::to('login.php?err=Error');