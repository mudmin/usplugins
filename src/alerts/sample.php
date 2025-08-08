<?php
require_once "../../../users/init.php";
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';

sessionValMessages(
    "Something went wrong!@!!!",
    "Every little thing....is gonna be alright",
    "This is a system message"
  );


?>

<?php

require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php';
