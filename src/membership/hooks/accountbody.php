<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(pluginActive('payments',true)){
$memSettings = $db->query("SELECT * FROM plg_mem_settings")->first();
if($memSettings->payments == 1){?>
<div class="form-group">
  <button type="button" onclick="window.location.href = 'account.php?change=membership';" name="button" class="btn btn-primary btn-block">Manage Membership</button>
</div>
<?php }
}
?>
