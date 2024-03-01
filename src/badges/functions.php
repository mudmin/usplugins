<?php
if (!function_exists('displayBadges')) {

  //categories 2+ are for "teams" not individuals even though we're using the legacy "user_id" field
  function displayBadges($user_id, $size = "25px", $category = 1)
  {
      global $abs_us_root, $us_url_root, $db;
      $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
      if($category < 1) { //use the default logic
        $category = 1;
      }
 
      $q = $db->query("SELECT pb.* FROM plg_badges_match pbm
      JOIN plg_badges pb ON pbm.badge_id = pb.id
      WHERE pbm.user_id = ? AND pbm.cat_id = ?", [$user_id, $category]);

      $c = $q->count();
  
      if ($c > 0) {
          $badges = $q->results(); 
          foreach ($badges as $b) {
              $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . $b->id . ".png";
              if (file_exists($badgeImagePath)) {
                  ?>
                  <img src="<?= $us_url_root . $plgset->badge_location  . $b->id . ".png" ?>"
                       title="<?= $b->badge ?>" alt="<?= $b->badge ?>" height="<?= $size ?>">
                  <?php
              }
          }
      }
    }
  }


if(!function_exists("displayAllBadges")){
  function displayAllBadges($user_id, $size = "25px")
  {
    displayBadges($user_id, $size);
    displayPermBadges($user_id, $size);
    displayTagBadges($user_id, $size);
    }
  }


if(!function_exists("displayPermBadges")){
function displayPermBadges($user_id, $size = "25px")
{
    global $abs_us_root, $us_url_root, $db;
    $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
  
    $badges = $db->query("SELECT 
    m.*,
    p.name
    FROM user_permission_matches m 
    LEFT JOIN permissions p ON m.permission_id = p.id
    WHERE m.user_id = ?",[$user_id])->results();

    
        foreach ($badges as $b) {
            $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . "perm_" .$b->permission_id . ".png";
            if (file_exists($badgeImagePath)) {
                ?>
                <img src="<?= $us_url_root . $plgset->badge_location . "perm_" .$b->permission_id . ".png"?>"
                     title="<?= $b->name ?>" alt="<?= $b->name ?>" height="<?= $size ?>">
                <?php
            }
        }
    }
  }

  if(!function_exists("displayTagBadges")){
    function displayTagBadges($user_id, $size = "25px")
    {
        global $abs_us_root, $us_url_root, $db;
        $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
      
        $badges = $db->query("SELECT 
        m.*
        FROM plg_tags_matches m 
        WHERE m.user_id = ?",[$user_id])->results();
    
        
            foreach ($badges as $b) {
                $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . "tag_" .$b->tag_id . ".png";
                if (file_exists($badgeImagePath)) {
                    ?>
                    <img src="<?= $us_url_root . $plgset->badge_location . "tag_" .$b->tag_id . ".png"?>"
                         title="<?= $b->tag_name ?>" alt="<?= $b->tag_name ?>" height="<?= $size ?>">
                    <?php
                }
            }
        }
      
  }


if(!function_exists("manageBadge")){
  function manageBadge($user_id,$badge,$action="give", $category = 1){
    $db = DB::getInstance();
    $return = [];
    if($category < 1){
      $category = 1;
    }
    if($category < 2){
    $c = $db->query("SELECT id FROM users WHERE id = ?",[$user_id])->count();
    if($c < 1){
      $return['success'] = false;
      $return['msg'] = "User does not exist";
      return $return;
    }
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
        $badge_id = $f->id;
      }
    }

    if($action == "give"){
      $c = $db->query("SELECT id FROM plg_badges_match WHERE user_id = ? AND badge_id = ? AND cat_id = ?",[$user_id,$badge,$category])->count();

      if($c > 0){
        $return['success'] = false;
        $return['msg'] = "User already has this badge";
        return $return;
      }else{
        $db->insert("plg_badges_match",['badge_id'=>$badge_id,'user_id'=>$user_id, 'cat_id' => $category]);
        $return['success'] = true;
        $return['msg'] = "User was awarded a badge";
        return $return;
      }
    }

    if($action == "take"){
      $db->query("DELETE FROM plg_badges_match WHERE user_id = ? AND badge_id = ? AND cat_id = ?",[$user_id,$badge_id,$category]);
      $return['success'] = true;
      $return['msg'] = "Badge removed from user";
      return $return;
    }
  }
}

if(!function_exists('countBadges')){
  function countBadges($user_id){
    $db = DB::getInstance();

    $q = $db->query("SELECT * FROM plg_badges_match WHERE user_id = ? AND cat_id = 0",[$user_id]);
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

    $c = $db->query("SELECT id FROM plg_badges_match WHERE user_id = ? AND badge_id = ? and cat_id = 0",[$user_id,$badge])->count();
    if($c > 0){
      return true;
    } else {
      return false;
    }
  }
}
