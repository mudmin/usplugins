<?php
//Please don't load functions system-wide if you don't need them system-wide.
// To make your plugin more efficient on resources, consider only loading resources that need to be loaded when they need to be loaded.
// For instance, you can do
// $currentPage = currentPage();
// if($currentPage == 'admin.php'){ //The administrative dashboard
//   bold("<br>See! I am only loading this when I need it!");
// }
// // Also, please wrap your functions in if(!function_exists())
// if(!function_exists('membershipFunction')) {
//   function membershipFunction(){ }
// }

function echoPlanName($id)
{
  global $db;
  $q = $db->query("SELECT plan_name FROM plg_mem_plans WHERE id = ?", [$id]);
  $c = $q->count();
  if ($c > 0) {
    $f = $q->first();
    echo $f->plan_name;
  } else {
    echo "None";
  }
}

function memberPlanStatus()
{
  global $user;
  $date = date("Y-m-d H:i:s");
  if ($user->data()->plg_mem_exp == "") {
    $status = "None";
  } elseif ($user->data()->plg_mem_exp < $date) {
    $status = "Expired";
  } else {
    $status = "Active";
  }
  return $status;
}

function changeOfPlans($from, $to, $uid)
{
  global $user, $us_url_root, $abs_us_root;
  $db = DB::getInstance();
  if ($from > 0) {
    $q = $db->query("SELECT * FROM plg_mem_plans WHERE id = ?", [$from]);
    $c = $q->count();
    if ($c > 0) {
      $f = $q->first();
      $perms = explode(",", $f->perms_added);
      foreach ($perms as $p) {
        $db->query("DELETE FROM user_permission_matches WHERE user_id = ? AND permission_id = ?", [$uid, $p]);
      }
    }
    if ($f->script_remove != '') {
      $safeRemove = basename($f->script_remove);
      $removePath = $abs_us_root . $us_url_root . 'usersc/plugins/membership/scripts/' . $safeRemove;
      if (file_exists($removePath) && is_file($removePath)) {
        include $removePath;
      }
    }
  }
  if ($to > 0) {
    $q = $db->query("SELECT * FROM plg_mem_plans WHERE id = ?", [$to]);
    $c = $q->count();
    if ($c > 0) {
      $f = $q->first();
      $db->update('users', $user->data()->id, ['plg_mem_level' => $to, 'plg_mem_expired' => 0]);
      $perms = explode(",", $f->perms_added);
      foreach ($perms as $p) {
        $fields = array(
          'permission_id' => $p,
          'user_id' => $uid,
        );
        $check = $db->query("SELECT * FROM user_permission_matches WHERE permission_id = ? AND user_id = ?", [$p, $uid])->count();
        if ($check < 1) {
          $db->insert('user_permission_matches', $fields);
        }
      }
    }
    if ($f->script_add != '') {
      $safeAdd = basename($f->script_add);
      $addPath = $abs_us_root . $us_url_root . 'usersc/plugins/membership/scripts/' . $safeAdd;
      if (file_exists($addPath) && is_file($addPath)) {
        include $addPath;
      }
    }
  }
}
