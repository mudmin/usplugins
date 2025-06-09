<?php 
require_once("../../../../users/init.php");

// Master account check
if(!in_array($user->data()->id,$master_account)){ 
    Redirect::to($us_url_root.'users/admin.php');
}

// Get POST data directly instead of session
$exportFields = $_POST['export_fields'] ?? [];
$skipAdmins = isset($_POST['skip_admins']) ? true : false;
$token = $_POST['csrf'] ?? '';

// Verify CSRF token
if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    exit;
}

if(empty($exportFields)){
    echo "No fields selected for export.";
    exit;
}

// Get available user fields (same logic as configure.php)
$sampleUser = $user->data();
$excludeFields = ['password', 'vericode'];

// Always start with these core fields
$availableFields = [
    'id' => 'User ID',
    'email' => 'Email',
    'username' => 'Username',
    'fname' => 'First Name',
    'lname' => 'Last Name',
    'join_date' => 'Join Date',
    'last_login' => 'Last Login',
    'logins' => 'Login Count'
];

// Now add dynamic fields starting from "active"
$autoSkip = true;
foreach($sampleUser as $key => $value){
    if($key == "active" && $autoSkip){
        $autoSkip = false;
    }
    if($autoSkip){ continue; }
    if(!in_array($key, $excludeFields) && !isset($availableFields[$key])){
        $availableFields[$key] = ucwords(str_replace('_', ' ', $key));
    }
}

// Add permissions as a special field
$availableFields['permissions'] = 'Permissions';

// Load additional fields if the file exists
$additionalFieldsFile = __DIR__ . '/../additional_fields.php';
if(file_exists($additionalFieldsFile)){
    include $additionalFieldsFile;
    if(isset($additional_fields) && is_array($additional_fields)){
        $availableFields = array_merge($availableFields, $additional_fields);
    }
}

// Build the query
$query = "SELECT u.* FROM users u";
$params = [];

if($skipAdmins){
    $query .= " WHERE u.id NOT IN (SELECT user_id FROM user_permission_matches WHERE permission_id = 2)";
}

$query .= " ORDER BY u.id";

$users = $db->query($query, $params)->results();

// Create CSV content
$csvContent = [];

// Add header row
$headers = [];
foreach($exportFields as $field){
    if(isset($availableFields[$field])){
        $headers[] = $availableFields[$field];
    }
}
$csvContent[] = $headers;

// Add user data
foreach($users as $u){
    $row = [];
    foreach($exportFields as $field){
        switch($field){
            case 'permissions':
                // Get user permissions with names
                $perms = $db->query("SELECT p.name FROM user_permission_matches upm 
                                   JOIN permissions p ON upm.permission_id = p.id 
                                   WHERE upm.user_id = ? ORDER BY p.name", [$u->id])->results();
                $permArray = [];
                foreach($perms as $p){
                    $permArray[] = $p->name;
                }
                $row[] = implode('|', $permArray);
                break;
            case 'active':
            case 'email_verified':
                $row[] = $u->$field == 1 ? 'Yes' : 'No';
                break;
            default:
                $row[] = $u->$field ?? '';
                break;
        }
    }
    $csvContent[] = $row;
}

// Generate CSV file
$filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
foreach($csvContent as $row){
    fputcsv($output, $row);
}
fclose($output);
exit;
?>