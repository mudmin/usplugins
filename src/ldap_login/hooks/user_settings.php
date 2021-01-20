<?php
if($user->data()->id != 1){
  Redirect::to($us_url_root.'users/account.php?Please+contact+administrator+to+change+info');
}
