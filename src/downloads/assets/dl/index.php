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

// Security Hardening: Ensure file is confined to the specific downloads folder
$downloadFolder = $abs_us_root . $us_url_root . "usersc/plugins/downloads/files/";
$safeFile = basename($resp['location']); 
$path = $downloadFolder . $safeFile;

if (!empty($safeFile) && file_exists($path) && is_file($path)) {
    $mime_type = mime_content_type($path);
    $fileSize = filesize($path);

    // Standard Download Headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: " . $mime_type);
    header("Content-Length: " . (string)$fileSize);
    header('Content-Disposition: attachment; filename="' . basename($resp['filename']) . '"');
    header("Content-Transfer-Encoding: binary");

    // Clean any existing output buffers to prevent file corruption
    if (ob_get_level()) {
        ob_end_clean();
    }

    readfile($path);
    exit();
} else {
    die("<h1 align='center'>File not found or access denied.</h1>");
}
?>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>