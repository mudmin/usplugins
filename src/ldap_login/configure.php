<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_ldap_login'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 if (isset($_GET['action'])) {
   switch($_GET['action']) {
     case "edit":
        include "files/edit_match.php";
        break;
      case "add":
        include "files/add_match.php";
        break;
      case "save":
        if(!Token::check(Input::get('csrf'))){
          include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
        }
        switch($_GET['type']) {
          case "add":
            $db->insert("us_ldap_matches", ["ldap"=>Input::get('ldap'), "permission"=>Input::get('permission')]);
            break;
          
          case "edit":
            $db->query("UPDATE us_ldap_matches SET ldap = ?, permission = ? WHERE id = ?", [Input::get('ldap'), Input::get('permission'), Input::get('id')]);
            break;
        }
        Redirect::to("admin.php?view=plugins_config&plugin=ldap_login");

        break;
      case "delete":
        if(!Token::check(Input::get('token'))){
          include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
        }
        $db->query("DELETE FROM us_ldap_matches WHERE id = ?", [Input::get('id')]);
        Redirect::to("admin.php?view=plugins_config&plugin=ldap_login");
   }
   die();
 }
 $token = Token::generate();
 ?>
 <div class="content mt-3">
   <div class="row">
     <div class="col-6">
       <h2>LDAP Settings</h2>
     <div class="form-group">
       <label for="ldap_server">LDAP Server</label>
       <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="LDAP Server URI" name="ldap_server" id="ldap_server" value="<?=$settings->ldap_server?>">
     </div>

     <div class="form-group">
       <label for="ldap_admin">Admin Username</label>
       <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="Admin Username" name="ldap_admin" id="ldap_admin" value="<?=$settings->ldap_admin?>">
     </div>

     <div class="form-group">
       <label for="ldap_admin_pw">Admin Password</label>
       <input type="password" autocomplete="off" class="form-control ajxtxt" data-desc="Admin Password" name="ldap_admin_pw" id="ldap_admin_pw" value="<?=$settings->ldap_admin_pw?>">
     </div>

     <div class="form-group">
       <label for="ldap_tree">LDAP Tree</label>
       <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="LDAP Tree" name="ldap_tree" id="ldap_tree" value="<?=$settings->ldap_tree?>">
     </div>

     <div class="form-group">
       <label for="ldap_port">LDAP Port Number</label>
       <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="LDAP Port Number" name="ldap_port" id="ldap_port" value="<?=$settings->ldap_port?>">
     </div>

     <div class="form-group">
       <label for="ldap_version">LDAP Version Number</label>
       <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="LDAP Version Number" name="ldap_version" id="ldap_version" value="<?=$settings->ldap_version?>">
     </div>

 </div>
 <div class="col-6">
    <h2>Important Notes</h2>
   1.  For security purposes, if you decide to use LDAP as an authentication method on your project, it must be the ONLY authentication method. Others will be ignored.  Only user 1 can login with their "regular" UserSpice username and password.
   All other users will be required to have their credentials go through the LDAP server.<br><br>
   2.  User 1 is also the only user that is allowed to change their password on the user_settings.php page. This is because changing their password won't actually do anything.<br><br>
   3.  During install we put in some credentials for a demo server.  You can login with <br>
   guest1/guest1password<br>
   guest2/guest2password<br>
   guest3/guest3password<br>
   to test.  You will want to update your real server configuration below before going live.<br><br>
   4.  We need your feedback.  If we need additional configuration options for LDAP, please let us know at <a href="https://userspice.com/bugs">https://userspice.com/bugs</a> or over on our <a href="https://discord.gg/j25FeHu">Discord channel</a>.
 </div>
   </div>
 <div class="row">
   <div class="col-6">
   <?php
$groupsQ = $db->query('SELECT a.id, a.ldap, p.name FROM us_ldap_matches a LEFT JOIN permissions p ON a.permission = p.id')->results();
?>
<br>
<h2>LDAP Permission Group Matches</h2><br>
<label class="switch switch-text switch-success">
      <input id="ldap_only_perms" type="checkbox" class="switch-input toggle" data-desc="LDAP Only Permissions" <?php if ($settings->ldap_only_perms==1) {
       echo 'checked="true"';
      } ?>>
      <span data-on="Yes" data-off="No" class="switch-label"></span>
      <span class="switch-handle"></span>
</label> Only Use LDAP Permissions<br>
<table id="forms" class='table table-hover table-list-search'>
	<thead>
		<th>Permission Name</th><th>LDAP Group</th><th>Manage</th>
	</thead>
	<tbody>
		<?php
		foreach($groupsQ as $g){?>
			<tr>
				<td><?=$g->name?></td>
				<td><?=$g->ldap?></td>
				<td><a href="admin.php?view=plugins_config&plugin=ldap_login&action=edit&id=<?=$g->id?>" class="btn btn-outline-primary">Manage</a></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<br>
<a href="admin.php?view=plugins_config&plugin=ldap_login&action=add" class="btn btn-primary">Add</a>
   </div>
<div class="col-6">
  <h2>Permissions</h2><br>
  LDAP Only Permissions will sync all LDAP permissions and remove any permissions that the user does not hold from LDAP groups upon login. This is recommended but could break your setup if you manually configured permissions<br><br>
  Matches that you add here will automatically add to users upon logging in if they are part of that group<br><br>
  You can use your full container name to match to your permission. For example: CN=Example,OU=User Accounts,DC=example,DC=userspice,DC=com<br><br>
  Permissions will not be removed upon leaving the LDAP group unless the LDAP Only Permissions is checked.
</div>
</div>
</div>