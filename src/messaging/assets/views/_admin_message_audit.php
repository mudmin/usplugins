<?php
// Per-message audit view: who received a system message and who has read it.
// Reached via admin.php?view=plugins_config&plugin=messaging&mode=admin_message_audit&id=<msg id>

if (!hasPerm(2)) {
    usError("This page is admin only.");
    Redirect::to("index.php");
}

$auditId = (int) Input::get('id');

$backLink = $us_url_root . 'users/admin.php?view=plugins_config&plugin=messaging&mode=admin_message_send';

$message = null;
if ($auditId > 0) {
    $message = $db->query(
        "SELECT * FROM plg_msg_messages WHERE id = ?",
        [$auditId]
    )->first();
}

if (!$message) {
    ?>
    <div class="alert alert-warning mt-3">
        No message found for that ID.
        <a href="<?= safeReturn($backLink) ?>">Back to messages</a>
    </div>
    <?php
    return;
}

$recipients = $db->query(
    "SELECT p.user_to, p.msg_read, p.msg_read_on, p.deleted,
            u.fname, u.lname, u.username, u.email
     FROM plg_msg AS p
     LEFT JOIN users AS u ON p.user_to = u.id
     WHERE p.msg_id = ?
     ORDER BY p.msg_read ASC, u.lname ASC, u.fname ASC",
    [$auditId]
)->results();

$totalRecipients = count($recipients);
$readCount = 0;
foreach ($recipients as $r) {
    if ($r->msg_read == 1) {
        $readCount++;
    }
}
$readPercent = $totalRecipients > 0 ? round(($readCount / $totalRecipients) * 100, 2) : 0;
?>
<div class="mt-3">
    <a href="<?= safeReturn($backLink) ?>">&laquo; Back to messages</a>
</div>

<div class="card mt-2">
    <div class="card-header"><b>Message Audit &mdash; <?= safeReturn($message->title) ?></b></div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <p class="mb-1"><b>Sent On:</b> <?= safeReturn(formatMsgDate($message->msg_sent_on)) ?></p>
                <p class="mb-1"><b>Send Method:</b> <?= safeReturn($message->send_method) ?></p>
                <p class="mb-1"><b>Expires:</b> <?= safeReturn(formatMsgDate($message->msg_expires_on, false)) ?></p>
            </div>
            <div class="col-12 col-md-6">
                <p class="mb-1"><b>Recipients:</b> <?= $totalRecipients ?></p>
                <p class="mb-1"><b>Read By:</b> <?= $readCount ?></p>
                <p class="mb-1"><b>Read Percent:</b> <?= $readPercent ?>%</p>
            </div>
        </div>
        <hr>
        <b>Message:</b>
        <div class="border rounded p-2 mt-1"><?= trustedHtml($message->msg) ?></div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><b>Recipients</b></div>
    <div class="card-body">
        <?php if ($totalRecipients == 0) { ?>
            <p class="text-muted mb-0">No recipient records found for this message.</p>
        <?php } else { ?>
        <table class="table table-hover table-striped paginate">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Read On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recipients as $r) {
                    $name = trim($r->fname . ' ' . $r->lname);
                    if ($name == '') {
                        $name = $r->username ?? '(deleted user)';
                    }
                ?>
                <tr>
                    <td>
                        <?= safeReturn($name) ?>
                        <?php if ($r->deleted == 1) { ?>
                            <span class="badge bg-secondary">deleted</span>
                        <?php } ?>
                    </td>
                    <td><?= safeReturn($r->username) ?></td>
                    <td><?= safeReturn($r->email) ?></td>
                    <td class="text-center">
                        <?php if ($r->msg_read == 1) { ?>
                            <span class="badge bg-success">Read</span>
                        <?php } else { ?>
                            <span class="badge bg-warning text-dark">Unread</span>
                        <?php } ?>
                    </td>
                    <td class="text-center"><?= safeReturn(formatMsgDate($r->msg_read_on)) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
</div>
