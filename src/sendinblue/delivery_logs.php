<?php
require_once '../../../users/init.php';
if (!pluginActive('sendinblue', true) || !in_array($user->data()->id, $master_account)) {
  http_response_code(403);
  exit;
}
$escape = static function ($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
$email = trim((string)Input::get('email'));
$messageId = trim((string)Input::get('message_id'));
if ($messageId !== '') {
  sendinblueDeliveryStatus($messageId);
}
// The tags column arrives with migration 00008.
$hasTags = $db->columnExists('plg_sendinblue_messages', 'tags');
// Stored tags have commas folded to spaces, so fold the search the same way.
$tag = Input::get('tag');
$tag = ($hasTags && is_string($tag)) ? trim(str_replace(',', ' ', html_entity_decode($tag, ENT_QUOTES, 'UTF-8'))) : '';
$where = [];
$binds = [];
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $where[] = 'recipient_email = ?';
  $binds[] = $email;
} else {
  $email = '';
}
if ($tag !== '') {
  $where[] = 'FIND_IN_SET(?, tags) > 0';
  $binds[] = $tag;
}
$rows = $db->query('SELECT * FROM plg_sendinblue_messages' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 50', $binds)->results();
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
?>
<main class="container-fluid py-4">
  <h1>Brevo delivery evidence</h1>
  <p>A successful send means Brevo accepted the request. A <strong>delivered</strong> event means the recipient's mail server accepted the message. Inbox placement is outside Brevo's visibility.</p>
  <form method="get" class="row g-2 mb-4">
    <div class="col-auto"><label for="email" class="col-form-label">Recipient email</label></div>
    <div class="col-auto"><input id="email" name="email" type="email" class="form-control" value="<?= $escape($email) ?>"></div>
    <?php if ($hasTags): ?>
      <div class="col-auto"><label for="tag" class="col-form-label">Tag</label></div>
      <div class="col-auto"><input id="tag" name="tag" type="text" class="form-control" value="<?= $escape($tag) ?>"></div>
    <?php endif; ?>
    <div class="col-auto"><button class="btn btn-primary" type="submit">Find</button></div>
  </form>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead><tr><th>Submitted</th><th>Recipient</th><th>Sender</th><th>Type</th><?php if ($hasTags): ?><th>Tags</th><?php endif; ?><th>Delivered to server</th><th>Latest Brevo event</th><th>Event time</th><th>Reason</th><th>Message ID</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?= $escape($row->accepted_at) ?></td>
          <td><?= $escape($row->recipient_email) ?></td>
          <td><?= $escape($row->sender_email) ?></td>
          <td><?= $escape($row->kind) ?></td>
          <?php if ($hasTags): ?><td><?= $escape(str_replace(',', ', ', (string)$row->tags)) ?></td><?php endif; ?>
          <td><?= $escape($row->delivered_at) ?></td>
          <td><?= $escape($row->status) ?></td>
          <td><?= $escape($row->last_event_at) ?></td>
          <td><?= $escape($row->last_reason) ?></td>
          <td><code><?= $escape($row->message_id) ?></code></td>
          <td><a href="?message_id=<?= rawurlencode($row->message_id) ?>&amp;email=<?= rawurlencode($email) ?>&amp;tag=<?= rawurlencode($tag) ?>">Refresh</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="small">Brevo's event API can be queried for up to 90 days. An opening may be caused by a security scanner. A later bounce can follow an earlier delivery event.</p>
</main>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
