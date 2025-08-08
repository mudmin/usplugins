<?php

$usSessionMessages = parseSessionMessages();
// $usSessionMessages['valErr'] = "Something went wrong!@!!!";
// $usSessionMessages['valSuc'] = "Every little thing....is gonna be alright";
// $usSessionMessages['genMsg'] = "This is a system message";
// dump($usSessionMessages);
$settings->err_time = $settings->err_time * 1000;
?>
<div class="alert-toast-wrapper"></div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.3.0/dist/themes/light.css" />
<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.3.0/dist/shoelace.js"></script>
<!-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script> -->
<script type="text/javascript">
// Always escape HTML for text arguments!
function escapeHtml(html) {
  const div = document.createElement('div');
  div.innerHTML = html;
  const strongTags = div.getElementsByTagName('strong');
  for (let i = 0; i < strongTags.length; i++) {
    const strongTag = strongTags[i];
    const boldTag = document.createElement('b');
    boldTag.innerHTML = strongTag.innerHTML;
    strongTag.parentNode.replaceChild(boldTag, strongTag);
  }
  return div.innerHTML;
}

function processListTags(html) {
  const div = document.createElement('div');
  div.innerHTML = html;
  const listTags = div.getElementsByTagName('li');
  if (listTags.length > 0) {
    const ul = document.createElement('ul');
    for (let i = 0; i < listTags.length; i++) {
      const li = document.createElement('li');
      li.innerHTML = listTags[i].innerHTML;
      ul.appendChild(li);
    }
    div.innerHTML = ul.outerHTML;
  }
  return div.innerHTML;
}

function notify(message, variant = 'primary', icon = 'info-circle', duration = 5000) {
  const processedMessage = processListTags(escapeHtml(message));
  const alert = Object.assign(document.createElement('sl-alert'), {
    variant,
    closable: true,
    duration: duration,
    innerHTML: `
      <sl-icon name="${icon}" slot="icon"></sl-icon>
      ${processedMessage}
    `
  });

  document.body.append(alert);
  return alert.toast();
}



$( document ).ready(function() {

  <?php
  //this handles err= in the URL
  if(Input::get('err') != ""){
  ?>
  notify("<?=htmlspecialchars_decode(Input::get('err'))?>", variant = 'primary', icon = 'question-circle', duration = "<?=$settings->err_time?>");

  <?php }

  //this handles msg= in the URL
  if(Input::get('msg') != ""){
  ?>
  notify("<?=htmlspecialchars_decode(Input::get('msg'))?>", variant = 'neutral', icon = 'chat-left-dots', duration = "<?=$settings->err_time?>");

  <?php }

  //this handles session based error message
  if($usSessionMessages['valErr'] != ""){
  ?>
  notify("<?=htmlspecialchars_decode($usSessionMessages['valErr'])?>", variant = 'danger', icon = 'exclamation-circle', duration = "<?=$settings->err_time?>");

  <?php }
  //this handles session based success message
  if($usSessionMessages['valSuc'] != ""){
  ?>
    notify("<?=htmlspecialchars_decode($usSessionMessages['valSuc'])?>", variant = 'success', icon = 'check', duration = "<?=$settings->err_time?>");

  <?php } ?>

  <?php
  //this handles session based success message
  if($usSessionMessages['genMsg'] != ""){
  ?>
  notify("<?=htmlspecialchars_decode($usSessionMessages['genMsg'])?>", variant = 'primary', icon = 'info-square', duration = "<?=$settings->err_time?>");
  <?php

} ?>

});
</script>
