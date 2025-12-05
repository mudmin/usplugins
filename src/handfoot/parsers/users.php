<?php
//turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../../../../users/init.php';

$db = DB::getInstance();
$response = ['success' => false, 'message' => ''];

// Get plugin settings
$hfSettings = $db->query("SELECT * FROM plg_handfoot_settings WHERE id = 1")->first();

$action = Input::get('action');
$token = Input::get('token');

// Token check
if (!Token::check($token)) {
  $response['message'] = "Token mismatch";
  echo json_encode($response);
  die;
}

// Search users action
if($action == 'search_users') {
  $term = Input::get('term');

  if(strlen($term) < 1) {
    $response['results'] = [];
    echo json_encode($response);
    die;
  }

  // Search users by username, first name, last name, or email
  $searchTerm = '%' . $term . '%';
  $users = $db->query("SELECT id, username, fname, lname, email
                       FROM users
                       WHERE username LIKE ? OR fname LIKE ? OR lname LIKE ? OR email LIKE ?
                       ORDER BY username ASC
                       LIMIT 20",
                       [$searchTerm, $searchTerm, $searchTerm, $searchTerm])->results();

  $results = [];
  foreach($users as $u) {
    $displayName = $u->fname && $u->lname ? $u->fname . ' ' . $u->lname . ' (' . $u->username . ')' : $u->username;
    $results[] = [
      'id' => $u->id,
      'text' => $displayName,
      'username' => $u->username,
      'fname' => $u->fname,
      'lname' => $u->lname,
      'email' => $u->email
    ];
  }

  $response['success'] = true;
  $response['results'] = $results;
  echo json_encode($response);
  die;
}

// Create user action
if($action == 'create_user') {
  // Check if user creation is allowed
  if(!$hfSettings || !$hfSettings->allow_user_creation) {
    $response['message'] = "User creation is not allowed";
    echo json_encode($response);
    die;
  }

  $email = trim(Input::get('email'));
  $fname = trim(Input::get('fname'));
  $lname = trim(Input::get('lname'));

  // Validation
  if(empty($email)) {
    $response['message'] = "Email is required";
    echo json_encode($response);
    die;
  }

  if(empty($fname)) {
    $response['message'] = "First name is required";
    echo json_encode($response);
    die;
  }

  if(empty($lname)) {
    $response['message'] = "Last name is required";
    echo json_encode($response);
    die;
  }

  // Validate email format
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = "Please enter a valid email address";
    echo json_encode($response);
    die;
  }

  // Check if email already exists as email OR username
  $existingEmail = $db->query("SELECT id FROM users WHERE email = ? OR username = ?", [$email, $email])->count();
  if($existingEmail > 0) {
    $response['message'] = "A user with this email already exists";
    echo json_encode($response);
    die;
  }

  // Use email as username
  $username = $email;

  // Create the user following the same pattern as _admin_users.php
  // Get settings if not already available
  if(!isset($settings)) {
    $settings = $db->query("SELECT * FROM settings WHERE id = 1")->first();
  }

  $password = randomstring(20);
  $vericode = uniqid() . randomstring(15);
  $vericode_expiry = date('Y-m-d H:i:s', strtotime("+{$settings->join_vericode_expiry} hours", strtotime(date('Y-m-d H:i:s'))));
  $join_date = date('Y-m-d H:i:s');

  if (isset($_SESSION['us_lang'])) {
    $newLang = $_SESSION['us_lang'];
  } else {
    $newLang = $settings->default_language;
  }

  try {
    $fields = [
      'username' => $username,
      'fname' => $fname,
      'lname' => $lname,
      'email' => $email,
      'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]),
      'permissions' => 1,
      'join_date' => $join_date,
      'email_verified' => 1,
      'vericode' => $vericode,
      'force_pr' => 1, // Force password reset on first login
      'vericode_expiry' => $vericode_expiry,
      'oauth_tos_accepted' => true,
      'language' => $newLang,
      'active' => 1,
    ];

    $db->insert('users', $fields);
    $newUserId = $db->lastId();

    // Add default permission
    $addNewPermission = ['user_id' => $newUserId, 'permission_id' => 1];
    $db->insert('user_permission_matches', $addNewPermission);

    // Include the during_user_creation hook if it exists
    $theNewId = $newUserId;
    $hookPath = $abs_us_root . $us_url_root . 'usersc/scripts/during_user_creation.php';
    if(file_exists($hookPath)) {
      include $hookPath;
    }

    // Log the action
    if(isset($user) && $user->isLoggedIn()) {
      logger($user->data()->id, 'Hand and Foot', "Created user $fname $lname ($email) via game setup.");
    }

    // Display name for Select2
    $displayName = $fname . ' ' . $lname . ' (' . $email . ')';

    $response['success'] = true;
    $response['message'] = "User created successfully";
    $response['user'] = [
      'id' => $newUserId,
      'text' => $displayName,
      'username' => $username,
      'fname' => $fname,
      'lname' => $lname,
      'email' => $email
    ];

  } catch (Exception $e) {
    $response['message'] = "Error creating user: " . $e->getMessage();
  }

  echo json_encode($response);
  die;
}

// Unknown action
$response['message'] = "Unknown action";
echo json_encode($response);
