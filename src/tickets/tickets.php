<?php
require_once "../../../users/init.php";
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("tickets",true)){ die("Tickets plugin not active");}
if(!isset($user) || !$user->isLoggedIn()){ ?>
  <a href="<?=$us_url_root?>users/login.php">Sorry, you must login.</a>
  <?php
  die;
}
$ticSettings = $db->query("SELECT * FROM plg_tickets_settings")->first();
if(!hasPerm([$ticSettings->perm],$user->data()->id) && !hasPerm([$ticSettings->perm_to_assign],$user->data()->id) && !hasPerm([2],$user->data()->id)){
  logger($user->data()->id,"Ticket Error","Tried to access ticket manager without permission");
  die("You don't have permission to be here");
}

$closed = Input::get('closed');
$filter = Input::get('filter');
if(!hasPerm([$ticSettings->perm_to_assign],$user->data()->id) && !hasPerm([2],$user->data()->id)){
  $me = true;
}else{
  $me = false;
}


if($closed != "true"){
  $cl = " AND closed = 0";
}else{
  $cl = "";
}

if(is_numeric($filter)){
  $fi = " AND category = ".$filter;
}else{
  $fi = "";
}

if($me){
  $mi = " AND agent = ".$user->data()->id." OR agent = 0";
}else{
  $mi = "";
}

if(!is_numeric(Input::get('limit'))){
  $limit = 500;
}else{
  $limit = Input::get('limit');
}

$ticketsQ = $db->query("SELECT * FROM plg_tickets WHERE id > 0 $cl $fi $mi ORDER BY id DESC LIMIT $limit ");
$ticketsC = $ticketsQ->count();
$tickets = $ticketsQ->results();


$assignable = $db->query("SELECT * FROM user_permission_matches WHERE permission_id = ?",[$ticSettings->perm])->results();
$agents = [];
foreach($assignable as $k=>$v){
  $q = $db->query("SELECT id,fname,lname FROM users WHERE id = ?",[$v->user_id]);
  $c = $q->count();
  if($c < 1){
    continue;
  }else{
    $f = $q->first();
    $agents[$f->id]['uid'] = $f->id;
    $agents[$f->id]['fname'] = $f->fname;
    $agents[$f->id]['lname'] = $f->lname;
  }
}

array_multisort(array_map(function($element) {
  return $element['fname'];
}, $agents), SORT_ASC, $agents);


if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }


  if(!empty($_POST['change_agent'])){
    $newAgent = Input::get('new_agent');
    if(hasPerm([$ticSettings->perm_to_assign],$user->data()->id) || $hasPerm([2],$user->data()->id)){
      $db->update("plg_tickets",Input::get('changeThis'),['agent'=>$newAgent,"last_updated"=>date("Y-m-d H:i:s")]);

      if($user->data()->id != $newAgent && $ticSettings->email_agent == 1){
        $fetchQ = $db->query("SELECT id,email FROM users WHERE id = ?",[$newAgent]);
        $fetchC = $fetchQ->count();
        if($fetchC > 0){
          $fetch = $fetchQ->first();
          email($fetch->email,"You have been assigned a ticket.","You have been assigned ticket #".Input::get('changeThis'));
        }
      }


      Redirect::to("tickets.php?err=Agent+changed&closed=$closed&filter=$filter&limit=$limit");
    }else{
      logger($user->data()->id,"Ticket Error","Tried to illegally change agent on ticket");
    }
  }
}


?>
<div class="row">
  <div class="col-12">
    <h2 class="text-center">Tickets (<?=$ticketsC?>)</h2>
    <p class="text-center">
      <?php
      $cp = currentPage();
      if($closed == ""){ ?>
        <a href="<?=$cp?>?closed=true">Show Closed Tickets</a>
      <?php }else{ ?>
        <a href="<?=$cp?>">Hide Closed Tickets</a>
      <?php } ?>
    </p>
    <table class="table table-striped table-hover paginate">
      <thead>
        <tr>
          <th>User</th>
          <th>
            <?=ucfirst($ticSettings->agent_term)?>
          </th>
          <th>Subject</th>
          <th>Status</th>
          <th>
            <?=ucfirst($ticSettings->cat_term)?>
          </th>
          <?php if($cl == ""){ ?>
            <th>Closed</th>
          <?php } ?>
          <th>Created</th>
          <th>Last Updated</th>
          <th>View</th>
          <?php if(hasPerm([$ticSettings->perm_to_assign],$user->data()->id)){ ?>
            <th>Assign To</th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($tickets as $t){ ?>
          <tr>
            <td><?php echouser($t->user);?></td>
            <td><?php echouser($t->agent);?></td>
            <td><?=substr($t->subject,0,100);?></td>
            <td><?=$t->status;?></td>
            <td><?=$t->category;?></td>
            <?php if($cl == ""){ ?>
              <td><?php bin($t->closed);?></td>
            <?php } ?>
            <td><?=$t->created?></td>
            <td><?=$t->last_updated?></td>
            <td>
              <a href="<?=$us_url_root.$ticSettings->single_view?>?id=<?=$t->id?>" class="btn btn-primary">View</a>
            </td>
            <?php if(hasPerm([$ticSettings->perm_to_assign],$user->data()->id)){ ?>
              <td>
                <form class="" action="" method="post">
                  <input type="hidden" name="csrf" value="<?=Token::generate();?>">
                  <input type="hidden" name="changeThis" value="<?=$t->id?>">
                  <select class="" name="new_agent">
                    <?php foreach($agents as $v){?>
                      <option <?php if($t->agent==$v['uid']){echo "selected='selected'";}?> value="<?=$v['uid']?>"><?=$v['fname']." ".$v['lname'];?></option>
                    <?php } ?>
                  </select>
                  <input type="submit" name="change_agent" value="Go">
                </form>
              </td>
            <?php } ?>

          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>
$(document).ready(function () {
  $('.paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
});
</script>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
