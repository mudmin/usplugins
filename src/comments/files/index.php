<?php
//Security and UserSpice Includes
require_once '../../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
include "plugin_info.php";

$db = DB::getInstance();
pluginActive($plugin_name);


//check for permission to use the plugin in general
if($user->data()->commentmod != 1){
  logger($user->data()->id,"Comments","Tried to visit comment moderation without permission");
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comment&err=You+do+not+have+permission+to+moderate+comments');
}



if (!empty($_POST)) {
  $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
  foreach($_POST as $k=>$v){
    if(is_numeric($k)){
      $checkQ = $db->query("SELECT * FROM us_comments_plugin WHERE id = ?",array($k));
      $checkC = $checkQ->count();
      if($checkC > 0){
        $check = $checkQ->first();
        //let's make sure that this is really an unapproved comment
        if($check->approved == 0){
          //if comment was approved
          if($v == 1){
            $db->update('us_comments_plugin',$k,['approved'=>1]);
          }elseif($v == 0){
            $db->update('us_comments_plugin',$k,['deleted'=>1]);
          }
        }
      }
    }
  }
}
$commentQ = $db->query("SELECT * FROM us_comments_plugin WHERE approved = 0 AND DELETED = 0");
$commentC = $commentQ->count();
$comment = $commentQ->results();
$token = Token::generate();
?>

<div id="page-wrapper">
  <div class="container">
    <!-- Page Heading -->
    <div class="row">
      <div class="col-xs-12 col-md-6">
        <h1>Comment Moderation</h1>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <?php if($commentC == 0){
          echo "<strong>There are no comments to moderate.</strong>";
        }else{ ?>
          <table class="table table-striped">
            <form class="" action="" method="post">
              <input type="hidden" value="<?=$token;?>" name="csrf">
              <thead>
                <tr>
                  <th>ID</th><th>User</th><th>Page</th><th>Comment</th><th><input type="submit" name="submit" value="Moderate"></th>
                </tr>
              </thead>
              <tbody>

                <?php foreach($comment as $c){?>
                  <tr>
                    <th><?=$c->id?></th>
                    <th><?php echouser($c->user);?></th>
                    <th><?php echopage($c->page); ?></th>
                    <th><?php echo $c->comment;?></th>
                    <th>
                      Approve
                      <input type="radio" name="<?=$c->id?>" value="1">
                      Reject
                      <input type="radio" name="<?=$c->id?>" value="0">
                    </th>
                  </tr>
                <?php } ?>
              </form>
            </tbody>
          </table>
        <?php } ?>

      </div>
    </div>
  </div>


  <!-- End of main content section -->

  <?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

  <!-- Place any per-page javascript here -->
  <script src="./../../../users/js/pagination/jquery.dataTables.js" type="text/javascript"></script>
  <script src="./../../../users/js/pagination/dataTables.js" type="text/javascript"></script>
  <script src="./../../../users/js/jwerty.js"></script>
  <script>
  $(document).ready(function() {
    $('#paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});


  });
</script>
