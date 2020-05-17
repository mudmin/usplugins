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
  <link href="<?=$us_url_root?>users/js/pagination/datatables.min.css" rel="stylesheet">
  <script type="text/javascript" src="js/pagination/datatables.min.js"></script>
  <script>

  $(document).ready(function () {
    $('.paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
    });

</script>
<?php }
}
