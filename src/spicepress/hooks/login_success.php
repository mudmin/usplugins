<?php
//if the login form had a request for access and a return url, we're going to process
global $user;
$wp_url = Input::get('return_url');

if(Input::get('request_wp_access') == 1 && $wp_url != ""){

//we need to do a check to make sure the return request is going to your legitimate site
$spicepress = $db->query("SELECT * FROM plg_spicepress_settings")->first();
  if (verifyReturnUrl($wp_url)) {
    //base url checks out, continue processing
    $string = "";
    $code = createSpicePressSession($user->data()->id);
    if($code['success'] == true){
      logger($user->data()->id,"SpicePress","Successful authentication");
      //we need to check if the url already has get variables
      if (strpos($wp_url, '?') !== false) {
        $string = "&success=true&code=".$code['code'];
      }else{
        $string = "?success=true&code=".$code['code'];
      }

    }else{ // authentication failed
      logger($user->data()->id,"SpicePress","FAILED authentication");
      if (strpos($wp_url, '?') !== false) {
        $string = "&success=false&code=false";
      }else{
        $string = "?success=false&code=false";
      }
    }

    $url = $wp_url . $string;
    Redirect::to($url);
  }else{
    logger($user->data()->id,"SpicePress","SECURITY - An invalid return url was attempted");
  }

}
