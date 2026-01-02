<?php
//protected content snippet
if(isset($_GET['failed']) && $_GET['failed'] == "true"){
  die("We are sorry, but your authentication failed. You cannot view this content");
}
//SETTING UP SOME BASIC VARIABLES
//this is the url to your login page with no trailing slash
$auth_url = "http://localhost/omt/usersc/login.php";

//this is the FULL URL link to your webhook url usually in usersc/plugins/spicepress/webhook.php
$webhook_url = "https://localhost/omt/usersc/plugins/spicepress/parsers/webhook.php";

global $_SESSION;
$protocol = ((!empty(Server::get('HTTPS')) && Server::get('HTTPS') != 'off') || Server::get('SERVER_PORT') == 443) ? "https://" : "http://";
$url = $protocol . Server::get('HTTP_HOST') . Server::get('REQUEST_URI');
$query = Server::get('QUERY_STRING');
if($query == ""){
  $pre = "?";
}else{
  $pre = "&";
}

//This section runs if you don't have the proper session variable and are trying to see protected content;
if(!isset($_SESSION['spicepress']) && !isset($_GET['success'])){
  //you must authenticate to see this content
  //you will be redirected to the userspice login page. If you are already logged in, you will redirect back immediately.
  $auth = $auth_url.$pre."request_wp_access=1&return_url=".$url;
  header("Location: $auth");
}elseif(!isset($_SESSION['spicepress']) && isset($_GET['success']) && $_GET['success'] == true){
  $code = $_GET['code'];
  //we THINK they've authenticated so let's check it

  $result = checkSpicePress($webhook_url,$code);
  //authentication passed
  if($result->success == true){
    $_SESSION['spicepress'] = $code;

    //if you want to you can strip all our query stuff from the url and refresh the page
    if(isset($_GET['code'])){
      unset($_GET['code']);
    }
    if(isset($_GET['success'])){
      unset($_GET['success']);
    }
    $baseUrl = Server::get('SERVER_NAME') . parse_url(Server::get('REQUEST_URI'), PHP_URL_PATH);
    $query = http_build_query($_GET);
    header("Location: $baseUrl.$query");

  }else{

    $failed_url = $url.$pre."failed=true";
    header("Location: $failed_url");
  }

}elseif(isset($_SESSION['spicepress'])){
  $code = $_SESSION['spicepress'];
  $result = checkSpicePress($webhook_url,$code);

  //authentication passed
  if($result->success == true){
    $_SESSION['spicepress'] = $code;
  }else{
    unset($_SESSION['spicepress']);

    $failed_url = $url.$pre."failed=true";
    header("Location: $failed_url");
  }

}

function checkSpicePress($webhook_url,$code){
  $ch = curl_init($webhook_url);
  $data = ['auth_code' => $code];
  $payload = json_encode($data);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_AUTOREFERER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $result = curl_exec($ch);

  curl_close($ch);
  $result = json_decode($result);
  return $result;
}
