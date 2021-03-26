<?php
if(count(get_included_files()) ==1) die();
?>
<div class="row">
  <div class="col-12 col-sm-6">
    <?php
    if(is_numeric($edit)){
      echo "<h4>Edit Webhook</h4>";
    }else{
      echo "<h4>Create Webhook</h4>";
    }
    ?>
    <br>
    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <div class="form-group">
        <label for="">Hook Name</label>
        <input required type="text" name="hook" value="<?php if(is_numeric($edit)){echo $h->hook;}?>" class="form-control">
      </div>
      <div class="form-group">
        <label for="">Which IP is authorized to hit this webhook? (1)</label>
        <input required type="text" name="auth" value="<?php if(is_numeric($edit)){echo $h->auth;}?>" class="form-control">
      </div>

      <h5>Create an optional Secret</h5>
      <br>
      <div class="form-group">
        <label for="">Second Factor Authentication Key (2)</label><br>
        <input type="text" name="twofa_key" value="<?php if(is_numeric($edit)){echo $h->twofa_key;}?>" class="form-control">
      </div>
      <div class="form-group">
        <label for="">Second Factor Authentication Value</label>
        <input type="text" name="twofa_value" value="<?php if(is_numeric($edit)){echo $h->twofa_value;}?>" class="form-control">
      </div>

      <h5>Setup Your Actions (3)</h5>
      <br>
      <div class="form-group">
        <label for="">Select the type of action this Webhook Should Perform  </label>
        <select required class="form-control" name="action_type">
          <option value=""<?php if(!is_numeric($edit)){echo "selected='selected'";}?> disabled>--Please Choose--</option>
          <option <?php if(is_numeric($edit) && $h->action_type == 'db'){echo "selected='selected'";}?> value="db">Raw Database Query</option>
          <option <?php if(is_numeric($edit) && $h->action_type == 'php'){echo "selected='selected'";}?> value="php">Execute PHP Script</option>
          <option <?php if(is_numeric($edit) && $h->action_type == 'exec'){echo "selected='selected'";}?> value="exec">Execute Bash Script or System Command (exec)</option>
        </select>
      </div>

      <div class="form-group">
        <label for="">Enter your action</label>
        <input required type="text" name="action" value="<?php if(is_numeric($edit)){echo $h->action;}?>" class="form-control">
      </div>


      <div class="form-group">
        <label for="">Do you want to log all the incoming data to the plg_webhook_data_logs table? (4)</label><br>
        <select required class="form-control" name="log">
          <option <?php if(is_numeric($edit) && $h->log == 1){echo "selected='selected'";}?> value="1">Yes</option>
          <option <?php if(is_numeric($edit) && $h->log == 0){echo "selected='selected'";}?> value="0">No</option>
        </select>
      </div>

      <div class="form-group">
        <label for="">Do you want to disable this webhook?</label><br>
        <select required class="form-control" name="disabled">
          <option <?php if(is_numeric($edit) && $h->disabled == 0){echo "selected='selected'";}?> value="0">No</option>
          <option <?php if(is_numeric($edit) && $h->disabled == 1){echo "selected='selected'";}?> value="1">Yes</option>
        </select>
      </div>
      <?php if(!is_numeric($edit)){ ?>
        <input type="submit" name="createWebhook" value="Create Webhook" class="btn btn-primary">
      <?php }else{ ?>
        <input type="hidden" name="edit" value="<?=$edit?>">
        <input type="submit" name="editWebhook" value="Edit Webhook" class="btn btn-primary">
      <?php } ?>
    </form>
  </div>

  <!-- right side -->
  <div class="col-12 col-sm-6">
    <h4>Webhook Setup Notes</h4>
    <br>
    <b> 1- Which IP is authorized to hit this webhook? </b> <br>
    Enter the IP address that is supposed to hit this webhook.<br>
    Enter * for any IP.<br>
    Enter w for any IP on the <a href="admin.php?view=ip">UserSpice IP Whitelist</a>.<br>
    <br>

    <b>2 - Second Factor Authentication</b><br>
    <p>If IP filtering is not enough security for your needs, you can pass a second secret to the webhook. It needs to be a Key/Value pair.  It can be anywhere in GET, POST or JSON. Declare the 'key' in the first input box and the 'value' in the second.</p>
    <p>For example, if you want to require a special code of 'ThisIsTheSecret' and you want to use the key of 'secret' and you want to pass this via GET for some reason, the url would look like <br><b>webhook.php?webhook_id=7&secret=ThisIsTheSecret</b>.</p>
    <br>

    <b>3 - Setup Your Actions</b><br>
    <p>This plugin accepts 3 types of "actions" a webhook can perform. It can do a raw database query, execute a php script, or run commands/scripts directly on the server. </p>

    <p>For <em>DB Queries</em>,write your query:<br>
      <b>UPDATE settings SET site_offline = 1</b>
    </p>

    <p>For <em>PHP Scripts</em>, see the example testscript.php in the webhooks/assets folder for an example of how to write one of these scripts. Enter the name of the script you want to run:<br>
      <b>testscript.php</b>
    </p>

    <p>For <em>System Execution</em>, you better know what you're doing.  Enter your command:<br>
      <b>echo testing123  > testfile.txt</b>
    </p>

    <b>4 - Logging Incoming Data</b><br>
    <p>This plugin can log ALL of the GET, POST, or JSON data that comes is sent to the webhook in the plg_webhook_data_logs table. If you need to store massive amounts of data, you may need to increase the column data type in your db.  Default limit is 65,535 characters.
    </p>
  </div>
</div>
<br><br>
<div class="row">
  <div class="col-12">
    <h1>Existing Webhooks</h1>
    <table class="table table-hover table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Auth IP</th>
          <th>Action Type</th>
          <th>Action</th>
          <th>Secret Key</th>
          <th>Secret Value</th>
          <th>Logged?</th>
          <th>View Logs</th>
          <th>Disabled?</th>
          <th>Edit</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($webhooks as $w){ ?>
          <tr>
            <td><?=$w->id?></td>
            <td><?=$w->hook?></td>
            <td><?=$w->auth?></td>
            <td><?=$w->action_type?></td>
            <td><?=$w->action?></td>
            <td><?=$w->twofa_key?></td>
            <td><?=$w->twofa_value?></td>
            <td><?=bin($w->log);?></td>
            <td>
              <a href="admin.php?view=plugins_config&plugin=webhooks&method=hooklogs&log=<?=$w->id?>">View</a>
            </td>
            <td><?=bin($w->disabled);?></td>
            <td>
              <a class="btn btn-primary" href="admin.php?view=plugins_config&plugin=webhooks&edit=<?=$w->id?>">Edit</a>
            </td>
          </tr>

        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
