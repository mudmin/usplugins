<?php
if(!function_exists('forumAccess')) {
  function forumAccess($board,$type,$uid){
    $db = DB::getInstance();
    $access = false;
    if($type != "write" && $type != "read"){
      return false;
    }
    if($type == "write" && $uid == 0){
      return false;
    }
    $type = "to_".$type;

    $checkQ = $db->query("SELECT id, $type FROM forum_boards WHERE id = ?",[$board]);
    $checkC = $checkQ->count();

    if($checkC < 1){
      return false;
    }else{
      $check = $checkQ->first();

      $perms = explode(",",$check->$type);
      foreach($perms as $k=>$v){
        if($v == ""){
          unset($perms[$k]);
        }
      }
      if($type == "to_read" && in_array(0,$perms)){
          return true;
      }

      foreach($perms as $p){
        if($p != ""){
            if(hasPerm([$p],$uid)){
            $access = true;
          }
        }
      }

      return $access;

    }
  }
}

if(!function_exists('forumCount')) {
  function forumCount($id,$type){
    $db = DB::getInstance();
    $type = "forum_".$type;
    if($type == "threads"){
        $count = $db->query("SELECT id FROM $type WHERE board = ? AND deleted = 0",[$id])->count();
    }elseif($type == "messages"){
        $count = $db->query("SELECT id FROM $type WHERE board = ? AND disabled = 0",[$id])->count();
    }else{
      $count = 0;
    }

    return $count;
}
}

if(!function_exists('forumLastPost')) {
  function forumLastPost($id,$type){
    $db = DB::getInstance();
    $msg = [];
    if($type == "boards"){
        $checkQ = $db->query("SELECT id,thread,title,created_on FROM forum_messages WHERE board = ? ORDER BY id DESC LIMIT 1",[$id]);
    }elseif($type == "threads"){
        $checkQ = $db->query("SELECT id,thread,title,created_on FROM forum_messages WHERE thread = ? ORDER BY id DESC LIMIT 1",[$id]);
    }

    $checkC = $checkQ->count();
    if($checkC > 0){
      $check = $checkQ->first();
      //more options can come here later
      $msg['id'] = $check->id;
      $msg['title'] = $check->title;
      $msg['thread'] = $check->thread;
      $msg['date'] = $check->created_on;
      return $msg;
    }else{
      $msg['id'] = 0;
      $msg['date'] = "Never";
    }
      return $msg;
}
}
