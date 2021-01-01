<?php
require_once '../users/init.php';
$db = DB::getInstance();
if(!pluginActive("downloads",true)){
  die("<h1 align='center'>Downloads are Disabled</h1>");
}

$id = Input::get('id');
$code = Input::get('code');
$mode = Input::get('mode');

$resp = processDownloadLink($id,$mode,$code);
if(!isset($resp['msg'])){
  $resp['msg'] = "";
}
if($resp['success'] == true){
  $suc = 1;
}else{
  $suc = 0;
}
if(isset($user) && $user->isLoggedIn()){
  $uid = $user->data()->id;
}else{
  $uid = 0;
}

$fields = [
  'link'=>$id,
  'dlcode'=>$code,
  'linkmode'=>$mode,
  'success'=>$suc,
  'message'=>$resp['msg'],
  'user'=>$uid,
  'ip'=>ipCheck(),
];
$db->insert("plg_download_logs",$fields);

if($resp['success'] != true){
  die("<h1 align='center'>".$resp['msg']."</h1>");
}

$path = $abs_us_root.$us_url_root."usersc/plugins/downloads/files/".$resp['location'];

$file = pathinfo($path);
// dnd($file);
$ext = pathinfo($path , PATHINFO_EXTENSION);

$mime_type=mime_content_type($path);

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Type: " . $mime_type);
header("Content-Length: " .(string)(filesize($path)) );
header('Content-Disposition: attachment; filename="'.$resp['filename'].'"');
header("Content-Transfer-Encoding: binary\n");
readfile($path);
exit();
// header("Content-type: text/plain");
// header("Content-Disposition: attachment; filename=logo.png");
// // require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
// $test = file_get_contents($abs_us_root.$us_url_root."usersc/plugins/downloads/assets/logo.png");
// echo $test;
?>

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
