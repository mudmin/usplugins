  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST)){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
  $queryQ = $db->query($_POST['query']);
  $queryC = $queryQ->count();
  $queryR = $queryQ->results();

 }
 $token = Token::generate();
 ?>
 <?php
 if(!function_exists('tableFromQueryPlugin')) {
 function tableFromQueryPlugin($results,$opts = []){
   if(!isset($opts['class'])) {$opts['class'] = "table table-striped paginate"; }
   if(!isset($opts['thead'])) {$opts['thead'] = ""; }
   if(!isset($opts['tbody'])) {$opts['tbody'] = ""; }
   if(!isset($opts['keys'])){
     foreach($results['0'] as $k=>$v){
       $opts['keys'][] = $k;
     }
     }

   ?>

   <table class="<?=$opts['class']?>" id="paginate">
     <thead class="<?=$opts['thead']?>">
       <tr>
         <?php foreach($opts['keys'] as $k){?>
           <th><?php echo ucfirst($k);?></th>
         <?php } ?>
       </tr>
     </thead>
     <tbody class="<?=$opts['tbody']?>">
       <?php foreach($results as $r){?>
         <tr>
           <?php foreach($r as $k=>$v){ ?>
             <td><?=$v?></td>
           <?php } ?>
         </tr>
       <?php } ?>
     </tbody>
   </table>
 <?php }
 }
  ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a><br>

          You may want to consider <a href="admin.php?view=backup"><font color="red"><strong>backing up your database</strong></font></a> if you are doing something dangerous.<br>
          <form class="" action="" method="post" id="queryForm">
            <input type="hidden" name="csrf" value="<?=$token?>" /><br>
            <font color="black"><strong>Enter your query here...</strong></font>
            <textarea autofocus class = "form-control" rows="8" name="query" id="query"><?php if(!empty($_POST['query'])){echo $_POST['query'];}?></textarea>
            <div class="text-right"><input type="submit" name="plugin_mysql" value="Execute" class="btn btn-danger"></div>
          </form>
          <?php if(!empty($_POST['query'])){?>
            <strong>Error Message:</strong> <?=$db->errorString();?><br>
            <strong># of Results:</strong> <?=$queryC?>
            <?php if($queryC > 0){
              tableFromQueryPlugin($queryR);
             } ?>

          <?php } //end query?>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
<script type="text/javascript">
$("#queryForm").submit(function(e){
  e.preventDefault();

  var form = this;
  var query = $("#query").val();
  query = query.toLowerCase();
  if (query.indexOf("drop") >= 0 || query.indexOf("delete") >= 0){
    if (confirm('This looks dangerous. Are you sure you want to do this?')) {
    form.submit();
    } else {
        // Do nothing!
    }
  }else{
    form.submit();
  }
});
</script>
<link href="<?=$us_url_root?>users/js/pagination/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="js/pagination/datatables.min.js"></script>
<script>

$(document).ready(function () {
   $('#paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
  });

</script>
