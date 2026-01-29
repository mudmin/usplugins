<?php 
//if you include this view somewhere else, you need to set $messages to the messages you want to display
//see usersc/plugins/messaging/assets/views/_admin_message_send.php for an example
?>
<div class="card">
        <div class="card-header"><b>Previous system messages</b></div>
            <div class="card-body">
                <table class="table table-hover table-striped paginate">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th class="text-center">Sent On</th>
                        <th class="text-end">Sent To</th>
                        <th class="text-end">Read By</th>
                        <th class="text-end">Read Percent</th>
                        <th class="text-center">Expires</th>
                        <th class="text-center">Send Method</th>
                        <th>Audit</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach($messages as $m){ ?>
                            <tr>
                                <td><?=$m->id?></td>
                                <td>
                                <a href="message_audit.php?id=<?=$m->id?>" target="_blank">
                                    <?=$m->title?>
                                </a>
                                </td>
                                <td class="text-center"><?=formatMsgDate($m->msg_sent_on)?></td>
                                <td class="text-end"><?=$m->recipients?></td>
                                <td class="text-end"><?=$m->msg_read_count?></td>
                                <td class="text-end"><?=round(($m->msg_read_count / $m->recipients) * 100, 2)?>%</td>
                                <td class="text-center
                                <?php if($m->expires == 1 && $m->msg_expires_on < date("Y-m-d")){ ?>
                                    text-danger
                                <?php } ?>
                                "><?=formatMsgDate($m->msg_expires_on,false)?></td>
                                <td class="text-center"><?=$m->send_method?></td>
                                <td>
                                    <a href="message_audit.php?id=<?=$m->id?>" class="btn btn-sm btn-outline-primary" target="_blank">Audit</a>
                                </td>
                            </tr>
                        
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>