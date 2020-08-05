<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted?>
<?php
$refSettings = $db->query("SELECT * FROM plg_refer_settings")->first();
$refReq = $refSettings->only_refer == 1 ? true : false;
$refCode = Input::get('ref');
if($refReq && $refCode == ""){
  bold("<font color='red'>You must have a valid referral code to register</font>");
}
?>
<div class="form-group">
<?php
if($refSettings->allow_un == 1){?>
<label for="ref">Please enter your referral code or the username of the person who referred you
<?php echo $refReq ? "*" : "" ?>
</label>
<?php
}else{ ?>
  <label for="ref">Please enter your referral code
  <?php echo $refReq ? "*" : "" ?>
  </label>
<?php }
?>
<input class="form-control" type="text" name="ref" value="<?=$refCode?>">
</div>
