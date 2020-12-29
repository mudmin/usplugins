<?php
if(canMakePlgLinks()){
$p = currentPage();
$lsettings = $db->query("SELECT * FROM plg_links_settings WHERE id = 1")->first();
if($lsettings->non_admins_see_all == 1 || hasPerm([2],$user->data()->id)){
  $links = $db->query("SELECT * FROM plg_links")->results();
}else{
  $links = $db->query("SELECT * FROM plg_links WHERE user = ?",[$user->data()->id])->results();
}

if(!empty($_POST['subNewLink'])){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

$logged_in = 1;
if($lsettings->allow_login_choice == 0 && (Input::get("logged_in") == 0 || Input::get("logged_in") == 1)){
$logged_in = Input::get("logged_in");
}elseif($lsettings->allow_login_choice == 2){
  $logged_in = 0;
}

$link_name = strtolower(Input::get('link_name'));
$link = Input::get('link');
$check = $db->query("SELECT id FROM plg_links WHERE link_name = ?",[$link_name])->count();
$seven = strtolower(substr($link,0,7));
$eight = strtolower(substr($link,0,8));

if($check > 0 || ($seven != "http://" && $eight != "https://")){
  Redirect::to($p."?err=Invalid Link");
}else{
  $fields = array(
    'link_name'=>$link_name,
    'link'=>$link,
    'user'=>$user->data()->id,
    'logged_in'=>$logged_in,
    'clicks'=>0,
  );
  $db->insert("plg_links",$fields);
  Redirect::to($p."?err=Link Created");
}
}
if(!empty($_POST['delLink'])){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  $d = Input::get('delMe');
  $check = $db->query("SELECT * FROM plg_links WHERE id = ? AND user = ?",[$d,$user->data()->id])->count();
  if($check > 0){
    $db->query("DELETE FROM plg_links WHERE id = ?",[$d]);
    Redirect::to($p.'?err=Deleted');
  }else{
    Redirect::to($p.'?err=That is not your link');
  }

}
?>
<h4>Create a New Link</h4>

<form class="" action="" method="post">
  <input type="hidden" name="csrf" value="<?=Token::generate();?>">
  Please give the link a name <span id="avail"></span><br>
  <input type="text" name="link_name" value="" id="link_name" class="form-control">
  <br>
  The final destination of the link (https://google.com) <span id="url"></span><br>
  <input type="text" name="link" value="" id="link" class="form-control">
  <br>

  <?php if($lsettings->allow_login_choice == 0){?>
    Require user to be logged in to view this link<br>
    <select class="form-control" name="logged_in"  required>
      <option disabled selected="selected">--Please Choose--</option>
      <option value="0">No</option>
      <option value="1">Yes</option>
    </select>
  <?php } ?>
  <br>
  <input type="submit" name="subNewLink" value="Create Link" id="sub" disabled class="btn btn-primary">
</form>

<h4>Your Links</h4>
<table class="table table-striped paginate">
  <thead>
    <tr class="text-left">
      <th>Link</th>
      <th>Link Name</th>
      <th>Link</th>
      <th>Must be logged in?</th>
      <th>Clicks</th>
      <th>Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($links as $l){?>
      <tr>
        <td>
          <button type="button" class=" btn btn-primary" onclick="copyStringToClipboard('<?=generatePluginLink($l->id)?>');">Copy</button>
        </td>
        <td><?=$l->link_name?></td>
        <td><?=$l->link?></td>
        <td><?php bin($l->logged_in);?></td>
        <td><?=$l->clicks?></td>
        <td>
         <?php if($l->user == $user->data()->id){ ?>
          <form class="" action="" method="post">
              <input type="hidden" name="csrf" value="<?=Token::generate();?>">
              <input type="hidden" name="delMe" value="<?=$l->id?>">
              <input type="submit" name="delLink" value="Delete" class="btn btn-danger">
        <?php } ?>
          </form>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>

$(document).ready(function () {
   $('.paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
  });

</script>
<script type="text/javascript">
$( "#link_name" ).change(function() { //use event delegation
  var value = $(this).val();
  var length = value.length;
  if(length < 1){
    $("#avail").html("<font color='red'>Name not available</font>");
  }

  var field = $(this).attr("id"); //the id in the input tells which field to update
  var desc = $(this).attr("data-desc"); //For messages
  var formData = {
    'link_name' 				: value
  };

  $.ajax({
    type 		: 'POST',
    url 		: '<?=$us_url_root?>usersc/plugins/links/assets/ln_parser.php',
    data 		: formData,
    dataType 	: 'json',
  })

  .done(function(data) {
    console.log(data);
    if(data.response == "good"){
      $("#avail").html("<font color='green'>Name is available</font>");
      $("#sub").prop("disabled",false);
    }else{
      $("#avail").html("<font color='red'>Name not available</font>");
      $("#sub").prop("disabled",true);
    }
  })
});

$( "#link" ).change(function() { //use event delegation
  var value = $(this).val();
  var seven = value.substring(0,7);
  seven = seven.toLowerCase();
  var eight = value.substring(0,8);
  eight = eight.toLowerCase();
  if(eight != "https://" && seven != "http://"){
    $("#url").html("<font color='red'>Links should begin with http:// or https://</font>");
  }else{
    $("#url").html("");
  }


});

</script>
<script type="text/javascript">
function copyStringToClipboard (textToCopy) {
  navigator.clipboard.writeText(textToCopy)
}
</script>

<?php } //end permission check
