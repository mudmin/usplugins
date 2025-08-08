<?php
global $user;
if($user->data()->id != 1){
  Redirect::to($us_url_root.'users/account.php?msg=Please+contact+administrator+to+change+info');
}
