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
require_once __DIR__ . '/vendor/autoload.php';
use League\OAuth2\Client\Provider\Google;


$googleSettings = $db->query('SELECT * FROM plg_google_login')->first();

if ($settings->glogin==0){
  die("Google Login is not enabled. Please enable it in the admin panel. Something fishy is going on.");
}

//Getting the Google Info from the DB
$clientId = $googleSettings->gid; //Google CLIENT ID
$clientSecret = $googleSettings->gsecret; //Google CLIENT SECRET
$redirectUrl = $googleSettings->gredirect;  //return url (url to script)

$provider = new Google([
  'clientId'     => $clientId,
  'clientSecret' => $clientSecret,
  'redirectUri'  => $redirectUrl,
  'accessType'   => 'online'
]);

?>
