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
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script type="text/javascript">
$( document ).ready(function() {
let modals = [];
  <?php
  //this handles err= in the URL
  if(Input::get('err') != ""){
  ?>
  modals.push({
    icon: 'question',
    width: 600,
    background: "#fff url(<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/trees.png)",
    backdrop: `
              rgba(0,0,123,0.4)
              url("<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/nyan-cat.gif")
              left top
              no-repeat
            `,
    title: "<?=htmlspecialchars_decode(Input::get('err'))?>",
    timer: <?=$settings->err_time?>*1000,
    timerProgressBar: true
  });
  <?php } ?>

  <?php
  //this handles msg= in the URL
  if(Input::get('msg') != ""){
  ?>
  modals.push({
    icon: 'info',
    width: 600,
    background: "#fff url(<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/trees.png)",
    backdrop: `
              rgba(63, 191, 191,0.4)
              url("<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/nyan-cat.gif")
              left top
              no-repeat
            `,
    title: "<?=htmlspecialchars_decode(Input::get('msg'))?>",
    timer: <?=$settings->err_time?>*1000,
    timerProgressBar: true
  });
  <?php } ?>

  <?php
  //this handles session based error message
  if($usSessionMessages['valErr'] != ""){
  ?>
  modals.push({
    icon: 'error',
    width: 600,
    background: "#fff url(<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/trees.png)",
    backdrop: `
              rgba(193, 66, 66,0.4)
              url("<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/nyan-cat.gif")
              left top
              no-repeat
            `,
    title: "<?=htmlspecialchars_decode($usSessionMessages['valErr'])?>",
    timer: <?=$settings->err_time?>*1000,
    timerProgressBar: true
  });
  <?php } ?>

  <?php
  //this handles session based success message
  if($usSessionMessages['valSuc'] != ""){
  ?>
  modals.push({
    icon: 'success',
    width: 600,
    background: "#fff url(<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/trees.png)",
    backdrop: `
              rgba(63, 191, 63,0.4)
              url("<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/nyan-cat.gif")
              left top
              no-repeat
            `,
    title: "<?=htmlspecialchars_decode($usSessionMessages['valSuc'])?>",
    timer: <?=$settings->err_time?>*1000,
    timerProgressBar: true
  });
  <?php } ?>

  <?php
  //this handles session based success message
  if($usSessionMessages['genMsg'] != ""){
  ?>
  modals.push({
    icon: 'warning',
    width: 600,
    background: "#fff url(<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/trees.png)",
    backdrop: `
              rgba(229, 229, 70,0.4)
              url("<?=$us_url_root?>usersc/plugins/alerts/assets/nyan-cat/images/nyan-cat.gif")
              left top
              no-repeat
            `,
    title: "<?=htmlspecialchars_decode($usSessionMessages['genMsg'])?>",
    timer: <?=$settings->err_time?>*1000,
    timerProgressBar: true
  });
  <?php } ?>
  Swal.queue(modals);
});
</script>
