<?php
/*
default alert style based on SweetAlert
written by Dan Hoover

To create another style simply copy this folder and rename it.
Edit the alerts.php with the styling of your choosing.

Feel free to use this as a guide.

When this plugin was written, there were 5 types of system messages
err= in the url
msg= in the url
genMsg in the $_SESSION
valSuc in the $_SESSION
valErr in the $_SESSION

All of these messages can be handled differently.  If you want to use a bootstrap banner for one type and a
modal for another, that's totally up to you. You can mix and match.  You can stick any sort of conditional
logic you can dream up in your alert system.

This example uses the SweetAlert2 Library
https://sweetalert2.github.io/#examples

*/

$usSessionMessages = parseSessionMessages();
// $usSessionMessages['valErr'] = "Something went wrong!@!!!";
// $usSessionMessages['valSuc'] = "Every little thing....is gonna be alright";
// $usSessionMessages['genMsg'] = "This is a system message";
// dump($usSessionMessages);

?>
<link rel="stylesheet" type="text/css" href="<?=$us_url_root?>usersc/plugins/alerts/assets/default/toastify.min.css">
<script type="text/javascript" src="<?=$us_url_root?>usersc/plugins/alerts/assets/default/toastify-js.js"></script>
<script type="text/javascript">
$( document ).ready(function() {
  let modals = [];
  console.log("<?=htmlspecialchars_decode(Input::get('err'))?>");
    <?php
    //this handles err= in the URL
    if(Input::get('err') != ""){
    ?>

    Toastify({
      text: "<?=strip_tags(htmlspecialchars_decode(Input::get('err')))?>",
      duration: <?=$settings->err_time?>*1000,
      newWindow: true,
      close: true,
      gravity: "top", // `top` or `bottom`
      position: "right", // `left`, `center` or `right`
      backgroundColor: "linear-gradient(to right, #283048, #859398)",
      stopOnFocus: true, // Prevents dismissing of toast on hover
      onClick: function(){} // Callback after click
    }).showToast();

    <?php } ?>

    <?php
    //this handles msg= in the URL
    if(Input::get('msg') != ""){
    ?>
    Toastify({
      text: "<?=strip_tags(htmlspecialchars_decode(Input::get('msg')))?>",
      duration: <?=$settings->err_time?>*1000,
      newWindow: true,
      close: true,
      gravity: "top", // `top` or `bottom`
      position: "right", // `left`, `center` or `right`
      backgroundColor: "linear-gradient(to right, #232526, #414345)",
      stopOnFocus: true, // Prevents dismissing of toast on hover
      onClick: function(){} // Callback after click
    }).showToast();

    <?php } ?>

    <?php
    //this handles session based error message
    if($usSessionMessages['valErr'] != ""){
    ?>
    Toastify({
      text: "<?=strip_tags(htmlspecialchars_decode($usSessionMessages['valErr']))?>",
      duration: <?=$settings->err_time?>*1000,
      newWindow: true,
      close: true,
      gravity: "top", // `top` or `bottom`
      position: "right", // `left`, `center` or `right`
      backgroundColor: "linear-gradient(to right, #EB3349, #F45C43)",
      stopOnFocus: true, // Prevents dismissing of toast on hover
      onClick: function(){} // Callback after click
    }).showToast();
    <?php } ?>

    <?php
    //this handles session based success message
    if($usSessionMessages['valSuc'] != ""){
    ?>
    Toastify({
      text: "<?=strip_tags(htmlspecialchars_decode($usSessionMessages['valSuc']))?>",
      duration: <?=$settings->err_time?>*1000,
      newWindow: true,
      close: true,
      gravity: "top", // `top` or `bottom`
      position: "right", // `left`, `center` or `right`
      backgroundColor: "linear-gradient(to right, #1D976C, #348F50 )",
      stopOnFocus: true, // Prevents dismissing of toast on hover
      onClick: function(){} // Callback after click
    }).showToast();
    <?php } ?>

    <?php
    //this handles session based success message
    if($usSessionMessages['genMsg'] != ""){
    ?>
    Toastify({
      text: "<?=strip_tags(htmlspecialchars_decode($usSessionMessages['genMsg']))?>",
      duration: <?=$settings->err_time?>*1000,
      newWindow: true,
      close: true,
      gravity: "top", // `top` or `bottom`
      position: "right", // `left`, `center` or `right`
      backgroundColor: "linear-gradient(to right, #1A2980, #26D0CE)",
      stopOnFocus: true, // Prevents dismissing of toast on hover
      onClick: function(){} // Callback after click
    }).showToast();
    <?php } ?>

});
</script>
