<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$show = Input::get('show');
$queryC = 0;
$displayError = '';
if (!empty($_POST)) {

  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }


  if (!empty($_POST['executeQuery'])) {
    $queryQ = $db->query($_POST['query']);
    $displayError = $db->errorString();
    $queryC = $queryQ->count();
    $queryR = $queryQ->results();
    $show = "";
  }

  if (!empty($_POST['delQuery'])) {
    $db->query("DELETE FROM plg_mysql_exp WHERE id = ?", [Input::get('q')]);
    Redirect::to('admin.php?view=plugins_config&plugin=mysql&show=saved');
  }

  if (!empty($_POST['exQuery'])) {

    $checkQ = $db->query("SELECT * FROM plg_mysql_exp WHERE id = ?", [Input::get('q')]);
    $checkC = $checkQ->count();
    if ($checkC > 0) {
      $check = $checkQ->first();
      $_POST['query'] = $check->query;
      $queryQ = $db->query($_POST['query']);
      $displayError = $db->errorString();
      $queryC = $queryQ->count();
      $queryR = $queryQ->results();
      $show = "";
    }
  }



  if (!empty($_POST['saveName'])) {
    $db->insert('plg_mysql_exp', ['qname' => Input::get('saveName'), 'query' => $_POST['query']]);
    usSuccess("Query saved");
    Redirect::to("?view=plugins_config&plugin=mysql");
  }
}

$saved = $db->query("SELECT * FROM plg_mysql_exp")->results();
if ($db->error() == true) {
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
if (!function_exists('tableFromQueryPlugin')) {
  function tableFromQueryPlugin($results, $opts = [])
  {

    if (!isset($opts['class'])) {
      $opts['class'] = "table table-striped paginate";
    }
    if (!isset($opts['thead'])) {
      $opts['thead'] = "";
    }
    if (!isset($opts['tbody'])) {
      $opts['tbody'] = "";
    }
    if (!isset($opts['keys']) && $results != []) {
      foreach ($results['0'] as $k => $v) {
        $opts['keys'][] = $k;
      }
    }
    if ($results != []) {
?>
      <table class="<?= $opts['class'] ?>" id="paginate">
        <thead class="<?= $opts['thead'] ?>">
          <tr>

            <?php foreach ($opts['keys'] as $k) { ?>
              <th><?php echo $k; ?></th>
            <?php } ?>
          </tr>
        </thead>
        <tbody class="<?= $opts['tbody'] ?>">
          <?php foreach ($results as $r) { ?>
            <tr>
              <?php foreach ($r as $k => $v) { ?>
                <td><?= $v ?></td>
              <?php } ?>
            </tr>
          <?php } ?>
        </tbody>
      </table>
<?php
    } else {
      echo "<h3>Table is Empty</h3>";
    }
  }
}


?>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-12 text-center">
      <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a><br>
      <p class="text-danger text-center"><b>This is a very powerful plugin. Use at your own risk.</b></p>
      <?php

      if ($show == "browser") { ?>
        <button type="button" onclick="window.location.href = 'admin.php?view=plugins_config&plugin=mysql';" name="button" class="btn btn-primary">Show Saved Queries</button>
      <?php }

      if ($show != "browser") { ?>
        <button type="button" onclick="window.location.href = 'admin.php?view=plugins_config&plugin=mysql&show=browser';" name="button" class="btn btn-warning ">Show Table Browser</button>
      <?php } ?>
    </div>
  </div>


  <?php
  if ($show == "") { ?>
    <div class="row">
      <div class="col-12 col-sm-6">
        <h3>Enter a Raw Query</h3>
        <form class="" action="" method="post">
          <?= tokenHere(); ?>
          <font color="black"><strong>Enter your query here...</strong></font><br>

          <textarea autofocus class="form-control autoresize" rows="4" name="query" id="query"><?php if (!empty($_POST['query'])) {
                                                                                                  echo $_POST['query'];
                                                                                                } ?></textarea>
          <input type="submit" name="executeQuery" value="Execute" class="btn btn-danger">
        </form>
      </div>


      <div class="col-12 col-sm-6">
        <h3>Save a Query for Later</h3>
        <form class="" action="" method="post" id="queryForm">
          <?= tokenHere(); ?>


          <label for="">Enter your query here</label>
          <textarea autofocus class="form-control autoresize" rows="4" name="query" id="query"><?php if (!empty($_POST['query'])) {
                                                                                                  echo $_POST['query'];
                                                                                                } ?></textarea>

          <div class="input-group mt-3">

            <input type="text" name="saveName" value="" class="form-control" required placeholder="Give your query a name">
            <input type="submit" name="saveQuery" value="Save" class="btn btn-success">
          </div>

        </form>
      </div>

    </div>
  <?
  }

  //display results

  if (!empty($_POST['query'])) {

  ?>

    <strong>Error Message:</strong>
    <?php if ($displayError != "ERROR #0: ") { ?>
      <font color="red">
      <?php } else { ?>
        <font color="black">
        <?php } ?>
        <?= $displayError; ?></font><br>
        <strong># of Results:</strong> <?= $queryC ?>
        <?php if ($queryC > 0) { ?>
          <div class="table-responsive">
          <?php
          tableFromQueryPlugin($queryR);
        } ?>
          </div>
        <?php } //end query
      if ($show != "browser") {
        ?>
          <h3>Saved Queries</h3>
          <table class="table table-striped" id="saved">
            <thead>
              <tr>
                <th>Name</th>
                <th>Query</th>
                <th>Delete</th>
                <th>Execute</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($saved as $s) { ?>
                <tr>
                  <td><?= $s->qname ?></td>
                  <td><?= substr($s->query, 0, 100); ?></td>
                  <td>
                    <form class="" action="" method="post">
                      <input type="hidden" name="q" value="<?= $s->id ?>">
                      <input type="hidden" name="csrf" value="<?= $token ?>" />
                      <input type="submit" name="delQuery" value="Delete" class="btn btn-info">
                    </form>
                  </td>
                  <td>
                    <form class="" action="" method="post">
                      <input type="hidden" name="q" value="<?= $s->id ?>">
                      <input type="hidden" name="csrf" value="<?= $token ?>" />
                      <input type="submit" name="exQuery" value="Execute" class="btn btn-danger">
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>

        <?php
      }
      if ($show == 'browser') { ?>
          <h3>Database Browser:</h3>
          <?php
          if (empty($_POST['query']) && Input::get('table') == '' && Input::get('tabledata') == '') {
            $tables = $db->query('SHOW TABLES')->results();
          ?>
            <table class='table table-striped paginate'>
              <thead>
                <th>Name</th>
              </thead>
              <tbody>
                <?php
                $t = 'Tables_in_'  . Config::get('mysql/db');
                foreach ($tables as $table) { ?>
                  <tr>
                    <td><a href="admin.php?view=plugins_config&plugin=mysql&show=browser&table=<?= $table->$t ?>"><?= $table->$t ?></a></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } elseif (Input::get('tabledata') != '') {  ?>
            <strong>Data for <?= Input::get('tabledata'); ?></strong><br><br>
            <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser"><- Back to tables</a></strong><br>
            <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser&table=<?= Input::get('tabledata'); ?>"><- Back to <?= Input::get('tabledata'); ?> structure</a></strong><br><br>
            <?php tableFromQueryPlugin($db->query('SELECT * FROM ' . Input::get('tabledata'))->results());  ?>

          <?php } elseif (Input::get('table') != '') {  ?>
            <strong>Structure for <?= Input::get('table'); ?></strong><br><br>
            <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser"><- Back to tables</a></strong><br>
            <strong><a href="admin.php?view=plugins_config&plugin=mysql&show=browser&tabledata=<?= Input::get('table'); ?>">
                <font color="blue">Browse Data in the <?= Input::get('table'); ?> table</font>
              </a></strong><br><br>
          <?php tableFromQueryPlugin($db->query('SHOW COLUMNS FROM ' . Input::get('table'))->results());
          } ?>
        <?php } //end browser
        ?>

        <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>


        <script type="text/javascript">
          $("#queryForm").submit(function(e) {
            e.preventDefault();

            var form = this;
            var query = $("#query").val();
            query = query.toLowerCase();
            if (query.indexOf("drop") >= 0 || query.indexOf("delete") >= 0) {
              if (confirm('This looks dangerous. Are you sure you want to do this?')) {
                form.submit();
              } else {
                // Do nothing!
              }
            } else {
              form.submit();
            }
          });
          $(document).ready(function() {
  $('.autoresize').on('input', function() {
    resizeTextbox(this);
  });

  function resizeTextbox(textbox) {
    $(textbox).css('height', 'auto');
    $(textbox).css('height', textbox.scrollHeight + 'px');
  }

  // Resize all textboxes initially
  $('.autoresize').each(function() {
    resizeTextbox(this);
  });
});
        </script>