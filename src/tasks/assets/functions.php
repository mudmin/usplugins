<?php
// $notifTypes = [
//     '1'=>'Built in Email (Must be configured in Email Settings)',
//     '2'=>'Notification (Requires Messaging Plugin)',
//     '3'=>'Direct Message (Requires Messaging Plugin)',
//     '4'=>'Alert (Requires Messaging Plugin)',
//     '5'=>'Brevo/SendinBlue (Requires Brevo Plugin)',
//     '6'=>'Twilio SMS (Requires Twilio Plugin)',
// ];

function notifyTask($task, $action, $user_id, $message = "")
{
    global $plg_settings, $db, $user;

    if ($plg_settings->send_notification_type == 0) {
        return true;
    }
    if ($action == "assign") {
        $title = "New " . $plg_settings->single_term . " Assigned";
        $message = "You have been assigned a new " . $plg_settings->single_term . " entitled " . $task->title . ".<br>Description: " . $task->description . "<br><br>Please check the " . $plg_settings->plural_term . " page for more details.";
        $to = [];
        $fetch = $db->query("SELECT user_id FROM plg_tasks_assignments WHERE task_id = ?", [$task->id])->results();
        foreach ($fetch as $f) {
            $to[] = $f->user_id;
        }
    } elseif ($action == "complete") {
        $title = $plg_settings->single_term . " Completed";
        $message = $task->title . " " . $plg_settings->single_term . " has been marked complete. Please check the " . $plg_settings->plural_term . " page for more details.";
        $to[] = $task->created_by;
    } elseif ($action == "sub_complete") {
        $title = "Sub " . $plg_settings->single_term . " Completed";
        $message = $task->title . " Sub " . $plg_settings->single_term . " has been marked complete. Please check the " . $plg_settings->plural_term . " page for more details.";
        $to[] = $task->created_by;
    } elseif ($action == "close") {
        $title = $plg_settings->single_term . " Closed";
        $message = $task->title . " " . $plg_settings->single_term . " has been closed. Great work!";
        $fetch = $db->query("SELECT user_id FROM plg_tasks_assignments WHERE task_id = ?", [$task->id])->results();
        foreach ($fetch as $f) {
            $to[] = $f->user_id;
        }
    } elseif ($action == "incomplete") {
        $title = $plg_settings->single_term . " marked INCOMPLETE";
        $message = $task->title . " " . $plg_settings->single_term . " has been marked from complete to incomplete. Please check the " . $plg_settings->plural_term . " page for more details.";
        $fetch = $db->query("SELECT user_id FROM plg_tasks_assignments WHERE task_id = ?", [$task->id])->results();
        foreach ($fetch as $f) {
            $to[] = $f->user_id;
        }
    } elseif ($action == "updated") {
        $title = $plg_settings->single_term . " updated";
        $message = $task->title . " " . $plg_settings->single_term . " has been updated. Please check the " . $plg_settings->plural_term . " page for more details.";
        $fetch = $db->query("SELECT user_id FROM plg_tasks_assignments WHERE task_id = ?", [$task->id])->results();
        foreach ($fetch as $f) {
            $to[] = $f->user_id;
        }
    } elseif ($action == "comment") {
        $title = "New Comment on " . $task->title;
        $message = "A new comment has been added to " . $task->title . ". <br><b>$message</b></
        
        Please check the " . $plg_settings->plural_term . " page for more details.";
        $to = [];
        $fetch = $db->query("SELECT user_id WHERE task_id = ?", [$task->id])->results();
        foreach ($fetch as $f) {
            $to[] = $f->user_id;
        }
        $to[] = $task->created_by;
    }

    // foreach($to as $k=>$v){
    //     if($v == $user_id){
    //         unset($to[$k]);
    //     }
    // }
    if ($to == []) {
        return true;
    }

    if (($plg_settings->send_notification_type == 1 || $plg_settings->send_notification_type == 2 || $plg_settings->send_notification_type == 3) && pluginActive("messaging", true)) {
        foreach ($to as $t) {
            sendPlgMessage($t, $title, $message, $user_id, $plg_settings->send_notification_type);
        }
    }

    if ($plg_settings->send_notification_type == 4 || $plg_settings->send_notification_type == 5) {
        foreach ($to as $t) {
            $email = $db->query("SELECT email FROM users WHERE id = ?", [$t])->first()->email;
            if ($email != "" && $plg_settings->send_notification_type == 4) {
                email($t, $title, $message);
            } elseif ($email != "" && $plg_settings->send_notification_type == 5 && function_exists("sendinblue")) {
                //sendinblue
                sendinblue($email, $title, $message);
            }
        }
    }

    if ($plg_settings->send_notification_type == 2 || $plg_settings->send_notification_type == 3 || $plg_settings->send_notification_type == 4) {
        foreach ($to as $t) {
            $db->insert("plg_messages", [
                'to_user' => $t,
                'from_user' => $user->data()->id,
                'title' => $title,
                'message' => $message,
                'type' => $plg_settings->send_notification_type,
                'created_at' => date("Y-m-d H:i:s"),
            ]);
        }
    }
}

if (!function_exists("taskDT")) {

    function taskDT($datetime)
    {
        if ($datetime == null || $datetime == "") {
            return "";
        }
        return date("M d, Y - g:i a", strtotime($datetime));
    }
}

if (!function_exists("taskSecure")) {
    function taskSecure()
    {
        global $user, $plg_settings, $db, $admin_page, $is_task_admin;
        if ($is_task_admin == "") {
            $is_task_admin = isTaskAdmin();
        }

        if ($admin_page === "") {
            die("This page has not been set as admin or not admin. Please fix this");
        }
        $granted = false;
        if (!isset($user) || !$user->isLoggedIn()) {
            return $granted;
        }

        if (!$admin_page && isset($user) && $user->isLoggedIn()) {
            //if you are using this plugin outside of the userspice dashboard, it's your job to secure non-admin pages.
            $granted = true;
            return $granted;
        }

        if ($is_task_admin) {
            $granted = true;
            return $granted;
        } else {
            return $granted; //false
        }
    }
}

if (!function_exists("isTaskAdmin")) {
    function isTaskAdmin()
    {
        global $user, $plg_settings, $db;
        $granted = false;
        if (!isset($user) || !$user->isLoggedIn()) {
            return $granted;
        }

        if (pluginActive("usertags", true) && $plg_settings->creator_tags != "") {
            $tags = explode(",", $plg_settings->creator_tags);
            foreach ($tags as $t) {
                $check = $db->query("SELECT id FROM plg_tags_matches WHERE user_id = ? AND tag_id = ?", [$user->data()->id, $t])->count();
                if ($check > 0) {
                    $granted = true;
                    return $granted;
                }
            }
        }

        $perms = explode(",", $plg_settings->creator_perms);


        if (hasPerm($perms, $user->data()->id)) {
            $granted = true;
            return $granted;
        } else {
            return $granted; //false
        }
    }
}

if (!function_exists("taskCategoryBadge")) {
    function taskCategoryBadge($task)
    {
        if (!isset($task->color)) {
            $task->color = "#000000";
        }
        if (!isset($task->icon)) {
            $task->icon = "fa fa-list";
        }
        if (!isset($task->category_name)) {
            $task->category_name = "Uncategorized";
        }

        $resp = '<span class="hideSpan">' . $task->category_name . '</span>' .
            '<span class="badge" style="background-color:' . $task->color . '"><i class="' . $task->icon . '"></i> ' . $task->category_name . '</span>';

        return $resp;
    }
}

function taskPriorityBadge($priority)
{

    $color = getTaskPriorityColor($priority);
    return '<span class="badge" style="border:1px solid black; color:black; background-color:' . $color . '">
    <i class="fa fa-thumbtack"></i>
    ' . $priority . '</span>';
}

if (!function_exists("getTaskPriorityColor")) {
    function getTaskPriorityColor($priority)
    {
        // Ensure priority is within our expected range
        $priority = max(0, min(100, $priority));

        // Calculate green to red gradient based on priority
        // Green (low priority) to Red (high priority)
        if ($priority <= 50) {
            // From green to yellow (0 - 50)
            $red = floor(255 * ($priority / 50));
            $green = 255;
        } else {
            // From yellow to red (51 - 100)
            $red = 255;
            $green = floor(255 * ((50 - $priority % 50) / 50));
        }
        $blue = 0; // Not used for this gradient

        // Convert to hex code and return
        return sprintf("#%02x%02x%02x", $red, $green, $blue);
    }
}

function markTaskComplete($id)
{
    global $db, $user;
    $check = $db->query("SELECT id FROM plg_tasks_assignments WHERE task_id = ? ", [$id]);

    foreach ($check->results() as $a) {
        $db->update("plg_tasks_assignments", $a->id, ["completed" => 1]);
    }

    $db->update("plg_tasks", $id, ["completed" => 1, "marked_complete_by" => $user->data()->id, "marked_complete_on" => date("Y-m-d H:i:s")]);
}

function markTaskIncomplete($id) // updated to enable toggling of task completion at the main task level by the user
{
    global $db, $user;
    $check = $db->query("SELECT id FROM plg_tasks_assignments WHERE task_id = ? ", [$id]);

    foreach ($check->results() as $a) {
        $db->update("plg_tasks_assignments", $a->id, ["completed" => 0]);
    }

    $db->update("plg_tasks", $id, ["completed" => 0, "marked_complete_by" => null, "marked_complete_on" => null]);
}

function markTaskClosed($id)
{
    global $db, $user;
    $fields = [
        'closed' => 1,
        'closed_by' => $user->data()->id,
        'closed_on' => date("Y-m-d H:i:s"),
    ];

    $db->update("plg_tasks", $id, $fields);
    //dump($db->errorString());
    $check = $db->query("SELECT id FROM plg_tasks_assignments WHERE task_id = ? ", [$id]);
    foreach ($check->results() as $a) {
        $db->update("plg_tasks_assignments", $a->id, ["closed" => 1]);
        //dump($db->errorString());
    }
}
