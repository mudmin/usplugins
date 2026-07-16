  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_quickcrud'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
  <div class="row">
    <div class="col-12">
      <h2 class="mb-3">Quick CRUD</h2>
    </div>
  </div>

  <?php include "db_editor.php"; ?>

  <div class="row mt-4">
    <div class="col-12">
      <div class="card mb-3">
        <div class="card-header"><strong>Basic Usage</strong></div>
        <div class="card-body">
          <p>Quick CRUD generates a directly editable table from any database query. Click a cell to edit it in
            place, duplicate or delete rows with one click, and insert new rows with the form below the table.
            Simply pass it a query and a table name:</p>
          <pre class="border rounded p-3 mb-0"><code>$query = $db->query("SELECT * FROM permissions")->results();
quickCrud($query, "permissions");</code></pre>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Options</strong></div>
        <div class="card-body">
          <p>Pass an optional third parameter with any of these keys:</p>
          <table class="table table-sm table-striped">
            <thead>
              <tr><th>Option</th><th>Effect</th></tr>
            </thead>
            <tbody>
              <tr><td><code>'noid'=>1</code></td><td>Hides the id column from the table</td></tr>
              <tr><td><code>'nodupe'=>1</code></td><td>Hides the Duplicate button</td></tr>
              <tr><td><code>'nodel'=>1</code></td><td>Hides the Delete button</td></tr>
              <tr><td><code>'class'=>"classname"</code></td><td>Optional class for the entire table</td></tr>
              <tr><td><code>'thead'=>"classname"</code></td><td>Optional class for the table head</td></tr>
              <tr><td><code>'tbody'=>"classname"</code></td><td>Optional class for the table body</td></tr>
            </tbody>
          </table>
          <pre class="border rounded p-3 mb-0"><code>quickCrud($query, "permissions", ['noid'=>1, 'nodel'=>1]);</code></pre>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Embedding the Database Editor</strong></div>
        <div class="card-body">
          <p>The table picker and editor below live in their own file, so you can offer them on any page of your
            site (a custom admin panel, for example), not just this plugin config screen. On a page that has
            already loaded <code>users/init.php</code>:</p>
          <pre class="border rounded p-3 mb-2"><code>include $abs_us_root.$us_url_root.'usersc/plugins/quickcrud/db_editor.php';</code></pre>
          <p class="mb-0">The editor only renders for logged-in users with Admin permission (level 2), and the
            AJAX endpoints enforce the same permission server-side. Including the file on a page visible to other
            users simply outputs nothing for them.</p>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><strong>Customizing Permissions</strong></div>
        <div class="card-body">
          <p class="mb-0">By default, QuickCRUD requires permission level 2 (Admin) everywhere. To change that,
            rename <code>permissions.override.php</code> (in the plugin folder) to <code>permissions.php</code> and
            adjust the <code>hasPerm()</code> call inside — it ships allowing levels 2 and 3. Once
            <code>permissions.php</code> exists, it replaces the built-in check for the database editor,
            <code>quickCrud()</code> tables, and the AJAX endpoints, and it survives plugin updates.</p>
        </div>
      </div>

      <div class="alert alert-danger">
        <strong>Important:</strong> while there is some basic sanitization, <strong>this is not for front-end
        use</strong>. Quick CRUD is for building "control panel" style tools for administrators. The parsers will
        not fire for non-admins, and if you edit them to do so, it is totally at your own risk.
      </div>
    </div> <!-- /.col -->
  </div> <!-- /.row -->
</div>
