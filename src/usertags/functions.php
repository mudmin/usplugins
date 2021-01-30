<?php
//checks if a user has a tag by either tag id or tag name (case sensitive)
if(!function_exists("hasTag")){
  function hasTag($tag,$user_id){

    $db = DB::getInstance();
    if(!is_numeric($user_id) || $user_id < 0 || $user_id == ""){
      return false;
    }

    if(is_numeric($tag)){
      $c = $db->query("SELECT * FROM plg_tags_matches WHERE tag_id = ? AND user_id = ?",[$tag,$user_id])->count();
      if($c < 1){
        return false;
      }else{
        return true;
      }
    }else{
      $c = $db->query("SELECT * FROM plg_tags_matches WHERE tag_name = ? AND user_id = ?",[$tag,$user_id])->count();
      if($c < 1){
        return false;
      }else{
        return true;
      }
    }
  return false;
}
}

//returns an array of users with a given tag by tag name or id
if(!function_exists("usersWithTag")){
  function usersWithTag($tag){
  $db = DB::getInstance();
  $users = [];
  if(is_numeric($tag)){
    $q = $db->query("SELECT user_id FROM plg_tags_matches WHERE tag_id = ?",[$tag])->results();
    foreach($q as $t){
      $users[] = $t->user_id;
    }
    return $users;
  }else{
    $q = $db->query("SELECT user_id FROM plg_tags_matches WHERE tag_name = ?",[$tag])->results();
    foreach($q as $t){
      $users[] = $t->user_id;
    }
    return $users;
  }
  return $users;
}
}
