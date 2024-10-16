<?php
if (!function_exists('tableFromQueryReports')) {
    function tableFromQueryReports($results, $opts = [])
    {
      $summing = false;
      if(isset($opts['totals']) && $opts['totals'] != "" && $opts['totals'] != []){
        $sumCols = $opts['totals'];
     
        $sums = [];
        $summing = true;
        if(isset($opts['number_format']) && $opts['number_format'] != "" && $opts['number_format'] != []){
          $roundCols = $opts['number_format'];
        }else{
          $roundCols = [];
        }
      }

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

              <?php 
              $counter = 0;
              foreach ($opts['keys'] as $k) { ?>
                <th><?php echo $k; ?> 
                <?php 
                if ($summing){ echo "<br>"; } 
                if ($summing && in_array($counter, $sumCols)) { ?>
                 
                <span class="tot<?=$counter?>"></span>
                <?php } ?>
              </th>
              <?php 
              $counter++;
            } ?>
            </tr>
          </thead>
          <tbody class="<?= $opts['tbody'] ?>">
            <?php foreach ($results as $r) {
              $colIndex = -1;
              ?>
              <tr>
                <?php foreach ($r as $k => $v) {
                  if($summing == true){
                  $colIndex++;
  
                  if(in_array($colIndex,$sumCols)){
                    if(!isset($sums[$colIndex])){
                      $sums[$colIndex] = 0;
                    }
                    if(is_numeric($v)){
                      $sums[$colIndex] += $v;
                    }
  
                  }
                }
                  ?>
                  <td><?= $v ?></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
          <?php if($summing == true){
            $colIndex = -1;
  
            ?>
            <tfoot>
              <tr>
                <?php foreach ($results['0'] as $k => $v) {
                  $colIndex++;
                  $round = 0;
                  if (in_array($colIndex, $sumCols)) {
                      if (isset($roundCols) && is_array($roundCols) && array_key_exists($colIndex, $roundCols)) {
                          $round = $roundCols[$colIndex];
                      }
                      ?>
                      <td class="text-primary"><b><?= number_format($sums[$colIndex] ?? "", $round); ?></b></td>
                  <?php }else{ ?>
                    <td></td>
                  <?php } ?>
                <?php } ?>
              </tr>
          <?php } ?>
        </table>
  <?php
  
      } else {
        echo "<h3>Table is Empty</h3>";
      }
    }
  }
