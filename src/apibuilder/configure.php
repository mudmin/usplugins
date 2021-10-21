<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
  <style media="screen">
  .hideme { display:none; }
</style>
<script type="text/javascript" src="<?=$us_url_root?>usersc/plugins/apibuilder/assets/oce.js"></script>
<?php
include "plugin_info.php";
pluginActive($plugin_name);
if(!empty($_POST)){
  if(!empty($_POST['genkeys'])){
    $users = $db->query("SELECT id FROM users")->results();
    foreach($users as $u){
      $code = strtoupper(randomstring(12).uniqid());
      $code = substr(chunk_split($code,5,'-'),0,-1);
      $db->update('users',$u->id,['apibld_key'=>$code]);
      // dnd($db->errorString());
    }
    Redirect::to('admin.php?view=plugins_config&plugin=apibuilder&jsmsg=Generated!');
  }

  if(!empty($_POST['newkeys'])){
    $num = Input::get('numkeys');
    if($num > 0){
      for ($i=0; $i < $num; $i++) {
        $code = strtoupper(randomstring(12).uniqid());
        $code = substr(chunk_split($code,5,'-'),0,-1);
        $fields = array(
          'api_key'=>$code,
          'ip'=>'127.0.0.1',
          'blocked'=>0,
        );
        $db->insert('plg_api_pool',$fields);
      }
    }
    Redirect::to('admin.php?view=plugins_config&plugin=apibuilder&jsmsg=Created!');
  }

}
$apisettings = $db->query("SELECT * FROM plg_api_settings")->first();

?>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-5">
      <h3 align="center">API Builder Setup</h3><br>
      <div class="form-group">
        <label for="site_offline">API Temp Offline <a href="#!" tabindex="-1" title="Note" data-trigger="focus" class="nounderline" data-toggle="popover" data-content="Still allows you to manage settings."><i class="fa fa-question-circle"></i></a></label>
        <span style="float:right;">
          <label class="switch switch-text switch-success">
            <input id="disabled" type="checkbox" class="switch-input toggleapi" data-desc="API Offline" <?php if($apisettings->disabled==1) echo 'checked="true"'; ?>>
            <span data-on="Yes" data-off="No" class="switch-label"></span>
            <span class="switch-handle"></span>
          </label>
        </span>
      </div>

      <div class="form-group">
        <label for="site_offline">UserSpice API Offline <a href="#!" tabindex="-1" title="Note" data-trigger="focus" class="nounderline" data-toggle="popover" data-content="Enables the official UserSpice generic API. Requires the API to not be offline and the API mode must be 4 or 5."><i class="fa fa-question-circle"></i></a></label>
        <span style="float:right;">
          <label class="switch switch-text switch-success">
            <input id="spice_api" type="checkbox" class="switch-input toggleapi" data-desc="UserSpice API" <?php if($apisettings->spice_api==1) echo 'checked="true"'; ?>>
            <span data-on="Yes" data-off="No" class="switch-label"></span>
            <span class="switch-handle"></span>
          </label>
        </span>
      </div>

      <div class="form-group">
        <label for="site_offline">Hide API devMessage <a href="#!" tabindex="-1" title="Note" data-trigger="focus" class="nounderline" data-toggle="popover" data-content="These developer messages give you extra information on the success and failure of your API calls, but you may want to turn them off."><i class="fa fa-question-circle"></i></a></label>
        <span style="float:right;">
          <label class="switch switch-text switch-success">
            <input id="dev_msg" type="checkbox" class="switch-input toggleapi" data-desc="Hide Developer Messages" <?php if($apisettings->dev_msg==1) echo 'checked="true"'; ?>>
            <span data-on="Yes" data-off="No" class="switch-label"></span>
            <span class="switch-handle"></span>
          </label>
        </span>
      </div>

      <div class="form-group">
        <label for="site_offline">UserSpice API Generic User Update Offline <a href="#!" tabindex="-1" title="Note" data-trigger="focus" class="nounderline" data-toggle="popover" data-content="This addon to the UserSpice API allows you to update any column in the users table that has not been blacklisted.  Even with this feature off, you can still update core columns like username and password."><i class="fa fa-question-circle"></i></a></label>
        <span style="float:right;">
          <label class="switch switch-text switch-success">
            <input id="spice_user_api" type="checkbox" class="switch-input toggleapi" data-desc="UserSpice User API" <?php if($apisettings->spice_user_api==1) echo 'checked="true"'; ?>>
            <span data-on="Yes" data-off="No" class="switch-label"></span>
            <span class="switch-handle"></span>
          </label>
        </span>
      </div>

      <!-- Force SSL -->
      <div class="form-group">
        <label for="force_ssl">Force HTTPS<a href="#!" tabindex="-1" title="Note" data-trigger="focus" class="nounderline" data-toggle="popover" data-content="Because you are passing keys, you generally want to use https. Not that even with this setting enabled, you can make http calls from the server to itself."><i class="fa fa-question-circle"></i></a></label>
        <span style="float:right;">
          <label class="switch switch-text switch-success">
            <input id="force_ssl" type="checkbox" class="switch-input toggleapi" data-desc="Force HTTPS" <?php if($apisettings->force_ssl==1) echo 'checked="true"'; ?>>
            <span data-on="Yes" data-off="No" class="switch-label"></span>
            <span class="switch-handle"></span>
          </label>
        </span>
      </div>

      <div class="form-group">
        <label for="api_fails">Attempts before ban <a href="#!" class="nounderline" data-toggle="popover" title="Note" data-content="How many times can an IP make an invalid authentication request before it is banned. Note that logging in successfully clears the attempts."><i class="fa fa-question-circle"></i></a></label>
        <div class="input-group">
          <input type="number" step="1" min="1" max="999999999" class="form-control apinum" data-desc="Attempts Before Ban" name="api_fails" id="api_fails" value="<?=$apisettings->api_fails?>">
        </div>
      </div>

      <div class="form-group">
        <label for="api_auth_type">API Mode <a href="#!" class="nounderline" data-toggle="popover" title="Note" data-content="See description on right"><i class="fa fa-question-circle"></i></a></label>
        <div class="input-group">
          <input type="number" step="1" min="1" max="5" class="form-control apimode" data-desc="API Mode" name="api_auth_type" id="api_auth_type" value="<?=$apisettings->api_auth_type?>">
        </div>
      </div>


    </div>
    <div class="col-sm-7">
      <h3 align="center">API Modes</h3><br>
      <strong>Mode 1:</strong> APIs are disabled. No changes can be made to the settings.
      <br>

      <strong>Mode 2:</strong> APIs only require a valid API key from a pool of valid keys.
      <br>

      <strong>Mode 3:</strong> APIs require a valid API key from a pool of valid keys and that key is locked to an IP or hostname.
      <br>

      <strong>Mode 4:</strong> Each user has their own API key and it can be used from anywhere.
      <br>

      <strong>Mode 5:</strong> Each user has their own API key and that key is locked to an IP or hostname.
      <br><br>
      <p>You can find the documentation for the generic UserSpice API <a href="https://docs.google.com/document/d/1qqNlqR1dkcbDUqG39nBAE__1ot_rDgvm6DFwW3IjKfA/edit?usp=sharing">here</a>.
      </p>
      
      <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>

    </div>
  </div>
  <br><br>
  <div class="row">
    <div class="col-12 hideme hideable" id="23">
      <?php
      $keys = $db->query("SELECT * FROM plg_api_pool ORDER BY id DESC")->results();
      ?>
      <div class="text-center">


        <h3>API Key Pool</h3><br>
        <form class="" action="" method="post">
          <input type="number" name="numkeys" value="0" min="1" step="1">
          <input type="submit" name="newkeys" value="Create New Key(s)" class="btn btn-success">
        </form>
      </div>
      <table class="table table-striped paginate">
        <thead>
          <tr>
            <th>ID</th>
            <th>Key</th>
            <th>IP/HOST<br>If Applicable</th>
            <th>Blocked?</th>
            <th>User</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($keys as $k){?>
            <tr>
              <td><?=$k->id?></td>
              <td><?=$k->api_key?></td>
              <td><p class="oce" data-id="<?=$k->id?>" data-field="ip" data-input="input"><?=$k->ip?></p></td>
              <td><p class="oce" data-id="<?=$k->id?>" data-field="blocked" data-input="select"><?php if($k->blocked == 1){echo "Yes";}else{echo "No";}?></p></td>
              <td><p class="oce" data-id="<?=$k->id?>" data-field="user_id" data-input="input"><?=$k->user_id?></p></td>
              <td><p class="oce" data-id="<?=$k->id?>" data-field="descrip" data-input="input"><?=$k->descrip?></p></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <div class="col-12 hideme hideable text-center" id="45" >
      <form class="" action="" method="post">
        <input type="submit" name="genkeys" value="Generate A New Key For Each User" class="btn btn-danger">
        <br><strong>Please Note:</strong> Users will still need to setup their IP/Hostname on their account.php page to access the API if IP verification is turned on.
      </form>
    </div>

  </div>

  <script type="text/javascript">
  $(document).ready(function() {
    var mode = "<?=$apisettings->api_auth_type?>";
    $('[data-toggle="popover"]').popover();
    function hideShow() {
      console.log(mode);
      if(mode == 1){
        $("#23").addClass("hideme");
        $("#45").addClass("hideme");
      }

      if(mode == 2 || mode == 3){
        $("#23").removeClass("hideme");
        $("#45").addClass("hideme");
      }

      if(mode == 4 || mode == 5){
        $("#23").addClass("hideme");
        $("#45").removeClass("hideme");
      }
    }

    function messages(data) {
      console.log(data);
      $('#messages').removeClass();
      $('#message').text("");
      $('#messages').show();
      if(data.success == "true"){
        $('#messages').addClass("sufee-alert alert with-close alert-success alert-dismissible fade show");
      }else{
        $('#messages').addClass("sufee-alert alert with-close alert-success alert-dismissible fade show");
      }
      $('#message').text(data.msg);
      $('#messages').delay(3000).fadeOut('slow');

    }

    $( ".toggleapi" ).change(function() { //use event delegation
      var value = $(this).prop("checked");
      $(this).prop("checked",value);

      var field = $(this).attr("id"); //the id in the input tells which field to update
      var desc = $(this).attr("data-desc"); //For messages
      var formData = {
        'value' 				: value,
        'field'					: field,
        'desc'					: desc,
        'type'          : 'toggle',
      };

      $.ajax({
        type 		: 'POST',
        url 		: '../usersc/plugins/apibuilder/assets/parser.php',
        data 		: formData,
        dataType 	: 'json',
      })

      .done(function(data) {

        messages(data);
      })
    });

    $( ".apinum" ).change(function() { //use event delegation
      var value = $(this).val();
      // console.log(value);

      var field = $(this).attr("id"); //the id in the input tells which field to update
      var desc = $(this).attr("data-desc"); //For messages
      var formData = {
        'value' 				: value,
        'field'					: field,
        'desc'					: desc,
        'type'          : 'num',
      };

      $.ajax({
        type 		: 'POST',
        url 		: '../usersc/plugins/apibuilder/assets/parser.php',
        data 		: formData,
        dataType 	: 'json',
      })

      .done(function(data) {
        messages(data);
      })
    });
    $( ".apimode" ).change(function() { //use event delegation
      var value = $(this).val();
      // console.log(value);

      var field = $(this).attr("id"); //the id in the input tells which field to update
      var desc = $(this).attr("data-desc"); //For messages
      var formData = {
        'value' 				: value,
        'field'					: field,
        'desc'					: desc,
        'type'          : 'apimode',
      };

      $.ajax({
        type 		: 'POST',
        url 		: '../usersc/plugins/apibuilder/assets/parser.php',
        data 		: formData,
        dataType 	: 'json',
      })

      .done(function(data) {
        messages(data);
        if(data.success == "true"){
          console.log(data.mode);
          mode = data.mode;
          hideShow();
        }
      })
    });

    $( ".apitxt" ).change(function() { //use event delegation
      var value = $(this).val();
      console.log(value);

      var field = $(this).attr("id"); //the id in the input tells which field to update
      var desc = $(this).attr("data-desc"); //For messages
      var formData = {
        'value' 				: value,
        'field'					: field,
        'desc'					: desc,
        'type'          : 'txt',
      };

      $.ajax({
        type 		: 'POST',
        url 		: '../usersc/plugins/apibuilder/assets/parser.php',
        data 		: formData,
        dataType 	: 'json',
      })

      .done(function(data) {
        messages(data);
      })
    });
    hideShow();


    function oceSuccess(data) {
      $('#messages').removeClass();
      $('#message').text("");
      $('#messages').show();
      $('#messages').addClass("sufee-alert alert with-close alert-success alert-dismissible fade show");
      $('#message').text(data);
      $('#messages').delay(3000).fadeOut('slow');
    }

    var oceOpts = {
      url:'../usersc/plugins/apibuilder/assets/oce.php',
      selectOptions : {'0':'No','1':'Yes'},
      allowNull : false}
      jQuery('.oce').oneClickEdit(oceOpts, oceSuccess);

    });
  </script>
