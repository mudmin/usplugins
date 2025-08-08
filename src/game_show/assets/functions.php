<?php
function gameTime($seconds) {
  if($seconds == 0){
    return "";
  }

//this is the messiest function I could possibly find online
$hours = 0;

$milliseconds = str_replace( "0.", '', $seconds - floor( $seconds ) );
if ( $seconds > 3600 ){
  $hours = floor( $seconds / 3600 );
}

$seconds = $seconds % 3600;

$minutes = gmdate('i',$seconds);
$minutes = (int)$minutes;

$seconds = gmdate('s',$seconds);
$seconds = (int)$seconds;
// $milliseconds = round($milliseconds/1,2);
$milliseconds = substr(round($milliseconds/100,0),0,2);

if($hours != 0){
  return $hours."h ".$minutes."m ".$seconds."s";
}elseif( $minutes != 0){
  return $minutes."m ".$seconds.".".$milliseconds."s";
}else{
  return $seconds.".".$milliseconds."s";
}

}

function parseBuzzET($start){
  $now = DateTime::createFromFormat('U.u', microtime(true));
  $now = $now->format("Y-m-d H:i:s.u");
  $dt1 = new DateTime($start);
  $dt1 = $dt1->format('U.u');

  $dt2 = new DateTime($now);
  $dt2 = $dt2->format('U.u');
  $diff = round($dt2 - $dt1,5);
  return $diff;
}

function fetchSounds(){
global $abs_us_root, $us_url_root;
$sounds = [];
$targetPath = $abs_us_root . $us_url_root . "usersc/plugins/game_show/assets/mp3/";
if (file_exists($targetPath) && is_dir($targetPath) ) {
  $scan_arr = scandir($targetPath);
  $files_arr = array_diff($scan_arr, array('.','..') );

  foreach ($files_arr as $file) {
    $file_path = $targetPath."/".$file;
    $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
    if ($file_ext=="mp3"){
      $sounds[] = substr($file,0,-4);
    }
  }
}
return $sounds;
}

function fetchColors(){
  global $db;
  $colorsQ = $db->query("SELECT * FROM gameshow_light_colors ORDER BY color")->results();
  $colors = [];
  foreach($colorsQ as $c){
    $colors[] = $c->color;
  }
  return $colors;
}

function fetchBuzzers($all = true){
  global $db, $user;
  if($all == true){
    $buzzersQ = $db->query("SELECT * FROM gameshow_buzzers WHERE owner = ?",[$user->data()->id]);
  }else{
    $buzzersQ = $db->query("SELECT * FROM gameshow_buzzers WHERE owner = ? AND disabled = ?",[$user->data()->id,0]);
  }

  $buzzersC = $buzzersQ->count();
  $buzzers = $buzzersQ->results();
  return $buzzers;
}

$game_modes = [
  "1"=>"Lockout Buzzers",
  "2"=>"Buzz Order",
  "3"=>"Test Mode - No lockout"
];

//I use a cron job to clear the bans after a certain amount of time.
function gameAPIFail($ip){
  global $db;
  $db->insert("gameshow_api_fail",['ip'=>$ip]);

  if($db->query("SELECT * FROM gameshow_api_fail WHERE ip = ?",[$ip])->count() >= 25){
    $db->insert("us_ip_blacklist",['ip'=>$ip]);
    writeGameBannedFile();
  }
}

function writeGameBannedFile(){
  global $abs_us_root,$us_url_root,$db;
  $q = $db->query("SELECT ip FROM us_ip_blacklist")->results();
  $string = "<";
  $string .= "?php $";
  $string .="banned = [";
  $array = [];
  foreach($q as $ip){
    $array[] = "'$ip->ip'";
  }
  $string .= implode(",",$array);
  $string .= "];";
  $file = fopen($abs_us_root.$us_url_root."usersc/plugins/game_show/banned.php","w");
  fwrite($file, $string);
  fclose($file);
}
