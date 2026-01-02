<?php
// require '../../../../users/init.php';
// $db = DB::getInstance();
// $settings = $db->query("SELECT * FROM settings")->first();
function loginbutton($buttonstyle = "square") {
	$button['rectangle'] = "01";
	$button['square'] = "02";
	$button = "<a href='?login'><img class=\"img-responsive\" src='assets/steam.png'></a>";

	echo $button;
}

if (isset($_GET['login'])){
	require 'openid.php';
	try {
		require 'SteamConfigLink.php';
		$openid = new LightOpenID($steamauth['domainname']);

		if(!$openid->mode) {
			$openid->identity = 'https://steamcommunity.com/openid';
			$authUrl = $openid->authUrl();
			// Validate redirect URL to prevent open redirect attacks
			$parsedUrl = parse_url($authUrl);
			if (!isset($parsedUrl['host']) || !preg_match('/^(.*\.)?steamcommunity\.com$/i', $parsedUrl['host'])) {
				echo 'Invalid authentication URL.';
				exit;
			}
			header('Location: ' . $authUrl);
		} elseif ($openid->mode == 'cancel') {
			echo 'User has canceled authentication!';
		} else {
			if($openid->validate()) {
				$id = $openid->identity;
				$ptn = "/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
				preg_match($ptn, $id, $matches);

				$_SESSION['steamid'] = $matches[1];
				if (!headers_sent()) {
					header('Location: '.$steamauth['loginpage']);
					exit;
				} else {
					?>
					<script type="text/javascript">
						window.location.href="<?=$steamauth['loginpage']?>";
					</script>
					<noscript>
						<meta http-equiv="refresh" content="0;url=<?=$steamauth['loginpage']?>" />
					</noscript>
					<?php
					exit;
				}
			} else {
				echo "User is not logged in.\n";
			}
		}
	} catch(ErrorException $e) {
		// Log the actual error for debugging but don't expose to users
		error_log('Steam Link Error: ' . $e->getMessage());
		echo 'An error occurred during authentication. Please try again.';
	}
}


// Version 4.0

?>
