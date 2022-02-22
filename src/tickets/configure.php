<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');}
include "plugin_info.php";

if(!pluginActive("forms",true)){
  die("The UserSpice Official Form Builder plugin is required for this plugin");
}
$check = $db->query("SELECT * FROM us_forms WHERE form = ?",['plg_tickets'])->count();
if($check < 1){
  $db->insert("us_forms",['form'=>'plg_tickets']);
  $db->insert("us_forms",['form'=>'plg_tickets_cats']);
  $db->insert("us_forms",['form'=>'plg_tickets_status']);
}
$ticSettings = $db->query("SELECT * FROM plg_tickets_settings")->first();
$permissions = $db->query("SELECT * FROM permissions WHERE id > 1")->results();

pluginActive($plugin_name);
if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  if(!empty($_POST['deleteAtt'])){
    if(is_numeric(Input::get('delCat'))){
      $db->query("DELETE FROM plg_tickets_cats WHERE id = ?",[Input::get('delCat')]);
      usSuccess("Category Deleted");
    }

    if(is_numeric(Input::get('delStat'))){
      $db->query("DELETE FROM plg_tickets_status WHERE id = ?",[Input::get('delStat')]);
      usSuccess("Status Deleted");
    }
  }

  if(!empty($_POST['saveSettings'])){
    $fields = [
      'perm'=>Input::get('perm'),
      'perm_to_assign'=>Input::get('perm_to_assign'),
      'agent_term'=>Input::get('agent_term'),
      'cat_term'=>Input::get('cat_term'),
      'cat_enabled'=>Input::get('cat_enabled'),
      'agents_act'=>Input::get('agents_act'),
      'users_act'=>Input::get('users_act'),
      'email_agent'=>Input::get('email_agent'),
      'email_user'=>Input::get('email_user'),
      'email_new'=>Input::get('email_new'),
      'ticket_view'=>Input::get('ticket_view'),
      'single_view'=>Input::get('single_view'),
    ];
    $db->update("plg_tickets_settings",1,$fields);
    Redirect::to("admin.php?view=plugins_config&plugin=tickets&err=Saved!");
  }

  if(!empty($_POST['submit']) && (!empty($_POST['cat']) || !empty($_POST['status']))){
    processForm();
    Redirect::to("admin.php?view=plugins_config&plugin=tickets&err=Saved!");
  }

}

$token = Token::generate();
?>
<style media="screen">
  .blue {
    color:blue;
  }
</style>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-12">
      <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <div class="row">
        <div class="col-6">
          <h1>Configure the Tickets Plugin!</h1>
          <a href="<?=$us_url_root?>usersc/plugins/tickets/documentation.php" class="blue">Read the Documentation!</a>
        </div>
        <div class="col-6 text-right">
          <a href="<?=$us_url_root?><?=$ticSettings->ticket_view?>" class="btn btn-primary">View Tickets</a>
        </div>
      </div>


      <div class="row">
        <div class="col-12 col-sm-6">
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=Token::generate();?>">
            <div class="form-group">
              <label for=""><?=ucfirst($ticSettings->agent_term);?> permission level</label>
              <select class="form-control" name="perm" required>
                <?php foreach($permissions as $p){ ?>
                  <option value="<?=$p->id?>" <?php if($ticSettings->perm == $p->id){echo "selected='selected'";}?>><?=$p->name?></option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for=""> Permission level to assign tickets to a particular <?=$ticSettings->agent_term;?></label>
              <select class="form-control" name="perm_to_assign" required>
                <?php foreach($permissions as $p){ ?>
                  <option value="<?=$p->id?>" <?php if($ticSettings->perm_to_assign == $p->id){echo "selected='selected'";}?>><?=$p->name?></option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for="">What do you want to call the people who answer tickets?</label>
              <input type="text" name="agent_term" value="<?=$ticSettings->agent_term?>" class="form-control">
            </div>

            <div class="form-group">
              <label for="">What term do you want to use for your ticket categories?</label>
              <input type="text" name="cat_term" value="<?=$ticSettings->cat_term?>" class="form-control">
            </div>

            <!-- <div class="form-group">
            <label for="">Are categories enabled?</label>
            <select class="form-control" name="cat_enabled" required>

            <option value="0"
            <?php
            //if($ticSettings->cat_enabled == 0){echo "selected='selected'";}
            ?>>No</option>
            <option value="1"
            <?php
            //if($ticSettings->cat_enabled == 1){echo "selected='selected'";}
            ?>
            >Yes</option>
          </select>
        </div> -->

        <div class="form-group">
          <label for="">Comma separated list of people to email when there is a new ticket. (Optional)</label>
          <textarea name="email_new" class="form-control"><?=$ticSettings->email_new?></textarea>
        </div>


      </div>
      <div class="col-12 col-sm-6">

        <div class="form-group">
          <label for="">Do <b>agents</b> see their tickets on their account page?</label>
          <select class="form-control" name="agents_act" required>

            <option value="0"
            <?php if($ticSettings->agents_act == 0){echo "selected='selected'";}?>>No</option>
            <option value="1"
            <?php if($ticSettings->agents_act == 1){echo "selected='selected'";} ?>>Yes</option>
          </select>
        </div>

        <div class="form-group">
          <label for="">Do <b>users</b> see their tickets on their account page?</label>
          <select class="form-control" name="users_act" required>

            <option value="0"
            <?php if($ticSettings->users_act == 0){echo "selected='selected'";}?>>No</option>
            <option value="1"
            <?php if($ticSettings->users_act == 1){echo "selected='selected'";} ?>>Yes</option>
          </select>
        </div>

        <div class="form-group">
          <label for="">Email <?=$ticSettings->agent_term?> when they are assigned a ticket?</label>
          <select class="form-control" name="email_agent" required>

            <option value="0"
            <?php if($ticSettings->email_agent == 0){echo "selected='selected'";}?>>No</option>
            <option value="1"
            <?php if($ticSettings->email_agent == 1){echo "selected='selected'";} ?>>Yes</option>
          </select>
        </div>

        <div class="form-group">
          <label for="">Email user when their ticket status changes?</label>
          <select class="form-control" name="email_user" required>

            <option value="0"
            <?php if($ticSettings->email_user == 0){echo "selected='selected'";}?>>No</option>
            <option value="1"
            <?php if($ticSettings->email_user == 1){echo "selected='selected'";} ?>>Yes</option>
          </select>
        </div>

        <div class="form-group">
          <label for="">Where is the <?=$ticSettings->agent_term?> ticket manager located?</label>
          <input type="text" name="ticket_view" value="<?=$ticSettings->ticket_view?>" class="form-control">
        </div>

        <div class="form-group">
          <label for="">Where is the view for a single ticket located?</label>
          <input type="text" name="single_view" value="<?=$ticSettings->single_view?>" class="form-control">
        </div>

        <div class="form-group text-right">
          <input type="submit" name="saveSettings" value="Save Settings" class="btn btn-primary">
        </div>
      </form>
    </div>

  </div>

</div> <!-- /.col -->
</div> <!-- /.row -->

<div class="row">
  <div class="col-12 col-sm-4">
    <h3>Create a New Status</h3>
    <?php displayForm("plg_tickets_status");?>
    <br>
    <h3>Existing</h3>
    <?php displayTable("plg_tickets_status");?>
  </div>

  <div class="col-12 col-sm-4">
    <h3>Create a New <?=ucfirst($ticSettings->cat_term);?></h3>
    <?php displayForm("plg_tickets_cats");?>
    <br>
    <h3>Existing</h3>
    <?php displayTable("plg_tickets_cats");?>
  </div>

  <div class="col-12 col-sm-4">
    <h3>Delete</h3>

    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      Delete a <?=ucfirst($ticSettings->cat_term);?>
      <select class="form-control" name="delCat">
        <option value="" disabled selected="selected">--None--</option>
        <?php
        $cats = $db->query("SELECT * FROM plg_tickets_cats")->results();

        foreach($cats as $c){ ?>
          <option value="<?=$c->id?>"><?=$c->cat?></option>

        <?php }
        ?>
      </select>

      <br>
      Delete a Status
      <select class="form-control" name="delStat">
        <option value="" disabled selected="selected">--None--</option>
        <?php
        $cats = $db->query("SELECT * FROM plg_tickets_status")->results();
        foreach($cats as $c){ ?>
          <option value="<?=$c->id?>"><?=$c->status?></option>
        <?php }
        ?>
      </select>
      <b>Note:</b>
      Tickets with these attributes will still have them and it will not throw errors to delete them.
      <br><br>
      <input type="submit" name="deleteAtt" value="Delete" class="btn btn-danger">

    </form>
  </div>
</div>
