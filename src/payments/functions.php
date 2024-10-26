<?php
function haltPayment($option){
  $db = DB::getInstance();
  $check = $db->query("SELECT * FROM plg_payments_options WHERE `option` = ? AND enabled = 1",[$option])->count();
  if($check < 1){
    return true;
  }else{
    return false;
  }
}

function showPaymentOptions($opts = []){
  $db = DB::getInstance();
  $q = $db->query("SELECT * FROM plg_payments_options WHERE enabled = 1");
  $c = $q->count();
  $r = $q->results();

    echo "<label>Please select a Payment Option</label>";
  if($c < 1){
    echo "All payment options are currently disabled. Please contact an administrator<br>";
  }else{
    ?>
    <div class="form-group">
      <select class="<?php if(isset($opts['class'])){echo $opts['class'];}?>" name="paymentOption" required>
        <?php if($c > 1){?>
        <option value="" disabled>--Please select a payment option</option>
      <?php }
      foreach($r as $p){ ?>
        <option value="<?=$p->option?>"><?=ucfirst($p->option)?></option>
      <?php } ?>
      </select>
    </div>
<?php
  }
}

function displayPayment($formInfo){
  global $user,$db,$abs_us_root,$us_url_root;
  $method = $formInfo['method'];
  	require $abs_us_root.$us_url_root.'usersc/plugins/payments/assets/'.$method.'/form_process.php';
    require $abs_us_root.$us_url_root.'usersc/plugins/payments/assets/'.$method.'/form_required.php';
    if(isset($formInfo['submit']) && $formInfo['submit'] != ""){
      echo $formInfo['submit'];
    }else{
      echo "<button class='btn btn-primary payment-form' type='submit'>Submit Payment</button><br>";
    }
    require $abs_us_root.$us_url_root.'usersc/plugins/payments/assets/'.$method.'/form_bottom.php';
}

function payment1($formInfo){
  global $user,$db,$abs_us_root,$us_url_root;
  $method = $formInfo['method'];
  	require $abs_us_root.$us_url_root.'usersc/plugins/payments/assets/'.$method.'/form_process.php';
    return $formInfo;
}

function payment2($formInfo){
  global $user,$db,$abs_us_root,$us_url_root;
  $method = $formInfo['method'];
  	require $abs_us_root.$us_url_root.'usersc/plugins/payments/assets/'.$method.'/form_required.php';
    return $formInfo;
}

function payment3($formInfo){
  global $user,$db,$abs_us_root,$us_url_root;
  $method = $formInfo['method'];
  	require $abs_us_root.$us_url_root.'usersc/plugins/payments/assets/'.$method.'/form_bottom.php';
    return $formInfo;
}

function logPayment($u,$amt_paid,$dt,$charge_id,$method,$notes,$failed){
  $db = DB::getInstance();
  $fields = [
    'user'=>$u,
    'amt_paid'=>$amt_paid,
    'dt'=>$dt,
    'charge_id'=>$charge_id,
    'method'=>$method,
    'notes'=>$notes,
    'failed'=>$failed,
  ];
  $db->insert('plg_payments',$fields);
}

if(!function_exists('paymentTableFromData')){
function paymentTableFromData($data,$opts = []){
  global $us_url_root;

  if(sizeof($data) == 0){
    if(!isset($opts['nodata'])){
      echo "<p align='center'>No data to display</p>";
    }else{
      echo $opts['nodata'];
    }
    return false;
  }
  //Pass id as 1 to show the id column
  if(!isset($opts['class'])){
    $opts['class'] = 'table table-striped paginate';
  }

  if(!isset($opts['id'])){
    $opts['id'] = 0;
  }

  ?>
  <!-- optional table class? -->
  <table class='<?=$opts['class']?>'>
    <thead>
      <?php
      if($opts['id'] == 1){?>
        <th>ID</th>
      <?php }
      foreach($data[0] as $key=>$value){?>
        <th><?php
        if(!isset($opts['sub']) || !array_key_exists($key,$opts['sub'])){
          echo ucfirst($key);
        }else{
          echo $opts['sub'][$key];
        }
        ?>
      </th>
      <?php } ?>
    </thead>
    <tbody>
      <?php foreach($data as $k=>$v){ ?>
        <tr>
          <?php
          // dump($v);
          foreach($v as $cell=>$contents){
            echo "<td>$contents</td>";
            }
            ?>
        </tr>
      <?php	} ?>
    </tbody>
  </table>


    <?php
  }
}
