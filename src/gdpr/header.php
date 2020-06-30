<?php
if(isset($settings->gdpract) && $settings->gdpract == 1){
  if(isset($user) && $user->isLoggedIn()){

    if(!empty($_POST['gdprhook'])){
      $token = $_POST['csrf'];
      if(!Token::check($token)){
        include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
      }
      if(!empty($_POST['gdprMoreInfo'])){
        Redirect::to($us_url_root.'usersc/plugins/gdpr/files/moreinfo.php');
      }

      if(!empty($_POST['gdprDelete'])){
        $token = $_POST['csrf'];
        if(!Token::check($token)){
          include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
        }
        Redirect::to($us_url_root.'usersc/plugins/gdpr/files/confirm_delete.php');
      }
      if(!empty($_POST['gdprAccept'])){
        $fields = array(
          'gdpr' => $settings->gdprver,
          'gdpr_date'=>date("Y-m-d H:i:s"),
        );
        $db->update('users',$user->data()->id,$fields);
        Redirect::to($us_url_root.$settings->redirect_uri_after_login);
      }
    }
    if($user->data()->gdpr == null || $user->data()->gdpr < $settings->gdprver){ ?>
      <div class="text-center">
      <?php
      $last = $db->query("SELECT * FROM us_gdpr ORDER BY id DESC LIMIT 1")->first();
      echo "<div class='text-primary'>".html_entity_decode($last->popup)."</div>";
      $page = currentPage();
      $token = Token::generate();
      ?>

      <form class="" action="" method="post">
        <input type="hidden" name="csrf" value="<?=$token?>" />
        <input type="hidden" name="gdprhook" value="1">
        <input type="submit" name="gdprAccept" value="<?=$last->btn_accept?>">
        <?php if($page != 'moreinfo.php'){?>
          <input type="submit" name="gdprMoreInfo" value="<?=$last->btn_more?>">
        <?php } ?>
        <?php if($last->delete == 1){ ?>
          <input type="submit" name="gdprDelete" value="<?=$last->btn_delete?>">
        <?php } ?>
      </form>
    </div>
      <?php
    } //out of date gdpr
  }
}//gdpract=1
?>
