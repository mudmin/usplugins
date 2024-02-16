<?php
$admin_page = true;
taskSecure();
// type 1 = alert
// type 2 = notification
// type 3 = message
$notifTypes = [
    '1' => 'Alert (Requires Messaging Plugin)',
    '2' => 'Notification (Requires Messaging Plugin)',
    '3' => 'Direct Message (Requires Messaging Plugin)',
    '4' => 'Built in Email (Must be configured in Email Settings)',
    '5' => 'Brevo/SendinBlue (Requires Brevo Plugin)',
    // '6'=>'Twilio SMS (Requires Twilio Plugin)',
];

$plgSet = $db->query("SELECT * FROM plg_tasks_settings")->first();
if (!empty($_POST['save'])) {
    if (!Token::check($_POST['csrf'])) {
        usError("Token Failed");
        Redirect::to($basePage . "method=manage&id=" . $id);
    }

    $fields = [
        'plugin_name' => Input::get('plugin_name'),
        'alternate_location' => Input::get('alternate_location'),
        'single_term' => Input::get('single_term'),
        'plural_term' => Input::get('plural_term'),
        'assign_to_individual' => Input::get('assign_to_individual'),
        'send_notification_type' => Input::get('send_notification_type')
    ];
    $creator_perms = Input::get('creator_perms');
    $creator_perms[2] = "2";
    $fields['creator_perms'] = implode(",", $creator_perms);
    $fields['creator_perms'] = $fields['creator_perms'] == "" ? "" : $fields['creator_perms'];

    $db->update('plg_tasks_settings', 1, $fields);

    usSuccess("Settings Saved");
    Redirect::to($basePage . "method=settings");
}
$creator_perms = explode(",", $plgSet->creator_perms);
$permissions = $db->query("SELECT * FROM permissions")->results();

?>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <form action="" method="post">
                <?= tokenHere(); ?>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <h5>Plugin Settings</h5>
                        </div>
                        <div class="col-12 col-md-6 text-end">
                            <input type="submit" name="save" value="Save Settings" class="btn btn-primary">
                        </div>

                    </div>

                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label for="" class="">System Name</label>
                            <input type="text" class="form-control" name="plugin_name" value="<?= $plgSet->plugin_name ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <label for="" class="">Alternate Location (from UserSpice Root)</label>
                            <input type="text" class="form-control" name="alternate_location" value="<?= $plgSet->alternate_location ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label for="" class="">Single Term</label>
                            <input type="text" class="form-control" name="single_term" value="<?= $plgSet->single_term ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <label for="" class="">Plural Term</label>
                            <input type="text" class="form-control" name="plural_term" value="<?= $plgSet->plural_term ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <label for="" class="">Allow Assign to Individuals</label>
                            <select name="assign_to_individual" class="form-select">
                                <option value="1" <?= $plgSet->assign_to_individual == 1 ? "selected" : "" ?>>Yes</option>
                                <option value="0" <?= $plgSet->assign_to_individual == 0 ? "selected" : "" ?>>No</option>
                            </select>
                            <small>You may want to disable this if you have a ton of users. You will need to install the User Tags plugin to group your users.</small>

                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <label for="" class="">Send Notifications for new <?= $plg_settings->plural_term ?> and Comments</label>
                            <select name="send_notification_type" id="" class="form-select">
                                <option value="" disabled selected="selected">--No Notifications--</option>
                                <?php foreach ($notifTypes as $k => $v) { ?>
                                    <option value="<?= $k ?>" <?= $plgSet->send_notification_type == $k ? "selected" : "" ?>><?= $v ?></option>
                                <?php } ?>
                            </select>

                        </div>
                        <div class="col-12">
                            <h5>Creator Permissions</h5>
                            <small>These are the permissions that a user must have to be able to create a task.</small>
                            <div class="row">
                                <?php foreach ($permissions as $p) { ?>
                                    <div class="col-12 col-sm 6 col-md-4 col-lg-3">
                                        <input type="checkbox" value="<?= $p->id ?>" name="creator_perms[<?= $p->id ?>]" <?php if (in_array($p->id, $creator_perms)) {
                                                                                                                                echo "checked";
                                                                                                                            }
                                                                                                                            if ($p->id == "2") {
                                                                                                                                echo "readonly checked disabled title='Admins can always create tasks'";
                                                                                                                            }
                                                                                                                            ?>> <?= $p->name ?>

                                    </div>
                                <?php } ?>
                            </div>
                        </div>


                    </div>
            </form>
        </div>
    </div>
</div>
</div>