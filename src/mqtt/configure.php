<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

   <?php
   include "plugin_info.php";
  
   pluginActive($plugin_name);
   $servers = $db->query("SELECT * FROM mqtt")->results();
   $errors = $db->errorInfo(); //looking for missing mqtt table
   if(!empty($_POST)){
     $token = $_POST['csrf'];
     if(!Token::check($token)){
       include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
     }
     $fields = array(
       'server'      => Input::get('server'),
       'port'        => Input::get('port'),
       'username'    => Input::get('username'),
       'password'    => Input::get('password'),
       'nickname'    => Input::get('nickname'),
     );

     $db->insert("mqtt",$fields);
     if($db->error()) {
       logger($user->data()->id,"MQTT Settings","Failed to insert to MQTT Table, Error: ".$db->errorString());
     }
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=mqtt&err=New+server+added');
   }
   ?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
           <?php  ?>
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure Your MQTT Servers</h1>
          <?php if($errors[0] == "42S02"){ ?>
            <font color="red"><strong>You must click "install" on the plugin to create the database table</strong></font>
          <?php } ?>
                  <p>To use MQTT in your code, the syntax is mqtt(id_number_of_server,topic,message);<br>
                  For example: mqtt(2,"Hello","World!"); //sends Msg of "World!" with topic of "Hello" to MQTT server 2.</p>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <form name='update' action='' method='post'>
                    <h3>Create a new MQTT Server Connection</h3>
                    <label>Server IP or Hostname:*</label>
                    <input required size='50' class='form-control' type='text' name='server' value='' />

                    <label>Server Port:*</label>
                    <input required size='50' class='form-control' type='number' min="0" max="99999" step="1" name='port' value='' />

                    <label>Username:</label>
                    <input size='50' class='form-control' type='text' name='username' value='' />

                    <label>Password:</label>
                    <input size='50' class='form-control' type='password' name='password' value='' />

                    <label>Server Nickname:</label>
                    <input size='50' class='form-control' type='text' name='nickname' value='' />
                    <input type="hidden" name="csrf" value="<?=Token::generate();?>" /><br>
                    <input class='btn btn-primary' name="update_only" type='submit' value='Add MQTT Server' class='submit' /><br>

                  </form>

                </div>

                <div class="col-sm-8">
                  <h3>Existing Servers</h3>
                  <!-- This msg div is here for posting One Click Edit response messages -->
                  <div id="msg" class="bg-info text-info"></div>
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Server</th>
                        <th>Port</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Nickname</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      foreach($servers as $s){ ?>
                        <tr>
                          <td><?=$s->id?></td>
                          <td><?=$s->server?></td>
                          <td><?=$s->port?></td>
                          <td><?=$s->username?></td>
                          <td><?=$s->password?></td>
                          <td><?=$s->nickname?></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
            </div>
          </div>
          </form>
