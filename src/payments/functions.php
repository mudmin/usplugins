<?php
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

if(!function_exists('tableFromData')){
function tableFromData($data,$opts = []){
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
  <script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
  <script>
  $(document).ready(function () {
     $('.paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
    });
  </script>
    <?php
  }
}
