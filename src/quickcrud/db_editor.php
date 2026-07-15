<?php
/**
 * QuickCRUD Database Editor (includable)
 *
 * Renders the table picker + editable CRUD table for the chosen table.
 * Include it on any page that has already loaded users/init.php:
 *
 *   include $abs_us_root.$us_url_root.'usersc/plugins/quickcrud/db_editor.php';
 *
 * Only renders for logged-in users with permission level 2 (Admin).
 * The AJAX parser (parsers/parser.php) enforces the same permission
 * server-side, so including this on a public page outputs nothing for
 * other visitors.
 */
if (!isset($user) || !function_exists('hasPerm')) { return; } // must be included after users/init.php
if (!$user->isLoggedIn() || !hasPerm([2], $user->data()->id)) { return; }

if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
$db = DB::getInstance();

$tables = $db->query('SHOW TABLES')->results();
$t = "";
$check = "Tablesin" . Config::get('mysql/db');
if (isset($tables[0]->$check)) {
    $t = $check;
}
$check = "Tables_in_" . Config::get('mysql/db');
if (isset($tables[0]->$check)) {
    $t = $check;
}

if ($t == "") { ?>
  <div class="alert alert-warning">
    Your database schema will work with the Quick CRUD plugin, however, it will not work with the automated database editor.
    If you'd like to help us, please consider filling out a ticket at <a href="https://bugs.userspice.com">https://bugs.userspice.com</a>
    and passing along this diagnostic information:<br>
    <?php dump($tables[0]); ?>
    Thanks so much for your help.
  </div>
  <?php
  return;
}

$sanitizedTableName = (!empty($_POST["seek"])) ? $_POST["seek"] : "Empty";
// Sanitize table name: basename for path traversal, regex for SQL injection
$sanitizedTableName = basename($sanitizedTableName);
if (!preg_match('/^[a-zA-Z0-9_]+$/', $sanitizedTableName)) {
    $sanitizedTableName = "Empty";
}
// Verify table exists in database whitelist
if ($sanitizedTableName !== "Empty") {
    $tableExists = false;
    foreach ($tables as $table) {
        if ($table->$t === $sanitizedTableName) {
            $tableExists = true;
            break;
        }
    }
    if (!$tableExists) {
        $sanitizedTableName = "Empty";
    }
}
?>
<h3>Database Editor</h3>
<p>Please note, this database editor is very powerful. It is only recommended that you edit things if you know what you're doing.</p>
<form action="" name="quickcrud_tables" method="post">
  <div class="row mb-3">
    <div class="col-12 col-md-5 col-lg-4">
      <select id="tables" name="seek" class="form-select">
        <option value="">Choose Table</option>
        <?php foreach ($tables as $table) { ?>
          <option value="<?= $table->$t ?>" <?= ($table->$t === $sanitizedTableName) ? 'selected' : '' ?>><?= $table->$t ?></option>
        <?php } ?>
      </select>
    </div>
  </div>
</form>
<!-- QuickCRUD: select2 4.0.13 via cdnjs -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $(document).ready(function () {
    $('#tables').select2({ width: '100%', placeholder: 'Choose Table' });
    // select2 fires a jQuery change event, so submit-on-change must be bound with jQuery
    $('#tables').on('change', function () {
      if (this.value !== '') { this.form.submit(); }
    });
  });
</script>
<?php
if ($sanitizedTableName !== "Empty") {
    ?>
    <h4>Currently Viewing '<?= htmlspecialchars(ucfirst($sanitizedTableName), ENT_QUOTES, 'UTF-8') ?>' table</h4>
    <?php
    $query = $db->query("SELECT * FROM $sanitizedTableName")->results();
    quickCrud($query, $sanitizedTableName);
}
