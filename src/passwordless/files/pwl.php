<?php
require_once "../../../../users/init.php";
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("passwordless",true)){
  die("Plugin is inactive");
}

$verifylink = Input::get("verifylink");
$attempt = Input::get("attempt");
$pwl = Input::get("pwl");
$errors = [];
$successes = [];

if($verifylink == "true" || $verifylink == true){
  die("If you can see this message, your link is correct");
}

if(!empty($_POST['email'])){
  if(!Token::check(Input::get('csrf'))){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  sendPasswordlessEmail(Input::get('email'));
  Redirect::to(currentPage()."?attempt=true");
}

?>
<div class="row">
  <div class="col-12 col-sm-6 offset-sm-3">
    <h3><?=$settings->site_name?> Passwordless Login</h3>
    <?php
    //base form
    if(empty($_POST) && empty($_GET)){ ?>


      <form class="" action="" method="post">
        <input type="hidden" name="csrf" value="<?=Token::generate();?>">
        <label for="email">Please enter your email</label>
        <div class="input-group">
          <input type="text" name="email" value="" class="form-control" required>
          <input type="submit" name="send" value="Send login email" class="btn btn-primary">
        </div>
      </form>

    <?php }elseif($attempt == "true"){ ?>
      <p>Please check your inbox for the login link.  If you did not receive an email, please check your spam folder. Once you receive the email, click the link and you will be logged in.</p>
      <p>If you did not receive the email, you can click the button below to try again.
        <div class="text-center">
          <a href="<?=currentPage();?>" class="btn btn-primary text-center">Try Again</a>
        </div>

      </p>

      <?php
    }elseif($pwl != ""){
      $try = authenticatePasswordlessEmail($pwl);
      if($try){
        $user = new User();
        $hooks =  getMyHooks(['page'=>'loginSuccess']);
        includeHook($hooks,'body');
        $dest = sanitizedDest('dest');
        # if user was attempting to get to a page before login, go there
        $_SESSION['last_confirm']=date("Y-m-d H:i:s");

        if (!empty($dest)) {
          $redirect=html_entity_decode(Input::get('redirect'));
          if(!empty($redirect) || $redirect!==''){

            Redirect::to($redirect);
          } else {

            Redirect::to($dest);
          }
        } elseif (file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php')) {

          # if site has custom login script, use it
          # Note that the custom_login_script.php normally contains a Redirect::to() call
          require_once $abs_us_root.$us_url_root.'usersc/scripts/custom_login_script.php';
        } else {
          if (($dest = Config::get('homepage')) ||
          ($dest = 'account.php')) {
            Redirect::to($dest);
          }
        }

      } else {
        $eventhooks =  getMyHooks(['page'=>'loginFail']);
        includeHook($eventhooks,'body');
        logger("0","Login Fail","A failed login on login.php");
        $msg = lang("SIGNIN_FAIL");
        $errors[] = '<strong>'.$msg.'</strong>';
      }
      sessionValMessages($errors, $successes, NULL);
      Redirect::to(currentPage());
    }

  ?>
</div>
</div>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
