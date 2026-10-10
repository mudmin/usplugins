<?php
require_once '../../../users/init.php';
if (!pluginActive('sendinblue', true)) {
  http_response_code(404);
  exit;
}
$mailSettingsRow = $db->query('SELECT `from`, public_delivery_status FROM plg_sendinblue WHERE id = 1')->first();
$tracking = !empty($mailSettingsRow->public_delivery_status) ? ($_SESSION['sendinblue_passwordless'] ?? null) : null;
if (!is_array($tracking) || ($tracking['accepted_at'] ?? 0) < time() - 3600) {
  $tracking = null;
}
$delivery = $tracking && !empty($tracking['message_id'])
  ? sendinblueDeliveryStatus($tracking['message_id']) : null;
$sender = $tracking['sender_email'] ?? '';
if ($sender === '') {
  $sender = $mailSettingsRow->from ?? '';
}
$escape = static function ($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
$sitePrefix = ($settings->site_name ?? 'UserSpice') !== 'UserSpice' ? $settings->site_name . ': ' : '';
if (in_array((int)($settings->email_login ?? 0), [2, 3], true)
    && lang('PASS_YOUR_CODE') !== '{ Missing Text }') {
  $subjectHint = $sitePrefix . html_entity_decode(lang('PASS_YOUR_CODE'), ENT_QUOTES);
  $subjectSuffix = ' followed by your one-time code';
} else {
  $translatedSubject = lang('EML_PASSWORDLESS_SUBJECT');
  $subjectHint = $sitePrefix . ($translatedSubject !== '{ Missing Text }'
    ? $translatedSubject : 'Please verify your email to login.');
  $subjectSuffix = ' followed by a timestamp';
}
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
?>
<main class="container py-4" style="max-width: 860px">
  <h1>Didn't get your sign-in email?</h1>
  <p>The message may be in a different folder or held by your organization's mail system. Your sign-in code expires after 15 minutes.</p>

  <section class="card my-4">
    <div class="card-body">
      <h2 class="h4">First, check your mailbox</h2>
      <ol>
        <li>Search <strong>All mail</strong> for a recent sign-in or verification message from <strong><?= $escape($sender ?: 'this site') ?></strong>. In Outlook, also search outside the Focused inbox by selecting <strong>Other</strong>.</li>
        <li>Check <strong>Spam</strong>, <strong>Junk</strong>, <strong>Promotions</strong>, <strong>Clutter</strong>, and any quarantine or held-mail notice.</li>
        <li>Make sure you are checking the same address you entered on the sign-in page. If your organization forwards mail, check the destination mailbox too.</li>
        <li>If the message arrives after the code expires, return to sign-in and request a new one. Use only the newest code.</li>
      </ol>
      <p class="mb-0"><strong>Subject begins with:</strong> <?= $escape($subjectHint) ?><?= $escape($subjectSuffix) ?>. The actual subject is deliberately not shown because it may contain your one-time code.</p>
    </div>
  </section>

  <section class="card my-4">
    <div class="card-body">
      <h2 class="h4">Still missing? Send this to your IT team</h2>
      <p>Please ask your mail administrator to trace a recent message from <strong><?= $escape($sender ?: 'the site sender') ?></strong> to your address, including spam, quarantine, forwarding, and mail-flow rules.</p>
      <?php if ($tracking): ?>
        <dl class="row mb-3">
          <dt class="col-sm-4">Recipient</dt><dd class="col-sm-8"><?= $escape($tracking['recipient_email'] ?? '') ?></dd>
          <dt class="col-sm-4">Mail Server message ID</dt><dd class="col-sm-8"><code><?= $escape($tracking['message_id'] ?? '') ?></code></dd>
          <dt class="col-sm-4">Submitted</dt><dd class="col-sm-8"><?= $escape(date('Y-m-d H:i:s T', $tracking['accepted_at'])) ?></dd>
          <dt class="col-sm-4">Latest Mail Server event</dt><dd class="col-sm-8"><?= $escape($delivery->status ?? 'Not available') ?></dd>
          <dt class="col-sm-4">Accepted by recipient server</dt><dd class="col-sm-8"><?= $escape($delivery->delivered_at ?? 'Not reported') ?></dd>
        </dl>
      <?php endif; ?>
      <p>Our Mail Server sends this sign-in email. A <em>delivered</em> event means the recipient's mail server accepted the message; it does not prove inbox placement. Please check your gateway's message trace for this message ID, any post-acceptance quarantine or deletion, and the recipient's mailbox rules.</p>
      <p>Check the message's <code>Authentication-Results</code> for SPF, DKIM, and DMARC, including alignment with the visible From domain. Those results must be checked on the actual message; this page cannot attest to them. If your mail system rejected the message, its SMTP response and reason are useful to our support team.</p>
      <p class="mb-0">For security, please do not forward a sign-in code or link to anyone, including support.</p>
    </div>
  </section>
  <p><a href="<?= $escape($us_url_root . 'users/passwordless.php') ?>">Return to sign-in</a></p>
</main>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
