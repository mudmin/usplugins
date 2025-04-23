<?php global $user;
if($user->isLoggedIn() && pluginActive("chat",true)){  ?>
    <li><a href="" id="toggleChatWindowBtn"><i class="fa fa-comments"></i></a></li> 
<?php } ?>

