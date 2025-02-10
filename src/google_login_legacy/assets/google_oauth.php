<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted 
$value=null;
$gender=null;
$link=null;
if($settings->glogin==1 && !$user->isLoggedIn()){
	require_once $abs_us_root.$us_url_root.'usersc/plugins/google_login/assets/google_helpers.php';
	if(isset($_REQUEST['code'])){
				$gClient->authenticate();
				$_SESSION['google_token'] = $gClient->getAccessToken();
				header('Location: ' . filter_var($redirectUrl, FILTER_SANITIZE_URL));
			}
			$gClient->setAccessType('online');
			$gClient->setApprovalPrompt('auto') ;
			if (isset($_SESSION['google_token'])) {
				$gClient->setAccessToken($_SESSION['google_token']);
			}

			if ($gClient->getAccessToken()) {
				$userProfile = $google_oauthV2->userinfo->get();
				//User Authenticated by Google
				if($settings->registration==0) {
					$findExistingUS=$db->query("SELECT * FROM users WHERE email = ?",array($userProfile['email']));
					if(!$findExistingUS->count()>0) {
						session_destroy();
						Redirect::to($us_url_root.'users/join.php');
						die();
					}
				}
				$gUser = new User();
				$sessionName = Config::get('session/session_name');
				Session::put($sessionName, $value);
				//Deal with a user having an account but no google creds
				$findExistingUS=$db->query("SELECT * FROM users WHERE email = ?",array($userProfile['email']));
				$feusc = $findExistingUS->count();
				if($feusc>0){$feusr = $findExistingUS->first();}
				if($feusc == 1){
					$fields=array('gpluslink'=>'https://plus.google.com/'.$userProfile['id'],'picture'=>$userProfile['picture'],'locale'=>"",'oauth_provider'=>"google",'oauth_uid'=>$userProfile['id']);
					$db->update('users',$feusr->id,$fields);
					$date = date("Y-m-d H:i:s");
					$db->query("UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?",[$date,$feusr->id]);
					$db->query("UPDATE users SET last_confirm = ? WHERE id = ?",[$date,$feusr->id]);
					$db->insert('logs',['logdate' => $date,'user_id' => $feusr->id,'logtype' => "User",'lognote' => "User logged in."]);
					$ip = ipCheck();
					$q = $db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
					$c = $q->count();
					if($c < 1){
						$db->insert('us_ip_list', array(
							'user_id' => $feusr->id,
							'ip' => $ip,
						));
					}else{
						$f = $q->first();
						$db->update('us_ip_list',$f->id, array(
							'user_id' => $feusr->id,
							'ip' => $ip,
						));
					}
				}
				$feusr=$gUser->checkUser('google',$userProfile['id'],$userProfile['given_name'],"",$userProfile['email'],$gender,"",$link,$userProfile['picture']);
				//dnd($feusr);
				if(isset($feusr->isNewAccount) && $feusr->isNewAccount) {
					echo 1;
						$theNewId=$feusr->id;
						include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
				}
				//Add UserSpice info to session
				Session::put($sessionName, $feusr->id);
				//Add Google info to the session
				$_SESSION['google_data'] = $userProfile;

				$_SESSION['google_token'] = $gClient->getAccessToken();

				$hooks = getMyHooks(['page'=>'loginSuccess']);
				includeHook($hooks,'body');

			} else {
				$authUrl = $gClient->createAuthUrl();

			}
		}
			// if(isset($authUrl)) {
			// 	echo '<a href="'.$authUrl.'"><img src="'
			// 	.$us_url_root.'/users/images/google.png" alt=""/></a>';
			// } else {
			// 	echo '<a href="users/logout.php?logout">Logout</a>';
			// }
      ?>
