<?php
$usSessionMessages = parseSessionMessages();
// $usSessionMessages['valErr'] = "Something went wrong!@!!!";
// $usSessionMessages['valSuc'] = "Every little thing....is gonna be alright";
// $usSessionMessages['genMsg'] = "This is a system message";
// dump($usSessionMessages);
$settings->err_time = $settings->err_time * 1000;
?>
<div class="alert-toast-wrapper"></div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.20.0/cdn/themes/light.css" />
<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.20.0/cdn/shoelace-autoloader.js"></script>

<script type="module">
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
  
  // Create alert element
  const alert = document.createElement('sl-alert');
  alert.variant = variant;
  alert.closable = true;
  alert.duration = duration;
  
  // Set inner HTML with icon
  alert.innerHTML = `
    <sl-icon name="${icon}" slot="icon"></sl-icon>
    ${processedMessage}
  `;

  document.body.append(alert);
  
  // Wait for the custom element to be defined before calling toast()
  return customElements.whenDefined('sl-alert').then(() => {
    return alert.toast();
  });
}

// Wrap the PHP generated JavaScript in a module-compatible function
document.addEventListener('DOMContentLoaded', function() {
  <?php
  //this handles err= in the URL
  if(Input::get('err') != ""){
  ?>
  notify(<?=json_encode(alerts_sanitizeMessage(Input::get('err')), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>, 'primary', 'question-circle', <?=$settings->err_time?>);

  <?php }

  //this handles msg= in the URL
  if(Input::get('msg') != ""){
  ?>
  notify(<?=json_encode(alerts_sanitizeMessage(Input::get('msg')), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>, 'neutral', 'chat-left-dots', <?=$settings->err_time?>);

  <?php }

  //this handles session based error message
  if($usSessionMessages['valErr'] != ""){
  ?>
  notify(<?=json_encode(alerts_sanitizeMessage($usSessionMessages['valErr']), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>, 'danger', 'exclamation-circle', <?=$settings->err_time?>);

  <?php }
  //this handles session based success message
  if($usSessionMessages['valSuc'] != ""){
  ?>
  notify(<?=json_encode(alerts_sanitizeMessage($usSessionMessages['valSuc']), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>, 'success', 'check', <?=$settings->err_time?>);

  <?php } ?>

  <?php
  //this handles session based success message
  if($usSessionMessages['genMsg'] != ""){
  ?>
  notify(<?=json_encode(alerts_sanitizeMessage($usSessionMessages['genMsg']), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>, 'primary', 'info-square', <?=$settings->err_time?>);
  <?php } ?>
});
</script>