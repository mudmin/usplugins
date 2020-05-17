<?php
if(!function_exists('quickCrud')) {
  function quickCrud($query,$table, $opts = []){
    global $db,$user,$abs_us_root,$us_url_root,$formNumber;
    if(hasPerm([2],$user->data()->id)){
    if(!isset($formNumber) || $formNumber == ""){
      $formNumber = 0;
    }else{
      $formNumber = $formNumber + 1;
    }

    if(!isset($opts['class'])) {$opts['class'] = "table table-striped paginate"; }
    if(!isset($opts['thead'])) {$opts['thead'] = ""; }
    if(!isset($opts['tbody'])) {$opts['tbody'] = ""; }

    if(!isset($opts['keys']) && $query != []){
      foreach($query['0'] as $k=>$v){
        $opts['keys'][] = $k;
      }
    }
    if($query != []){
      $row = "";
      ?>
      <table class="<?=$opts['class']?> editable" id="paginate">
        <thead class="<?=$opts['thead']?>">
          <tr>

            <?php foreach($opts['keys'] as $k){?>
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
            $id = $r->id;
            $row = $r;
            ?>
            <tr>
              <?php foreach($r as $k=>$v){ ?>
                <td
                 data-key="<?=$k?>" data-row="<?=$id?>" data-method="update"
                <?php if($k == "id"){echo "class='uneditable'";}?>
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
            foreach ($row as $key => $value) {
              if($key == 'id'){continue;}
               ?>
              <div class="form-group">
                <label for=""><?=$key?></label><br>
                <input class="form-control" type="text" name="<?=$key?>" value="">
              </div>
            <?php } ?>
            <button type="button" name="button" data-form="<?=$formNumber?>" class="btn btn-info insert">Insert</button><br><br>
          </form>
        </div>
      </div>
      <script src="<?=$us_url_root?>usersc/plugins/quickcrud/assets/editable.js"></script>
      <script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
      <script>
      $(document).ready(function () {
         $('.editable').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
        });
      </script>
      <script type="text/javascript">
        $('.editable').editableTableWidget();
        $('#editable td.uneditable').on('change', function(evt, newValue) {
          	return false;
          });
          $('.editable td').on('change', function(evt, newValue) {

        	$.post( "<?=$us_url_root?>usersc/plugins/quickcrud/assets/parser.php", {
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

        $(".trigger").click(function(data) {

          var formData = {
            row: $(this).attr("data-row"),
            method: $(this).attr("data-method"),
            table: "<?=$table?>"
          };
          $.ajax({
            type 		: 'POST',
            url 		: "<?=$us_url_root?>usersc/plugins/quickcrud/assets/parser.php",
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
            url 		: "<?=$us_url_root?>usersc/plugins/quickcrud/assets/parser.php",
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
