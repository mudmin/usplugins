<?php
require_once '../../../users/init.php';
$db = DB::getInstance();

$ip = ipCheck();
$q = $db->query("SELECT * FROM us_ip_whitelist WHERE ip = ?",[$ip])->count();
if($q < 1){
  logger(1,"uptimeIP","$ip tried to call the uptime trigger");
  die;
}
if(!pluginActive("uptime",true)){
  die("Plugin not active");
}
$upset = $db->query("SELECT * FROM plg_uptime_settings")->first();

$sites = $db->query("SELECT * FROM plg_uptime WHERE disabled = 0")->results();
$notifs = [];
$counter = 0;
foreach($sites as $s){
  $db->update("plg_uptime",$s->id,['last_check'=>date("Y-m-d H:i:s")]);

  $sendNotif = false;
  $remoteFile = $s->url;
  $diag = Input::get('diag');
  if($diag == "true"){
    logger(1,"Uptime","Attempting ".$s->url);
  }

  // Open file
  $handle = @fopen($remoteFile, 'r');
  // Check if file exists
  if(!$handle){
       $ct = strtotime(date("Y-m-d H:i:s"));
      //Site is down!
      //did we know this already and if so
      if($s->notified_down == ""){ //we didn't know
        $db->update("plg_uptime",$s->id,['notif_down'=>date("Y-m-d H:i:s")]);
        $sendNotif = true;
        $notifs[$s->site]['msg'] = "Site is DOWN";
        $counter++;
        $db->update("plg_uptime",$s->id,['first_down'=>date("Y-m-d H:i:s")]);
      }else{
        //ok, so we already knew it was down, but is it time to re-notify?
        $minutes = ((strtotime($s->notified_down) - time()) / 60)*-1;
        if($minutes >= $upset->notify_every){
          $db->update("plg_uptime",$s->id,['notif_down'=>date("Y-m-d H:i:s")]);
          $sendNotif = true;
          $notifs[$s->site]['msg'] = "Site is still DOWN";
          $counter++;
        }
      }
  }else{
      if($s->first_down != ""){
        //site was previously down, so we need to check if it's fully back up

        if($s->ustarget != 1){
        //this is not a userspice site.
        //it's a site that was down and is now back up, so we can send notifications
        $minutes = round( ( (strtotime($s->first_down) - time()) / 60)*-1);
        //we're going to store the amount of time the site was down in a log
        $db->insert("plg_uptime_downtime",['site'=>$s->id,'downtime'=>$minutes]);
        $db->update('plg_uptime',$s->id,['first_down'=>'','notified_down'=>'']);
        $sendNotif = true;
        $notifs[$s->site]['msg'] = "Site is back up after $minutes minutes";
        $counter++;

      }else{
        //userspice site. Let's also check if mysql is up.
        $ch = curl_init($remoteFile);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

      if(strpos($result, "SQLSTATE") !== false || strpos($result, "Error establishing a database") !== false  ){
          //sql is still down, but is it time to re-notify?
          $minutes = round( ( (strtotime($s->notified_down) - time()) / 60)*-1);

          if($minutes >= $upset->notify_every){
            $db->update("plg_uptime",$s->id,['notif_down'=>date("Y-m-d H:i:s")]);
            $sendNotif = true;
            $notifs[$s->site]['msg'] = "Your server appears to be up but SQL is still DOWN";
            $counter++;
          }
        }else{
          //sql is back up!
          $minutes = ((strtotime($s->first_down) - time()) / 60)*-1;
          //we're going to store the amount of time the site was down in a log
          $db->insert("plg_uptime_downtime",['site'=>$s->id,'downtime'=>$minutes]);
          $db->update('plg_uptime',$s->id,['first_down'=>'','notified_down'=>'']);
          $sendNotif = true;
          $notifs[$s->site]['msg'] = "Site is back up after $minutes minutes. SQL has also been verified.";
          $counter++;
        }
      }
      //so at this point we have handled if normal sites are up and down.
      //we've also taken care of any sites that were down, but are now up.
      //the only thing we need to do is to handle sites that were up and are still up
      //and check for userspice sites that are up, but have mysql down.
    }elseif(!isset($notifs[$s->site]) && $s->ustarget == 1){
        //This is a userspice site that has not triggered another notification
        $ch = curl_init($remoteFile);
    		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    		$result = curl_exec($ch);
    		curl_close($ch);

      if(strpos($result, "SQLSTATE") !== false || strpos($result, "Error establishing a database") !== false  ){
          $sendNotif = true;
          $notifs[$s->site]['msg'] = "SQL is DOWN";
          $counter++;
          $db->update("plg_uptime",$s->id,['first_down'=>date("Y-m-d H:i:s")]);
    		}else{
          //let's just save this site data while we have it
          $result = json_decode($result);
          if(isset($result->usver) && isset($result->phpver)){
            $fields = [
              'usver'=>Input::sanitize($result->usver),
              'phpver'=>Input::sanitize($result->phpver),
            ];
            $db->update('plg_uptime',$s->id,$fields);
          }
        }
      } //end special instructions for reachable userspice sites
  } //end site is reachable
} //end foreach sites

//do notifications need to be sent?

$string = "";
$html = "Uptime has the following important notifications for you.<br>";
foreach($notifs as $k=>$v){
  $q = $db->query("SELECT * FROM plg_uptime WHERE site = ?",[$k]);
  $c = $q->count();
  if($c < 1){
    logger(1,'uptimeError',"Trying to notify a site $k that does not exist");
  }else{
    $f = $q->first();
    $string .= $f->site."(".$f->url.") - ".$v['msg'];
    $html .= $f->site."(".$f->url.") - ".$v['msg']."<br>";
  }
}

$send = $db->query("SELECT * FROM plg_uptime_notifications")->results();
$settings = $db->query("SELECT * FROM settings")->first();
$email = $db->query("SELECT * FROM email")->first();
foreach($send as $s){
  if($sendNotif){ //make sure there's something to send
    if($s->method == "pushover" && pluginActive('pushover',true)){
      pushoverNotification($settings->plg_po_key,$string);
    }

    if($s->method == "email"){
      email($s->target,"Important Server Uptime Notification",$html);
    }
  }
}
