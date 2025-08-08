<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

   <?php
   include "plugin_info.php";
   pluginActive($plugin_name);
   $edit = Input::get('edit');
   if(is_numeric($edit)){
     $serverQ = $db->query("SELECT * FROM mqtt WHERE id = ?",[$edit]);
     $serverC = $serverQ->count();
     if($serverC > 0){
       $s = $serverQ->first();
     }else{
       Redirect::to("admin.php?view=plugins_config&plugin=mqtt&err=Server not found");
     }
   }
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

     if(!is_numeric($edit)){
       $db->insert("mqtt",$fields);
       if($db->error()) {
         logger($user->data()->id,"MQTT Settings","Failed to insert to MQTT Table, Error: ".$db->errorString());
       }
       Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=mqtt&err=New+server+added');
     }else{
       $db->update("mqtt",$edit,$fields);
       if($db->error()) {
         logger($user->data()->id,"MQTT Settings","Failed to insert to MQTT Table, Error: ".$db->errorString());
       }
       Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=mqtt&err=Server settings edited');
     }


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
                    <?php if(!is_numeric($edit)){
                      $e = false;
                      ?>
                      <h3>Create a new MQTT Server Connection</h3>
                    <?php }else{
                      $e = true;
                      ?>
                    <h3>Edit MQTT Server Connection</h3>
                    <?php } ?>
                    <label>Server IP or Hostname:*</label>
                    <input required size='50' class='form-control' type='text' name='server' value='<?php if($e){echo $s->server; }?>' />

                    <label>Server Port:*</label>
                    <input required size='50' class='form-control' type='number' min="0" max="99999" step="1" name='port' value='<?php if($e){echo $s->port; }?>' />

                    <label>Username:</label>
                    <input size='50' class='form-control' type='text' name='username' value='<?php if($e){echo $s->username; }?>' />

                    <label>Password:</label>
                    <input size='50' class='form-control' type='password' name='password' value='<?php if($e){echo $s->password; }?>' />

                    <label>Server Nickname:</label>
                    <input size='50' class='form-control' type='text' name='nickname' value='<?php if($e){echo $s->nickname; }?>' />
                    <input type="hidden" name="csrf" value="<?=Token::generate();?>" /><br>
                    <?php if($e) { ?>
                      <input class='btn btn-primary' name="update_only" type='submit' value='Edit MQTT Server' class='submit' /><br>
                    <?php }else{ ?>
                      <input class='btn btn-primary' name="update_only" type='submit' value='Add MQTT Server' class='submit' /><br>
                    <?php } ?>


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

                        <th>Nickname</th>
                        <th>Edit</th>
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
        
                          <td><?=$s->nickname?></td>
                          <td>
                            <a class="btn btn-primary" href="<?=$us_url_root?>users/admin.php?view=plugins_config&plugin=mqtt&edit=<?=$s->id?>">Edit</a>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
            </div>
          </div>
          </form>
