<?php
if(count(get_included_files()) == 1) die(); //Direct Access Not Permitted
$permissions = $db->query("SELECT * FROM permissions")->results();
$ldap = $db->query("SELECT * FROM us_ldap_matches WHERE id = ?", [Input::get('id')])->first();
?>
<h1>Edit Permission Match</h1>
<br>
<div class="content mt-3">
   <div class="row">
     <div class="col-4">
     <form action="admin.php?view=plugins_config&plugin=ldap_login&action=save&type=edit" method="post">
        <div class="form-group">
            <label for="ldap">LDAP Group Full Name</label>
            <input type="text" autocomplete="off" class="form-control" data-desc="LDAP Server URI" name="ldap" value="<?=$ldap->ldap?>">
        </div>
        <div class="form-group">
            <label for="permission">UserSpice Permission</label><br>
            <select name="permission" id="permission">
                <?php foreach($permissions as $permission) {
                    if ($permission->id === $ldap->permission) {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }
                    echo "<option value=\"{$permission->id}\" $selected>$permission->name</option>";
                }?>
            </select>
        </div>
        <input type="hidden" name="id" value="<?=Input::get('id')?>"/>
        <input type="hidden" name="csrf" value="<?=Token::generate()?>"/>
        <br><br>
        <input type="submit" class="btn btn-primary" value="Save"/>
        <a href="admin.php?view=plugins_config&plugin=ldap_login&action=delete&id=<?=Input::get('id')?>&token=<?=Token::generate()?>" class="btn btn-danger">Remove</a>
        </form>
    </div>
</div>