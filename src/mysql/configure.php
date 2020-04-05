<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  $show = Input::get('show');
  $queryC = 0;
  $displayError = '';
  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    if(!empty($_POST['delQuery'])){
      $db->query("DELETE FROM plg_mysql_exp WHERE id = ?",[Input::get('q')]);
      Redirect::to('admin.php?view=plugins_config&plugin=mysql&show=saved');
    }

    if(!empty($_POST['exQuery'])){
      $checkQ = $db->query("SELECT * FROM plg_mysql_exp WHERE id = ?",[Input::get('q')]);
      $checkC = $checkQ->count();
      if($checkC > 0){
        $check = $checkQ->first();
        $_POST['query'] = $check->query;
        $show = "";
      }
    }

    if(!empty($_POST['query'])){
      $queryQ = $db->query($_POST['query']);
      $displayError = $db->errorString();
      $queryC = $queryQ->count();
      $queryR = $queryQ->results();
      if(Input::get('save') == 1){
        $db->insert('plg_mysql_exp',['qname'=>Input::get('saveName'),'query'=>$_POST['query']]);
      }
    }
  }

  $saved = $db->query("SELECT * FROM plg_mysql_exp")->results();
  if($db->error() == true){
    $db->query("CREATE TABLE `plg_mysql_exp` (
      id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
      `qname` varchar(255) NOT NULL,
      `query` text
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

    Redirect::to('admin.php?view=plugins_config&plugin=mysql&err=Plugin+updated');
  }

  $token = Token::generate();
  ?>
  <?php
  if(!function_exists('tableFromQueryPlugin')) {
    function tableFromQueryPlugin($results,$opts = []){

      if(!isset($opts['class'])) {$opts['class'] = "table table-striped paginate"; }
      if(!isset($opts['thead'])) {$opts['thead'] = ""; }
      if(!isset($opts['tbody'])) {$opts['tbody'] = ""; }
      if(!isset($opts['keys']) && $results != []){
        foreach($results['0'] as $k=>$v){
          $opts['keys'][] = $k;
        }
      }
      if($results != []){
        ?>
        <table class="<?=$opts['class']?>" id="paginate">
          <thead class="<?=$opts['thead']?>">
            <tr>

              <?php foreach($opts['keys'] as $k){?>
                <th><?php echo $k;?></th>
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
        <?php
      }else{
        echo "<h3>Table is Empty</h3>";
      }
    }
  }

  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a><br>

        You may want to consider <a href="admin.php?view=backup"><font color="red"><strong>backing up your database</strong></font></a> if you are doing something dangerous.<br>
        <?php if($show != ""){?>
          <button type="button" onclick="window.location.href = 'admin.php?view=plugins_config&plugin=mysql';" name="button" class="btn btn-info">Enter Raw Query</button>
        <?php } ?>
        <?php if($show != "saved"){?>
          <button type="button" onclick="window.location.href = 'admin.php?view=plugins_config&plugin=mysql&show=saved';" name="button" class="btn btn-primary">Show Saved Queries</button>
        <?php } ?>
        <?php if($show != "browser"){?>
          <button type="button" onclick="window.location.href = 'admin.php?view=plugins_config&plugin=mysql&show=browser';" name="button" class="btn btn-warning">Show Table Browser</button>
        <?php } ?>

        <?php
        if($show == ""){?>
          <form class="" action="" method="post" id="queryForm">
            <input type="hidden" name="csrf" value="<?=$token?>" /><br>
            <font color="black"><strong>Enter your query here...</strong></font><br>
            <input type="checkbox" name="save" value="1">Save this query for future use and name it
            <input type="text" name="saveName" value="" placeholder="query name">
            <textarea autofocus class = "form-control" rows="4" name="query" id="query"><?php if(!empty($_POST['query'])){echo $_POST['query'];}?></textarea>
            <input type="submit" name="plugin_mysql" value="Execute" class="btn btn-danger">
          </form>
          <?
        }

        //display results
        if(!empty($_POST['query'])){

          ?>
          <strong>Error Message:</strong>
          <?php if($displayError != "ERROR #0: "){?>
            <font color = "red">
            <?php } else{ ?>
              <font color = "black">
              <?php } ?>
              <?=$displayError;?></font><br>
              <strong># of Results:</strong> <?=$queryC?>
              <?php if($queryC > 0){
                tableFromQueryPlugin($queryR);
              } ?>

            <?php } //end query
            if($show == 'saved' && $show != ''){
              ?>
              <h3>Saved Queries</h3>
              <table class="table table-striped" id="saved">
                <thead>
                  <tr>
                    <th>Name</th><th>Query</th><th>Delete</th><th>Execute</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($saved as $s){?>
                    <tr>
                      <td><?=$s->qname?></td>
                      <td><?=substr($s->query,0,100);?></td>
                      <td>
                        <form class="" action="" method="post">
                          <input type="hidden" name="q" value="<?=$s->id?>">
                          <input type="hidden" name="csrf" value="<?=$token?>" />
                          <input type="submit" name="delQuery" value="Delete" class="btn btn-info">
                        </form>
                      </td>
                      <td>
                        <form class="" action="" method="post">
                          <input type="hidden" name="q" value="<?=$s->id?>">
                          <input type="hidden" name="csrf" value="<?=$token?>" />
                          <input type="submit" name="exQuery" value="Execute" class="btn btn-danger">
                        </form>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

            <?php }//end saved table
            if($show == 'browser'){?>
              <h3>Database Broswer:</h3>
              <?php
              if(empty($_POST['query']) && Input::get('table') == '' && Input::get('tabledata') == ''){
                $tables = $db->query('SHOW TABLES')->results();
                ?>
                <table  id="paginate" class='table table-striped paginate'>
                  <thead>
                    <th>Name</th>
                  </thead>
                  <tbody>
                    <?php
                    $t = 'Tables_in_'	. Config::get('mysql/db');
                    foreach($tables as $table){?>
                      <tr>
                        <td><a href="admin.php?view=plugins_config&plugin=mysql&show=browser&table=<?=$table->$t?>"><?=$table->$t?></a></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              <?php }elseif(Input::get('tabledata') != ''){  ?>
                <strong>Data for <?=Input::get('tabledata');?></strong><br><br>
                <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser"><- Back to tables</a></strong><br>
                <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser&table=<?=Input::get('tabledata');?>"><- Back to <?=Input::get('tabledata');?> structure</a></strong><br><br>
                <?php tableFromQueryPlugin($db->query('SELECT * FROM '. Input::get('tabledata'))->results());  ?>

              <?php }elseif(Input::get('table') != ''){  ?>
                <strong>Structure for <?=Input::get('table');?></strong><br><br>
                <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser"><- Back to tables</a></strong><br>
                <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser&tabledata=<?=Input::get('table');?>"><font color="blue">Browse Data in the <?=Input::get('table');?> table</font></a></strong><br><br>
                <?php tableFromQueryPlugin($db->query('SHOW COLUMNS FROM '. Input::get('table'))->results()); } ?>
              <?php } //end browser
              ?>


            </div> <!-- /.col -->
          </div> <!-- /.row -->
          <script type="text/javascript">
          $("#queryForm").submit(function(e){
            e.preventDefault();

            var form = this;
            var query = $("#query").val();
            query = query.toLowerCase();
            if (query.indexOf("drop") >= 0 || query.indexOf("delete") >= 0){
              if (confirm('This looks dangerous. Are you sure you want to do this?')) {
                form.submit();
              } else {
                // Do nothing!
              }
            }else{
              form.submit();
            }
          });
        </script>
        <link href="<?=$us_url_root?>users/js/pagination/datatables.min.css" rel="stylesheet">
        <script type="text/javascript" src="js/pagination/datatables.min.js"></script>
        <script>

        $(document).ready(function () {
          $('#paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
          $('#saved').DataTable({"pageLength": 10,"stateSave": true,"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, 100, 500]], "aaSorting": []});
        });

      </script>
