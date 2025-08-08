<?php
include "../../../../users/init.php";
$db = DB::getInstance();
$settings = $db->query("SELECT * FROM settings")->first();
include "../plugin_info.php";
$check = $db->query("SELECT id FROM us_plugins WHERE plugin = ? and status = ?",array($plugin_name,"active"))->count();
if($check < 1){
  die("Plugin disabled");
}
if(!in_array($user->data()->id, $master_account)){
  die("Permission denied");
}
$type = Input::get('type');
$url = Input::get('url');
$hash = Input::get('hash');
$api = "https://bugs.userspice.com/api/v2/";
// dump($_POST);
	$zipFile = "temp.zip";
	if($type == 'plugin'){
		$extractPath = "../../../plugins";
    $return = $us_url_root."users/admin.php?view=plugins";
	}elseif($type == 'widget'){
		$extractPath = "../../../widgets";
    $return = $us_url_root."users/admin.php";
	}elseif($type == 'template'){
		$extractPath = "../../../templates";
    $return = $us_url_root."users/admin.php?view=templates";
	}elseif($type == 'translation'){
		$extractPath = "../../../../users/lang";
    $return = $us_url_root."users/admin.php?err=Language(s)+Installed.";
	}else{
		die("Something's wrong");
	}

  	$zip_resource = fopen($zipFile, "w");

  	$ch_start = curl_init();
  	curl_setopt($ch_start, CURLOPT_URL, $url);
  	curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
  	curl_setopt($ch_start, CURLOPT_HEADER, 0);
  	curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
  	curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
  	curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
  	curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
  	curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
  	curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
  	curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
  	$page = curl_exec($ch_start);
  	if(!$page)
  	{
  	 echo "Error :- ".curl_error($ch_start);
  	}
  	curl_close($ch_start);

  	$zip = new ZipArchive;

  	if($zip->open($zipFile) != "true")
  	{
  	 echo "Error :- Unable to open the Zip File";
  	}
  	$newCrc = base64_encode(hash_file("sha256",$zip->filename));

    //we are going to recheck the api from inside the parser to confirm that nothing has been edited in the javascript

      //create a new cURL resource
      $ch = curl_init($api);
        $data = array(
          'key' => $settings->spice_api,
          'type' => $type,
          'call' => 'recheck',
          'url' => $url,
      );

      $payload = json_encode($data);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      $result = substr($result,1,-1);
      curl_close($ch);

  	if($newCrc == $hash && $newCrc == $result){ //Note that we are checking the hash against the api call and the one supplied by ajax
      $zip->extractTo($extractPath);
      $zip->close();
      $msg = [];
      $msg['success'] = true;
      $msg['url'] = $return;
      echo json_encode($msg);
    }else{
      $msg = [];
      $msg['success'] = false;
      $msg['error'] = "The hash does not match.  This means one of 2 things. Either the file on the server has been tampered with or (more likely) the file was
       updated and we forgot to update the hash.  Please fill out a bug report. You can still download this plugin at $url if you wish.";

      echo json_encode($msg);
    }


?>
