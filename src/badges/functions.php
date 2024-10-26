<?php
if (!function_exists('displayBadges')) {

  //categories 2+ are for "teams" not individuals even though we're using the legacy "user_id" field
  function displayBadges($user_id, $size = "25px", $category = 1, $link = false, $blank = false)
  {
    global $abs_us_root, $us_url_root, $db;
    $target = parseBadgeTarget($blank);
    $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
    if ($category < 1) { //use the default logic
      $category = 1;
    }

    $q = $db->query("SELECT pb.* FROM plg_badges_match pbm
      JOIN plg_badges pb ON pbm.badge_id = pb.id
      WHERE pbm.user_id = ? AND pbm.cat_id = ?", [$user_id, $category]);

    $c = $q->count();

    if ($c > 0) {
      $badges = $q->results();
      foreach ($badges as $b) {
        $badgelink = appendBadgeLink($link, $b->id);
        $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . $b->id . ".png";
        if (file_exists($badgeImagePath)) {
          if($badgelink !== false){ ?>
            <a href='<?=$badgelink?>&type=badge&category=<?=$category?>' <?=$target?>>
         <?php  } ?>        
          <img src="<?= $us_url_root . $plgset->badge_location  . $b->id . ".png" ?>" title="<?= $b->badge ?>" alt="<?= $b->badge ?>" height="<?= $size ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true">
        <?php
        if($badgelink !== false){ ?>
          </a>
        <?php 
        }
        }
      }
    }
  }
}


if (!function_exists("displayAllBadges")) {
  function displayAllBadges($user_id, $size = "25px", $link = false, $blank = false)
  { 
    displayBadges($user_id, $size, 1, $link, $blank);
    displayPermBadges($user_id, $size, $link, $blank);
    displayTagBadges($user_id, $size, $link, $blank);
  }
}


if (!function_exists("displayPermBadges")) {
  function displayPermBadges($user_id, $size = "25px", $link = false, $blank = false)
  {
    global $abs_us_root, $us_url_root, $db;
    $target = parseBadgeTarget($blank);
    $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();

    $badges = $db->query("SELECT 
    m.*,
    p.name
    FROM user_permission_matches m 
    LEFT JOIN permissions p ON m.permission_id = p.id
    WHERE m.user_id = ?", [$user_id])->results();


    foreach ($badges as $b) {
      $badgelink = appendBadgeLink($link, $b->permission_id);
      $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . "perm_" . $b->permission_id . ".png";
      if (file_exists($badgeImagePath)) {
        if($badgelink !== false){ ?>
          <a href='<?=$badgelink?>&type=perm' <?=$target?>>
        <?php  } ?>

        <img src="<?= $us_url_root . $plgset->badge_location . "perm_" . $b->permission_id . ".png" ?>" title="<?= $b->name ?>" alt="<?= $b->name ?>" height="<?= $size ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true">
      <?php
      if($badgelink !== false){ ?>
        </a>
      <?php
      } 
      }
    }
  }
}

if(!function_exists("fetchPermBadges")){
  function fetchPermBadges($user_id, $size = "25px", $link = false, $blank = false) {
    global $abs_us_root, $us_url_root, $db;
    $target = parseBadgeTarget($blank);
    $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
    $badges = []; // Array to store badge data

    $userPerm = $db->query("SELECT 
        m.*,
        p.name
        FROM user_permission_matches m 
        LEFT JOIN permissions p ON m.permission_id = p.id
        WHERE m.user_id = ?", [$user_id])->results();

    foreach ($userPerm as $b) {
        $badgelink = appendBadgeLink($link, $b->permission_id);
        $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . "perm_" . $b->permission_id . ".png";

        if (file_exists($badgeImagePath)) {
            // Store badge data
            $badges[] = [
                'id' => $b->permission_id,
                'name' => $b->name,
                'path' => $badgeImagePath,
                'link' => $badgelink
            ];   
        }
    }
    return $badges; 
}
}

if (!function_exists("displayTagBadges")) {
  function displayTagBadges($user_id, $size = "25px", $link = false, $blank = false)
  {
    global $abs_us_root, $us_url_root, $db;
    $target = parseBadgeTarget($blank);
    $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();

    $badges = $db->query("SELECT 
        m.*
        FROM plg_tags_matches m 
        WHERE m.user_id = ?", [$user_id])->results();


    foreach ($badges as $b) {
      $badgelink = appendBadgeLink($link, $b->tag_id);
      $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . "tag_" . $b->tag_id . ".png";
      if (file_exists($badgeImagePath)) {
        if($badgelink !== false){ ?>
          <a href='<?=$badgelink?>&type=tag' <?=$target?>>
        <?php  }
      ?>
        <img src="<?= $us_url_root . $plgset->badge_location . "tag_" . $b->tag_id . ".png" ?>" title="<?= $b->tag_name ?>" alt="<?= $b->tag_name ?>" height="<?= $size ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true">
<?php
      if($badgelink !== false){ ?>
        </a>
      <?php
      }
      }
    }
  }
}

if(!function_exists("fetchTagBadges")){
  function fetchTagBadges($user_id, $size = "25px", $link = false, $blank = false) {
    global $abs_us_root, $us_url_root, $db;
    $target = parseBadgeTarget($blank);
    $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
    $badges = []; // Array to store badge data

    $userTags = $db->query("SELECT 
            m.*
            FROM plg_tags_matches m 
            WHERE m.user_id = ?", [$user_id])->results();

    foreach ($userTags as $b) {
        $badgelink = appendBadgeLink($link, $b->tag_id);
        $badgeImagePath = $abs_us_root . $us_url_root . $plgset->badge_location . "tag_" . $b->tag_id . ".png";

        if (file_exists($badgeImagePath)) {
            // Store badge data
            $badges[] = [
                'id' => $b->tag_id,
                'name' => $b->tag_name, 
                'path' => $badgeImagePath,
                'link' => $badgelink
            ];   
        }
    }
    return $badges; 
}

}


if (!function_exists("manageBadge")) {
  function manageBadge($user_id, $badge, $action = "give", $category = 1)
  {
    $db = DB::getInstance();
    $return = [];
    if ($category < 1) {
      $category = 1;
    }
    if ($category < 2) {
      $c = $db->query("SELECT id FROM users WHERE id = ?", [$user_id])->count();
      if ($c < 1) {
        $return['success'] = false;
        $return['msg'] = "User does not exist";
        return $return;
      }
    }
    if (is_numeric($badge)) {
      $q = $db->query("SELECT id FROM plg_badges WHERE id = ?", [$badge]);
      $c = $q->count();
      if ($c < 1) {
        $return['success'] = false;
        $return['msg'] = "Badge does not exist";
        return $return;
      } else {
        $f = $q->first();
        $badge_id = $f->id;
      }
    } else {
      $q = $db->query("SELECT id FROM plg_badges WHERE badge = ?", [$badge]);
      $c = $q->count();
      if ($c < 1) {
        $return['success'] = false;
        $return['msg'] = "Badge does not exist";
        return $return;
      } else {
        $f = $q->first();
        $badge_id = $f->id;
      }
    }

    if ($action == "give") {
      $c = $db->query("SELECT id FROM plg_badges_match WHERE user_id = ? AND badge_id = ? AND cat_id = ?", [$user_id, $badge, $category])->count();

      if ($c > 0) {
        $return['success'] = false;
        $return['msg'] = "User already has this badge";
        return $return;
      } else {
        $db->insert("plg_badges_match", ['badge_id' => $badge_id, 'user_id' => $user_id, 'cat_id' => $category]);
        $return['success'] = true;
        $return['msg'] = "User was awarded a badge";
        return $return;
      }
    }

    if ($action == "take") {
      $db->query("DELETE FROM plg_badges_match WHERE user_id = ? AND badge_id = ? AND cat_id = ?", [$user_id, $badge_id, $category]);
      $return['success'] = true;
      $return['msg'] = "Badge removed from user";
      return $return;
    }
  }
}

if (!function_exists('countBadges')) {
  function countBadges($user_id)
  {
    $db = DB::getInstance();

    $q = $db->query("SELECT * FROM plg_badges_match WHERE user_id = ? AND cat_id = 0", [$user_id]);
    $c = $q->count();
    $return = 0;
    if ($c > 0) {
      $return = $c;
    }
    return $return;
  }
}

if (!function_exists("hasBadge")) {
  function hasBadge($user_id, $badge)
  {
    $db = DB::getInstance();

    $c = $db->query("SELECT id FROM plg_badges_match WHERE user_id = ? AND badge_id = ? and cat_id = 0", [$user_id, $badge])->count();
    if ($c > 0) {
      return true;
    } else {
      return false;
    }
  }
}


if(!function_exists("fetchAllValidBadges")){

//this function takes an array of categories and checks for all the badges 
//which are both in the db and have the file in the file system
function fetchAllValidBadges($cats = [])
{
  global $db, $abs_us_root, $us_url_root;
  $plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
  $valid = [];
  //convert array to comma separated string
  $count = count($cats);
  if ($count < 1) {
    return $valid;
  }
  $list = implode(',', array_fill(0, $count, '?'));

  $badges = $db->query("SELECT * FROM plg_badges WHERE cat_id IN (" . $list . ")", $cats)->results();

  //sort badges into cat_id and make sure the file exists
  foreach ($badges as $b) {

    $badgeImagePath = $us_url_root . $plgset->badge_location . $b->id . ".png";

    $fullPath = $abs_us_root . $badgeImagePath;
    if (file_exists($fullPath)) {
      // dump("exists");
      $valid[$b->cat_id][] = ['id' => $b->id, 'name' => $b->badge, 'path' => $badgeImagePath, 'cat_id' => $b->cat_id];
    }
    // else{
    //   dump("does not exist");
    // }
  }

  return $valid;
}
}

if(!function_exists("appendBadgeLink")){
function appendBadgeLink($link, $badge_id){
  if($link != false){
    //if link contains a ? add an & to the end
    if(strpos($link, "?") !== false){
      $link .= "&";
    }
    //if link does not contain a ? add it to the end
    if(strpos($link, "?") === false){
      $link .= "?";
    }
  }
  if($link == "" || $link == "#"){
    $link = false;
  }
  if($link != false){
    $link .= "badge=" . $badge_id;
  }
  return $link;
}
}


if(!function_exists("displayPossibleBadges")){
  function displayPossibleBadges($category, $user_id, $size = "25px", $link = false, $blank = false) {
    global $abs_us_root, $us_url_root, $db;
    $target = parseBadgeTarget($blank);
  
   
    // 1. Fetch possible badges for the category 
    $possibleBadges = fetchAllValidBadges([$category]); 
    
    // Include permission and tag badges for category 1
    if($category == 1){
      $permBadges = fetchPermBadges($user_id, $size, $link); // Fetch permission badges
      $tagBadges = fetchTagBadges($user_id, $size, $link); // Fetch tag badges

      // Convert perm and tag badges to the same format as possible badges and mark them as owned
      foreach($permBadges as $permBadge){
        $possibleBadges[1][] = [
          'id' => "perm_" . $permBadge['id'], // Distinguish perm badges with a prefix
          'name' => $permBadge['name'],
          'path' => str_replace($abs_us_root, '', $permBadge['path']),
          'cat_id' => 1, // Assuming permission badges are part of category 1
          'owned' => true // Mark as owned
        ];
      }
      
      foreach($tagBadges as $tagBadge){
        $possibleBadges[1][] = [
          'id' => "tag_" . $tagBadge['id'], // Distinguish tag badges with a prefix
          'name' => $tagBadge['name'],
          'path' => str_replace($abs_us_root, '', $tagBadge['path']),
          'cat_id' => 1, // Assuming tag badges are part of category 1
          'owned' => true // Mark as owned
        ];
      }
    }

    // 2. Check which badges the user already has
    $userBadges = $db->query("SELECT badge_id FROM plg_badges_match WHERE user_id = ? AND cat_id = ?", [$user_id, $category])->results(); 
    $userBadgeIds = array_column($userBadges, 'badge_id'); // Extract badge IDs for easy comparison

    // Separate badges into two arrays: ones the user has and ones the user does not have
    $ownedBadges = [];
    $unownedBadges = [];

    // 3. Sort badges based on ownership and display them
    if(isset($possibleBadges[$category])){
    foreach($possibleBadges[$category] as $badge) { 
      if(in_array($badge['id'], $userBadgeIds) || !empty($badge['owned'])){
        // If badge is marked as owned or the ID is found in userBadgeIds, it's an owned badge
        $ownedBadges[] = $badge;
      } else {
        $unownedBadges[] = $badge;
      }
    }
  }

    // Merge owned badges first, followed by unowned badges
    $sortedBadges = array_merge($ownedBadges, $unownedBadges);

    foreach($sortedBadges as $badge) {
      $badgelink = appendBadgeLink($link, $badge['id']);
      $badgeImagePath = $badge['path'];

      // Check if badge is owned to decide on grayscale filter
      $filter = (in_array($badge['id'], $userBadgeIds) || !empty($badge['owned'])) ? "" : "style='filter: grayscale(100%);'";
      
      if($badgelink !== false){ ?>
        <a href='<?=$badgelink?>&type=badge&category=<?=$category?>' <?=$target?>>
      <?php } ?> 
      <img src="<?= $badgeImagePath ?>" title="<?= $badge['name'] ?>" alt="<?= $badge['name'] ?>" height="<?= $size ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" <?= $filter ?>>
      <?php if($badgelink !== false){ ?>
        </a>
      <?php }
    }
  }
}


if(!function_exists("parseBadgeTarget")){
  function parseBadgeTarget($target){
    if($target == ""){
      return false;
    }
    if($target == "_blank"){
      return true;
    }
    return false;
  }
}
