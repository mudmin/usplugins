<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
}
$bases = $db->query("SELECT * FROM plg_db_explainer_databases ORDER BY db_name")->results();
$used_bases = [];
$db_id = Input::get('db_id');
$table_id = Input::get('table_id');


if (!empty($_POST['saveKeyChanges'])) {
  $row = Input::get('row');
  $keyTable = Input::get('keyTable');
  $keyColumn = Input::get('keyColumn');
  if (is_numeric($keyTable) && is_numeric($keyColumn)) {
    $db->update("plg_db_explainer_columns", $row, ["related_to_table" => $keyTable, "related_to_column" => $keyColumn]);
    usSuccess("Key updated");
    Redirect::to($us_url_root . 'users/admin.php?view=plugins_config&plugin=db_explainer&db_id=' . $db_id . '&table_id=' . $table_id);
  } else {
    usError("You must provide both a table and column to make an association.");
    Redirect::to($us_url_root . 'users/admin.php?view=plugins_config&plugin=db_explainer&db_id=' . $db_id . '&table_id=' . $table_id);
  }
}

if (!empty($_POST['clearAssociation'])) {
  $row = Input::get('row');


  $db->update("plg_db_explainer_columns", $row, ["related_to_table" => null, "related_to_column" => null]);
  usSuccess("Related table/column cleared");
  Redirect::to($us_url_root . 'users/admin.php?view=plugins_config&plugin=db_explainer&db_id=' . $db_id . '&table_id=' . $table_id);
}

if (!empty($_POST['dbname'])) {

  $dbname = Input::get('dbname');
  $db2 = DB::getDB($dbname);
  import_raw_database_to_explainer($db2, $dbname);
  usSuccess("Database imported/updated");
  Redirect::to($us_url_root . 'users/admin.php?view=plugins_config&plugin=db_explainer');
}
if (is_numeric($db_id)) {
  $databaseQ = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
  $databaseC = $databaseQ->count();
  if ($databaseC > 0) {
    $database = $databaseQ->first();
  } else {
    usError("Database not found");
    Redirect::to($us_url_root . 'users/admin.php?view=plugins_config&plugin=db_explainer');
  }
}
// if (is_numeric($db_id) && !is_numeric($table_id)) {
//   $where = "WHERE c.db_id = ?";
//   $binds = [$db_id];
// } else if (is_numeric($db_id)) {
//   $where = "WHERE c.db_id = ? AND c.table_id = ?";
//   $binds = [$db_id, $table_id];
// }

if (is_numeric($db_id)) {
  $where = "WHERE c.db_id = ?";
  $binds = [$db_id];
  $cols = $db->query("SELECT 
  t.table_name,
  t2.table_name as related_to_table_name,
  c2.column_name as related_to_column_name,
  c.*          
  FROM plg_db_explainer_columns c 
  LEFT OUTER JOIN plg_db_explainer_tables t ON c.table_id = t.id
  LEFT OUTER JOIN plg_db_explainer_tables t2 ON c.related_to_table = t2.id
  LEFT OUTER JOIN plg_db_explainer_columns c2 ON c.related_to_column = c2.id
  $where
  ORDER BY t.table_name", $binds)->results();
}

if (!empty($_POST['save_settings'])) {
  $db_id = Input::get('db_id');
  $diagram_is_public = Input::get('diagram_is_public');
  $required_perms = "";
  if (!empty(Input::get('required_perms'))) {
    $required_perms = Input::get('required_perms');
    $required_perms = implode(",", Input::get('required_perms'));
  }

  $required_tags = "";
  if (!empty(Input::get('required_tags'))) {
    $required_tags = Input::get('required_tags');
    $required_tags = implode(",", Input::get('required_tags'));
  }

  $databse_description = Input::get('databse_description');
  $db->update("plg_db_explainer_databases", $db_id, [
    'diagram_is_public' => $diagram_is_public,
    'required_perms' => $required_perms,
    'required_tags' => $required_tags,
    'db_description' => $databse_description
  ]);

  usSuccess("Settings saved");
  Redirect::to($us_url_root . 'users/admin.php?view=plugins_config&plugin=db_explainer&db_id=' . $db_id);
}

$all_tables = [];
$all_tables_cols = [];

?>

<div class="content mt-3">
  <div id="messages" class="sufee-alert alert with-close alert-primary alert-dismissible fade show d-none">
    <span id="message"></span>
    <button type="button" class="close btn-close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <div class="row">
    <div class="col-12">
      <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>DB Explainer Plugin
        <a href="<?= $us_url_root ?>usersc/plugins/db_explainer/documentation.php" class="btn btn-outline-primary btn-sm">Documentation</a>
      </h1>
      <div class="row">
        <?php if ($db_id == "") { ?>
          <div class="col-12 col-sm-3">
            <h4>Your Imported Databases</h4>
            <ul>
              <?php foreach ($bases as $base) {
                $used_bases[] = $base->db_name;
              ?>
                <li><a href="<?= $us_url_root ?>users/admin.php?view=plugins_config&plugin=db_explainer&db_id=<?= $base->id ?>"><?= $base->db_name ?></a></li>
              <?php }

              if (!in_array($config['mysql']['db'], $used_bases)) {
                $value = $config['mysql']['db'];
              } else {
                $value = "";
              }

              ?>
              <hr>
              <h4>Import/Update a Database</h4>
              <p>At this time, in order to import a database, it must be available to the current db user of your UserSpice install. If there is interest, we will make a connector to go out and grab other databases. If you specify a name that has already been imported, it will be updated, however you will not lose your descriptions and assocations. Note that columns deleted since your last import will not automatically go away at this time. There will be a purge feature to clear those.</p>
              <form action="" method="post">
                <?= tokenHere(); ?>
                <div class="input-group">
                  <input type="text" name="dbname" value="<?= $value ?>" class="form-control" required>
                  <div class="input-group-append">
                    <input type="submit" name="import" value="Import" placeholder="DB Name" class="btn btn-primary ">

                  </div>
                  <?php if (!empty($value)) { ?><br>
                    <small>You can import a different database by changing the name above.</small>
                  <?php } ?>

                </div>
              </form>
              <p class="mt-3">
                If appreciate this work and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
              </p>
          </div>
        <?php } else {  ?>
          <div class="col-12">

            <div class="row">
              <div class="col-12 col-md-4">
                <div class="card">
                  <div class="card-header">
                    <h4>Export for AI and LLMs</h4>
                  </div>
                  <div class="card-body">
                    <a target="_blank" class="btn btn-sm btn-outline-primary" href="<?= $us_url_root ?>usersc/plugins/db_explainer/download_columns.php?export_type=db&db_id=<?= $db_id ?>">Export Column Definitions</a>

                    <a target="_blank" class="btn btn-sm btn-outline-primary" href="<?= $us_url_root ?>usersc/plugins/db_explainer/download_tables.php?export_type=db&db_id=<?= $db_id ?>">Export Table Definitions</a>
                  </div>
                </div>

              </div>
              <div class="col-12 col-md-4">
                <div class="card">
                  <div class="card-header">
                    <h4>Backup Your Explainer and Import Backups</h4>
                  </div>
                  <div class="card-body">

                    <a target="_blank" class="btn btn-sm btn-outline-primary" href="<?= $us_url_root ?>usersc/plugins/db_explainer/export_explainer.php?db_id=<?= $db_id ?>">Export Full Explainer</a>

                    <a target="_blank" class="btn btn-sm btn-outline-primary" href="<?= $us_url_root ?>usersc/plugins/db_explainer/import_explainer.php?db_id=<?= $db_id ?>">Import Explainer</a>
                  </div>
                </div>

              </div>
              <?php if (is_numeric($table_id)) { ?>
                <div class="col-12 col-md-4">
                  <div class="card">
                    <div class="card-header">
                      <h4>Single Table</h4>
                    </div>
                    <div class="card-body">

                      <a target="_blank" class="btn btn-sm btn-outline-primary" href="<?= $us_url_root ?>usersc/plugins/db_explainer/download.php?export_type=table&table_id=<?= $table_id ?>">Export Table Schema</a>

                      <a target="_blank" href="<?= $us_url_root ?>users/admin.php?view=plugins_config&plugin=db_explainer&db_id=<?= $db_id ?>" class="btn btn-sm btn-secondary">View Full Database</a>
                    </div>
                  </div>

                </div>
              <?php } ?>

            </div>
            <div class="row mt-3">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <h4>Database: <?= $database->db_name ?>
                          <?php if ($database->db_description != "") {
                            echo " - " . $database->db_description;
                          } ?>
                          <a target="" class="btn btn-sm btn-primary" href="<?= $us_url_root ?>users/admin.php?view=plugins_config&plugin=db_explainer" class="btn btn-sm btn-primary ms-3">Home</a>
                          <a target="_blank" class="btn btn-sm btn-outline-primary" href="<?= $us_url_root ?>usersc/plugins/db_explainer/diagram.php?db_id=<?= $db_id ?>">Visualize Database</a>
                        </h4>
                      </div>
                      <div class="col-12 col-md-6 text-end text-right pull-right">

                        <a href="#table_defs" class="btn btn-outline-primary btn-sm">Edit Table Descriptions</a>
                        <a href="#db_settings" class="btn btn-outline-primary btn-sm">Important Settings</a>
                      </div>
                    </div>

                  </div>
                  <div class="card-body">

                    <table class="table table-striped table-hover paginate">
                      <thead>
                        <tr>
                          <th>Table Name</th>
                          <th>Column Name</th>
                          <th>Column Type</th>
                          <th>Column Length</th>
                          <th>Description</th>
                          <th>Related Table</th>
                          <th>Related Column</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($cols as $col) {
                          //in order to try to avoid duplicate queries, we're going to pick up the table names and columns as we go if we are looking at the full database. 

                          if (!in_array($col->table_name, $all_tables)) {
                            $all_tables[$col->table_id] = $col->table_name;
                            $all_tables_cols[$col->table_name] = [];
                          }

                          //we need every column and table regardless for selecting keys  
                          $all_tables_cols[$col->table_name][$col->id] = $col->column_name;
                          if (is_numeric($table_id) && $col->table_id != $table_id) {
                            continue;
                          }

                        ?>
                          <tr>
                            <td>
                              <?php if (is_numeric($table_id)) {
                                echo $col->table_name;
                              } else {
                              ?>
                                <a href="<?= $us_url_root ?>users/admin.php?view=plugins_config&plugin=db_explainer&db_id=<?= $db_id ?>&table_id=<?= $col->table_id ?>"><?= $col->table_name ?></a>
                              <?php } ?>

                            </td>
                            <td><?= $col->column_name ?></td>
                            <td><?= $col->column_type ?></td>
                            <td><?= $col->column_length ?></td>
                            <td>
                              <div class="hideSpan"><?= $col->column_description ?></div>
                              <input type="text" class="form-control descrip" data-id="<?= $col->id ?>" data-col="column_description" value="<?= $col->column_description ?>">
                            </td>
                            <td><?= $col->related_to_table_name ?></td>
                            <td><?= $col->related_to_column_name ?></td>
                            <td>
                              <div class="hideSpan"><?= $col->id ?></div>
                              <button class="btn btn-sm btn-success changeKey" data-table-name="<?= $col->table_name ?>" data-column-name="<?= $col->column_name ?>" data-column-type="<?= $col->column_type ?>" data-column-length="<?= $col->column_length ?>" data-column-description="<?= $col->column_description ?>" data-id="<?= $col->id ?>" data-key-table="<?= $col->related_to_table ?>" data-key-table-name="<?= $col->related_to_table_name ?>" data-key-column="<?= $col->related_to_column ?>" data-key-column-name="<?= $col->related_to_column_name ?>">Edit Key
                              </button>
                            </td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                <?php } ?>
                </div>
              </div>
            </div>
          </div>
      </div>
    </div>
  </div>
  <!-- Modal for editing keys -->
  <div class="modal fade" id="editKeyModal" tabindex="-1" aria-labelledby="editKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="editKeyForm" method="post">
          <div class="modal-header">
            <h5 class="modal-title" id="editKeyModalLabel">Edit Key</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <?= tokenHere(); ?>
            <div class="mb-3">
              <label for="keyTable" class="form-label">Related Table</label>
              <select class="form-select" id="keyTable" name="keyTable">
                <!-- Options will be dynamically populated using JavaScript -->
              </select>
            </div>
            <div class="mb-3">
              <label for="keyColumn" class="form-label">Related Column</label>
              <select class="form-select" id="keyColumn" name="keyColumn">
                <!-- Options will be dynamically populated based on the selected table using JavaScript -->
              </select>
            </div>

          </div>
          <input type="hidden" name="view" value="plugins_config">
          <input type="hidden" name="plugin" value="db_explainer">
          <input type="hidden" name="db_id" value="<?= $db_id ?>">
          <input type="hidden" name="table_id" value="<?= $table_id ?>">
          <input type="hidden" name="row" id="modalRow" value="">
          <div class="modal-footer">
            <input type="submit" class="btn btn-danger" name="clearAssociation" id="clearAssociation" value="Clear Associations">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <input type="submit" class="btn btn-primary" name="saveKeyChanges" id="saveKeyChanges" value="Save Changes">
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php
  if (isset($db_id) && $db_id > 0) {
    $tables = $db->query("SELECT * FROM plg_db_explainer_tables WHERE db_id = ? ORDER BY table_name", [$db_id])->results();

  ?>
    <div class="row mt-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header" id="table_defs">

            <h3>Table Descriptions</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-12">
                <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th width="25%">Table Name</th>
                      <th>Table Description</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tables as $table) { ?>
                      <tr>
                        <td><?= $table->table_name ?></td>
                        <td>
                          <input type="text" class="form-control tableDescrip" data-id="<?= $table->id ?>" data-col="table_description" value="<?= $table->table_description ?>">
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>



    <div class="row mt-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header" id="db_settings">

            <h3>Change Database Settings</h3>
          </div>
          <div class="card-body">
            <form action="" method="post">
              <input type="submit" class="btn btn-primary" value="Save DB Settings" name="save_settings">
              <?= tokenHere(); ?>
              <input type="hidden" name="db_id" value="<?= $db_id ?>">
              <input type="hidden" name="view" value="plugins_config">
              <input type="hidden" name="plugin" value="db_explainer">
              <div class="row">
                <div class="col-12 col-sm-6 col-lg-3">
                  <label for="database_name">Database Description</label><br>
                  <input type="text" name="databse_description" value="<?= $database->db_description ?>" class="form-control">
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                  <label for="database_name">Diagram is Public</label><br>
                  <select name="diagram_is_public" class="form-control">
                    <option value="0" <?php if ($database->diagram_is_public == 0) {
                                        echo "selected";
                                      } ?>>No</option>
                    <option value="1" <?php if ($database->diagram_is_public == 1) {
                                        echo "selected";
                                      } ?>>Yes</option>
                  </select>
                </div>

                <?php
                if (pluginActive("usertags", true)) {
                  $tags = $db->query("SELECT * FROM plg_tags")->results();
                  $used_tags = explode(",", $database->required_tags);
                ?>
                  <div class="col-12 col-sm-6 col-lg-3">
                    <label for="required_perms">If private, permissions allowed to view diagram</label><br>
                    <?php
                    foreach ($tags as $t) { ?>

                      <input type="checkbox" name="required_tags[]" value="<?= $t->id ?>" <?php if (in_array($t->id, $used_tags)) {
                                                                                            echo "checked";
                                                                                          } ?> class="form-check-input" id="perm_<?= $t->id ?>"><span class="ms-2"><?= $t->tag ?></span><br>

                    <?php } ?>
                  </div>
                <?php  } ?>


                <div class="col-12 col-sm-6 col-lg-3">
                  <label for="required_perms">If private, permissions allowed to view diagram</label>

                  <br>
                  <?php
                  $permissions = fetchAllPermissions();
                  //parse comma separted list of permissions
                  $used = explode(",", $database->required_perms);

                  foreach ($permissions as $p) { ?>
                    <input type="checkbox" name="required_perms[]" value="<?= $p->id ?>" <?php if (in_array($p->id, $used)) {
                                                                                            echo "checked";
                                                                                          } ?> class="form-check-input" id="perm_<?= $p->id ?>"><span class="ms-2"><?= $p->name ?></span><br>
                  <?php  } ?>

                </div>




              </div>

            </form>
          </div>

        </div>
      </div>
    </div>

    <p>
      If appreciate this work and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
    </p>

  <?php }
  // dump($all_tables);
  // dump($all_tables_cols);
  ?>
  <style>
    .hideSpan {
      display: none;
    }
  </style>

  <script>
    var allTables = <?php echo json_encode($all_tables); ?>;
    var allTablesCols = <?php echo json_encode($all_tables_cols); ?>;

    $(document).ready(function() {

      // Event listener for the tableDescrip input change
      $('.tableDescrip').change(function() {
        var id = $(this).data('id');
        var col = $(this).data('col');
        var val = $(this).val();

        // Construct the URL using a PHP variable
        var url = '<?= $us_url_root ?>usersc/plugins/db_explainer/parsers/update_db.php';

        $.ajax({
          url: url,
          type: 'post',
          data: {
            id: id,
            col: col,
            val: val
          },
          success: function(response) {
            console.log(response);

            // Parse JSON response
            var responseData = JSON.parse(response);

            // Check if the response contains 'success' property
            if (responseData.success) {
              // Change the input background color to green for a second
              $('.tableDescrip[data-id="' + id + '"]').css('background-color', '#5cb85c');
              setTimeout(function() {
                $('.tableDescrip[data-id="' + id + '"]').css('background-color', '#fff');
              }, 1000);
            } else {
              // Handle the case where the response is not successful (pink background?)
              // Add your logic here if needed
            }
          },
          error: function(xhr, textStatus, errorThrown) {
            // Handle AJAX error if necessary
            console.error('Error:', errorThrown);
          }
        });
      });


      $('.descrip').change(function() {
        var id = $(this).data('id');
        var col = $(this).data('col');
        var val = $(this).val();

        // Construct the URL using a PHP variable
        var url = '<?= $us_url_root ?>usersc/plugins/db_explainer/parsers/update_db.php';

        $.ajax({
          url: url,
          type: 'post',
          data: {
            id: id,
            col: col,
            val: val
          },
          success: function(response) {
            console.log(response);

            // Parse JSON response
            var responseData = JSON.parse(response);

            // Check if the response contains 'success' property
            if (responseData.success) {
              // Change the input background color to green for a second
              $('.descrip[data-id="' + id + '"]').css('background-color', '#5cb85c');
              setTimeout(function() {
                $('.descrip[data-id="' + id + '"]').css('background-color', '#fff');
              }, 1000);
            } else {
              // Handle the case where the response is not successful (pink background?)
              // Add your logic here if needed
            }
          },
          error: function(xhr, textStatus, errorThrown) {
            // Handle AJAX error if necessary
            console.error('Error:', errorThrown);
          }
        });
      });

      // Function to populate the keyTable dropdown
      function populateKeyTableDropdown(selectedTableId, selectedTableName) {
        const keyTableDropdown = document.getElementById('keyTable');
        keyTableDropdown.innerHTML = '';

        // Populate keyTableDropdown with table names from allTables
        for (const [tableId, tableName] of Object.entries(allTables)) {
          const option = document.createElement('option');
          option.value = tableId;
          option.textContent = tableName;
          option.setAttribute('data-table', tableName); // Add data-table attribute
          keyTableDropdown.appendChild(option);
        }

        // Set the selected table
        keyTableDropdown.value = selectedTableId;
      }



      // Function to populate the keyColumn dropdown based on the selected table
      function populateKeyColumnDropdown(selectedTableName, selectedColumnName) {
        const keyColumnDropdown = document.getElementById('keyColumn');
        keyColumnDropdown.innerHTML = '';

        // Get the columns for the selected table from allTablesCols
        const columns = Object.keys(allTablesCols[selectedTableName]);

        if (columns) {
          columns.forEach((columnKey) => {
            const columnName = allTablesCols[selectedTableName][columnKey];
            const option = document.createElement('option');
            option.value = columnKey; // Use the column key as the value
            option.textContent = columnName; // Display the column name
            option.setAttribute('data-table', selectedTableName); // Add data-table attribute
            keyColumnDropdown.appendChild(option);

            // Set the selected option based on data-column-name
            if (columnName === selectedColumnName) {
              option.selected = true;
            }
          });
        }
      }





      // Event listener for the "Edit Key" button click
      document.querySelectorAll('.changeKey').forEach((button) => {
        button.addEventListener('click', function() {
          // Get the data attributes from the clicked button
          const tableId = this.getAttribute('data-table-id');
          const tableName = this.getAttribute('data-table-name');
          const columnName = this.getAttribute('data-column-name');
          const columnId = this.getAttribute('data-id'); // Get the data-id attribute
          const keyTable = this.getAttribute('data-key-table'); // Get the data-key-table attribute
          const keyColumn = this.getAttribute('data-key-column'); // Get the data-key-column attribute

          // Populate and show the modal
          populateKeyTableDropdown(tableId, tableName);

          // Set the selected values in the keyTable and keyColumn dropdowns
          document.getElementById('keyTable').value = keyTable;

          // Now, populate the keyColumn dropdown based on the selected table
          populateKeyColumnDropdown(tableName, columnName);

          // Clear the value of the keyColumn dropdown (reset to the first option)
          document.getElementById('keyColumn').selectedIndex = 0;

          // Set the value of the modalRow input
          document.getElementById('modalRow').value = columnId;

          $('#editKeyModal').modal('show');
        });
      });





      // Event listener for the keyTable dropdown change
      document.getElementById('keyTable').addEventListener('change', function() {
        const selectedTableId = this.value;
        const selectedTableOption = this.options[this.selectedIndex];
        const keyColumnDropdown = document.getElementById('keyColumn');
        keyColumnDropdown.innerHTML = '';

        // Get the selected table name from the data-table attribute
        const selectedTableName = selectedTableOption.getAttribute('data-table');

        // Get the columns for the selected table from allTablesCols
        const columns = Object.keys(allTablesCols[selectedTableName]);

        if (columns) {
          columns.forEach((columnKey) => {
            const columnName = allTablesCols[selectedTableName][columnKey];
            const option = document.createElement('option');
            option.value = columnKey; // Use the column key as the value
            option.textContent = columnName; // Display the column name
            option.setAttribute('data-table', selectedTableName); // Add data-table attribute
            keyColumnDropdown.appendChild(option);
          });
        }

        // Set the first option as selected
        if (keyColumnDropdown.options.length > 0) {
          keyColumnDropdown.selectedIndex = 0;
        }

        // Enable or disable the keyColumn dropdown based on the selection in keyTable
        keyColumnDropdown.disabled = !selectedTableId;
      });



      // Event listener for saving key changes
      document.getElementById('saveKeyChanges').addEventListener('click', function() {
        // Get the selected values from the dropdowns
        const selectedKeyTable = document.getElementById('keyTable').value;
        const selectedKeyColumn = document.getElementById('keyColumn').value;

        // Update the data attributes of the clicked button
        const clickedButton = document.querySelector('.changeKey[data-bs-dismiss="modal"]');
        clickedButton.setAttribute('data-key-table', selectedKeyTable);
        clickedButton.setAttribute('data-key-column', selectedKeyColumn);

        // Close the modal
        $('#editKeyModal').modal('hide');
      });

    });
  </script>


  <!-- Do not close the content mt-3 div in this file -->