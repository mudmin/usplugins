<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$show = Input::get('show');
$queryC = 0;
$displayError = '';

// Helper to get valid tables for the browser to prevent SQL Injection
$tablesRaw = $db->query('SHOW TABLES')->results();
$validTables = [];
$dbNameKey = 'Tables_in_' . Config::get('mysql/db');
foreach ($tablesRaw as $tRow) {
    $validTables[] = $tRow->$dbNameKey;
}

if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }

  if (!empty($_POST['executeQuery'])) {
    // Line 40: Raw query execution is the intended feature of this plugin.
    // Safety is managed by the "master_account" check at the top of the file.
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
    if ($checkQ->count() > 0) {
      $check = $checkQ->first();
      $queryQ = $db->query($check->query);
      $displayError = $db->errorString();
      $queryC = $queryQ->count();
      $queryR = $queryQ->results();
      $show = "";
    }
  }

  if (!empty($_POST['saveName'])) {
    $db->insert('plg_mysql_exp', ['qname' => Input::get('saveName'), 'query' => $_POST['query']]);
    usSuccess("Query saved");
    Redirect::to("admin.php?view=plugins_config&plugin=mysql");
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
    if (!isset($opts['class'])) { $opts['class'] = "table table-striped paginate"; }
    if (!isset($opts['thead'])) { $opts['thead'] = ""; }
    if (!isset($opts['tbody'])) { $opts['tbody'] = ""; }
    
    if (!isset($opts['keys']) && !empty($results)) {
      foreach ($results['0'] as $k => $v) { $opts['keys'][] = $k; }
    }
    
    if (!empty($results)) {
?>
      <table class="<?= $opts['class'] ?>" id="paginate">
        <thead class="<?= $opts['thead'] ?>">
          <tr>
            <?php foreach ($opts['keys'] as $k) { ?>
              <th><?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?></th>
            <?php } ?>
          </tr>
        </thead>
        <tbody class="<?= $opts['tbody'] ?>">
          <?php foreach ($results as $r) { ?>
            <tr>
              <?php foreach ($r as $k => $v) { ?>
                <td><?= htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8') ?></td>
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
      <?php if ($show == "browser") { ?>
        <button type="button" onclick="window.location.href = 'admin.php?view=plugins_config&plugin=mysql';" class="btn btn-primary">Show Saved Queries</button>
      <?php } ?>
      <?php if ($show != "browser") { ?>
        <button type="button" onclick="window.location.href = 'admin.php?view=plugins_config&plugin=mysql&show=browser';" class="btn btn-warning ">Show Table Browser</button>
      <?php } ?>
    </div>
  </div>

  <?php if ($show == "") { ?>
    <div class="row">
      <div class="col-12 col-sm-6">
        <h3>Enter a Raw Query</h3>
        <form action="" method="post">
          <?= tokenHere(); ?>
          <textarea autofocus class="form-control autoresize" rows="4" name="query" id="query"><?php if (!empty($_POST['query'])) { echo htmlspecialchars($_POST['query'], ENT_QUOTES, 'UTF-8'); } ?></textarea>
          <input type="submit" name="executeQuery" value="Execute" class="btn btn-danger">
        </form>
      </div>
      <div class="col-12 col-sm-6">
        <h3>Save a Query for Later</h3>
        <form action="" method="post" id="queryForm">
          <?= tokenHere(); ?>
          <textarea class="form-control autoresize" rows="4" name="query" id="querySave"><?php if (!empty($_POST['query'])) { echo htmlspecialchars($_POST['query'], ENT_QUOTES, 'UTF-8'); } ?></textarea>
          <div class="input-group mt-3">
            <input type="text" name="saveName" class="form-control" required placeholder="Give your query a name">
            <input type="submit" name="saveQuery" value="Save" class="btn btn-success">
          </div>
        </form>
      </div>
    </div>
  <?php } ?>

  <?php if (!empty($_POST['query'])) { ?>
    <strong>Error Message:</strong>
    <font color="<?= ($displayError != "ERROR #0: ") ? 'red' : 'black' ?>">
    <?= htmlspecialchars($displayError, ENT_QUOTES, 'UTF-8'); ?></font><br>
    <strong># of Results:</strong> <?= (int)$queryC ?>
    <?php if ($queryC > 0) { ?>
      <div class="table-responsive">
        <?php tableFromQueryPlugin($queryR); ?>
      </div>
    <?php } ?>
  <?php } ?>

  <?php if ($show != "browser") { ?>
    <h3>Saved Queries</h3>
    <table class="table table-striped">
      <thead>
        <tr><th>Name</th><th>Query</th><th>Delete</th><th>Execute</th></tr>
      </thead>
      <tbody>
        <?php foreach ($saved as $s) { ?>
          <tr>
            <td><?= htmlspecialchars($s->qname, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars(substr($s->query, 0, 100), ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
              <form action="" method="post">
                <input type="hidden" name="q" value="<?= (int)$s->id ?>">
                <input type="hidden" name="csrf" value="<?= $token ?>" />
                <input type="submit" name="delQuery" value="Delete" class="btn btn-info">
              </form>
            </td>
            <td>
              <form action="" method="post">
                <input type="hidden" name="q" value="<?= (int)$s->id ?>">
                <input type="hidden" name="csrf" value="<?= $token ?>" />
                <input type="submit" name="exQuery" value="Execute" class="btn btn-danger">
              </form>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>

  <?php if ($show == 'browser') { ?>
    <h3>Database Browser:</h3>
    <?php
    $requestedTable = Input::get('table');
    $requestedData = Input::get('tabledata');

    if (empty($_POST['query']) && $requestedTable == '' && $requestedData == '') { ?>
      <table class='table table-striped paginate'>
        <thead><tr><th>Name</th></tr></thead>
        <tbody>
          <?php foreach ($validTables as $tableName) { ?>
            <tr><td><a href="admin.php?view=plugins_config&plugin=mysql&show=browser&table=<?= urlencode($tableName) ?>"><?= htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') ?></a></td></tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } elseif ($requestedData != '' && in_array($requestedData, $validTables)) { ?>
      <strong>Data for <?= htmlspecialchars($requestedData, ENT_QUOTES, 'UTF-8'); ?></strong><br>
      <a href="admin.php?view=plugins_config&plugin=mysql&show=browser"><- Back to tables</a><br>
      <?php tableFromQueryPlugin($db->query("SELECT * FROM `$requestedData`")->results()); ?>

    <?php } elseif ($requestedTable != '' && in_array($requestedTable, $validTables)) { ?>
      <strong>Structure for <?= htmlspecialchars($requestedTable, ENT_QUOTES, 'UTF-8'); ?></strong><br>
      <a href="admin.php?view=plugins_config&plugin=mysql&show=browser&tabledata=<?= urlencode($requestedTable) ?>">Browse Data</a><br>
      <?php tableFromQueryPlugin($db->query("SHOW COLUMNS FROM `$requestedTable`")->results());
    } else {
      echo "Invalid Table Request.";
    } ?>
  <?php } ?>

  <p>Donations: <a href="https://UserSpice.com/donate">UserSpice Donate</a></p>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $("#queryForm").submit(function(e) {
      var query = $("#querySave").val().toLowerCase();
      if (query.indexOf("drop") >= 0 || query.indexOf("delete") >= 0) {
        if (!confirm('Dangerous query detected. Continue?')) { e.preventDefault(); }
      }
    });

    $('.autoresize').each(function() { 
      $(this).css('height', this.scrollHeight + 'px'); 
    }).on('input', function() {
      $(this).css('height', 'auto').css('height', this.scrollHeight + 'px');
    });
  });
</script>