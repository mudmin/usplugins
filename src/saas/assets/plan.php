<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted

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
  $db->insert('us_saas_levels',$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=saas&v=plan&err=Added');
}
$perms = $db->query("SELECT * FROM permissions WHERE id > 2")->results();
$plans = $db->query("SELECT * FROM us_saas_levels ORDER BY users ASC")->results();

?>
<div class="row">
  <div class="col-12 col-sm-6">
    <h3>New Plan</h3>
    <form class="" action="" method="post">
      <div class="form-group">
        <label>Plan Name</label>
        <input class="form-control" type="text" name="level" value="" required>
      </div>
      <div class="form-group">
        <label>Users</label>
        <input class="form-control" type="number" name="users" min="1" step="1" value="" required>
      </div>
      <div class="form-group">
        <label>Plan Description</label>
        <input class="form-control" type="text" name="details" value="" required>
      </div>
      <div class="form-group">
        <label>Assignable Permissions by Owners/Managers</label>

        <?php
        $count = count($perms);
        if($count == 0){echo "<br>You do not have any permission levels higher than permission 2";}
        foreach($perms as $p){ ?>
          <div class="col-4">
            <input type="checkbox" name="perms[]" value="<?=$p->id?>"><?=$p->name?>
          </div>
        <?php } ?>
      </div>
      <br>
        <input type="submit" name="createPlan" value="Create Plan" class="btn btn-primary">

    </form>
</div>
  <div class="col-12 col-sm-6">
  </div>
  <div class="col-12">
    <h3>Exising Plans</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Level</th><th>Users</th><th>Details</th><th>Assignable<br>Permissions</th><th>Manage</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($plans as $p){ ?>
          <tr>
            <td><?=$p->level?></td>
            <td><?=$p->users;?></td>
            <td><?=$p->details?></td>
            <td><?=$p->perms?></td>
            <td><a href="admin.php?view=plugins_config&plugin=saas&v=manage_plan&o=<?=$p->id?>">Manage</a></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
