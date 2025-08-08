<?php
//This is an example file of how to run things before a user is created through OAuth.
//The most likely scenario is that you will want to prevent this action, so you can simply kill the page or redirect with an error such as
//usError("New users must register locally");
//Redirect::to($us_url_root.'usersc/login.php');

//If you want to do something else, you can do it here. 
//However, at this point, you really only have access to the things that were passed to the function which you can var dump as
//dump($userData);
//dnd($response);

//or you can take care of things in the other scripts.