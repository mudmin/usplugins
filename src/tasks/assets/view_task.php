<?php
if (in_array($method, $admin_methods)) {
    $admin_page = true;
} else {
    $admin_page = false;
}
taskSecure();
$id = Input::get("id");
$simple = true;

$taskQ = $db->query("SELECT
a.*,
t.*,
c.*,
c.child_table as child_table
FROM plg_tasks_assignments a
LEFT OUTER JOIN plg_tasks t ON a.task_id = t.id 
LEFT OUTER JOIN plg_tasks_categories c ON c.id = t.category_id
WHERE t.id = ? AND a.user_id = ?
ORDER BY t.due_date ASC, t.priority DESC
LIMIT 500", [$id, $user->data()->id]);

$taskC = $taskQ->count();
if ($taskC < 1) {
    usError($plg_settings->single_term . " not found");
    Redirect::to($basePage . "method=tasks");
}

$task = $taskQ->first();
$child_table = $task->child_table;
$requiredSubsComplete = false;
$subsQ = $db->query("SELECT * FROM  {$child_table} WHERE task_id = ?", [$id]);
$subsC = $subsQ->count();
$subs = $subsQ->results();
foreach ($subs as $s) {
    if ($s->line_required == 1 && $s->completed == 0) {
        $requiredSubsComplete = false;
        break;
    } else {
        $requiredSubsComplete = true;
    }
}
if ($subsC > 0) {
    $simple = false;
}

if ($simple) {
    $col = "offset-sm-3";
} else {
    $col = "";
}
$badgeClass = "me-2 pb-2 badge badge-sm ";
if ($task->closed == 1) {
    //closed badge
    $closed = "<div class='" . $badgeClass . " bg-danger'>Closed</div>";
} else {
    $closed = "<div class='" . $badgeClass . " bg-success'>Open</div>";;
}


if ($task->completed == 1) {

    $completed = "<div class='" . $badgeClass . "' style='background-color:blue;'>Marked Completed</div>";
} else {
    if ($task->closed == 1) {
        $completed = "<div class='" . $badgeClass . "'  style='background-color:gray;'>Not Completed</div>";
    } else {
        $completed = "";
    }
}


$dt = date("Y-m-d H:i:s");
$action = Input::get('action');

if ($action == "complete_sub") {
    $sub_id = Input::get('sub_id');
    $sub = $db->query("SELECT * FROM {$child_table} WHERE id = ?", [$sub_id])->first();
    if ($sub->completed == 1) {
        usError("Sub " . $plg_settings->single_term . " Already Marked Complete");
        Redirect::to($basePage . "method=view_task&id=" . $id);
    } else {
        $db->update($child_table, $sub_id, ['completed' => 1,'completed_by' => $user->data()->id, 'completed_on' => date("Y-m-d H:i:s")]); //updated to include completed_by and completed_on info
        usSuccess("Sub " . $plg_settings->single_term . " Marked Complete");
        notifyTask($task, "sub_complete", $user->data()->id, $message = "");
        Redirect::to($basePage . "method=view_task&id=" . $id);
    }
}


// if(Input::get('complete_sub') == "true"){

//     $db->update($child_table, $sub_id, ['completed' => 1]);
//     usSuccess("Sub " . $plg_settings->single_term . " Marked Complete");
//     Redirect::to($basePage . "method=view_task&id=" . $task_id);
// }

if (Input::get('mark_complete') == "true" && ($subsC < 1 || $requiredSubsComplete)) {
    markTaskComplete($id);
    usSuccess("Task Marked Complete");
    $_SESSION['launchTaskConfetti'] = true;
    notifyTask($task, "complete", $user->data()->id, $message = "");
    Redirect::to($basePage . "method=view_task&id=" . $id);
}

if (Input::get('mark_complete') == "false") {
    markTaskIncomplete($id);
    usSuccess("Task Marked Incomplete");
    notifyTask($task, "incomplete", $user->data()->id, $message = "");
    Redirect::to($basePage . "method=view_task&id=" . $id);
}

if ($is_task_admin && Input::get('mark_closed') == "true") {
    markTaskClosed($id);
    usSuccess("Task Marked Closed");
    // notifyTask($task,"close", $user->data()->id, $message = "");
    Redirect::to($basePage . "method=view_task&id=" . $id);
}

?>

<h3 class="text-center">
    <?= $task->title ?> <?= $closed ?> <?= $completed ?>
</h3>
<h5 class="text-center">
    <?php
    if ($task->due_date < $dt) {
        $due_date = "<div class='" . $badgeClass . "bg-danger'>Past Due (" . taskDT($task->due_date) . ")</div>";
    } else {
        $due_date = "<div class='" . $badgeClass . "bg-success'>Due: " . taskDT($task->due_date) . "</div>";
    }
    echo $due_date;
    ?>
</h5>

<div class="row">
    <div class="col-12 col-sm-6 <?= $col ?>">
        <div class="card mb-2">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md-6"><?= $plg_settings->single_term ?> Information</div>
                    <div class="col-12 col-md-6 text-end"><?= taskCategoryBadge($task); ?>
                        <?= taskPriorityBadge($task->priority) ?>

                    </div>
                </div>

            </div>
            <div class="card-body">

                <?php if ($task->completed == 0 || ($task->closed == 0 && $is_task_admin)) { ?>
                    <p class="text-end">
                        <?php if ($task->completed == 0 && ($subsC < 1 || $requiredSubsComplete)) { ?>
                            <a href="<?= $basePage ?>method=view_task&mark_complete=true&id=<?= $id ?>" class="btn btn-outline-success btn-sm">Mark <?= $plg_settings->single_term ?> Complete</a>
                        <?php } elseif ($task->completed == 1 && ($subsC < 1 || $requiredSubsComplete)) { ?>
                            <a href="<?= $basePage ?>method=view_task&mark_complete=false&id=<?= $id ?>" class="btn btn-outline-danger btn-sm">Mark <?= $plg_settings->single_term ?> Incomplete</a>
                        <?php } ?>

                        <?php if ($task->closed == 0 && $is_task_admin) { ?>
                            <a href="<?= $basePage ?>method=view_task&mark_closed=true&id=<?= $id ?>" class="btn btn-outline-danger btn-sm">Mark <?= $plg_settings->single_term ?> Closed</a>
                        <?php } ?>
                    </p>
                <?php } ?>


                <p class="card-text"><b>Title:</b> <?= $task->title ?></p>

                <p class="card-text"><b>Description:</b></p>
                <p class="card-text"><?= $task->description ?></p>
            </div>
        </div>

    </div>
    <?php if (!$simple) { ?>
        <div class="col-12 col-sm-6">
            <div class="card mb-2">
                <div class="card-header">
                    Sub <?= $plg_settings->plural_term ?>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Required</th>
                                <th>Status</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subs as $sub) { ?>
                                <tr>
                                    <td><?= $sub->line ?></td>
                                    <td><?= bin($sub->line_required) ?></td>
                                    <td>
                                        <?php if ($sub->completed == 1) { ?>
                                            <span class="badge" style="background-color:blue;">Marked Complete</span>
                                        <?php } else { ?>
                                            <a href="<?= $basePage ?>method=view_task&action=complete_sub&sub_id=<?= $sub->id ?>&id=<?= $id ?>" class="btn btn-outline-success btn-sm">Mark Complete</a>
                                        <?php } ?>
                                    </td>

                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php } else {
        require_once $abs_us_root . $us_url_root . 'usersc/plugins/tasks/assets/_comments.php';
    } ?>
</div>
<?php if (!$simple) {
    echo "<br>";
    require_once $abs_us_root . $us_url_root . 'usersc/plugins/tasks/assets/_comments.php';
} ?>