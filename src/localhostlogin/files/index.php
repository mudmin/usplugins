<?php
require_once '../../../../users/init.php';
// Needs a genuinely local request AND define('US_LOCALHOST_LOGIN', true) in users/init.php,
// so turning the plugin on can't expose this page wherever the code is deployed.
if(!isLocalhost() || !defined('US_LOCALHOST_LOGIN') || US_LOCALHOST_LOGIN !== true) {
  logger(1,"Localhost Login","ACCESS DENIED: not a local request, or US_LOCALHOST_LOGIN is not defined in users/init.php.");
  Redirect::to('../../../../users/login.php');
  die();
}
// if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
?>
<?php
if($user->isLoggedIn()) {
  Redirect::to($us_url_root.'index.php');
}

if (Input::exists()) {
  $token = Input::get('csrf');
  if(!Token::check($token)){
    include('../../../../usersc/scripts/token_error.php');
  }

  $user = Input::get('username');
  $_SESSION[Config::get('session/session_name')] = $user;
  logger($user,"Localhost Login","Authenticated via Localhost Dropdown");
  Redirect::to('../../../../users/index.php');
}
if(!isset($dest)){
  $dest=$us_url_root;
}
?>
<div id="page-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <form name="login" id="login-form" class="form-signin" action="" method="post">
          <h2 class="form-signin-heading"></i> <?=lang("SIGNIN_TITLE","");?></h2>
          <input type="hidden" name="dest" value="<?= $dest ?>" />

          <div class="form-group">
            <label for="username" >User</label>
            <select  class="form-control" type="text" name="username" id="username" required autofocus>
              <?php $users=fetchAllUsers();
              if($users) {
                foreach($users as $theUser) { ?>
                  <option value="<?=$theUser->id?>"><?=$theUser->fname?> <?=$theUser->lname?> <?=$theUser->username?></option>
                <?php }
              } ?>
            </select>
          </div>

          <input type="hidden" name="csrf" value="<?=Token::generate(); ?>">
          <button class="submit  btn  btn-primary" id="next_button" type="submit"><i class="fa fa-sign-in"></i> <?=lang("SIGNIN_BUTTONTEXT","");?></button>

        </form><br>
      </div>
    </div>
  </div>
</div>

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
