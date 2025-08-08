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
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted 
require_once $abs_us_root.$us_url_root.'usersc/plugins/google_login/assets/Google/Google_Client.php';
require_once $abs_us_root.$us_url_root.'usersc/plugins/google_login/assets/Google/contrib/Google_Oauth2Service.php';
if(!isset($settings)){
$settings = $db->query('SELECT * FROM settings')->first();
}
if ($settings->glogin==0){
  die();
}
$gurl = $abs_us_root.$us_url_root;

//Getting the Google Info from the DB
$clientId = $settings->gid; //Google CLIENT ID
$clientSecret = $settings->gsecret; //Google CLIENT SECRET
$redirectUrl = $settings->gredirect;  //return url (url to script)
$homeUrl = $settings->ghome;  //return to home

$gClient = new Google_Client();
$gClient->setApplicationName('Login to '.$settings->site_name);
$gClient->setClientId($clientId);
$gClient->setClientSecret($clientSecret);
$gClient->setRedirectUri($redirectUrl);

$google_oauthV2 = new Google_Oauth2Service($gClient);
?>
