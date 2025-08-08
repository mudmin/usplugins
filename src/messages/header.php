<?php
//Please don't load code on the header of every page if you don't need it on the header of every page.
// bold("<br>Demo Header.php Loaded");
if(($settings->messaging == 1) && ($user->isLoggedIn())){
  $msgQ = $db->query("SELECT id FROM messages WHERE msg_to = ? AND msg_read = 0 AND deleted = 0",array($user->data()->id));
  $msgC = $msgQ->count();
  if($msgC == 1){
    $grammar = 'Message';
  }else{
    $grammar = 'Messages';
  }
}
