<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>
<?php
global $user;
$pntSettings = $db->query("SELECT * FROM plg_points_settings")->first();
$uc = ucfirst(pointsNameReturn());
if($pntSettings->show_acct_bal == 1){
$pntName = ucfirst(pointsNameReturn());
echo "<strong>$pntName</strong>";
echo ": ".$user->data()->plg_points."<br>";
}
if($pntSettings->allow_arb_trans == 1){ ?>
<form class="" action="" method="post">
  <h4>Transfer <?=$uc?></h4>
  <div class="form-group">
    <label for="">Points*</label>
    <input class="form-control" type="number" name="points" value="" min=".00000001" max="<?=$user->data()->plg_points?>" required>
  </div>
  <div class="form-group">
    <label for="">Transfer to (Username or ID)*</label>
    <input class="form-control" type="text" name="to" value="" required>
  </div>
  <div class="form-group">
    <label for="">Reason*</label>
    <input class="form-control" type="text" name="reason" value="" required>
  </div>
  <div class="form-group">
    <input type="submit" name="transferPoints" value="Transfer - Cannot be undone!" class="btn btn-danger">
  </div>
</form>
<?php
}
?>
