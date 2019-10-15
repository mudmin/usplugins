<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted Leave this line in place?>
<?php
//This demo will list permission levels a user has

if(isset($user) && $user->isLoggedIn()){
  $plgPermsData = [];
  $plgPerms = $data = fetchUserPermissions($user->data()->id);
  foreach($plgPerms as $p){
    $pnQ = $db->query("SELECT * FROM permissions WHERE id = ?",[$p->permission_id]);
    $pnC = $pnQ->count();
    if($pnC > 0){
      $pn = $pnQ->first();
      $plgPermsData[] = $pn->name;
    }
  }
}
sort($plgPermsData);
 ?>
<strong>Permissions</strong><br>
<?php foreach($plgPermsData as $p){
  //if($p != 'User'){
  echo $p."<br>";
  //}
}
