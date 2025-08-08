<?php
$id = Input::get('id');
$source = Input::get('source');
if ($source == "") {
    $source = "messages";
}
$msgQ = $db->query("SELECT * FROM plg_msg_messages WHERE id = ?", [$id]);
$msgC = $msgQ->count();
if ($msgC == 0) {
    usError("Message not found");
    Redirect::to($source . ".php");
}
$msg = $msgQ->first();
$recip = $db->query("SELECT 
n.*, 
u.fname,
u.lname
FROM plg_msg n 
LEFT OUTER JOIN users u on u.id = n.user_to 
WHERE n.msg_id = ?", [$id])->results();

?>
<h4 class="text-center">Message Audit</h4>
<div class="row">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Message Details</h5>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="">Title</label>
                    <input type="text" class="form-control" value="<?= $msg->title ?>" disabled readonly>
                </div>

                <div class="form-group mb-3">
                    <label for="">Message</label>
                    <div style="border:1px solid black; padding:.5rem;">
                        <?= hed($msg->msg); ?>
                    </div>

                </div>


                <div class="form-group mb-3">
                    <label for="">Message Properies</label>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="fw-bold">Sent On</td>
                                <td><?= $msg->msg_sent_on ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Sent By</td>
                                <td>
                                    <?php if ($msg->user_from == 0) {
                                        echo "<em>System - </em> ";
                                        if ($msg->sent_by != 0) {
                                            echo " triggered by:";
                                            echouser($msg->sent_by);
                                        } else {
                                            echo " trigged automatically";
                                        }
                                    } else {
                                        echouser($msg->user_from);
                                    } ?>

                                </td>

                            <tr>
                                <td class="fw-bold">Sent To</td>
                                <td>
                                    <?php if ($msg->recipients == 1) {
                                        echo "1 recipient";
                                    } else {
                                        echo $msg->recipients . " recipients";
                                    }
                                    ?>

                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Send Method</td>
                                <td><?= $msg->send_method ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Expires</td>
                                <td><?= $msg->msg_expires_on ?></td>
                            </tr>
                            </tr>
                        </tbody>
                    </table>

                </div>


            </div>
        </div>


    </div>
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Message Recipients</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>

                        <tr>
                            <th>Recipient</th>
                            <th>Read On</th>
                            <th>Deleted</th>
                        </tr>

                    </thead>
                    <tbody>
                        <?php foreach ($recip as $r) {
                            if ($r->fname == "" && $r->lname == "" && $r->msg_read_on == "") {
                                continue;
                            }
                        ?>
                            <tr>
                                <td><?= $r->fname ?> <?= $r->lname ?></td>
                                <td>
                                    <?php if ($r->msg_read_on == "") { ?>
                                        <span class="text-muted"><em>unread</em></span>
                                    <?php } else {
                                        echo  $r->msg_read_on;
                                    }
                                    ?>
                                </td>
                                <td><?=bin($r->deleted);?></td>
                            </tr>
                        <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>