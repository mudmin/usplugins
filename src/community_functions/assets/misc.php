<?php
if(!function_exists("isMobileDevice")){
function isMobileDevice() {
    if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])){
        return TRUE;
    }else{
        return FALSE;
    }
}
}

if(!function_exists("get_profile_pic")){
  function get_profile_pic($id,$w){
    $thatUser=fetchUserDetails(NULL,NULL,$id);
    //if(pluginActive('profile_pic') && $thatUser->profile_pic != ''){
        if($thatUser->profile_pic != ''){
    $useravatar='<img src="/usersc/plugins/profile_pic/files/'.$thatUser->profile_pic.'" class="img-thumbnail" style="width:'.$w.'px" >';
    }else{
    $grav = get_gravatar(strtolower(trim($thatUser->email)));
    $useravatar = '<img src="'.$grav.'" class="img-thumbnail" style="width:'.$w.'px" alt="'.echousername($thatUser->id).'">';
    }
    return $useravatar;
}
}
