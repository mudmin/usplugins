<?php
if(count(get_included_files()) == 1) die(); //Direct Access Not Permitted
$permissions = $db->query("SELECT * FROM permissions")->results();

?>
<h1>Add Permission Match</h1>
<br>
<div class="content mt-3">
   <div class="row">
     <div class="col-4">
         <form action="admin.php?view=plugins_config&plugin=ldap_login&action=save&type=add" method="post">
        <div class="form-group">
            <label for="ldap">LDAP Group Full Name</label>
            <input type="text" autocomplete="off" class="form-control" name="ldap" value="">
        </div>
        <div class="form-group">
            <label for="permission">UserSpice Permission</label><br>
            <select name="permission" id="permission">
                <?php foreach($permissions as $permission) {
                    echo "<option value=\"{$permission->id}\">$permission->name</option>";
                }?>
            </select>
        </div>
        <input type="hidden" name="csrf" value="<?=Token::generate()?>"/>
        <br><br>
        <input type="submit" class="btn btn-primary" value="Save"/>
        </form>
    </div>
</div>