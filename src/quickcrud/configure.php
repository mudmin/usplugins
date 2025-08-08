  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_quickcrud'])){
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
        <h3>Basic Instructions</h3>
        <p>This is a quick and dirty plugin that allows you to make CRUD Tables based on DB queries. Simply
        pass it a db query and a table name and it will automatically generate a directly editable table with
        copy and delete buttons. At the bottom of the table will also be a form to insert a new row.
      </p>
      <p>Usage<br>
        <code>
        $query = $db->query("SELECT * FROM permissions")->results();<br>
        $table = "permissions";<br>
        quickCrud($query,$table);<br>
      </code>
      </p>
      <p>Optional Parameters<br>
        You can pass a third parameter ($opts) with these options.<br>
        <code>
          $opts = [<br>
            'noid'=>1, //hides the id column from the table<br>
            'nodupe'=>1, //hides duplicate button<br>
            'nodel'=>1, //hides delete button<br>
            'class'=>"classname", //optional class for entire table<br>
            'thead'=>"classname", //optional class for table head<br>
            'tbody'=>"classname", //optional class for table body <br>
          ];<br>
        </code>
      </p>
      <h3>IMPORTANT NOTICE</h3>
      <p>While there is some basic sanitization, <strong>THIS IS NOT FOR FRONT END USE</strong>.  This
        is to simplify making "control panel" type things for administrators. The parsers will not fire
        for non-admins and if you edit it to do so, it is totally at your own risk.
      </p>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->

<?php if(in_array($user->data()->id,$master_account)){ ?>
    <h3>Database Editor</h3>
    <p>Please note, this database editor is very powerful. It is only recommended that you edit things if you know what you're doing.</p>
    <?php // BEGIN DROPDOWN
    $tables = $db->query('SHOW TABLES')->results();
    $q = "";
    $t = "Tablesin" . Config::get('mysql/db');
    if(isset($tables[0]->$t)){
      $q = $t;
    }

    $t = "Tables_in_" . Config::get('mysql/db');
    if(isset($tables[0]->$t)){
      $q = $t;
    }
    if($q == ""){ ?>

      Your database schema will work with the Quick Crud plugin, however, it will not work with the automated database editor.  If you'd like to help us, please consider filling out a ticket at <a href="https://bugs.userspice.com">https://bugs.userspice.com</a> and passing along this diagnistic information:<br>
      <?php dump($table[0]);?>
      Thanks so much for your help.
    <?php }else{ ?>
    <form action="" name="form" method="post" >
      <select id="tables" name="seek" onchange="this.form.submit()">
       <option value="">Choose Table</option>
    <?php
    // populate select box


    foreach($tables as $table){

      ?>
    <option value='<?=$table->$t?>'><?=$table->$t?></option>
    <?php } ?>
      </select>
    </form>
    <?php
    $tbl = (!empty($_POST["seek"])) ? $_POST["seek"] : "Empty"; ?>
    <H4>Currently Viewing '<?=ucfirst($tbl)?>' table</H4>
    <?php
    $query = $db->query("SELECT * FROM $tbl")->results();
     quickCrud($query,$tbl);
   }
 }
    // END DROPDOWN ?>
