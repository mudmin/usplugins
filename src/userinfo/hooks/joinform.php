<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;

$check = $db->query("SELECT id FROM users_form")->count();
if($check > 0){
  if(currentPage() == 'join.php'){
  if(pluginActive("forms",true)){
  $plgform = displayForm('users',['nosubmit'=>true,'noclose'=>1]);
  }
}else{
    $e = $db->query("SELECT * FROM plg_userinfo")->first();
    if(currentPage() == "user_settings.php"){
      $us = true;
      if(pluginActive("forms",true)){
      $plgform = displayForm('users',['nosubmit'=>true,'noclose'=>1,'update'=>$user->data()->id]);
      }
    }else{
      $us = false;
      if(pluginActive("forms",true)){
      $plgform = displayForm('users',['nosubmit'=>true,'noclose'=>1]);
      }
    }

    $string = randomstring(10);

    if($e->fname == 1){ ?>
    <script type="text/javascript">
      $("#fname-group").hide();
      <?php if(!$us){?>  $("#fname").val("<?=$string?>"); <?php } ?>
    </script>
    <?php
    }

    if($e->lname == 1){?>
    <script type="text/javascript">
      $("#lname-group").hide();
      <?php if(!$us){?>  $("#lname").val("<?=$string?>"); <?php } ?>
    </script>
    <?php
    }

    if($e->uname == 1){?>
    <script type="text/javascript">
      $("#email-group").hide();
      <?php if(!$us){?>  $("#email").val("<?=$string?>@<?=$e->domain?>"); <?php } ?>
    </script>
    <?php
    }

    if($e->uname == 2){?>
    <script type="text/javascript">
      $("#username-group").hide();
      <?php if(!$us){?>  $("#username").val("<?=$string?>"); <?php } ?>
    </script>
    <?php
    }

}
}
