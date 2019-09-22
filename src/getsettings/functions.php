<?php
function getSettings($id = 1) {
  global $user;
  if($user->isLoggedIn()) {
    $userId = $user->data()->id;
  } else {
    $userId = 1;
  }
  $db = DB::getInstance();
  $settings = $db->query("SELECT * FROM settings WHERE id = ?",[$id]);
  if(!$db->error()) {
    if($settings->count()==1) {
      return $settings->first();
    } else {
      logger($userId,"getSettings","Settings were requested for ID #".$id." but none could be retrieved");
      return false;
    }
  } else {
    logger($userId,"getSettings","Unable to retrieve Settings ID#".$id.", Error: ".$db->errorString());
    return false;
  }
}
