<?php
function canIViewPaste(){
  global $pset,$user,$db,$_GET;
  $link = "";
  if(is_array($_GET)){
    $size = count($_GET);
  }else{
    return false;
  }


  if($size < 1){
    return false;
  }


  foreach($_GET as $k=>$v){
    $link = Input::sanitize($k);
    break;
  }
  if($link == "" || $link == null){
    return false;
  }
  if($pset->delete_days == 0){
    $pset->delete_days = 120;
  }
  $delete = date("Y-m-d H:i:s" ,strtotime("+ ".$pset->delete_days." days",strtotime(date("Y-m-d H:i:s"))));
  $q = $db->query("SELECT * FROM plg_spicebin WHERE link = ?",[$link]);
  $c = $q->count();
  if($c < 1){
    return false;
  }

  $paste = $q->first();

  if($paste->private == 0){
    $db->update("plg_spicebin",$paste->id,['delete_on'=>$delete,"views"=>$paste->views + 1]);
    return $paste;
  }elseif($paste->private == 2 && Input::get('code') == $paste->code){
    $db->update("plg_spicebin",$paste->id,['delete_on'=>$delete,"views"=>$paste->views + 1]);
    return $paste;
  }elseif($paste->private > 0 && isset($user) && $user->isLoggedIn() && (hasPerm([2])) || $user->data()->id == $paste->user) {
    $db->update("plg_spicebin",$paste->id,['delete_on'=>$delete,"views"=>$paste->views + 1]);
    return $paste;
  }else{
    return false;
  }

}

function canIPaste(){
  global $pset,$user,$db;
  if($pset->create_privacy == 1 &&
    (!isset($user) || !$user->isLoggedIn())){
      return false;
    }

if(function_exists("hasTag") && $pset->tag !=""){
  if(hasTag($pset->tag,$user->data()->id)){
    return true;
  }
}

  if(is_numeric($pset->perm)){
    if(!hasPerm([$pset->perm])){
      return false;
    }else{
      return true;
    }
  }
  //nothing failed, so allow paste
  return true;
}

function echoPasteCode(){
  global $string;
  highlight_string($string);
}

function binLangDropdown($pset, $sel = ""){
  $db = DB::getInstance();
  $langs = $db->query("SELECT * FROM plg_spicebin_lang ORDER BY common DESC, lang ASC")->results();
  ?>
  <select class="form-control" name="lang">
    <option value="">Just a <?=$pset->product_single?></option>
    <?php foreach($langs as $l) {
      switch ($l->lang) {
      case "clike":
        $term = "c";
        break;
      case "htmlmixed":
        $term = "html";
        break;
      default:
        $term = $l->lang;
    }
      ?>
      <option value="<?=$l->lang?>" <?php if($sel == $l->lang){ echo "selected = 'selected'";} ?>><?=strtoupper($term)?></option>
    <?php } ?>
  </select>
  <?php
}
