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

<?php }
}
