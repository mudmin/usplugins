<?php
/*
UserSpice 5
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
?>

<?php

$db=DB::getInstance();

$settingsQ=$db->query("SELECT * FROM settings");
$settings=$settingsQ->first();

$clientId=$settings->twclientid;
$secret=$settings->twclientsecret;
$callback=$settings->twcallback;

if(!isset($_SESSION)){session_start();}
require_once($abs_us_root.$us_url_root."usersc/plugins/twitch_login/assets/twitch.php");

  
$provider = new TwitchProvider([
    'clientId'                => $clientId,     // The client ID assigned when you created your application
    'clientSecret'            => $secret, // The client secret assigned when you created your application
    'redirectUri'             => $callback,  // Your redirect URL you specified when you created your application
    'scopes'                  => ['user:read:email']  // The scopes you would like to request
]);

$link = $provider->getAuthorizationUrl();
$_SESSION['twitchstate'] = $provider->getState();

?>