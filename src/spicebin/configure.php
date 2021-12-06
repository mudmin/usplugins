<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  $pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
  $perms = $db->query("SELECT * FROM permissions ORDER BY name")->results();
  $tags = $db->query("SELECT * FROM plg_tags ORDER BY tag")->results();
  pluginActive($plugin_name);
  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    $fields = [];
    foreach($pset as $k=>$v){
      if($k == "id"){ continue; }
      $fields[$k] = Input::get($k);
    }
    $pre = ["your_page","create_page","view_page"];
    foreach($pre as $p){
      if (substr($fields[$p], 0, 1) === '/'){
        $fields[$p] = ltrim($fields[$p], '/');
      }
    }
    $db->update("plg_spicebin_settings",1,$fields);
    sessionValMessages([],"Settings saved");
    Redirect::to("admin.php?view=plugins_config&plugin=spicebin");
  }

  function getFiles(string $path) : array
  {

    $files = [];
    $items = scandir($path);
    foreach ($items as $item) {
      if(!is_dir($path.'/'.$item))
      $files[] = str_replace(".css","",$item);
    }
    return $files;
  }
  $themes = getFiles($abs_us_root.$us_url_root."usersc/plugins/spicebin/assets/theme");

  $token = Token::generate();
  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>Configure the SpiceBin Plugin!
          <a href="<?=$us_url_root?>usersc/plugins/spicebin/management.php" class="btn btn-primary"><?=$pset->product_single?> Management</a>
          <a href="<?=$us_url_root?>usersc/plugins/spicebin/documentation.php" class="btn btn-primary">Documentation</a>
        </h1>
        <br>
        <p>SpiceBin is a Pastebin-style paste tool built on UserSpice designed to give you a good bit of flexibility. For more information, please read <a href="<?=$us_url_root?>usersc/plugins/spicebin/documentation.php" style="color:blue;">the documentation</a>.

          If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate" style="color:blue;">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=Token::generate()?>">
            <div class="row">
              <div class="col-12 col-md-6">
                <h3>General <?=$pset->product_name?> Settings</h3>
                <div class="form-group">
                  <label for="">Product Name</label>
                  <p>Although the plugin is called SpiceBin, you don't have to use that name on your site. What would you like it to be?</p>
                  <input class="form-control" type="text" name="product_name" value="<?=$pset->product_name?>">
                </div>

                <div class="form-group">
                  <label for="">What do you want to call a single "paste?"</label>
                  <p>If you have a creative name like Blurbs. What would you call a single one? Blurb? </p>
                  <input class="form-control" type="text" name="product_single" value="<?=$pset->product_single?>">
                </div>

                <div class="form-group">
                  <label for="">What do you want to call a group of "pastes?"</label>
                  <p>Blurbs? </p>
                  <input class="form-control" type="text" name="product_plural" value="<?=$pset->product_plural?>">
                </div>
                <h3>Page Settings</h3>

                <p>We have created some pages for your users to create, update and delete <?=$pset->product_plural?>.  You may choose to leave those pages in the plugin folder or you can also move or customize them.  There is also a sidebar that can show the last 10 <?=$pset->product_plural?> or 10 random <?=$pset->product_plural?>.  Note that the sidebar is hidden on small screens. <a href="<?=$us_url_root?>usersc/plugins/spicebin/documentation.php" style="color:blue;">The documentation</a> will give you more details on creating and customizing these pages.</p>

                <div class="form-group" style="padding-bottom:2em;" >
                  <label for="">Where is your <?=$pset->product_single?> creation page?</label>
                  <input class="form-control" type="text" name="create_page" value="<?=$pset->create_page?>">
                  <label for="">Create page sidebar configuration</label>
                  <select class="form-control" name="lten_create">
                    <option value="0" <?php if($pset->lten_create == 0){echo "selected='selected'";}?>>Do not show the sidebar</option>
                    <option value="1" <?php if($pset->lten_create == 1){echo "selected='selected'";}?>>Show the last 10 <?=$pset->product_plural?></option>
                    <option value="2" <?php if($pset->lten_create == 2){echo "selected='selected'";}?>>Show 10 random <?=$pset->product_plural?></option>
                  </select>

                </div>

                <div class="form-group" style="padding-bottom:2em;" >
                  <label for="">Where is your view a single <?=$pset->product_single?> page?</label>
                  <input class="form-control" type="text" name="view_page" value="<?=$pset->view_page?>">
                  <label for="">View page sidebar configuration</label>
                  <select class="form-control" name="lten_view">
                    <option value="0" <?php if($pset->ltenviewe == 0){echo "selected='selected'";}?>>Do not show the sidebar</option>
                    <option value="1" <?php if($pset->lten_view == 1){echo "selected='selected'";}?>>Show the last 10 <?=$pset->product_plural?></option>
                    <option value="2" <?php if($pset->lten_view == 2){echo "selected='selected'";}?>>Show 10 random <?=$pset->product_plural?></option>
                  </select>
                </div>

                <div class="form-group" style="padding-bottom:2em;" >
                  <label for="">Where is your logged in user's dashboard of their <?=$pset->product_plural?> page?</label>
                  <input class="form-control" type="text" name="your_page" value="<?=$pset->your_page?>">
                  <label for="">Dashboard sidebar configuration</label>
                  <select class="form-control" name="lten_your">
                    <option value="0" <?php if($pset->lten_your == 0){echo "selected='selected'";}?>>Do not show the sidebar</option>
                    <option value="1" <?php if($pset->lten_your == 1){echo "selected='selected'";}?>>Show the last 10 <?=$pset->product_plural?></option>
                    <option value="2" <?php if($pset->lten_your == 2){echo "selected='selected'";}?>>Show 10 random <?=$pset->product_plural?></option>
                  </select>
                </div>

                <h3>Theme</h3>
                <div class="form-group">
                  <label for=""><?=$pset->product_single?> styling theme</label>
                  <p>
                    Note that UserSpice has its own theme templates for the whole site which can be configured
                    <a href="<?=$us_url_root?>users/admin.php?view=templates" style="color:blue;">here</a> and you can download more
                    <a href="<?=$us_url_root?>users/admin.php?view=spice" style="color:blue;">here</a>. These themes refer to the textbox for entering and viewing <?=$pset->product_plural?>.  You can preview themes <a href="https://codemirror.net/demo/theme.html#<?=$pset->theme?>" target="_blank" style="color:blue;">here</a>.
                  </p>

                  <select class="form-control" name="theme">
                    <?php foreach($themes as $t) { ?>
                      <option value="<?=$t?>" <?php if($t == $pset->theme){ echo "selected = 'selected'";} ?>><?=$t?></option>
                    <?php } ?>
                  </select>

                </div>
              </div>

              <div class="col-12 col-md-6">
                <h3>Privacy & Access Control Settings</h3>
                <div class="form-group">
                  <label for="">Who can create <?=$pset->product_plural?>?</label>
                  <p>Note that you can determine WHICH logged in users can create <?=$pset->product_plural?> later, but if you allow guests to create create <?=$pset->product_plural?>, then those settings are ignored.</p>
                  <select class="form-control" name="create_privacy">
                    <option value="0" <?php if($pset->create_privacy == 0){echo "selected='selected'";}?>>Logged in Users and Guests</option>

                    <option value="1" <?php if($pset->create_privacy == 1){echo "selected='selected'";}?>>Logged in Users Only</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="">Who can create <?=$pset->product_plural?> (Permission Level)?</label>
                  <p>If you are restricting access to only logged in users, then you can also restrict to users with only a certain permission level.  If you want all logged in users, then please choose "User" for this setting.</p>
                  <select class="form-control" name="perm">

                    <?php foreach($perms as $p){ ?>
                      <option value="<?=$p->id?>" <?php if($pset->perm == $p->id){echo "selected='selected'";}?>><?=$p->name?></option>
                    <?php  } ?>

                  </select>
                </div>

                <div class="form-group">
                  <label for="">Who can manage (view all / delete all) <?=$pset->product_plural?> (Permission Level)?</label>
                  <p>This is an admin-level feature but you can delegate it to non-admins here.</p>
                  <select class="form-control" name="mng_perm">

                    <?php foreach($perms as $p){ ?>
                      <option value="<?=$p->id?>" <?php if($pset->mng_perm == $p->id){echo "selected='selected'";}?>><?=$p->name?></option>
                    <?php  } ?>

                  </select>
                </div>


                <div class="form-group">
                  <label for="">Who can create <?=$pset->product_plural?> (User Tags)?</label>
                  <p>The User Tags plugin is a great way to "tag" users and give them a special ability without creating and managing a whole permission level. If you have that plugin installed, then tags will show up here and you can select a tag to allow people to create <?=$pset->product_plural?>.</p>
                  <select class="form-control" name="tag">
                    <option value="">--No Tag --</option>
                    <?php foreach($tags as $p){ ?>
                      <option value="<?=$p->id?>" <?php if($pset->tag == $p->tag){echo "selected='selected'";}?>><?=$p->tag?></option>
                    <?php  } ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="">Who can manage (view/delete) <?=$pset->product_plural?> (User Tags)?</label>
                  <p>This is the same admin-level permission as above, but with user tags.</p>
                  <select class="form-control" name="mng_tag">
                    <option value="">--No Tag --</option>
                    <?php foreach($tags as $p){ ?>
                      <option value="<?=$p->id?>" <?php if($pset->mng_tag == $p->tag){echo "selected='selected'";}?>><?=$p->tag?></option>
                    <?php  } ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="">Can users access their <?=$pset->product_plural?> from their account page??</label>
                  <p>Enabling this will put a button on the acount page linking them to their pastes. Otherwise, you can create a link in your menu or do some other linking manually.</p>
                  <select class="form-control" name="account">
                    <option value="0" <?php if($pset->account == 0){echo "selected='selected'";}?>>No</option>

                    <option value="1" <?php if($pset->account == 1){echo "selected='selected'";}?>>Yes</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="">What do you want the account page button to say?</label>
                  <input class="form-control" type="text" name="product_button" value="<?=$pset->product_button?>">
                </div>

                <div class="form-group">
                  <label for=""><?=$pset->product_plural?> are automatically deleted after X days.</label>
                  <p>Setting this to 0 means <?=$pset->product_plural?> are never automatically deleted. Please see <a href="<?=$us_url_root?>usersc/plugins/spicebin/documentation.php" style="color:blue;">the documentation</a> for some important details.</p>
                  <input class="form-control" type="number" step="1" min="0" name="delete_days" value="<?=$pset->delete_days?>">
                </div>

                <div class="form-group" style="padding-bottom:2em;" >
                  <label for="">Delete Mode</label>
                  <p>Please see <a href="<?=$us_url_root?>usersc/plugins/spicebin/documentation.php" style="color:blue;">the documentation</a>.</p>
                  <select class="form-control" name="del_mode">
                    <option value="0" <?php if($pset->del_mode == 0){echo "selected='selected'";}?>>I will setup a cron job</option>
                    <option value="1" <?php if($pset->del_mode == 1){echo "selected='selected'";}?>>Delete old <?=$pset->product_plural?> when an admin logs in</option>
                  </select>

                </div>

                <div class="text-right">
                  <input type="submit" name="save" value="Save Settings" class="btn btn-primary">
                </div>
              </div>

            </div>



          </form>
        </div> <!-- /.col -->
      </div> <!-- /.row -->
