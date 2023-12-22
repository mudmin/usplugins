<?php
function dlPluginModes(){
  $modes = [
    1=>"Downloads disabled (Mode 1)",
    2=>"Registered users can download all files with direct links. No custom links. (Mode 2)",
    3=>"Users with certain permissions can download all files with direct links. No custom links. (Mode 3)",
    4=>"All Direct and Custom links work (Mode 4)",
  ];
  return $modes;
}

function returnDownloadMode($id){
  $modes = dlPluginModes();
  if(array_key_exists($id,$modes)){
    return($modes[$id]);
  }else{
    return "Unknown";
  }
}

function processDownloadLink($id,$mode,$code){
  global $user,$abs_us_root,$us_url_root;
  $db = DB::getInstance();
  $resp = [];
  $plgSet = $db->query("SELECT * FROM plg_download_settings")->first();

  //check some basic parameters that are being passed
  //at this time we'll require login for all downloads. That could change.
  if(!is_numeric($id) || !is_numeric($mode) || $code == ""){
    if($plgSet->dlmode < 2){
      $resp['success'] = false;
      $resp['msg'] = "Invalid Link";
      return $resp;
    }
  }

  if(!isset($user) || !$user->isLoggedIn()){
    $resp['success'] = false;
    $resp['msg'] = "You must be logged in to download this file";
    return $resp;
  }

//This section kills the download based on the MODE
  if($plgSet->dlmode < 2){
    $resp['success'] = false;
    $resp['msg'] = "Downloads are disabled";
    return $resp;
  }

  if($plgSet->dlmode == 3){ //an array of permission levels
    $perms = explode("," , $plgSet->perms);
    foreach($perms as $k=>$v){
      $perms[$k] = trim($v);
      if(!is_numeric($perms[$k])){
        unset($perms[$k]);
      }
    }
    if(!hasPerm($perms,$user->data()->id)){
    $resp['success'] = false;
    $resp['msg'] = "You do not have permission to download this file";
    return $resp;
  }
  }
  //permission has been established
  if($mode == 1){ //direct download

  $fileQ = $db->query("SELECT * FROM plg_download_files WHERE id = ? AND dlcode = ?",[$id,$code]);
  $fileC = $fileQ->count();
  if($fileC < 1){
    $resp['success'] = false;
    $resp['msg'] = "File not found";
    return $resp;
  }else{
    $file = $fileQ->first();
    //one last check to see if the file actually exists
    if(!file_exists($abs_us_root.$us_url_root."usersc/plugins/downloads/files/".$file->location)){
      //let's not delete if from the db in case it's only gone temporarily. We'll disable it. For now.
      //We'll let the error handling below deal with it.
      $db->update("plg_download_files",$file->id,['disabled'=>1]);
      $file->disabled = 1;
    }

    if($file->disabled == 1){
      $resp['success'] = false;
      $resp['msg'] = "This file has been disabled";
      return $resp;
    }else{
      $resp['success'] = true;
      $resp['location'] = $file->location;
      $resp['filename'] = $file->filename;
      $db->update("plg_download_files",$file->id,['downloads'=>$file->downloads+1]);
      return $resp;
    }
  }
  }


  if($mode == 2 && $plgSet->dlmode == 4){
    $resp = checkDownloadLinkRules($id,$code);
    return $resp;
  }

  if($mode == 2 && $plgSet->dlmode < 4) {
  $resp['success'] = false;
  $resp['msg'] = "Custom links do not work in this mode";
  }

  $resp['success'] = false;
  $resp['msg'] = "Unspecified error";
  return $resp;
}

function checkDownloadLinkRules($id,$dlcode){
  global $user,$abs_us_root,$us_url_root;
  $db = DB::getInstance();
  $linkQ = $db->query("SELECT * FROM plg_download_links WHERE id = ? AND dlcode = ?",[$id,$dlcode]);
  $linkC = $linkQ->count();
  $resp = [];

  if($linkC < 1){
    $resp['success'] = false;
    $resp['msg'] = "This link does not exist";
    return $resp;
  }else{
    $link = $linkQ->first();
  }

  if($link->disabled == 1){
    $resp['success'] = false;
    $resp['msg'] = "This link has been disabled";
    return $resp;
  }

  $fileQ = $db->query("SELECT * FROM plg_download_files WHERE id = ? AND disabled = 0",[$link->file]);
  $fileC = $fileQ->count();
  if($fileC < 1){
    $resp['success'] = false;
    $resp['msg'] = "File not found";
    return $resp;
  }else{
    $file = $fileQ->first();
    //one last check to see if the file actually exists
    if(!file_exists($abs_us_root.$us_url_root."usersc/plugins/downloads/files/".$file->location)){
      //let's not delete if from the db in case it's only gone temporarily. We'll disable it. For now.
      //We'll let the error handling below deal with it.
      $resp['success'] = false;
      $resp['msg'] = "File does not exist";
      return $resp;
    }
  }

    if(is_numeric($link->user) && $link->user > 0){
      if($link->user != $user->data()->id){
        $resp['success'] = false;
        $resp['msg'] = "This is not your file";
        return $resp;
      }
    }

    if($link->perms != ""){
    $perms = explode("," , $link->perms);
    foreach($perms as $k=>$v){
      $perms[$k] = trim($v);
      if(!is_numeric($perms[$k])){
        unset($perms[$k]);
      }
    }
    if(!hasPerm($perms,$user->data()->id)){
    $resp['success'] = false;
    $resp['msg'] = "You do not have permission to download use this link";
    return $resp;
    }
  }

    if(is_numeric($link->max) && $link->max > 0){
      if($link->max <= $link->used){
        $resp['success'] = false;
        $resp['msg'] = "This link has reached its download limit";
        return $resp;
      }
    }

    if($link->expires != "0000-00-00 00:00:00" && $link->expires != "" ){
      if($link->expires < date("Y-m-d H:i:s")){
        $resp['success'] = false;
        $resp['msg'] = "This link has expired";
        return $resp;
      }
    }

    $resp['success'] = true;
    $resp['location'] = $file->location;
    $resp['filename'] = $file->filename;
    $db->update("plg_download_files",$file->id,['downloads'=>$file->downloads+1]);
    $db->update("plg_download_links",$link->id,['used'=>$link->used+1]);
    return $resp;
}

function generatePluginDownloadLink($fileid,$uid = "",$perms = "", $max = "", $expires = ""){
  global $user,$abs_us_root,$us_url_root;
  $db = DB::getInstance();
  $resp = [];

  //check to make sure the file is both in the db and the file system
  $fileQ = $db->query("SELECT * FROM plg_download_files WHERE id = ?",[$fileid]);
  $fileC = $fileQ->count();
  if($fileC < 1){
    $resp['success'] = false;
    $resp['msg'] = "File not found in db";
    return $resp;
  }else{
    $file = $fileQ->first();
    if(!file_exists($abs_us_root.$us_url_root."usersc/plugins/downloads/files/".$file->location)){
      $resp['success'] = false;
      $resp['msg'] = "File $file not found in $file->location";
      return $resp;
    }
  }

  //I'm not doing a ton of checks here.  Don't pass it dumb info.
  $fields = array(
    'file'=>$fileid,
    'user'=>$uid,
    'perms'=>$perms,
    'max'=>$max,
    'expires'=>$expires,
    'folder'=>$file->folder,
    'dlcode'=>uniqid().randomstring(7),
  );

  $db->insert("plg_download_links",$fields);
  if($db->error()) {
    $resp['success'] = false;
    $resp['msg'] = "DB Error: ".$db->errorString();
    return $resp;
  }else{
    $resp['success'] = true;
    $resp['msg'] = "";
    return $resp;
  }
}

function getFileLocationFromDLLink($id){
  global $db;
  $q = $db->query("SELECT location FROM plg_download_files WHERE id = ?",[$id]);
  $c = $q->count();
  if($c < 1){
    return "Unknown";
  }else{
    $f = $q->first();
    return $f->location;
  }
}

function getFileLocationFromDLCustomLink($id){
  global $db;
  $a = $db->query("SELECT * FROM plg_download_links WHERE id = ?",[$id]);
  $b = $a->count();

  if($b < 1){
    return "Unknown";
  }else{
    $d = $a->first();
    $id = $d->file;
  }
  $q = $db->query("SELECT location FROM plg_download_files WHERE id = ?",[$id]);
  $c = $q->count();
  if($c < 1){
    return "Unknown";
  }else{
    $f = $q->first();
    return $f->location;
  }
}

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
