<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$id = Input::get('o');
$planQ = $db->query("SELECT * FROM us_saas_levels WHERE id = ?",[$id]);
$planC = $planQ->count();
if($planC < 1){Redirect::to('admin.php?view=plugins_config&plugin=saas&v=plan&err=Plan+not+found');}
$plan = $planQ->first();
$explode = explode(',',$plan->perms);

if(!empty($_POST['createPlan'])){
  foreach($_POST['perms'] as $k=>$v){
    if($v == 1 || $v == 2){
      unset($_POST['perms'][$k]);
    }
  }
  $pr = implode(',', $_POST['perms']);
  $fields = array(
    'level'=>Input::get('level'),
    'users'=>Input::get('users'),
    'details'=>Input::get('details'),
    'perms'=>$pr,
  );
  $db->update('us_saas_levels',$id,$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=saas&v=plan&err=Saved');
}

if(!empty($_POST['deact'])){

    Redirect::to('admin.php?view=plugins_config&plugin=saas&v=plan&err=Deleted');
}
$perms = $db->query("SELECT * FROM permissions WHERE id > 2")->results();
?>
<div class="row">
  <div class="col-12">
    <form class="" action="" method="post">
      <div class="form-group">
        <label>Plan Name</label>
        <input class="form-control" type="text" name="level" value="<?=$plan->level?>" required>
      </div>
      <div class="form-group">
        <label>Users</label>
        <input class="form-control" type="number" name="users" min="1" step="1" value="<?=$plan->users?>" required>
      </div>
      <div class="form-group">
        <label>Plan Description</label>
        <input class="form-control" type="text" name="details" value="<?=$plan->details?>" required>
      </div>
      <div class="form-group"><br>
        <label>Assignable Permissions by Owners/Managers</label>
        <br><strong>Note: Removing a permission from a level does NOT automatially strip it from all the users. You must do that manually.</strong><br>
        <?php
        $count = count($perms);
        if($count == 0){echo "<br>You do not have any permission levels higher than permission 2";}
        foreach($perms as $p){
          ?>
          <div class="col-4">
            <input type="checkbox" name="perms[]" value="<?=$p->id?>" <?php if(in_array($p->id,$explode)){echo "checked";}?>><?=$p->name?>
          </div>
        <?php } ?>
      </div>
      <br>
        <input type="submit" name="createPlan" value="Update Plan" class="btn btn-primary">
    </form>
  </div>
