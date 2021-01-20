<?php
if (!isset($user) || !$user->isLoggedIn()) {
	require ('assets/steamauthlogin.php');

	if (isset($err) && $err=="This+Steam+account+is+not+linked+to+a+user+on+this+site")
		$err = "";

	if (!isset($_SESSION['steamid']))
		loginbutton();
	else {
		include ('assets/userInfo.php');

		$lookupQ = $db->query("SELECT id,logins FROM users WHERE steam_id = ?",[$_SESSION['steamid']]);
		$lookupC = $lookupQ->count();

		if ($lookupC > 0) {
			$lookup           = $lookupQ->first();
			$_SESSION[Config::get('session/session_name')] = $lookup->id;
			$db->update('users',$lookup->id, [
				'logins'       => $lookup->logins+1,
				'steam_avatar' => $steamprofile['avatarfull'],
				'steam_un'     => $steamprofile['personaname']
			]);

			if (file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir'))
				include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir');

			if (file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script'))
				include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script');

			Redirect::to($us_url_root.$settings->redirect_uri_after_login);
		} else {
			$checkUn  = $db->query("SELECT id FROM users WHERE username = ?",[$steamprofile['personaname']])->count();
			$username = $steamprofile['personaname'];

			if ($checkUn >= 1)
				$username = $steamprofile['personaname'].randomstring(6); //close enough

			$fields = array(
				'username'        => $username,
				'steam_id'        => $steamprofile['steamid'],
				'steam_avatar'    => $steamprofile['avatarfull'],
				'steam_un'        => $steamprofile['personaname'],
				'username'        => $username,
				'fname'           => $username,
				'lname'           => $username,
				'email'           => $username."@".$settings->steam_domain,
				'password'        => password_hash(randomstring(20), PASSWORD_BCRYPT, array('cost' => 12)),
				'permissions'     => 1,
				'account_owner'   => 1,
				'join_date'       => date("Y-m-d H:i:s"),
				'email_verified'  => 1,
				'active'          => 1,
				'vericode'        => randomstring(12),
				'vericode_expiry' => "2016-01-01 00:00:00"
			);

			if ($db->insert('users',$fields)) {
				$theNewId         = $db->lastId();
				$_SESSION[Config::get('session/session_name')] = $theNewId;

				$fields = array(
					'user_id'       => $theNewId,
					'permission_id' => 1,
				);

				$db->insert('user_permission_matches',$fields);
				include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
				Redirect::to($us_url_root.$settings->redirect_uri_after_login);
			} else {
				$_SESSION = [];
				Redirect::to($us_url_root.'users/login.php?msg=There+was+a+problem+with+your+Steam+login');
			}
		}
	}
} else
	Redirect::to($us_url_root."users/account.php");
?>
