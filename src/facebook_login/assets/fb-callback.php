<?php
$noMaintenanceRedirect = true; 
require_once '../../../../users/init.php';

$fbSettings = $db->query("SELECT * FROM plg_facebook_login")->first();

$appID=$fbSettings->fbid;
$secret=$fbSettings->fbsecret;
$version=$fbSettings->graph_ver;
$callback=$fbSettings->fbcallback;

require_once $abs_us_root.$us_url_root."usersc/plugins/facebook_login/assets/vendor/autoload.php";

$provider = new \League\OAuth2\Client\Provider\Facebook([
    'clientId'          => $appID,
    'clientSecret'      => $secret,
    'redirectUri'       => $callback,
    'graphApiVersion'   => $version,
]);

// No code set
if (!isset($_GET['code'])) {
  Redirect::to($us_url_root);
  die();
}

// Invalid state
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['facebook_state'])) {
  unset($_SESSION['facebook_state']);
  Redirect::to($us_url_root);
  die();
}

$token = $provider->getAccessToken('authorization_code', [
  'code' => $_GET['code']
]);

try {
  $user = $provider->getResourceOwner($token);

} catch (\Exception $e) {
  unset($_SESSION['facebook_state']);
  Redirect::to($us_url_root);
  die();
}

//Check to see if the user has granted email permission
$fbEmail = $user->getEmail();
if (!$fbEmail) {
  $helper = $provider->getAuthorizationUrl([
    'scope' => ['email'],
  ]);
  $_SESSION['facebook_state'] = $provider->getState();
  header("Location: " . $helper);
  exit;
}

$fields = [
  "email" => $fbEmail,
  "fname" => $user->getFirstName(),
  "lname" => $user->getLastName(),
  "fb_uid" => $user->getId(),
  "picture" => $user->getPictureUrl(),
];

// call userspice's built in social login function and specify the login type for totp
socialLogin($user->getEmail(), null, ["fb_uid"=>$user->getId()], $fields, "facebook");

?>
