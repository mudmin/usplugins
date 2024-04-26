<?php
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
$fbSettings = $db->query("SELECT * FROM plg_facebook_login")->first();

$appID=$fbSettings->fbid;
$secret=$fbSettings->fbsecret;
$version=$fbSettings->graph_ver;
$callback=$fbSettings->fbcallback;

if(!isset($_SESSION)){session_start();}
require_once $abs_us_root.$us_url_root."usersc/plugins/facebook_login/assets/vendor/autoload.php";

$provider = new \League\OAuth2\Client\Provider\Facebook([
    'clientId'          => $appID,
    'clientSecret'      => $secret,
    'redirectUri'       => $callback,
    'graphApiVersion'   => $version,
]);

$link = $provider->getAuthorizationUrl([
  'scope' => ['email'],
]);

$_SESSION['facebook_state'] = $provider->getState();

?>
