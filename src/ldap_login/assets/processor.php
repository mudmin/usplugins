<script type="text/javascript">
  alert("here");
</script>
<?php
// config
$ldapserver = $settings->ldap_server;          // from LDAP configuration page inside US
$ldapAdmin_user = $settings->ldap_admin;     // if user has admin search priviliges on all LDAP, then no need to define the whole CN. just the username will be enough // from LDAP configuration page inside US
$ldapAdmin_pass  = $settings->ldap_admin_pw;  // from LDAP configuration page inside US
$ldaptree   = $settings->ldap_tree;       // from LDAP configuration page inside US
$ldapPort = $settings->ldap_port;
$ldapVersion =  $settings->ldap_version;
$username = Input::get('username');         // from login form
$password = Input::get('password');       // from login form
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
            echo "userDN = $userDN <br>";
            //Now we need to bind the logged in user
            $ldapBindUser = ldap_bind($ldapconn, $userDN, $password);
            if($ldapBindUser){
                // Show user's data
                // https://www.manageengine.com/products/ad-manager/help/csv-import-management/active-directory-ldap-attributes.html
                echo '<pre>';
                foreach($attribute_mapping as $am) {
                    echo $am['us'].": ".$data[0][$am['ldap']][0].', <br>';
                }
                //echo 'Full name: '.$data[0]["displayname"][0].'<br>';
                //echo 'First name: '.$data[0]["givenname"][0].'<br>';
                //echo 'Last name: '.$data[0]["sn"][0].'<br>';
                //echo 'username: '.$data[0]["uid"][0].'<br>';  // use uid,sAMAccountName, or userPrincipalName
                //echo 'Email: '.$data[0]["mail"][0].'<br>';
                //echo 'Title: '.$data[0]["title"][0].'<br>';
                //echo 'Department: '.$data[0]["department"][0].'<br>';
                //echo 'Employee #: '.$data[0]["employeeid"][0].'<br>';
                echo '</pre>';
            } else {
                echo "LDAP user bind failed... (not athenticated)";
            }
            //echo '<h1>Dump all data</h1><pre>';
            //print_r($data);
            //echo '</pre>';
        } else {
            echo "LDAP user bind failed... (user not found)";
        }
    } else {
        echo "LDAP admin bind failed...";
    }

}

// all done? clean up
ldap_close($ldapconn);
