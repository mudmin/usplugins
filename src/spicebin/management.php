<?php
require_once '../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("spicebin",true)){ die ("SpiceBin is disabled");}
if(!isset($user) || !$user->isLoggedIn()){
  die("Not logged in");
}
$pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
$access = false;
if($pset->mng_tag != "" && pluginActive("usertags",true)){
  if(hasTag($pset->mng_tag)){
    $access = true;
  }
}
if(hasPerm($pset->mng_perm)){
  $access = true;
}

if(!$access){
  logger($user->data()->id,"SECURITY","Tried to access SpiceBin management");
  die("You do not have access to be here. This incident has been logged");
}
$search = Input::get('search');
$mode = Input::get('mode');
$snip = Input::get('snip');
$label = "";
if(!is_numeric($snip)){
  $snip = 150;
}
if(!empty($_POST['delete'])){
  // dnd($_POST);
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  $delete = Input::get('delete');
  $counter = 0;
  foreach($delete as $d){
    //an admin can delete any paste, otherwise, check if the paste belongs to the submitter
    if(!hasPerm([2])){
      $conf = $db->query("SELECT * FROM plg_spicebin WHERE id = ? AND user = ?",[$d,$uid])->count();
      if($conf < 1){
        continue;
      }
    }
    $db->query("DELETE from plg_spicebin WHERE id = ?",[$d]);
    ++$counter;
  }
  sessionValMessages($counter." deleted");
  $currentPage = currentPage();
    Redirect::to($currentPage);
}

$c = 0;
if($mode == "content" OR $mode == "title"){
  if($search != ""){
    if($mode == "title"){
      $q = $db->query("SELECT * FROM plg_spicebin WHERE title LIKE ? ORDER BY id DESC LIMIT 500",["%".$search."%"]);
    }elseif($mode == "content"){
      $q = $db->query("SELECT * FROM plg_spicebin WHERE paste LIKE ? ORDER BY id DESC LIMIT 500",["%".$search."%"]);
    }
    $c = $q->count();
    $pastes = $q->results();
    if($c < 1){
      $label = "No results found.";
    }else{
      $label = "Search results for <em>$search</em>.";
    }
  }else{
    $c = 0;
    $label = "Please enter a search term";
  }
}
if($mode == "user"){
  if($search != ""){
    $usersQ = $db->query("SELECT * FROM users WHERE id = ? OR username = ? OR email = ?",[$search,$search,$search]);
    $usersC = $usersQ->count();

    if($usersC < 1){
      $c = 0;
      $label = "No users found";
    }elseif($usersC > 1){
      $c = 0;
      $label = "Please enter something more specific";
    }elseif($usersC == 1){
      $u = $usersQ->first();
      $name = $u->fname." ".$u->lname . " (".$u->email.")";
      $q = $db->query("SELECT * FROM plg_spicebin WHERE user = ? ORDER BY id DESC",[$u->id]);
      $c = $q->count();
      $pastes = $q->results();
      if($c < 1){
        $label = "No $pset->product_plural found for <em>$name</em>";
      }else{
        $label = "$name search results";
      }
    }
  }else{
    $c = 0;
    $label = "Please enter something a search term";
  }
}
if($mode == ""){
  $q = $db->query("SELECT * FROM plg_spicebin ORDER BY id DESC LIMIT 500");
  $c = $q->count();
  $pastes = $q->results();
  if($c == 1){
    $label = "The only $pset->product_single on your system :)";
  }elseif($c < 1){
    $label = "You do not have any $pset->product_plural";
  }
  $label = "Last $c $pset->product_plural";
}
?>
<h2><?=$pset->product_single?> Management</h2>
<div class="row">
  <div class="col-12 col-md-3">
    <form class="" action="" method="get">
      Snippet length
      <div class="input-group">
        <input type="number" name="snip" step="1" min="0" value="<?=$snip?>" class="form-control" required>
        <input type="submit" name="go" value="Go" class="btn btn-primary">
      </div>
    </form>
  </div>

  <div class="col-12 col-md-3">
    <form class="" action="" method="get">
      Find all <?=$pset->product_plural?> of a user by email, username, or user id
      <div class="input-group">
        <input type="hidden" name="mode" value="user">
        <input type="text" name="search" value="" class="form-control" required>
        <input type="submit" name="go" value="Go" class="btn btn-primary">
      </div>

    </form>
  </div>

  <div class="col-12 col-md-3">
    <form class="" action="" method="get">
      Search <?=$pset->product_plural?> by title
      <div class="input-group">
        <input type="hidden" name="mode" value="title">
        <input type="text" name="search" value="" class="form-control" required>
        <input type="submit" name="go" value="Go" class="btn btn-primary">
      </div>
    </form>
  </div>

  <div class="col-12 col-md-3">
    <form class="" action="" method="get">
      Search <?=$pset->product_plural?> content (may take some time)
      <div class="input-group">
        <input type="hidden" name="mode" value="content">
        <input type="text" name="search" value="" class="form-control" required>
        <input type="submit" name="go" value="Go" class="btn btn-primary">
      </div>
    </form>
  </div>
</div>
<br>
<h3 class="text-center"><?=$label?></h3>
<?php
if($c > 0){ ?>
  <form class="" action="" method="post" onsubmit="return confirm('Do you really want to do this? It cannot be undone.');">
    <input type="hidden" name="csrf" value="<?=Token::generate();?>">
  <div class="text-right" style="margin-bottom:2em;">
    <input type="submit" name="deleteSelected" value="Delete Selected" class="btn btn-danger">
  </div>
  <table class="table table-striped paginate">
    <thead>
      <tr>
        <th>Name</th>
        <th>User</th>
        <th>Created On</th>
        <th>Snippet</th>
        <th>Expires</th>
        <th>Views</th>
        <th>
          <input type="checkbox" name="all" id="checkall" /> Delete
        </th>
      </tr>
    </thead>
    <tbody>

      <?php
      foreach($pastes as $p){ ?>
        <tr>
          <td>
            <a href="<?=$us_url_root.$pset->view_page?>?<?=$p->link?>"><?=$p->title?></a>
          </td>
          <td><?=echouser($p->user);?></td>
          <td><?=$p->created_on?></td>
          <td><?=substr($p->paste,0,$snip);?></td>
          <td><?=$p->delete_on?></td>
          <td><?=$p->views?></td>
          <td>
            <input type="checkbox" class="delete" name="delete[]" value="<?=$p->id?>">
          </td>
        </tr>
        <?php } ?>
      </form>
    </tbody>
  </table>
  <script type="text/javascript">
  $('#checkall').change(function () {
   if($(this).is(":checked")) {
     console.log("checked");
     $('input:checkbox').attr('checked','checked');
   } else {
     console.log("not");
     $('input:checkbox').removeAttr('checked');
   }
  });
  </script>
  <script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
  <script type="text/javascript">
  $(document).ready(function () {
     $('.paginate').DataTable({"pageLength": 500,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
    });

  </script>
<?php }
require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php';
?>
