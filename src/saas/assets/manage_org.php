<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$org = Input::get('o');
$orgQ = $db->query("SELECT * FROM us_saas_orgs WHERE id = ?",[$org]);
$orgC = $orgQ->count();
if($orgC < 1 || $org < 2){Redirect::to('admin.php?view=plugins_config&plugin=saas&v=org&err=Org+not+found');}
$org = $orgQ->first();
$orgs = $db->query("SELECT * FROM us_saas_orgs ORDER BY active DESC, org ASC")->results();
$ones = $db->query("SELECT * FROM users WHERE id > 1 AND account_owner = 1")->results();
$plans = $db->query("SELECT * FROM us_saas_levels ORDER BY users ASC")->results();
$pl = [];
$pl[0]="Reserved";
foreach($plans as $p){
  $pl[$p->id] = $p->level;
}

if(!empty($_POST['createOrg'])){

  $fields = array(
    'org'=>Input::get('org'),
    'owner'=>Input::get('owner'),
    'level'=>Input::get('plan'),
    'active'=>1,
  );
  $db->update('us_saas_orgs',$org->id,$fields);
  if($org->owner != Input::get('owner')){
    $check = $db->query("SELECT account_owner WHERE id = ? AND account_owner = 1")->count();
    if($check > 0){
      $db->update('users',Input::get('owner'),['account_owner'=>$org->id]);
      $db->update('users',$org->owner,['account_owner'=>1]);
    }else{
      Redirect::to('admin.php?view=plugins_config&plugin=saas&v=manage_org&o='.$o->id.'&err=Owner+could+not_be+changed');
    }

  }
  $db->update('users',Input::get('owner'),['account_owner'=>$id]);
  Redirect::to('admin.php?view=plugins_config&plugin=saas&v=org');
}

if(!empty($_POST['deact'])){
  $users = $db->query("SELECT * FROM users WHERE account_owner = ?",[$org->id])->results();
  $db->query("DELETE FROM us_saas_mrg WHERE org = ?",[$org->id]);
  foreach($users as $u){$db->update('users',$u->id,['permissions'=>0]);}
  if($u->id != 1 && $u->id != $org->owner){
  $db->update('us_saas_orgs',$org->id,['active'=>0]);
  }
    Redirect::to('admin.php?view=plugins_config&plugin=saas&v=org');
}

if(!empty($_POST['transfer'])){
  $transfer_to = Input::get('transfer_to');
  if(is_numeric($transfer_to)){
    $check = $db->query("SELECT * FROM us_saas_orgs WHERE active = 1 AND id = ?",[$transfer_to])->count();
    if($check > 0){
      $db->query("DELETE FROM us_saas_mrg WHERE org = ?",[$org->id]);
      $users = $db->query("SELECT * FROM users WHERE account_owner = ?",[$org->id])->results();
      foreach($users as $u){
        if($u->id != 1 && $u->id != $org->owner){
        $db->update('users',$u->id,['account_owner'=>$transfer_to]);
      }

      $db->update('us_saas_orgs',$org->id,['active'=>0]);
      }
        Redirect::to('admin.php?view=plugins_config&plugin=saas&v=org');
    }
  }
}

$orgs = $db->query("SELECT * FROM us_saas_orgs WHERE id > 1 ORDER BY active DESC, org ASC")->results();
$ones = $db->query("SELECT * FROM users WHERE id > 1 AND account_owner = 1")->results();
$plans = $db->query("SELECT * FROM us_saas_levels ORDER BY users ASC")->results();
$pl = [];
$pl[0]="Reserved";
foreach($plans as $p){
  $pl[$p->id] = $p->level;
}
?>
<div class="row">
  <div class="col-12">
    <form class="" action="" method="post">
      <div class="form-group">
        <label>Owner</label>
        <select class="form-control" name="owner" required>
          <option value="<?=$org->owner?>" selected><?php echouser($org->owner);?></option>
          <?php foreach($ones as $o){?>
            <option value="<?=$o->id?>"><?php echouser($o->id);?></option>
          <?php } ?>
        </select>
      </div>
      <div class="form-group">
        <label>Org Name</label>
        <input class="form-control" type="text" name="org" value="<?=$org->org?>" required>
      </div>
      <div class="form-group">
        <label>Plan</label>
        <select class="form-control" name="plan" required>
          <option value="<?=$org->level?>" selected><?=$pl[$org->level];?></option>
          <?php foreach($plans as $p){?>
            <option value="<?=$p->id?>"><?=$p->details;?></option>
          <?php } ?>
        </select>
      </div>
        <div class="form-group">
      <label>Active</label>
      <select class="form-control" name="active" required>
        <option value="<?=$org->active?>" selected><?=bin($org->active)?></option>
        <option value="0"><?=bin(0)?></option>
        <option value="1"><?=bin(1)?></option>
      </select>

    </div>

        <input type="submit" name="createOrg" value="Update Org" class="btn btn-primary">
    </form>




  </div>
  <div class="row">
    <div class="col-12 col-sm-6">
      <form class="" action="" method="post">
        <input type="submit" name="deact" value="Deactivate Org and All Its Users" class="btn btn-danger">
      </form>
    </div>

    <div class="col-12 col-sm-6">
      <form class="" action="" method="post">
        <input type="submit" name="transfer" value="Deactivate and Transfer users to..." class="btn btn-warning"
        onclick="return confirm('Are you sure?');">
        <select class="form-control" name="transfer_to" required>
          <option value="" selected disalbled>--------</option>
          <option value="1">Reserved/Default Org</option>
          <?php foreach($orgs as $o){
            if($o->id == $org->id || $org->active == 0){continue;}?>
            <option value="<?=$o->id?>"><?=$o->org?></option>
         <?php } ?>
        </select>
      </form>
    </div>
  </div>
  <div class="col-12">
    Note: Deactivating an org, deactivates all of its users EXCEPT the owner. The owner will still be able to login,
    but will fail the check and will not be able to access org features.  The same applies for transfering,
    except users are not deactivated.  Note that since transferring an administrative task, the recipient will
    be allowed to exceed their user limit with the new users ported in. They just won't be able to create additional
    users.
  </div>
</div>
