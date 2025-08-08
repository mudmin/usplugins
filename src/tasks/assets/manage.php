<?php
$admin_page = true;
$customPage = $abs_us_root . $us_url_root . $plg_settings->alternate_location . '/includes/during_task_assignment.php';
taskSecure();
$notifyAssign = false;
$id = Input::get('id');
$taskQ = $db->query("SELECT 
    t.*,
    c.id as category_id,
    c.category_name as category_name,
    c.child_table as child_table,
    c.has_subitems as has_subitems
    FROM plg_tasks t 
    LEFT OUTER JOIN plg_tasks_categories c ON c.id = t.category_id
    WHERE t.id = ?", [$id]);
if ($taskQ->count() < 1) {
    usError("Task not found");
    Redirect::to($basePage);
}
$task = $taskQ->first();
$assignmentsQ = $db->query("SELECT 
a.*, 
u.fname as fname,
u.lname as lname
FROM plg_tasks_assignments a
LEFT OUTER JOIN users u ON u.id = a.user_id
WHERE a.task_id = ?", [$id]);
$assignmentsC = $assignmentsQ->count();
$assignments = $assignmentsQ->results();
$tags = [];
$users = [];
if (pluginActive("usertags", true)) {
    $fetch = $db->query("SELECT * FROM plg_tags")->results();
    foreach ($fetch as $f) {
        $tags[$f->id] = $f->tag;
    }
    //sort tags by value
    asort($tags);
}

if ($assignmentsC < 1 && $tags == [] && $plg_settings->assign_to_individual == 0) {
    usError("You must have the User Tags plugin installed if you are going to disable assigning to an individual.");
    Redirect::to($basePage);
}

if ($plg_settings->assign_to_individual == 1) {
    $users = $db->query("SELECT id,fname,lname,email FROM users ORDER BY fname")->results();
}

$close_task = Input::get('close_entire_task');

if ($close_task != "") {
    if (!Token::check($_POST['csrf'])) {
        usError("Token Failed");
        Redirect::to($basePage . "method=manage&id=" . $id);
    }
    $db->update('plg_tasks', $id, ['closed' => 1]);

    usSuccess($plg_settings->single_term . " Closed and Updated");
    Redirect::to($basePage);
}

if (!empty($_POST['updateTask'])) {
    //token check
    if (!Token::check($_POST['csrf'])) {
        usError("Token Failed");
        Redirect::to($basePage . "method=manage&id=" . $id);
    }



    $keep = Input::get('keep_existing_assignments');
    //update base task
    $fields = [
        'title' => Input::get('title'),
        'description' => Input::get('description'),
        'due_date' => Input::get('due_date'),
        'priority' => Input::get('priority'),
    ];
    $db->update('plg_tasks', $id, $fields);


    //update assignments
    $assign = false;
    $assign_user = Input::get('assign_to_user');
    $assign_tag = Input::get('assign_to_tags');


    if (($assign_user != "" || $assign_tag != "") && $keep != "1") {

        $db->query("DELETE FROM plg_tasks_assignments WHERE task_id = ?", [$id]);
        $assign = true;
    }

    if ($assign_user != "") {
        $notifyAssign = true;
        $check = $db->query("SELECT * FROM plg_tasks_assignments WHERE task_id = ? AND user_id = ?", [$id, $assign_user])->count();

        if ($check < 1) {
            $assign = true;
            $db->insert("plg_tasks_assignments", [
                'task_id' => $id,
                'user_id' => $assign_user,
                'assigned_on' => date("Y-m-d H:i:s"),
                'assigned_by' => $user->data()->id,
            ]);
            dump($db->errorString());
            if (file_exists($customPage)) {
                include $customPage;
            }
        }
    }
    if ($assign_tag != "") {
        $notifyAssign = true;
        $tag_users = $db->query("SELECT * FROM plg_tags_matches WHERE tag_id = ?", [$assign_tag])->results();


        foreach ($tag_users as $u) {
            $check = $db->query("SELECT * FROM plg_tasks_assignments WHERE task_id = ? AND user_id = ?", [$id, $u->user_id])->count();
            if ($check < 1) {
                $assign = true;
                $assigned_user = $u->user_id;
                $db->insert("plg_tasks_assignments", [
                    'task_id' => $id,
                    'user_id' => $assigned_user,
                    'assigned_on' => date("Y-m-d H:i:s"),
                    'assigned_by' => $user->data()->id,
                ]);
                if (file_exists($customPage)) {
                    include $customPage;
                }
            }
        }
    }
    if ($notifyAssign) {
        notifyTask($task, "assign", $user->data()->id, $message = "");
    }
    $new_sub = Input::get('new_sub');
    $new_sub_required = Input::get('new_sub_required');
    $msg = "";
    if ($new_sub != "") {
        $counter = 0;
        foreach ($new_sub as $k => $v) {
            if ($v != "") {
                $counter++;
                $required = $new_sub_required[$k];
                $db->insert($task->child_table, [
                    'task_id' => $id,
                    'line' => $v,
                    'line_required' => $required
                ]);
            }
        }
        if (!$notifyAssign) {
            notifyTask($task, "updated", $user->data()->id, $message = "");
        }

        if ($counter == 1) {
            $msg = $counter . " new sub task added";
        } else {
            $msg = $counter . " new sub tasks added";
        }
    }
    $mark_complete = Input::get('mark_complete');
    $mark_incomplete = Input::get('mark_incomplete');
    $delete_sub = Input::get('delete_sub');
    if ($mark_complete != "") {
        foreach ($mark_complete as $k => $v) {
            $db->update($task->child_table, $k, ['completed' => 1, 'completed_by' => $user->data()->id, 'completed_on' => date("Y-m-d H:i:s")]);
        }
        notifyTask($task, "complete", $user->data()->id, $message = "");
    }
    if ($mark_incomplete != "") {
        foreach ($mark_incomplete as $k => $v) {
            $db->update($task->child_table, $k, ['completed' => 0, 'completed_by' => null, 'completed_on' => null]); //updated to set null values rather than 0 and ""
        }
        notifyTask($task, "incomplete", $user->data()->id, $message = "");
    }
    if ($delete_sub != "") {
        foreach ($delete_sub as $k => $v) {
            $db->delete($task->child_table, ['id', '=', $k]);
        }
    }


    usSuccess($plg_settings->single_term . " Updated");
    Redirect::to($basePage . "method=manage&id=" . $id);
}

$subsQ = $db->query("SELECT * FROM " . $task->child_table . " WHERE task_id = ?", [$id]);
$subsC = $subsQ->count();
$subs = $subsQ->results();
$subsOpen = false;
foreach ($subs as $s) {
    if ($s->completed == 0) {
        $subsOpen = true;
    }
}

$taskClosedClass = ($task->closed == 1) ? 'closed-task' : '';
if ($assignmentsC < 1 && $task->closed == 0) { ?>

    <div class="alert alert-warning">
        <h4 class="alert-heading">No Assignments</h4>
        <p>This task has not been assigned to anyone yet. You can assign it below.</p>
    </div>
<?php }
if ($subsC > 0 && $task->closed == 0 && $subsOpen == false) { ?>

    <div class="alert alert-success">
        <h4 class="alert-heading">All <?= $plg_settings->plural_term ?> complete</h4>
        <p>If you're happy with everything, you can close this <?= $plg_settings->single_term ?>.</p>
    </div>
<?php

}
?>
<style>
    .closed-task {
        background-color: #f8d7da;
        /* Add your desired background color for closed tasks */
    }

    .btn-tiny {
        padding: 0.1rem 0.3rem;
        font-size: 0.8rem;
    }
</style>
<form action="" method="post">
    <div class="row">
        <div class="col-12 col-sm-6">

            <?= tokenHere() ?>
            <h3>Manage <?= $plg_settings->single_term ?> #<?= $task->id ?>
                <?php if ($task->closed == 1) { ?>
                    <span class="badge bg-danger">Closed</span>
                <?php } else {  ?>
                    <input type="submit" name="close_entire_task" class="btn btn-success mb-2" value="Close this <?= $plg_settings->single_term ?>">
                <?php } ?>

            </h3>
            <div class="card">
                <div class="card-header">
                    <h4>Basic Task Information</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="">Title</label>
                        <input type="text" class="form-control" name="title" value="<?= $task->title ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="">Description</label>
                        <textarea name="description" id="" cols="30" rows="3" class="form-control"><?= $task->description ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="">Due Date</label>
                        <input type="datetime-local" class="form-control" name="due_date" value="<?= $task->due_date ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="">Priority <span id="priorityValue"></span></label><br>
                        <input type="range" min="1" max="100" step="1" id="priority" name="priority" data-target="priorityValue" class="slider" value="<?= $task->priority ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="">Category (Cannot be changed)</label>
                        <input type="text" class="form-control" value="<?= $task->category_name ?>" disabled readonly>
                    </div>

                    <div class="form-group">
                        <label for="">Created By</label>
                        <input type="text" class="form-control" value="<?= echouser($task->created_by); ?>" disabled readonly>
                    </div>

                </div>
            </div>

        </div>

        <div class="col-12 col-sm-6">
            <div class="text-end">
                <input type="submit" name="updateTask" value="Save Changes" class="me-2 mb-2 btn btn-primary btn-lg">
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <h4>Current <?= $plg_settings->single_term ?> Assignments</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="assinged_to"><b>Currently</b> Assigned To</label>
                        <div class="row">
                            <?php foreach ($assignments as $a) { ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <li><?= $a->fname ?> <?= $a->lname ?></li>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card mb-3">
                <div class="card-header">
                    <h4>Update <?= $plg_settings->single_term ?> Assignments</h4>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="">Update Assignments</label>
                        <br>
                        <input type="checkbox" name="keep_existing_assignments" value="1" checked> Keep Existing Assignments (Add new ones only)
                    </div>


                    <?php
                    if (count($users) > 0 && $task->closed == 0) { ?>
                        <div class="form-group mt-3">
                            <label for="">Assign to <b>User</b></label>
                            <select name="assign_to_user" id="" class="form-select select2">
                                <option value="" disabled selected>-- Select User --</option>
                                <?php foreach ($users as $u) { ?>
                                    <option value="<?= $u->id ?>"><?= $u->fname ?> <?= $u->lname ?> (<?= $u->email ?>)</option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <?php
                    if (count($tags) > 0 && $task->closed == 0) { ?>
                        <div class="form-group mt-3">
                            <label for="">Assign to All Users with a <b>Tag</b></label>
                            <select name="assign_to_tags" id="" class="form-select select2">
                                <option value="" disabled selected>-- Select Tag --</option>
                                <?php
                                foreach ($tags as $k => $v) {
                                ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>
                </div>
            </div>


            <?php

            if ($task->closed == 0) { ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>Add <b>New</b> Sub <?= $plg_settings->plural_term ?></h4>
                    </div>
                    <div class="card-body mb-3">
                        <small><em>Any <?= $plg_settings->single_term ?> that do not have a description will not be added</em></small>

                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?= $plg_settings->single_term ?>
                                        <!-- create small plus task button -->
                                        <!-- <button type="button" class="btn btn-tiny btn-outline-success" id="addSubTask"><i class="fa fa-plus"></i> Add</button> -->


                                    </th>
                                    <th>Required</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i < 4; $i++) { ?>
                                    <tr>
                                        <td>
                                            <textarea name="new_sub[<?= $i ?>]" class="form-control" rows="1" placeholder="Task Description"></textarea>

                                        </td>
                                        <td>
                                            <select name="new_sub_required[<?= $i ?>]" class="form-select">
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>

                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php } ?>



        </div>



    </div>
    <?php if ($subsC > 0) { ?>
        <div class="card">
            <div class="card-header">
                <h4><b>Existing</b> Sub <?= $plg_settings->plural_term ?></h4>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Sub Item</th>
                            <th>Completed</th>
                            <th>Completed By</th>
                            <th>Completed On</th>
                            <th>Required</th>
                            <th>Toggle Status</th>
                            <th>Delete Task</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subs as $s) { ?>
                            <tr>
                                <td>
                                    <textarea name="line[<?= $s->id ?>]" rows="1" class="form-control"><?= $s->line ?></textarea>
                                </td>
                                <td>
                                    <?php if ($s->completed == 1) { ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">No</span>
                                    <?php } ?>
                                </td>
                                <td><?php if ($s->completed_by > 0) {
                                        echouser($s->completed_by);
                                    }  ?></td>
                                <td><?php if ($s->completed_on != "") {
                                        echo date("M j, Y g:i a", strtotime($s->completed_on));
                                    }  ?></td>

                                <td>
                                    <?php if ($s->line_required == 1) { ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">No</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($s->completed == 1) {
                                        $name = "mark_incomplete";
                                        $label = "Mark Incomplete";
                                    } else {
                                        $name = "mark_complete";
                                        $label = "Mark Complete";
                                    }
                                    ?>
                                    <input type="checkbox" name="<?= $name ?>[<?= $s->id ?>]" value="1"> <?= $label ?>

                                </td>
                                <td>
                                    <input type="checkbox" name="delete_sub[<?= $s->id ?>]" value="1"> Delete
                            </tr>
                        <?php } ?>
                </table>

            </div>
        <?php }  ?>

        </div>




</form>
<br>
<?php require_once $abs_us_root . $us_url_root . 'usersc/plugins/tasks/assets/_comments.php'; ?>
<br>

<script>
    $(document).ready(function() {

        $("#addSubTask").click(function() {
            var rowCount = $("table tbody tr").length + 1; // Count existing rows for indexing
            var row = '<tr><td><textarea name="new_sub[' + rowCount + ']" class="form-control" rows="1" placeholder="Task Description"></textarea></td><td><select name="new_sub_required[' + rowCount + ']" class="form-select"><option value="1">Yes</option><option value="0">No</option></select></td></tr>';
            $("table tbody").append(row);
        });
    });
</script>