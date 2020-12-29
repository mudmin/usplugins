<?php

// Also, please wrap your functions in if(!function_exists())
if(!function_exists('canMakePlgLinks')) {
  function canMakePlgLinks(){
    global $user,$db;
    $lsettings = $db->query("SELECT * FROM plg_links_settings WHERE id = 1")->first();
    $can = false;
    $perms = explode(",",$lsettings->perms);
    foreach($perms as $p){
      if(hasPerm([$p],$user->data()->id)){
        $can = true;
      }
    }
    return $can;
  }
}

if(!function_exists('generatePluginLink')) {
  function generatePluginLink($id){
    global $db;
    $q = $db->query("SELECT * FROM plg_links WHERE id = ?",[$id]);
    $c = $q->count();
    if($c > 0){
      $f = $q->first();
      $lsettings = $db->query("SELECT * FROM plg_links_settings WHERE id = 1")->first();
      $parser = substr($lsettings->parser_location,-9);
      if($parser == "index.php"){
        $parser = strtok($lsettings->parser_location, 'index.php');
      }else{
        $parser = $lsettings->parser_location;
      }
      $url = $lsettings->base_url."/".$parser."?".$f->link_name;
      return $url;
    }else{
      return "#";
    }
  }
}

if(!function_exists('linkNameFromId')) {
  function linkNameFromId($id){
    global $db;
    $q = $db->query("SELECT * FROM plg_links WHERE id = ?",[$id]);
    $c = $q->count();
    if($c > 0){
      $f = $q->first();
      return $f->link_name;
    }else{
      return "Unknown";
    }
  }
}
