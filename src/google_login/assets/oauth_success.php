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
require_once '../../../../users/init.php';
$googleSettings = $db->query('SELECT * FROM plg_google_login')->first();
if($settings->glogin==1 && !$user->isLoggedIn()){
	require_once $abs_us_root.$us_url_root.'usersc/plugins/google_login/assets/google_helpers.php';
	if (!empty($_GET['error'])) {
		usError(Input::get('error'));
		Redirect::to($us_url_root);
		die();
	} elseif (empty($_GET['code'])) {
		Redirect::to($us_url_root);
		die();
	} elseif (empty($_GET['state']) || $_GET['state'] !== $_SESSION['google_state']) {
		unset($_SESSION['google_state']);
		Redirect::to($us_url_root);
		die();
	} else {
		$token = $provider->getAccessToken('authorization_code', [
			'code' => Input::get('code')
		]);

		try {
			$userProfile = $provider->getResourceOwner($token);
		} catch (Exception $e) {
			Redirect::to($us_url_root);
			die();
		}

	}
	//User Authenticated by Google
	$fields = [
		"gpluslink" => 'https://plus.google.com/'.$userProfile->getId(),
		"picture" => $userProfile->getAvatar(),
		'locale' => $userProfile->getLocale(),
		'oauth_provider' => "google",
		'oauth_uid' => $userProfile->getId(),
		"fname" => $userProfile->getFirstName(),
		"lname" => $userProfile->getLastName(),
		"email" => $userProfile->getEmail(),
	];

	socialLogin($userProfile->getEmail(), null, ["oauth_uid"=>$userProfile->getId()], $fields);
}
