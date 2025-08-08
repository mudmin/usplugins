<?php
$noMaintenanceRedirect = true; 
require_once '../../../../users/init.php';

$db=DB::getInstance();

$settingsQ=$db->query("SELECT * FROM settings");
$settings=$settingsQ->first();

if(!isset($_SESSION)){session_start();}

$clientId=$settings->twclientid;
$secret=$settings->twclientsecret;
$callback=$settings->twcallback;
$whereNext=$settings->finalredir;

require_once($abs_us_root.$us_url_root."usersc/plugins/twitch_login/assets/twitch.php");
$provider = new TwitchProvider([
    'clientId'                => $clientId,     // The client ID assigned when you created your application
    'clientSecret'            => $secret, // The client secret assigned when you created your application
    'redirectUri'             => $callback,  // Your redirect URL you specified when you created your application
    'scopes'                  => ['user:read:email']  // The scopes you would like to request
]);

if (empty(Input::get('state')) || (isset($_SESSION['twitchstate']) && Input::get('state') !== $_SESSION['twitchstate'])) {
    if (isset($_SESSION['twitchstate'])) {
        unset($_SESSION['twitchstate']);
    }
    Redirect::to($us_url_root);
    die();
}
try {
$accessToken = $provider->getAccessToken('authorization_code', [
	'code' => Input::get('code')
]);
$resourceOwner = $provider->getResourceOwner($accessToken);
$twuser = $resourceOwner->toArray();
$twUsername = $twuser['data'][0]['login'];
$twId = $twuser['data'][0]['id'];

}catch (Exception $e) {
	unset($_SESSION['twitchstate']);
  Redirect::to($us_url_root);
  die();
}


$fields = [
  "tw_uid" => $twId,
  "tw_uname" => $twUsername
];
if(!empty($twuser['data'][0]['email'])) $fields['email'] = $twuser['data'][0]['email'];

socialLogin($twuser['data'][0]['email'], $twUsername, ["tw_uid"=>$twId], $fields, "twitch");
