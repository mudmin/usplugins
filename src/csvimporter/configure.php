<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);

if(!empty($_POST['plugin_importer'])){
   $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
}

// Get available user fields dynamically
$sampleUser = $user->data();
$excludeFields = ['password', 'vericode']; // Fields to never export

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
$additionalFieldsFile = __DIR__ . '/additional_fields.php';
if(file_exists($additionalFieldsFile)){
    include $additionalFieldsFile;
    if(isset($additional_fields) && is_array($additional_fields)){
        $availableFields = array_merge($availableFields, $additional_fields);
    }
}

// Handle CSV Export - redirect removed since form now points directly to parsers/export.php

// Handle CSV Import (existing functionality)
if (!empty($_FILES)) {
    $temp = explode(".", $_FILES["file"]["name"]);
    $newfilename = "import.csv";
    if(move_uploaded_file($_FILES["file"]["tmp_name"], $newfilename)){
        $added = [];
        $failed = [];

        $arrResult  = array();
        $handle     = fopen($newfilename, "r");
        if(empty($handle) === false) {
            while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
                $arrResult[] = $data;
            }
            fclose($handle);
        }
        foreach($arrResult as $a){
            $email = $a[0];
            $pass = $a[1];
            $un = $a[2];
            $fn = $a[3];
            $ln = $a[4];
            $rest = $a[5];
            $perms = $a[6];

            //lots of checks happening here.
            //does the email exist or is it not in a valid format.
            $emailCheck = $db->query("SELECT * FROM users WHERE email = ?",array($email))->count();
            if(($emailCheck > 0) || (!filter_var($email, FILTER_VALIDATE_EMAIL))) {
                $failed[]="Invalid Email $email - Either bad format or duplicate";
                continue;
            }

            //if the username is not specified, make it the email address
            if($un == ""){$un = $email;}
            //is the username already in the system
            $unCheck = $db->query("SELECT * FROM users WHERE username = ?",array($un))->count();
            if($unCheck > 0){
                $failed[]="Username $un already exists";
                continue;
            }

            //if last name is not specified, make it the username
            if($ln == ""){
                $ln = $un;
            }

            //if no password, create one
            if($pass == ""){$pass = randomstring(15); $rest = 1;}

            //if the password is not already encrypted in bcrypt, encrypt it
            if(substr($pass,0,4) != "$2y$"){
                $prePass = $pass;
                $pass = password_hash($pass, PASSWORD_BCRYPT, array('cost' => 12));
            }else{
                $prePass = "Pre-Encrypted Password Provided";
            }

            //get an array of valid permissions for this user
            if($perms == ""){
                $perms = [];
            }else{
                $permFail = false;
                $permArray = explode("|",$perms);
                foreach($permArray as $pa){
                    if($pa == 1){continue;}
                    $check = $db->query("SELECT * FROM permissions WHERE id = ?",[$pa])->count();
                    if($check < 1){
                        $permFail = true;
                    }
                }
                if($permFail == true){
                    $failed[]="Username $un was given an invalid permission of $perms";
                    continue;
                }else{
                    $perms = $permArray;
                }
            }

            //if you made it this far, create the user
            $theNewId = $user->create(array(
                'username' => $un,
                'fname' => $fn,
                'lname' => $ln,
                'email' => $email,
                'password' => $pass,
                'permissions' => 1,
                'account_owner' => 1,
                'join_date' => date("Y-m-d H:i:s"),
                'email_verified' => 1,
                'active' => 1,
                'vericode' => randomstring(15),
                'vericode_expiry' => date("Y-m-d H:i:s"),
                'oauth_tos_accepted' => true
            ));
            foreach($perms as $p){
                $db->insert("user_permission_matches",['permission_id'=>$p,'user_id'=>$theNewId]);
            }
            if($rest == 1){$db->update('users',$theNewId,['force_pr'=>1]);}
            include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
            logger($theNewId,"User","Registered via CSV Import");
            $added[] = [$theNewId,$email,$prePass];
        }//end foreach
        unlink($newfilename);
    }else{
        bold("Unable to move file");
    }
}
$token = Token::generate();
?>
<style media="screen">
    p {color:black;}
    .nav-tabs .nav-link {
        color: #495057;
    }
    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }
</style>
<div class="content mt-3">
    <div class="row">
        <div class="col-sm-12">
            <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
            <h1>CSV User Import/Export</h1>
            
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="importExportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab" aria-controls="import" aria-selected="true">Import Users</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="export-tab" data-bs-toggle="tab" data-bs-target="#export" type="button" role="tab" aria-controls="export" aria-selected="false">Export Users</button>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content" id="importExportTabContent">
                <!-- Import Tab -->
                <div class="tab-pane fade show active" id="import" role="tabpanel" aria-labelledby="import-tab">
                    <div class="mt-3">
                        <h3>Import Users from CSV</h3>
                        <form class="" action="" method="post" name="plugin_importer" enctype="multipart/form-data">
                            <input type="hidden" name="csrf" value="<?=$token?>" />
                            <div class="mb-3">
                                <label for="file" class="form-label">Select CSV file:</label>
                                <input type="file" name="file" class="form-control" accept=".csv" />
                            </div>
                            <input type="submit" name="plugin_importer" value="Import Users" class="btn btn-primary" />
                        </form>
                        
                        <?php if(isset($added)){ ?>
                            <h4 class="mt-4">Import Results</h4>
                            <h5 class="text-success">Successes (<?php echo count($added);?>)</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th><th>Email</th><th>Password <input id="chkShowPassword" type="checkbox" /> Show passwords</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($added as $add){?>
                                        <tr>
                                            <td><?=$add[0];?></td>
                                            <td><?=$add[1];?></td>
                                            <td><span class="pwtoggle" style="display:none;"><?=$add[2];?></span></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            
                            <h5 class="text-danger">Failures (<?php echo count($failed);?>)</h5>
                            <table class="table table-striped">
                                <tbody>
                                    <?php foreach($failed as $f){?>
                                        <tr>
                                            <td><?php echo $f;?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                        
                        <div class="mt-4">
                            <h5>Import Format Information</h5>
                            <p>This plugin expects a CSV with <strong>no headers</strong> and columns in this order:</p>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Email (required)</th><th>Password</th><th>Username</th><th>First Name</th><th>Last Name</th><th>Force Reset (1 or null)</th><th>Permissions (| separated)</th>
                                    </tr>
                                </thead>
                            </table>
                            <small class="text-muted">
                                <strong>Notes:</strong><br>
                                • Email is required and must be unique<br>
                                • If no password provided, one will be auto-generated<br>
                                • If no username given, email will be used as username<br>
                                • If no last name given, username will be used<br>
                                • Permissions should be separated by | (e.g., 1|3|5)<br>
                                • Additional columns will be ignored
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Export Tab -->
                <div class="tab-pane fade" id="export" role="tabpanel" aria-labelledby="export-tab">
                    <div class="mt-3">
                        <h3>Export Users to CSV</h3>
                        <form action="<?=$us_url_root?>usersc/plugins/csvimporter/parsers/export.php" method="post" name="plugin_exporter" target="_blank">
                            <input type="hidden" name="csrf" value="<?=$token?>" />
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Select fields to export:</strong></label>
                                <div class="row">
                                    <?php 
                                    $columnCount = 0;
                                    $totalFields = count($availableFields);
                                    $fieldsPerColumn = ceil($totalFields / 2);
                                    
                                    foreach($availableFields as $fieldKey => $fieldLabel){ 
                                        if($columnCount == 0) echo '<div class="col-md-6">';
                                        
                                        $isChecked = in_array($fieldKey, ['email', 'username', 'fname', 'lname']) ? 'checked' : '';
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="export_fields[]" value="<?=$fieldKey?>" id="field_<?=$fieldKey?>" <?=$isChecked?>>
                                            <label class="form-check-label" for="field_<?=$fieldKey?>"><?=$fieldLabel?></label>
                                        </div>
                                    <?php 
                                        $columnCount++;
                                        if($columnCount >= $fieldsPerColumn) {
                                            echo '</div>';
                                            $columnCount = 0;
                                        }
                                    } 
                                    if($columnCount > 0) echo '</div>'; // Close last column if needed
                                    ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skip_admins" id="skip_admins" value="1">
                                    <label class="form-check-label" for="skip_admins">
                                        Skip Admin Users (permission ID 2)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="selectAllFields()">Select All</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllFields()">Clear All</button>
                            </div>
                            
                            <input type="submit" name="plugin_exporter" value="Export to CSV" class="btn btn-success" />
                        </form>
                        
                        <div class="mt-4">
                            <h5>Custom Fields</h5>
                            <div class="alert alert-info">
                                <strong>Adding Custom Export Fields:</strong><br>
                                You can add custom export fields by creating a file called <code>additional_fields.php</code> in this plugin directory.<br><br>
                                The file should contain:
                                <pre class="mt-2 mb-2"><code>&lt;?php
$additional_fields = [
    'custom_field_key' =&gt; 'Custom Field Label',
    'another_field' =&gt; 'Another Field Name'
];
?&gt;</code></pre>
                                These fields will appear as additional export options. Make sure the keys match actual database columns or implement custom logic in the export handler.
                                <?php if(file_exists(__DIR__ . '/additional_fields.php')): ?>
                                    <br><br><span class="badge bg-success">✓ Custom fields file detected and loaded</span>
                                <?php else: ?>
                                    <br><br><span class="badge bg-secondary">No custom fields file found</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- /.col -->
    </div> <!-- /.row -->
</div>

<script type="text/javascript">
$(function () {
    // Password toggle functionality
    $("#chkShowPassword").bind("click", function () {
        if ($(this).is(":checked")) {
            $(".pwtoggle").show();
        } else {
            $(".pwtoggle").hide();
        }
    });
});

// Export field selection functions
function selectAllFields() {
    $('input[name="export_fields[]"]').prop('checked', true);
}

function clearAllFields() {
    $('input[name="export_fields[]"]').prop('checked', false);
}
</script>