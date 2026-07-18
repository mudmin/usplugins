<?php
if(!function_exists('quickCrudHasPerm')) {
  //central permission check for the editor, quickCrud(), and the AJAX parser
  //rename permissions.override.php to permissions.php in this folder to customize it
  function quickCrudHasPerm(){
    global $user;
    if(!isset($user) || !$user->isLoggedIn()){ return false; }
    $override = __DIR__.'/permissions.php';
    if(file_exists($override)){
      return (bool)(include $override);
    }
    return hasPerm([2],$user->data()->id);
  }
}

if(!function_exists('quickCrudTableExists')) {
  //validates a table name against the actual database schema
  function quickCrudTableExists($table){
    static $tables = null;
    if(!is_string($table) || !preg_match('/^[a-zA-Z0-9_]+$/', $table)){ return false; }
    if($tables === null){
      $db = DB::getInstance();
      $tables = [];
      foreach($db->query('SHOW TABLES')->results(true) as $row){
        $tables[] = (string)array_values($row)[0];
      }
    }
    return in_array($table, $tables, true);
  }
}

if(!function_exists('quickCrudKey')) {
  /**
   * Detects the key quickCrud uses to address rows in a table.
   * Prefers the primary key (composite keys included); falls back to the
   * first single-column unique index on a NOT NULL column.
   * Returns ['cols'=>[...], 'auto'=>'colname'|null], or null when the
   * table has no usable key.
   */
  function quickCrudKey($table){
    static $cache = [];
    if(array_key_exists($table, $cache)){ return $cache[$table]; }
    if(!quickCrudTableExists($table)){ return $cache[$table] = null; }
    $db = DB::getInstance();
    $columns = $db->query("SHOW COLUMNS FROM `$table`")->results();
    $notNull = [];
    $auto = null;
    foreach($columns as $c){
      if($c->Null === 'NO'){ $notNull[] = $c->Field; }
      if(stripos($c->Extra ?? '', 'auto_increment') !== false){ $auto = $c->Field; }
    }
    $indexes = [];
    foreach($db->query("SHOW KEYS FROM `$table` WHERE Non_unique = 0")->results() as $k){
      $indexes[$k->Key_name][(int)$k->Seq_in_index] = $k->Column_name;
    }
    $cols = [];
    if(isset($indexes['PRIMARY'])){
      ksort($indexes['PRIMARY']);
      $cols = array_values($indexes['PRIMARY']);
    }else{
      foreach($indexes as $keyCols){
        if(count($keyCols) == 1 && in_array(reset($keyCols), $notNull, true)){
          $cols = array_values($keyCols);
          break;
        }
      }
    }
    if(empty($cols)){ return $cache[$table] = null; }
    return $cache[$table] = [
      'cols' => $cols,
      'auto' => in_array($auto, $cols, true) ? $auto : null,
    ];
  }
}

if(!function_exists('quickCrudRowToken')) {
  //JSON token identifying one row by its key column values, e.g. {"id":5}
  function quickCrudRowToken($row, $crudKey){
    $vals = [];
    foreach($crudKey['cols'] as $c){
      if(!property_exists($row, $c)){ return null; }
      $vals[$c] = $row->$c;
    }
    return json_encode($vals);
  }
}

if(!function_exists('quickCrudRowWhere')) {
  //turns a row token back into a DB.php where array; accepts a bare value
  //for single-column keys. Returns null if the row can't be addressed.
  function quickCrudRowWhere($table, $token){
    $crudKey = quickCrudKey($table);
    if($crudKey === null){ return null; }
    $vals = json_decode((string)$token, true);
    if(!is_array($vals)){
      if(count($crudKey['cols']) == 1 && $token !== null && $token !== ''){
        $vals = [$crudKey['cols'][0] => $token];
      }else{
        return null;
      }
    }
    $where = ['and'];
    foreach($crudKey['cols'] as $c){
      if(!array_key_exists($c, $vals) || is_array($vals[$c])){ return null; }
      $where[] = [$c, '=', $vals[$c]];
    }
    return count($crudKey['cols']) == 1 ? $where[1] : $where;
  }
}

if(!function_exists('quickCrud')) {
  function quickCrud($query,$table, $opts = []){
    global $db,$user,$abs_us_root,$us_url_root,$formNumber;
    if (!isset($GLOBALS['userspice_nonce'])) {
        $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
    }
    if(quickCrudHasPerm()){
    if(!isset($formNumber) || $formNumber == ""){
      $formNumber = 0;
    }else{
      $formNumber = $formNumber + 1;
    }

    if(!isset($opts['class'])) {$opts['class'] = "table table-striped"; }
    if(!isset($opts['thead'])) {$opts['thead'] = ""; }
    if(!isset($opts['tbody'])) {$opts['tbody'] = ""; }

    if(!isset($opts['keys']) && $query != []){
      foreach($query['0'] as $k=>$v){
        $opts['keys'][] = $k;
      }
    }
    $crudKey = quickCrudKey($table);
    if($crudKey !== null && isset($query[0])){
      foreach($crudKey['cols'] as $c){
        //the caller's query has to select the key columns or rows can't be addressed
        if(!property_exists($query[0], $c)){ $crudKey = null; break; }
      }
    }
    $keyCols = ($crudKey !== null) ? $crudKey['cols'] : [];
    if($crudKey === null){
      //update, duplicate, and delete all need a way to address a single row
      $opts['nodupe'] = true;
      $opts['nodel'] = true;
    }elseif($crudKey['auto'] === null){
      //duplicate relies on the database minting a fresh auto_increment value
      $opts['nodupe'] = true;
    }
    if($query != []){
      $row = "";
      if($crudKey === null){
        echo "<div class='alert alert-warning'>No usable primary or unique key was found for table `".htmlspecialchars($table)."` (or your query does not select its column(s)), so inline editing, duplicating, and deleting are disabled.</div>";
      }
      ?>
      <table class="<?=$opts['class']?> editable" id="paginate">
        <thead class="<?=$opts['thead']?>">
          <tr>

            <?php foreach($opts['keys'] as $k){
              if(isset($opts['noid']) && in_array($k, ($keyCols ?: ['id']), true)){ continue; };
              ?>
              <th><?php echo $k;?></th>
            <?php } ?>
            <?php if(!isset($opts['nodupe'])){?>
              <th>Duplicate</th>
            <?php } ?>
            <?php if(!isset($opts['nodel'])){?>
              <th>Delete</th>
            <?php } ?>
          </tr>
        </thead>
        <tbody class="<?=$opts['tbody']?>">
          <?php foreach($query as $r){
            $id = ($crudKey !== null) ? htmlspecialchars((string)quickCrudRowToken($r, $crudKey), ENT_QUOTES, 'UTF-8') : '';
            $row = $r;
            ?>
            <tr>
              <?php foreach($r as $k=>$v){
                if(isset($opts['noid']) && in_array($k, ($keyCols ?: ['id']), true)){ continue; };
              ?>

                <td
                 data-key="<?=$k?>" data-row="<?=$id?>" data-method="update"
                <?php if(in_array($k, $keyCols, true)){echo "class='uneditable'";}?>
                  ><?=$v?></td>
              <?php } ?>
              <?php if(!isset($opts['nodupe'])){?>
                <td><button type="button" name="dupe" class="btn btn-primary trigger"
                    data-row="<?=$id?>" data-method="duplicate"
                    >Duplicate</button></td>
              <?php } ?>
              <?php if(!isset($opts['nodel'])){?>
                <td><button type="button" name="del" class="btn btn-danger trigger"
                    data-row="<?=$id?>" data-method="delete"
                    >Delete</button></td>
              <?php } ?>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <div class="row">
        <div class="col-12">
          <h4>Insert into <?=$table?></h4>
          <form class="editableForm" action="" method="post" id="form<?=$formNumber?>" >
            <?php
            foreach ($row as $col => $value) {
              //auto_increment keys are minted by the database; everything else (including non-auto keys) is enterable
              if($crudKey !== null && $col === $crudKey['auto']){continue;}
               ?>
              <div class="form-group">
                <label for=""><?=$col?></label><br>
                <input class="form-control" type="text" name="<?=$col?>" value="">
              </div>
            <?php } ?>
            <button type="button" name="button" data-form="<?=$formNumber?>" class="btn btn-info insert">Insert</button><br><br>
          </form>
        </div>
      </div>
      <script src="<?=$us_url_root?>usersc/plugins/quickcrud/assets/editable.js"></script>
      <script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
      <script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
      $(document).ready(function () {
         $('.editable').each(function () {
           if (!$.fn.DataTable.isDataTable(this)) {
             $(this).DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
           }
         });
        });
      </script>
      <script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
        <?php if($crudKey !== null){ ?>
        $('.editable').editableTableWidget();
        $('#editable td.uneditable').on('change', function(evt, newValue) {
          	return false;
          });
          $('.editable td').on('change', function(evt, newValue) {

        	$.post( "<?=$us_url_root?>usersc/plugins/quickcrud/parsers/parser.php", {
            value: newValue,
            key: $(this).attr("data-key"),
            row: $(this).attr("data-row"),
            method: $(this).attr("data-method"),
            table: "<?=$table?>"
           })
        		.done(function( data ) {
            			if(data != ""){alert(data);}
        		});
        	;
        });
        <?php } ?>

        $(".trigger").click(function(data) {

          var formData = {
            row: $(this).attr("data-row"),
            method: $(this).attr("data-method"),
            table: "<?=$table?>"
          };
          $.ajax({
            type 		: 'POST',
            url 		: "<?=$us_url_root?>usersc/plugins/quickcrud/parsers/parser.php",
            data 		: formData,
            dataType 	: 'json',
            encode 		: true
          })
          .done(function(data) {
            if(data.reload == true){
              location.reload(true);
            }
            if(data.msg != ""){
              alert(data.msg);
            }
          })
        });

        $(".insert").click(function(data) {
          var formData = {
            data: $("#form<?=$formNumber?>").serialize(),
            method: "insert",
            table: "<?=$table?>"
          };
          $.ajax({
            type 		: 'POST',
            url 		: "<?=$us_url_root?>usersc/plugins/quickcrud/parsers/parser.php",
            data 		: formData,
            dataType 	: 'json',
            encode 		: true
          })
          .done(function(data) {
            if(data.reload == true){
              location.reload(true);
            }
            if(data.msg != ""){
              alert(data.msg);
            }
          })
        });
      </script>
      <?php
    }else{
      echo "<h3>Table is Empty</h3>";
    }
   }
  }
}
