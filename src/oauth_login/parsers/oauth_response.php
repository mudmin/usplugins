<?php
require_once '../../../../users/init.php';
if(!pluginActive('oauth_login',true)){
    die('OAuth is disabled');
}
$oSettings = $db->query("SELECT * FROM plg_oauth_login")->first();

$authCode = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;
$response = $_GET['response'] ?? null;
$response = json_decode(base64_decode($response), true);

// Verify the state to prevent CSRF attacks
if ($state !== $_SESSION['oauth_state']) {
    die('Invalid state parameter');
}

// Exchange the authorization code for an access token
$tokenUrl = $oSettings->server_url . 'usersc/plugins/oauth_server/auth.php';
$clientId = $oSettings->client_id;
$clientSecret = $oSettings->client_secret;
$redirectUri = $oSettings->redirect_uri;

$tokenData = exchangeCodeForToken($tokenUrl, $clientId, $clientSecret, $authCode, $redirectUri);

if (isset($tokenData['error'])) {
    $authCode = $_GET['code'] ?? null;
    logger(1, "OAuth Client", "Received Auth Code: $authCode");

    if ($authCode === null) {
        die('Authorization code is missing');
    }

    // dump($tokenUrl);
    // dump($clientId);
    // dump($clientSecret);
    // dump($authCode);
    // dump($tokenData);
    die("unspecified errror");
}

// Combine token data with user data
$userData = array_merge($tokenData, $response['userdata']);

// Check if user exists
$user = new User();
$existingUser = false;
$exustingUserQ = $db->query("SELECT * FROM users WHERE email = ?", [$userData['email']]);
$existingUserC = $exustingUserQ->count();
if ($existingUserC > 0) {
    $existingUser = $exustingUserQ->first();
}

if ($existingUser) {

    $user->find($existingUser->id);
    if (isset($response['instructions']['updateUserData']) && $response['instructions']['updateUserData'] == true) {
        updateUserData($existingUser->id, $response);
    }
    //this function does all the logic of adding tags to the user and what it can and cannot, do so we don't need a pre-check
    storeUserTags($existingUser->id, $response);

  
    $log = [
        'user_id' => $existingUser->id,
        'new_user' => 0
    ];
    $db->insert('plg_oauth_client_logins', $log);
    
    $sessionName = Config::get('session/session_name');
    if($oauthSettings->login_script != "" 
    && file_exists($abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/login_scripts/' . $oauthSettings->login_script)){
        require_once $abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/login_scripts/' . $oauthSettings->login_script;
    }
	Session::put($sessionName, $existingUser->id);
    if(file_exists($abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php')){
        require_once $abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php';
    }


    if(function_exists('setLoginMethod')){
        setLoginMethod('oauth');
    }
    Redirect::to($us_url_root . $settings->redirect_uri_after_login);

    

} else {
    // User doesn't exist, create a new account
    $userId = createNewUser($userData, $response);

    //should be redirected by this point
    die("We're sorry. Something went wrong.");
}

// Store the access token
storeAccessToken($userId, $tokenData['access_token'], $tokenData['expires_in']);



// Redirect to a success page or dashboard
Redirect::to($us_url_root . 'users/account.php');

function updateUserData($userId, $response)
{
    global $db;
    $existingQ = $db->query("SELECT * FROM users WHERE id = ?", [$userId]);
    $existingC = $existingQ->count();
    if ($existingC == 0) {
        return;
    } else {
        $existing = $existingQ->first();
    }
    if (isset($response['userdata'])) {
        $userData = $response['userdata'];
        $fields = [];
        foreach ($userData as $key => $value) {
            if ($key == 'email' || $key == 'id' || $key == 'username') {
                continue;
            }
            //make sure the column is in the users table so you don't cause the whole thing to fail
            if (isset($existing->$key) && $existing->$key != $value) {
                $fields[$key] = $value;
            }

        }
        if (count($fields) > 0) {
            $db->update('users', $userId, $fields);
        }

    }
}

function createNewUser($userData, $response)
{
    global $db, $abs_us_root, $us_url_root, $settings, $oauthSettings;
    if(file_exists($abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/assets/before_user_creation.php')){
        require_once $abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/assets/before_user_creation.php';
    }
    $user = new User();
    $fields = [
        'email' => $userData['email'],
        'username' => $userData['email'],
        'fname' => $userData['fname'],
        'lname' => $userData['lname'],
        'password' => password_hash(randomstring(20), PASSWORD_BCRYPT, ['cost' => 14]),
        'permissions' => 1,
        'join_date' => date('Y-m-d H:i:s'),
        'email_verified' => 1,
        'vericode' => randomstring(12),
        'vericode_expiry' => date('Y-m-d H:i:s'),
        'oauth_tos_accepted' => true,
        'language' => 'en-US',
        'active' => 1
    ];

    $theNewId = $user->create($fields);
    $sessionName = Config::get('session/session_name');
	Session::put($sessionName, $theNewId);

    updateUserData($theNewId, $response);
    storeUserTags($theNewId, $response);

    $log = [
        'user_id' => $theNewId,
        'new_user' => 1
    ];
    $db->insert('plg_oauth_client_logins', $log);

    include $abs_us_root . $us_url_root . 'usersc/scripts/during_user_creation.php';
    if($oauthSettings->login_script != "" 
    && file_exists($abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/login_scripts/' . $oauthSettings->login_script)){
        require_once $abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/login_scripts/' . $oauthSettings->login_script;
    }
    if (file_exists($abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php')) {
        require_once $abs_us_root . $us_url_root . 'usersc/scripts/custom_login_script.php';
    }

    Redirect::to($us_url_root . $settings->redirect_uri_after_login);
    return false;
}

function storeAccessToken($userId, $accessToken, $expiresIn)
{
    global $db;
    $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
    $db->query("INSERT INTO plg_oauth_client_tokens (user_id, access_token, expires_at) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE access_token = VALUES(access_token), expires_at = VALUES(expires_at)",
        [$userId, $accessToken, $expiresAt]
    );
}

function storeUserTags($userId, $response)
{
    global $db;

    // Check if we should update tags
    if (!isset($response['instructions']['updateTags']) || !$response['instructions']['updateTags']) {
        return;
    }

    $tags = $response['tags'];
    $createTagIfNeeded = $response['instructions']['createTagIfNeeded'] ?? false;
    $removeTagIfNotSpecified = $response['instructions']['removeTagIfNotSpecified'] ?? false;

    // Get all existing tags for this user
    $existingTagsQ = $db->query("SELECT plg_tags.id, plg_tags.tag FROM plg_tags
                                 JOIN plg_tags_matches ON plg_tags.id = plg_tags_matches.tag_id
                                 WHERE plg_tags_matches.user_id = ?", [$userId]);
    $existingTags = $existingTagsQ->results();
    $existingTagNames = array_column($existingTags, 'tag', 'id');

    $newTagNames = array_column($tags, 'tag_name');
    $newTagNames = array_map('strtolower', $newTagNames);

    // Add new tags and associate them with the user
    foreach ($tags as $tag) {
        $tagName = strtolower($tag['tag_name']); // Normalize to lowercase for comparison

        // Check if the tag exists in the plg_tags table
        $tagExistsQ = $db->query("SELECT * FROM plg_tags WHERE LOWER(tag) = ?", [$tagName]);
        if ($tagExistsQ->count() > 0) {
            $tagId = $tagExistsQ->first()->id;
        } else {
            if ($createTagIfNeeded) {
                // Insert new tag into plg_tags
                $db->insert('plg_tags', ['tag' => $tag['tag_name']]);
                $tagId = $db->lastId();
            } else {
                // Skip if we're not allowed to create new tags
                continue;
            }
        }

        // Check if the tag is already associated with the user
        $tagMatchQ = $db->query("SELECT * FROM plg_tags_matches WHERE tag_id = ? AND user_id = ?", [$tagId, $userId]);
        if ($tagMatchQ->count() == 0) {
            // Associate the tag with the user
            $db->insert('plg_tags_matches', ['tag_id' => $tagId, 'tag_name' => $tag['tag_name'], 'user_id' => $userId]);
        }
    }

    // Remove old tags if instructed
    if ($removeTagIfNotSpecified) {
        foreach ($existingTags as $existingTag) {
            if (!in_array(strtolower($existingTag->tag), $newTagNames)) {
                // Remove the tag association from plg_tags_matches
                $db->query("DELETE FROM plg_tags_matches WHERE tag_id = ? AND user_id = ?", [$existingTag->id, $userId]);
            }
        }
    }
}


function exchangeCodeForToken($tokenUrl, $clientId, $clientSecret, $authCode, $redirectUri)
{
    $data = [
        'grant_type' => 'authorization_code',
        'code' => $authCode,
        'redirect_uri' => $redirectUri,
        'client_id' => $clientId,
        'client_secret' => $clientSecret
    ];

    logger(1, "OAuth Client", "Sending token request to: $tokenUrl");
    logger(1, "OAuth Client", "Token request data: " . json_encode($data));

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_POST, true);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($result === FALSE) {
        $error = curl_error($ch);
        curl_close($ch);
        logger(1, "OAuth Client", "cURL error: $error");
        return ['error' => 'cURL error: ' . $error];
    }

    curl_close($ch);

    logger(1, "OAuth Client", "Received response from token endpoint. HTTP Code: $httpCode, Response: $result");

    if ($httpCode !== 200) {
        return [
            'error' => 'Failed to get access token. HTTP Code: ' . $httpCode,
            'response' => $result
        ];
    }

    return json_decode($result, true);
}

