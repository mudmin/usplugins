<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $usFormUpdate,$userId;

if(currentPage() == "user_settings.php"){
  $usFormUpdate = $user->data()->id;
}else{
  $usFormUpdate = $userId;
}

$e = $db->query("SELECT * FROM plg_userinfo")->first();
if(pluginActive("forms",true)){
$plgform = displayForm('users',['nosubmit'=>true,'noclose'=>1,'update'=>$usFormUpdate]);
}


$string = randomstring(10);
if($e->fname == 1){ ?>
<script type="text/javascript">
  $("#fname-group").hide();
  $("#fname").val("<?=$string?>");
</script>
<?php
}
$string = randomstring(10);
if($e->fname == 1){ ?>
<script type="text/javascript">
  $("#fname-group").hide();
</script>
<?php
}

if($e->lname == 1){?>
<script type="text/javascript">
  $("#lname-group").hide();
</script>
<?php
}

if($e->uname == 1){?>
<script type="text/javascript">
  $("#email-group").hide();
</script>
<?php
}

if($e->uname == 2){?>
<script type="text/javascript">
  $("#username-group").hide();
</script>
<?php
}
?>
<script type="text/javascript">
$(document).ready(function(){
  $('#adminUser :input').prop("required", false);
  });
</script>
