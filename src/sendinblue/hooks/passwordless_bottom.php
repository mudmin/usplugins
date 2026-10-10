<?php
if (Input::get('method') !== 'check_email') {
  return;
}
// Without the override, passwordless mail goes through core email(), not Brevo,
// so there is no Brevo delivery to report on or help with.
if (!file_exists(dirname(__DIR__) . '/override.php')) {
  return;
}
$helpUrl = $us_url_root . 'usersc/plugins/sendinblue/delivery_help.php';
$mailSettingsRow = $db->query('SELECT * FROM plg_sendinblue WHERE id = 1')->first();
$tracking = $_SESSION['sendinblue_passwordless'] ?? null;
$showStatus = !empty($mailSettingsRow->public_delivery_status) && is_array($tracking)
  && !empty($tracking['message_id']) && ($tracking['accepted_at'] ?? 0) >= time() - 3600
  && strcasecmp((string)($tracking['recipient_email'] ?? ''), rawurldecode((string)Input::get('email'))) === 0;
$statusUrl = $us_url_root . 'usersc/plugins/sendinblue/assets/parsers/delivery_status.php';
?>
<?php if ($showStatus): ?>
<div class="alert alert-info mt-4" role="status" aria-live="polite" id="mail-delivery-status">
  Checking the delivery status of your sign-in email…
</div>
<script>
(() => {
  const statusElement = document.getElementById('mail-delivery-status');
  const endpoint = <?= json_encode($statusUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const csrf = <?= json_encode(Token::generate(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  let checks = 0;
  async function checkDelivery() {
    checks++;
    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf}).toString()
      });
      const result = await response.json();
      statusElement.textContent = result.message || 'Delivery status is unavailable.';
      statusElement.className = 'alert mt-4 ' + (result.state === 'delivered' ? 'alert-success' : result.state === 'problem' ? 'alert-warning' : 'alert-info');
      if (result.state === 'delivered' || result.state === 'problem' || result.state === 'unavailable') return;
    } catch (error) {
      statusElement.textContent = 'Delivery status is temporarily unavailable. You can still use the help link below.';
    }
    if (checks < 12) window.setTimeout(checkDelivery, 10000);
    else statusElement.textContent += ' Automatic checks have stopped; refresh this page to check again.';
  }
  checkDelivery();
})();
</script>
<?php endif; ?>
<p class="text-center mt-4 mb-0">
  <a href="<?= htmlspecialchars($helpUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Didn't get the email?</a>
</p>
