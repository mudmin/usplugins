<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  if(!empty($_POST['plugin_v4api'])){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    // Redirect::to('admin.php?err=I+agree!!!');
  }
  $token = Token::generate();
  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>API Patching Results</h1>
        If you see errors, you may want to manually copy the files or seek help on Discord.

        <?php

        if(!function_exists('tableFromQuery')) {
          function tableFromQuery($results,$opts = []){
            if(!isset($opts['class'])) {$opts['class'] = "table table-striped"; }
            if(!isset($opts['thead'])) {$opts['thead'] = ""; }
            if(!isset($opts['tbody'])) {$opts['tbody'] = ""; }
            if(!isset($opts['keys'])){
              foreach($results['0'] as $k=>$v){
                $opts['keys'][] = $k;
              }
            }

          ?>

            <table class="<?=$opts['class']?> paginate">
              <thead class="<?=$opts['thead']?>">
                <tr>
                  <?php foreach($opts['keys'] as $k){?>
                    <th><?php
                    if(isset($opts['ucFirst'])){
                      echo ucfirst($k);
                    }else{
                      echo $k;
                    }?>
                  </th>
                <?php } ?>
              </tr>
            </thead>
            <tbody class="<?=$opts['tbody']?>">
              <?php foreach($results as $r){?>
                <tr>
                  <?php foreach($r as $k=>$v){ ?>
                    <td><?=$v?></td>
                  <?php } ?>
                </tr>
              <?php } ?>
            </tbody>
          </table>

        <?php }
      }

      tableFromQuery($db->query("SELECT logdate,lognote FROM logs WHERE logtype = ? ORDER BY id DESC",['API Upgrade'])->results());
      ?>
    </div> <!-- /.col -->
  </div> <!-- /.row -->
