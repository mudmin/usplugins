<?php
if (!function_exists('displayBadges')) {
  function displayBadges($user_id, $size = "25px")
  {
      global $abs_us_root, $us_url_root, $db;
      $q = $db->query("SELECT * FROM plg_badges_match WHERE user_id = ?", [$user_id]);
      $c = $q->count();

      if ($c > 0) {
          $badges = $q->results();
          $badgeids = array_column($badges, 'badge_id');
          $placeholders = implode(',', array_fill(0, $c, '?'));
          $badges = $db->query("SELECT * FROM plg_badges WHERE id IN ($placeholders)",$badgeids)->results();
      
          foreach ($badges as $b) {
              $badgeImagePath = $abs_us_root . $us_url_root . "usersc/plugins/badges/files/" . $b->id . ".png";
              if (file_exists($badgeImagePath)) {
                  ?>
                  <img src="<?= $us_url_root . "usersc/plugins/badges/files/" . $b->id . ".png" ?>"
                       title="<?= $b->badge ?>" alt="<?= $b->badge ?>" height="<?= $size ?>">
                  <?php
              }
          }
      }
  }
}


if(!function_exists("manageBadge")){
  function manageBadge($user_id,$badge,$action="give"){
    $db = DB::getInstance();
    $return = [];

    $c = $db->query("SELECT id FROM users WHERE id = ?",[$user_id])->count();
    if($c < 1){
      $return['success'] = false;
      $return['msg'] = "User does not exist";
      return $return;
    }

    if(is_numeric($badge)){
      $q = $db->query("SELECT id FROM plg_badges WHERE id = ?",[$badge]);
      $c = $q->count();
      if($c < 1){
        $return['success'] = false;
        $return['msg'] = "Badge does not exist";
        return $return;
      }else{
        $f = $q->first();
        $badge_id = $f->id;
      }
    }else{
      $q = $db->query("SELECT id FROM plg_badges WHERE badge = ?",[$badge]);
      $c = $q->count();
      if($c < 1){
        $return['success'] = false;
        $return['msg'] = "Badge does not exist";
        return $return;
      }else{
        $f = $q->first();
        $badge_id = $first->id;
      }
    }

    if($action == "give"){
      $c = $db->query("SELECT id FROM plg_badges_match WHERE user_id = ? AND badge_id = ?",[$user_id,$badge])->count();

      if($c > 0){
        $return['success'] = false;
        $return['msg'] = "User already has this badge";
        return $return;
      }else{
        $db->insert("plg_badges_match",['badge_id'=>$badge_id,'user_id'=>$user_id]);
        $return['success'] = true;
        $return['msg'] = "User was awarded a badge";
        return $return;
      }
    }

    if($action == "take"){
      $db->query("DELETE FROM plg_badges_match WHERE user_id = ? AND badge_id = ?",[$user_id,$badge_id]);
      $return['success'] = true;
      $return['msg'] = "Badge removed from user";
      return $return;
    }
  }
}

if(!function_exists('countBadges')){
  function countBadges($user_id){
    $db = DB::getInstance();

    $q = $db->query("SELECT * FROM plg_badges_match WHERE user_id = ?",[$user_id]);
    $c = $q->count();
    $return = 0;
    if($c > 0){
      $return = $c;
    }
    return $return;
  }
}

if(!function_exists("hasBadge")){
  function hasBadge($user_id,$badge){
    $db = DB::getInstance();

    $c = $db->query("SELECT id FROM plg_badges_match WHERE user_id = ? AND badge_id = ?",[$user_id,$badge])->count();
    if($c > 0){
      return true;
    } else {
      return false;
    }
  }
}
