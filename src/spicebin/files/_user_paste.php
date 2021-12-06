<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive("spicebin",true)){ die ("SpiceBin is disabled");}
if(hasPerm([2]) && is_numeric(Input::get('user'))){
  $uid = Input::get('user');
}else{
  $uid = $user->data()->id;
}

$pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
$paste = canIPaste();
if(!$paste){
  die("You are not allowed to ".$pset->product_single);
}
else{
  // dump($paste);
}

if(!empty($_POST['delete'])){
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
  if(hasPerm([2]) && is_numeric(Input::get('user'))){
    Redirect::to($currentPage."&user=".$uid);
  }else{
    Redirect::to($currentPage);
  }
}
include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_last_ten_logic.php";
?>
<div class="row">
  <div class="<?=$main?>">
    <div class="text-left">
      <a class="btn btn-primary btn-lg" href="<?=$us_url_root.$pset->create_page?>" >Create <?=$pset->product_single?></a>
    </div>

    <?php
    $pastesQ = $db->query("SELECT * FROM plg_spicebin WHERE user = ? ORDER BY id DESC",[$uid]);
    $pastesC = $pastesQ->count();
    $pastes = $pastesQ->results();

    if($pastesC > 0){ ?>
      <h2>Your <?=$pset->product_plural?></h2>
      <form class="" action="" method="post" onsubmit="return confirm('Do you really want to do this? It cannot be undone.');">
        <input type="hidden" name="csrf" value="<?=Token::generate();?>">
        <div class="text-right" style="margin-bottom:2em;">
          <input type="submit" name="deleteSelected" value="Delete Selected" class="btn btn-danger">
        </div>

        <table class="table table-striped paginate">
          <thead>
            <tr>
              <th>Name</th>
              <th>Created On</th>
              <?php if($pset->delete_days > 0) { ?>
                <th>Expires</th>
              <?php } ?>
              <th>Views</th>
              <th>
                <input type="checkbox" name="all" id="checkall" /> Delete
              </th>
            </tr>
          </thead>
          <tbody>
            <input type="hidden" name="csrf" value="<?=Token::generate();?>">
            <?php
            foreach($pastes as $p){ ?>
              <tr>
                <td>
                  <a href="<?=$us_url_root.$pset->view_page?>?<?=$p->link?>"><?=$p->title?></a>
                </td>
                <td><?=$p->created_on?></td>
                <?php if($pset->delete_days > 0) { ?>
                  <td>
                    <?php if($p->no_auto == 1){
                      echo "Never";
                    }else{
                      echo $p->delete_on;
                    }
                    ?>
                  </td>
                <?php } ?>
                <td><?=$p->views?></td>
                <td>
                  <input type="checkbox" class="delete" name="delete[]" value="<?=$p->id?>">
                </td>
              </tr>
            <?php } ?>
          </form>
        </tbody>
      </table>
    <?php }else{
      echo "<h3>You do not have any " . $pset->product_plural. "</h3>";
    }  ?>
  </div>

  <?php if($pset->$col > 0) { ?>
    <div class="col-2 d-none d-lg-block">
      <?php include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_last_ten.php"; ?>
    </div>
  <?php }?>
</div>

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
  $('.paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
});

</script>
