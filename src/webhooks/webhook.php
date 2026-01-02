<?php
require_once("../../../users/init.php");
$db = DB::getInstance();
ipCheckBan();
$ip = ipCheck();
$json = file_get_contents('php://input');
$json = json_decode($json, "true");

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
  $webhook = $q->first();
  if($webhook->auth == "*"){
    //just keep going
  }elseif($webhook->auth == "w"){
    $c = $db->query("SELECT * FROM us_ip_whitelist WHERE ip = ?",[$webhook->auth])->count();
    if($c < 1){
      $fields = [
        'hook'=>$webhook->id,
        'ip'=>$ip,
        'subject'=>"Non-Whitelisted IP",
        'log'=>"Attempted to visit webhook from a non-whitelisted ip",
      ];
      $db->insert("plg_webhook_activity_logs",$fields);
    }else{
      if($ip != $webhook->auth){
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
      'log'=>"Provided an invalid Key Value Pair.",
    ];
    $db->insert("plg_webhook_activity_logs",$fields);
    die;
  }
}

http_response_code ( 200 );
$msg = [];

if($webhook->log == 1){
  $fields = [
    'hook'=>$webhook->id,
    'ip'  =>$ip,
    'log' =>json_encode($data)
  ];
  $db->insert("plg_webhook_data_logs",$fields);
}

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
$msg['msg'] = $action;
$fields = [
  'hook'=>$webhook->id,
  'ip'=>$ip,
  'subject'=>"Action Performed",
  'log'=>$action,
];
$db->insert("plg_webhook_activity_logs",$fields);
echo json_encode($msg);
die;

function performWebhookAction($id,$action_type,$action,$data){
  global $db,$abs_us_root,$us_url_root;
  if($action_type == "db"){
    $trimmedAction = trim($action);
    if (!preg_match('/^\s*SELECT\s/i', $trimmedAction)) {
      return "Only SELECT queries are allowed";
    }
    
    $dangerous = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER', 'CREATE', 'REPLACE', 'UNION', '--', ';', 'EXEC', 'EXECUTE'];
    $upperAction = strtoupper($trimmedAction);
    foreach ($dangerous as $keyword) {
      if (strpos($upperAction, $keyword) !== false) {
        return "Query contains blocked keywords";
      }
    }

    $db->query($action);
    if(!$db->error()){
      return "success";
    }else{
      return $db->errorString();
    }
  }elseif($action_type == "php"){
    // HARDENING: Only allow inclusion of files that actually exist in the assets folder
    $assetPath = $abs_us_root . $us_url_root . "usersc/plugins/webhooks/assets/";
    $requestedFile = basename($action);
    
    // Get list of all .php files in the assets directory
    $allowedFiles = glob($assetPath . "*.php");
    $whiteList = [];
    foreach($allowedFiles as $file) {
        $whiteList[] = basename($file);
    }

    // Verify the requested file is exactly one of the files in the directory
    if (in_array($requestedFile, $whiteList)) {
        $fullPath = $assetPath . $requestedFile;
        require_once($fullPath);
        return "success";
    } else {
        return "File not found or access denied";
    }

  }elseif($action_type == "exec"){
    return "Command execution is disabled for security reasons";
  }
}