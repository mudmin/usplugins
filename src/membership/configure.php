<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$edit = Input::get('edit');
$o = false;
$levels = $db->query("SELECT * FROM plg_mem_plans WHERE disabled = 0 ORDER BY ordering")->results();
if(is_numeric($edit)){
  $thisPlanQ = $db->query("SELECT * FROM plg_mem_plans WHERE id = ? AND disabled = 0",[$edit]);
  $thisPlanC = $thisPlanQ->count();
  if($thisPlanC < 1){
    Redirect::to('admin.php?view=plugins_config&plugin=membership&err=Plan+does+not+exist');
  }else{
  $thisPlan = $thisPlanQ->first();
  $thesePerms = explode(",",$thisPlan->perms_added);
  }
  $e = true;
}else{
  $e = false;
}

 if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
 }

 if(!empty($_POST['memset'])){
 $fields = array(
   'cur'=>Input::get('cur'),
   'sym'=>Input::get('sym'),
   'payments'=>Input::get('payments'),
 );
$db->update('plg_mem_settings',1,$fields);
 Redirect::to('admin.php?view=plugins_config&plugin=membership&err=Saved');
}

 if(!empty($_POST['disableThis'])){
   $da = Input::get('disableThis');
   if(is_numeric($da)){
     $db->update('plg_mem_plans',$da,['disabled'=>1]);
     Redirect::to('admin.php?view=plugins_config&plugin=membership&err=Plan+deleted');
   }
 }

 if(!empty($_POST['plugin_cost'])){
   $fields = array(
     'plan'=>Input::get('plan'),
     'cost'=>Input::get('cost'),
     'days'=>Input::get('days'),
     'descrip'=>Input::get('descrip'),
   );
   $db->insert('plg_mem_cost',$fields);
   Redirect::to('admin.php?view=plugins_config&plugin=membership&err=Pricing+option+added');
 }

 if(!empty($_POST['plugin_membership'])){

   $perms = Input::get('perm');
   if($perms == ''){Redirect::to('admin.php?view=plugins_config&plugin=membership&err=You+must+add+a+permission+level&edit='.$edit);}
   $ordering = Input::get('ordering');
   if($ordering == ''){
     $grabQ = $db->query("SELECT ordering FROM plg_mem_plans ORDER BY ordering desc");
     $grabC = $grabQ->count();
     if($grabC > 0){
        $grab = $grabQ->first();
        $ordering = (ceil($grab->ordering / 10) * 10)+10;
     }else{
       $ordering = 10;
     }
   }

   $fields = array(
     'plan_name'=>Input::get('plan_name'),
     'plan_desc'=>Input::get('plan_desc'),
     'icon'=>Input::get('icon'),
     'ordering'=>$ordering,
     'perms_added'=>implode(', ',$perms),
     'script_add'=>Input::get('script_add'),
     'script_remove'=>Input::get('script_remove'),
   );
   if(!$e){
   $db->insert('plg_mem_plans',$fields);
   Redirect::to('admin.php?view=plugins_config&plugin=membership&err=Level+created');
 }else{
   $db->update('plg_mem_plans',$edit,$fields);
   Redirect::to('admin.php?view=plugins_config&plugin=membership&err=Level+updated');
 }
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
  <div class="row">
    <div class="col-12">
      <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h2>Configure Membership Levels
        <button type="button" onclick="window.location.href = '<?=$us_url_root?>usersc/plugins/membership/manage_members.php';" name="button" class="btn btn-primary">Manage Members</button>
      </h2><br>

      <h3>Global Settings</h3>
      <?php $memset = $db->query("SELECT * FROM plg_mem_settings")->first(); ?>
      <form class="" action="" method="post">
        <input type="hidden" name="csrf" value="<?=$token?>" />
      <div class="row">
        <div class="col-5 form-group">
          <label for="">Currency Symbol (1 character)</label>
          <input type="text" name="sym" value="<?=$memset->sym?>" size="1">
        </div>
        <div class="col-5 form-group">
          <label for="">Currency Code (3 letters)</label>
          <input type="text" name="cur" value="<?=$memset->cur?>" size="3">
        </div>
        <div class="col-5 form-group">
          <label for="">Allow Plan Selection/Payments on account.php?</label>
          <select class="" name="payments">
            <option value="0" <?php if($memset->payments == 0){echo "selected='selected'";}?>>No</option>
            <option value="1" <?php if($memset->payments == 1){echo "selected='selected'";}?>>Yes</option>
          </select>
        </div><br>
        <div class="col-2 form-group">
          <input type="submit" name="memset" value="Save Global Settings" class="btn-primary">
        </div>
      </div>
    </form>
    </div>
  </div>

 		<div class="row">
 			<div class="col-sm-7 col-12">

          <h3><?php if($e){ echo "Update"; }else{ echo "Add New";}?> Level</h3>
          <form class="" action="<?php if($e){ echo "admin.php?view=plugins_config&plugin=membership&edit=$edit";}?>" method="post" >
            <input type="hidden" name="csrf" value="<?=$token?>" />

            <div class="form-group">
              <label  for="plan_name">Level Name*</label>
              <input class="form-control" type="text" name="plan_name" value="<?php if($e){echo $thisPlan->plan_name;}?>" required>
            </div>

            <div class="form-group">
              <label for="plan_desc">Description*</label>
              <input class="form-control" type="text" name="plan_desc" value="<?php if($e){echo $thisPlan->plan_desc;}?>" required>
            </div>

            <div class="form-group">
              <label  for="icon">Icon Filename (Just the filename located in usersc/membership/icons)</label>
              <input class="form-control" type="text" name="icon" value="<?php if($e){echo $thisPlan->icon;}?>">
            </div>

            <div class="form-group">
              <label for="icon">Permissions Added*</label><br>
              <?php
              $perms = $db->query("SELECT * FROM permissions WHERE id > 2 ORDER BY name")->results();
              foreach($perms as $p){?>
                <div class="col-4">
                  <input type="checkbox" name="perm[]" value="<?=$p->id?>" <?php if($e && in_array($p->id,$thesePerms)){echo "checked";}?>> <?=ucfirst($p->name)." ($p->id)"?>
                </div>
              <?php } ?>
            </div>
            <br><br>
            <div class="form-group">
              <label for="plan_desc">Ordering - Put a number to determine the order this option is shown. (Optional)</label>
              <input class="form-control" type="number" name="ordering" value="<?php if($e){echo $thisPlan->ordering;}?>" placeholder="default order">
            </div>
            <div class="form-group">
              <label  for="script">Optional - If you want to run a certain script when this option is chosen, please put it in
                usersc/plugins/membership/scripts and put the name here. There is an example in that folder.</label>
              <input class="form-control" type="text" name="script_add" value="" placeholder="begin_amateur_plan.php">
            </div>
            <div class="form-group">
              <label  for="script">Optional - Script when plan is cancelled</label>
              <input class="form-control" type="text" name="script_remove" value="" placeholder="end_amateur_plan.php">
            </div>
            <div class="form-group">
              <?php if($e){ ?>
                <input type="submit" name="plugin_membership" value="Update Level" class="btn btn-primary">
              <?php }else{ ?>
                <input type="submit" name="plugin_membership" value="Add Level" class="btn btn-primary">
              <?php } ?>

            </div>
          </form>
 			</div> <!-- /.col -->
      <div class="col-sm-5 col-12">
          <h3><?php if($e){ echo "Update"; }?> Pricing Option</h3>
          <form class="" action="<?php if($e){ echo "admin.php?view=plugins_config&plugin=membership&edit=$edit";}?>" method="post" >
            <input type="hidden" name="csrf" value="<?=$token?>" />

            <div class="form-group">
              <label  for="plan_name">Plan</label>
              <select class="form-control" name="plan" required>
                <option value="" selected="selected" disabled >--Choose Plan--</option>
                <?php foreach($levels as $l){ ?>
                  <option value="<?=$l->id?>"><?=$l->plan_name?></option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for="days"># of Days</label>
              <input class="form-control" type="number" name="days" value="" min="1" step="1" placeholder="30" required>
            </div>

            <div class="form-group">
              <label for="cost">Cost - No Symbols</label>
              <input class="form-control" type="number" name="cost" value="" min=".00" step=".01" placeholder="30.00" required>
            </div>

            <div class="form-group">
              <label  for="descrip">Description - We will automatically add the number of days to this description</label>
              <input class="form-control" type="text" name="descrip" value="" placeholder="1 month" required>
            </div>

            <div class="form-group">

                <input type="submit" name="plugin_cost" value="Add Option" class="btn btn-primary">

            </div>
          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <div class="row">
      <div class="col-12">
        <h4>Existing Plans</h4>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Plan Name</th><th>Description</th><th>Perms Added</th><th>Icon</th><th>Ordering</th><th>Delete</th><th>Edit</th>
          </thead>
          <tbody>
            <?php
            foreach($levels as $l){ ?>
              <tr>
                <td><?=$l->plan_name?></td>
                <td><?=$l->plan_desc?></td>
                <td><?=$l->perms_added?></td>
                <td><?=$l->icon?></td>
                <td><?=$l->ordering?></td>
                <td>
                  <form class="" action="" method="post" onsubmit="return confirm('Do you really want to delete this level?');">
                    <input type="hidden" name="csrf" value="<?=$token?>" />
                    <input type="hidden" name="disableThis" value="<?=$l->id?>" />
                    <input type="submit" name="disableButton" value="Delete" class="btn btn-danger">
                  </form>
                </td>
                <td><button type="button" onclick="window.location.href = 'edit=<?=$l->id?>';" name="button" class="btn btn-success">Edit</button></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

    </div>
    <div class="row">
      <div class="col-12">
        <h4>Pricing Options</h4>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Plan Name</th><th>Description</th><th>Days</th><th>Cost</th><th>Edit</th>
          </thead>
          <tbody>
            <?php
            foreach($levels as $l){
              $n = $l->plan_name;
               $costs = $db->query("SELECT * FROM plg_mem_cost WHERE plan = ? ORDER BY days",[$l->id])->results();
               foreach($costs as $c){ ?>
                 <tr>
                   <td><?=$n?></td>
                   <td><?=$c->descrip?></td>
                   <td><?=$c->days?></td>
                   <td><?=$c->cost?></td>
                   <td><button type="button" onclick="window.location.href = '<?=$us_url_root?>usersc/plugins/membership/paymentOption.php?edit=<?=$c->id?>';" name="button" class="btn btn-success">Edit</button></td>
                 </tr>

             <?php }  ?>
            <?php } ?>
          </tbody>
        </table>
      </div>

    </div>
