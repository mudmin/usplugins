<?php
require_once '../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/assets/oauth_provider.php';
$oauthSettings = $db->query("SELECT * FROM plg_oauth_server_settings")->first();

$oauthProvider = new OAuthProvider();

// Check if the user is already logged in
$user = new User();
if ($user->isLoggedIn()) {
    // The user is already logged in, so we skip the login form
    $clientId = Input::get('client_id');
    $state = Input::get('state');

    // Retrieve redirect_uri from the database based on client_id

    $clientDataQ = $db->query("SELECT * FROM plg_oauth_server_clients WHERE client_id = ?", [$clientId]);
    $clientDataC = $clientDataQ->count();
    if($clientDataC > 0){
        $clientData = $clientDataQ->first();
    }else{
        die('Client not found');
    }
    $redirectUri = $clientData->redirect_uri;

    // Generate the auth code
    $authCode = $oauthProvider->generateAuthCode($user->data()->id, $clientId, $redirectUri);
    logger(1, "OAuth Server", "Generated Auth Code: $authCode for client: $clientId");

    // Collect user data and encode it
    $response = [];
    $response['userdata']['fname'] = $user->data()->fname;
    $response['userdata']['lname'] = $user->data()->lname;
    $response['userdata']['email'] = $user->data()->email;

    if($clientData->login_script != "" && file_exists($abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/login_scripts/' . $clientData->login_script)){
        include $abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/login_scripts/' . $clientData->login_script;
    }

    $response = base64_encode(json_encode($response));

    // Construct the redirect URL
    $redirectUrl = $redirectUri . '?code=' . $authCode;
    $redirectUrl .= '&response=' . urlencode($response);
    if (!empty($state)) {
        $redirectUrl .= '&state=' . urlencode($state);
    }

    // Redirect the user
    Redirect::to($redirectUrl);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grant_type']) && $_POST['grant_type'] === 'authorization_code') {
    $oauthProvider->handleTokenRequest();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    if (!Token::check(Input::get('csrf'))) {
        include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
    }

    // Handle the login form submission
    $username = Input::get('username');
    $password = Input::get('password');
    $remember = false;

    // Attempt to log the user in
    $login = $user->login($username, $password, $remember);
    if ($login) {
        // User successfully logged in, repeat the above process
        $clientId = Input::get('client_id');
        $state = Input::get('state');

        // Retrieve redirect_uri from the database based on client_id
        $clientData = $db->query("SELECT * FROM plg_oauth_server_clients WHERE client_id = ?", [$clientId])->first();
        $redirectUri = $clientData->redirect_uri;

        // Generate the auth code
        $authCode = $oauthProvider->generateAuthCode($user->data()->id, $clientId, $redirectUri);
        logger(1, "OAuth Server", "Generated Auth Code: $authCode for client: $clientId");

        // Collect user data and encode it
        $response = [];
        $response['userdata']['fname'] = $user->data()->fname;
        $response['userdata']['lname'] = $user->data()->lname;  
        $response['userdata']['email'] = $user->data()->email;

        if($clientData->login_script != "" && file_exists($abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/login_scripts/' . $clientData->login_script)){
            include $abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/login_scripts/' . $clientData->login_script;
        }

        $response = base64_encode(json_encode($response));

        // Construct the redirect URL
        $redirectUrl = $redirectUri . '?code=' . $authCode;
        $redirectUrl .= '&response=' . urlencode($response);
        if (!empty($state)) {
            $redirectUrl .= '&state=' . urlencode($state);
        }

        // Redirect the user
        Redirect::to($redirectUrl);
        exit;
    } else {
        // Login failed, show error message
        $error = "Invalid username or password";
        $authData = json_decode(base64_decode(Input::get('authData')), true);

      
       
    }
} else {
    // This is the initial OAuth request, show login form
    $authData = $oauthProvider->handleAuthorizationRequest();

    if (!is_array($authData)) {
        // There was an error, handleAuthorizationRequest() has already sent the response
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$authData['login_title']?></title>
   
</head>
<body>
<form method="post" action="">
<?= tokenHere(); ?>
<input type="hidden" name="authData" value="<?=base64_encode(json_encode($authData));?>">
<input type="hidden" name="client_id" value="<?php echo htmlspecialchars($authData['client_id']); ?>">
<input type="hidden" name="state" value="<?php echo htmlspecialchars($authData['state']); ?>">
<?php 

if (file_exists($abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/login_forms/' . $authData['login_form'])) {
    include $abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/login_forms/' . $authData['login_form'];
} else {
    include $abs_us_root . $us_url_root . 'usersc/plugins/oauth_server/login_forms/default_login.php';
}
?>
    


    </form>
</body>
</html>
