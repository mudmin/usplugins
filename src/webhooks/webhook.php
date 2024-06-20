<?php
require_once("../../../users/init.php");
$db = DB::getInstance();
ipCheckBan();
$ip = ipCheck();
$json = file_get_contents('php://input');
$json = json_decode($json, "true");

//combine all data
$data = [];
$data["datatypes_received"] = [];
foreach($_GET as $k=>$v){
  $data[Input::sanitize($k)] = Input::sanitize($v);
  $data["datatypes_received"][] = "GET";
}

foreach($_POST as $k=>$v){
  $data[Input::sanitize($k)] = Input::sanitize($v);
  $data["datatypes_received"][] = "POST";
}

if($json != ""){
  $data["datatypes_received"][] = "JSON";
  foreach($json as $k=>$v){
    $data[Input::sanitize($k)] = Input::sanitize($v);
  }
}

//did not specify webhook_id
if(!isset($data['webhook_id'])){
  $fields = [
    'hook'=>0,
    'ip'=>$ip,
    'subject'=>"No Webhook",
    'log'=>"Did not specifiy webhook_id",
  ];
  $db->insert("plg_webhook_activity_logs",$fields);
  die;
}
$q = $db->query("SELECT * FROM plg_webhooks WHERE id = ?",[$data['webhook_id']]);
$c = $q->count();

//specified a webhook_id that doesn't exist
if($c < 1){
  $fields = [
    'hook'=>0,
    'ip'=>$ip,
    'subject'=>"Inv Webhook",
    'log'=>"Attempted to visit webhook ".$data['webhook_id']." which does not exist",
  ];
  $db->insert("plg_webhook_activity_logs",$fields);
  die;
}else{
  //webhook found and retreived
  $webhook = $q->first();
  if($webhook->auth == "*"){
    //just keep going
  }elseif($webhook->auth == "w"){
    $c = $db->query("SELECT * FROM us_ip_whitelist WHERE ip = ?",[$webhook->auth])->count();
    if($c < 1){
      //not found in the whitelist
      $fields = [
        'hook'=>$webhook->id,
        'ip'=>$ip,
        'subject'=>"Non-Whitelisted IP",
        'log'=>"Attempted to visit webhook from a non-whitelisted ip",
      ];
      $db->insert("plg_webhook_activity_logs",$fields);
    }else{
      if($ip != $webhook->auth){
        //ip did not match
        $fields = [
          'hook'=>$webhook->id,
          'ip'=>$ip,
          'subject'=>"IP Mismatch",
          'log'=>"Attempted to visit webhook from the wrong ip",
        ];
        $db->insert("plg_webhook_activity_logs",$fields);
        die;
      }
    }
  }
}

//look for a special second factor key that should be provided in the data itself
if($webhook->twofa_key != "" || $webhook->twofa_value != ""){

  if(!isset($data[$webhook->twofa_key])){
    $fields = [
      'hook'=>$webhook->id,
      'ip'=>$ip,
      'subject'=>"2FA Key Not Provided",
      'log'=>"Did not provide either the key or the value",
    ];
    $db->insert("plg_webhook_activity_logs",$fields);
    die;
  }


  if($data[$webhook->twofa_key] != $webhook->twofa_value){
    $fields = [
      'hook'=>$webhook->id,
      'ip'=>$ip,
      'subject'=>"2FA Key Invalid",
      'log'=>"Provided an invalid Key Value Pair. Key-".$webhook->twofa_key." value-".$webhook->twofa_value,
    ];
    $db->insert("plg_webhook_activity_logs",$fields);
    die;
  }
}



//At this point, we've confirmed that a valid webhook_id has been passed
//and that it came from a valid ip. We have also verified the 2fa pair,
//if configured.  So now the webhook can stop playng dumb and at least respond
http_response_code ( 200 ); //ok
$msg = [];

//if the webhook setup requests the data to be logged, let's do that now
if($webhook->log == 1){
  $fields = [
    'hook'=>$webhook->id,
    'ip'  =>$ip,
    'log' =>json_encode($data)
  ];
  $db->insert("plg_webhook_data_logs",$fields);
}

//check if webhook has been disabled
if($webhook->disabled > 0){
  $msg['msg'] = "Webhook has been disabled";
  $fields = [
    'hook'=>$webhook->id,
    'ip'=>$ip,
    'subject'=>"Disabled",
    'log'=>"Attempted to visit a disabled webhook",
  ];
  $db->insert("plg_webhook_activity_logs",$fields);
  echo json_encode($msg);
  die;
}

$action = performWebhookAction($webhook->id,$webhook->action_type,$webhook->action,$data);
if (is_string($action)) {
  $msg['msg'] = $action;
} else {
  $msg=$action;
  $action=json_encode($msg);
}
$fields = [
  'hook'=>$webhook->id,
  'ip'=>$ip,
  'subject'=>"Action Performed",
  'log'=>$action,
];
$db->insert("plg_webhook_activity_logs",$fields);
echo json_encode($msg);
die;

//end of script

function performWebhookAction($id,$action_type,$action,$data){
  global $db,$abs_us_root,$us_url_root;
  if($action_type == "db"){
    $db->query($action);
    if(!$db->error()){
      return "success";
    }else{
      $es = $db->errorString();
      $fields = [
        'hook'=>$id,
        'ip'=>ipCheck(),
        'subject'=>"Bad DB Query",
        'log'=>$db->errorString(),
      ];
      $db->insert("plg_webhook_activity_logs",$fields);
      return $es;
    }
  }elseif($action_type == "php"){
    if(file_exists($abs_us_root.$us_url_root."usersc/plugins/webhooks/assets/".$action)){
      require_once($abs_us_root.$us_url_root."usersc/plugins/webhooks/assets/".$action);
      if(empty($return_object)) return "success";
      else return $return_object;
    }else{
      return "File not found";
    }
  }elseif($action_type == "exec"){
    $output=null;
    $retval=null;
    $try = exec($action, $output, $retval);
    $output = json_encode($output);
    return "Val: ".$retval." Data: ".$output;

  }

}
