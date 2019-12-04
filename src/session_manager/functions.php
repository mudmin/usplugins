<?php
if(!function_exists('UserSessionCount')) {
	function UserSessionCount() {
		global $user;
		$db=DB::getInstance();
		$q=$db->query("SELECT * FROM us_user_sessions WHERE fkUserID = ? AND UserSessionEnded=0",[$user->data()->id]);
		return $q->count();
	}
}

if(!function_exists('fetchUserSessions')) {
	function fetchUserSessions($all=false) {
		global $user;
		$db = DB::getInstance();
		if(!$all) $q = $db->query("SELECT * FROM us_user_sessions WHERE fkUserID = ? AND UserSessionEnded=0 ORDER BY UserSessionStarted",[$user->data()->id]);
		else $q = $db->query("SELECT * FROM us_user_sessions WHERE fkUserID = ? ORDER BY UserSessionStarted",[$user->data()->id]);
		if($q->count()>0) return $q->results();
		else return false;
	}
}

if(!function_exists('fetchAdminSessions')) {
	function fetchAdminSessions($all=false) {
		global $user;
		$db = DB::getInstance();
		if(!$all) $q = $db->query("SELECT * FROM us_user_sessions WHERE UserSessionEnded=0 ORDER BY UserSessionStarted");
		else $q = $db->query("SELECT * FROM us_user_sessions ORDER BY UserSessionStarted");
		if($q->count()>0) return $q->results();
		else return false;
	}
}

if(!function_exists('killSessions')) {
	function killSessions($sessions,$admin=false) {
		global $user;
		$db = DB::getInstance();
		$i=0;
		foreach($sessions as $session) {
			if(!$admin) $db->query("UPDATE us_user_sessions SET UserSessionEnded=1,UserSessionEnded_Time=NOW() WHERE kUserSessionID = ? AND fkUserId = ?",[$session,$user->data()->id]);
			else $db->query("UPDATE us_user_sessions SET UserSessionEnded=1,UserSessionEnded_Time=NOW() WHERE kUserSessionID = ?",[$session]);
			if(!$db->error()) {
				$i++;
				logger($user->data()->id,"User Tracker","Killed Session ID#$session");
			} else {
				$error=$db->errorString();
				logger($user->data()->id,"User Tracker","Error killing Session ID#$session: $error");
			}
		}
		if($i>0) return $i;
		else return false;
	}
}

if(!function_exists('passwordResetKillSessions')) {
	function passwordResetKillSessions($uid=NULL) {
		global $user;
		$db = DB::getInstance();
		if(is_null($uid)) $q = $db->query("UPDATE us_user_sessions SET UserSessionEnded=1,UserSessionEnded_Time=NOW() WHERE fkUserID = ? AND UserSessionEnded=0 AND kUserSessionID <> ?",[$user->data()->id,$_SESSION['kUserSessionID']]);
		else $q = $db->query("UPDATE us_user_sessions SET UserSessionEnded=1,UserSessionEnded_Time=NOW() WHERE fkUserID = ? AND UserSessionEnded=0",[$uid]);
		if(!$db->error()) {
			$count=$db->count();
			if(is_null($uid)) {
				if($count==1) logger($user->data()->id,"User Tracker","Killed 1 Session via Password Reset.");
				if($count >1) logger($user->data()->id,"User Tracker","Killed $count Sessions via Password Reset.");
			} else {
				if($count==1) logger($user->data()->id,"User Tracker","Killed 1 Session via Password Reset for UID $uid.");
				if($count >1) logger($user->data()->id,"User Tracker","Killed $count Sessions via Password Reset for UID $uid.");
			}
			return $count;
		} else {
			$error=$db->errorString();
			if(is_null($uid)) {
					logger($user->data()->id,"User Tracker","Password Reset Session Kill failed, Error: ".$error);
			} else {
				logger($user->data()->id,"User Tracker","Password Reset Session Kill failed for UID $uid, Error: ".$error);
			}
			return $error;
		}
	}
}

if(!function_exists('storeUser')) {
	function storeUser($api=false) {
		global $user;
		global $us_url_root;
		if(!$user->isLoggedIn()) return false;
		$db=DB::getInstance();
		if(isset($_SESSION['kUserSessionID']) && isset($_SESSION['fingerprint']) && $_SESSION['fingerprint']!='') $q=$db->query("SELECT * FROM us_user_sessions WHERE kUserSessionID = ? AND fkUserID = ? AND UserFingerprint = ?",[$_SESSION['kUserSessionID'],$user->data()->id,$_SESSION['fingerprint']]);
		if(isset($q) && $q->count()==1) {
			$result=$q->first();
			if($result->UserSessionEnded==0) {
				if(!$api) {
					$db->update('us_user_sessions',['kUserSessionID' => $result->kUserSessionID],['UserSessionLastUsed' => date("Y-m-d H:i:s"),'UserSessionLastPage' => currentPageStrict()]);
					if($db->error()) {
						logger($user->data()->id,"User Tracker","Failed to re-track User Session, Error: ".$db->errorString());
						return false;
					} else return true;
				} else return true;
			} else {
				if($api) return false;
					$user->logout();
					Redirect::to($us_url_root.'users/?msg=Your session was ended remotely');
			}
		} else {
			if(isset($_SESSION['fingerprint']) && $_SESSION['fingerprint']!='') {
				$fields = [
					'fkUserID' => $user->data()->id,
					'UserFingerprint' => $_SESSION['fingerprint'],
					'UserSessionIP' => ipCheck(),
					'UserSessionOS' => getOS(),
					'UserSessionBrowser' => getBrowser(),
					'UserSessionStarted' => date("Y-m-d H:i:s"),
					'UserSessionLastUsed' => date("Y-m-d H:i:s"),
					'UserSessionLastPage' => currentPageStrict(),
					'UserSessionEnded' => 0,
					'UserSessionEnded_Time' => NULL,
				];
				$db->insert('us_user_sessions',$fields);
				if($db->error()) {
					logger($user->data()->id,"User Tracker","Failed to track User Session, Error: ".$db->errorString());
					return true;
				} else {
					$_SESSION['kUserSessionID']=$db->lastId();
					return true;
				}
			} else return true;
		}
	}
}

if(!function_exists('fetchUserFingerprints')) {
	function fetchUserFingerprints() {
		global $user;
		$db = DB::getInstance();
		$q = $db->query("SELECT *,CASE WHEN fp.kFingerprintAssetID IS NULL THEN false ELSE true END AssetsAvailable FROM us_fingerprints f LEFT JOIN us_fingerprint_assets fp ON fp.fkFingerprintID=f.kFingerprintID WHERE f.fkUserID = ? AND f.Fingerprint_Expiry > NOW()",[$user->data()->id]);
		if($q->count()>0) return $q->results();
		else return false;
	}
}

if(!function_exists('expireFingerprints')) {
	function expireFingerprints($fingerprints) {
		global $user;
		$db = DB::getInstance();
		$i=0;
		foreach($fingerprints as $fingerprint) {
			$db->query("UPDATE us_fingerprints SET Fingerprint_Expiry=NOW() WHERE kFingerprintID = ? AND fkUserId = ?",[$fingerprint,$user->data()->id]);
			if(!$db->error()) {
				$i++;
				logger($user->data()->id,"Two FA","Expired Fingerprint ID#$fingerprint");
			} else {
				$error=$db->errorString();
				logger($user->data()->id,"Two FA","Error expiring Fingerprint ID#$fingerprint: $error");
			}
		}
		if($i>0) return $i;
		else return false;
	}
}

if(!function_exists('getOS')) {
	function getOS() {

	    global $user_agent;

	    $os_platform  = "Unknown OS Platform";

	    $os_array     = array(
	                          '/windows nt 10/i'      =>  'Windows 10',
	                          '/windows nt 6.3/i'     =>  'Windows 8.1',
	                          '/windows nt 6.2/i'     =>  'Windows 8',
	                          '/windows nt 6.1/i'     =>  'Windows 7',
	                          '/windows nt 6.0/i'     =>  'Windows Vista',
	                          '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
	                          '/windows nt 5.1/i'     =>  'Windows XP',
	                          '/windows xp/i'         =>  'Windows XP',
	                          '/windows nt 5.0/i'     =>  'Windows 2000',
	                          '/windows me/i'         =>  'Windows ME',
	                          '/win98/i'              =>  'Windows 98',
	                          '/win95/i'              =>  'Windows 95',
	                          '/win16/i'              =>  'Windows 3.11',
	                          '/macintosh|mac os x/i' =>  'Mac OS X',
	                          '/mac_powerpc/i'        =>  'Mac OS 9',
	                          '/linux/i'              =>  'Linux',
	                          '/ubuntu/i'             =>  'Ubuntu',
	                          '/iphone/i'             =>  'iPhone',
	                          '/ipod/i'               =>  'iPod',
	                          '/ipad/i'               =>  'iPad',
	                          '/android/i'            =>  'Android',
	                          '/blackberry/i'         =>  'BlackBerry',
	                          '/webos/i'              =>  'Mobile'
	                    );

	    foreach ($os_array as $regex => $value)
	        if (preg_match($regex, $user_agent))
	            $os_platform = $value;

	    return $os_platform;
	}
}

if(!function_exists('getBrowser')) {
	function getBrowser() {

	    global $user_agent;

	    $browser        = "Unknown Browser";

	    $browser_array = array(
	                            '/msie/i'      => 'Internet Explorer',
	                            '/firefox/i'   => 'Firefox',
	                            '/safari/i'    => 'Safari',
	                            '/chrome/i'    => 'Chrome',
	                            '/edge/i'      => 'Edge',
	                            '/opera/i'     => 'Opera',
	                            '/netscape/i'  => 'Netscape',
	                            '/maxthon/i'   => 'Maxthon',
	                            '/konqueror/i' => 'Konqueror',
	                            '/mobile/i'    => 'Handheld Browser'
	                     );

	    foreach ($browser_array as $regex => $value)
	        if (preg_match($regex, $user_agent))
	            $browser = $value;

	    return $browser;
	}
}
