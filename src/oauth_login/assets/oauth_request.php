<?php
$noMaintenanceRedirect = true; 
require_once '../../../../users/init.php';
if(!pluginActive('oauth_login',true)){
    die('OAuth is disabled');
}
$oSettings = $db->query("SELECT * FROM plg_oauth_login")->first();

if (!$oSettings) {
    logger(1, "OAuth Client", "Failed to retrieve OAuth settings from database");
    die("OAuth settings not found. Please check your configuration.");
}

// OAuth server authorization endpoint
$authEndpoint = $oSettings->server_url . 'usersc/plugins/oauth_server/auth.php';

// Client credentials
$clientId = $oSettings->client_id;
$redirectUri = $oSettings->redirect_uri;

if (empty($clientId) || empty($redirectUri)) {
    logger(1, "OAuth Client", "Client ID or Redirect URI is missing in the settings");
    die("OAuth client configuration is incomplete. Please check your settings.");
}

// Generate a random state parameter for CSRF protection
try {
    $state = bin2hex(random_bytes(16));
} catch (Exception $e) {
    logger(1, "OAuth Client", "Failed to generate random state: " . $e->getMessage());
    die("An error occurred while preparing the OAuth request.");
}

// Store the state in the session for later verification
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['oauth_state'] = $state;

// Build the authorization URL
$authParams = [
    'response_type' => 'code',
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'state' => $state,
    'scope' => 'profile' // Add any scopes you need
];

$authUrl = $authEndpoint . '?' . http_build_query($authParams);

// Log the outgoing request
logger(1, "OAuth Client", "Initiating OAuth request. Auth URL: " . $authUrl);

// Redirect the user to the authorization URL
Redirect::to($authUrl);
exit;