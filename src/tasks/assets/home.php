<?php
$admin_page = true;
taskSecure();
$closed = Input::get("closed");
if ($closed != "true") {
    $term = "Open";
    $closedVar = 0;
} else {
    $term = "Closed";
    $closedVar = 1;
}

$existing = $db->query("SELECT 
    t.*,
    c.category_name,
    c.icon,
    c.color,
    CONCAT(COALESCE(SUM(CASE WHEN ptlg.completed = 1 THEN 1 ELSE 0 END), 0), '/', COUNT(ptlg.task_id)) AS completed_tasks
FROM plg_tasks t 
LEFT OUTER JOIN plg_tasks_categories c ON c.id = t.category_id
LEFT JOIN plg_tasks_lines_general ptlg ON ptlg.task_id = t.id
WHERE t.closed = {$closedVar}
GROUP BY t.id, c.category_name, c.icon, c.color
ORDER BY t.due_date ASC 
LIMIT 500
")->results();



// $existing = $db->query("SELECT 
// t.*,
// c.category_name,
// c.icon,
// c.color
// FROM plg_tasks t 
// LEFT OUTER JOIN plg_tasks_categories c ON c.id = t.category_id
// WHERE t.closed = {$closedVar} ORDER BY t.due_date ASC LIMIT 500")->results();

$cats = $db->query("SELECT * FROM plg_tasks_categories")->results();
$categories = [];
foreach ($cats as $cat) {
    $categories[$cat->id] = $cat->category_name;
}

if (!empty($_POST['close_task'])) {
    if (!Token::check($_POST['csrf'])) {
        usError("Token Failed");
        Redirect::to($basePage . "method=manage&id=" . $id);
    }
    $id = Input::get('id');
    $db->update("plg_tasks", $id, ['closed' => 1]);
    usSuccess("Task Closed");
    Redirect::to($basePage);
}

if (!empty($_POST['create_task'])) {
    if (!Token::check($_POST['csrf'])) {
        usError("Token Failed");
        Redirect::to($basePage . "method=manage&id=" . $id);
    }

    $title = Input::get('title');
    $description = Input::get('description');
    $due_date = Input::get('due_date');
    $priority = Input::get('priority');
    $category_id = Input::get('category_id');

    $db->insert("plg_tasks", [
        'title' => $title,
        'description' => $description,
        'due_date' => $due_date,
        'priority' => $priority,
        'category_id' => $category_id,
        'created_by' => $user->data()->id,
        'created_on' => date("Y-m-d H:i:s"),
    ]);

    usSuccess("Task Created, please assign it to someone.");
    Redirect::to($basePage . "method=manage&id=" . $db->lastId());
}

?>
<div class="row">
    <div class="col-12 col-sm-4">
        <div class="card">
            <div class="card-header">
                <h3>New <?= $plg_settings->single_term ?></h3>
            </div>
            <div class="card-body">
                <form action="" method="post">

                    <?= tokenHere() ?>

                    <div class="form-group">
                        <label for="">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="">Description</label>
                        <textarea name="description" id="" cols="30" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="">Due Date</label>
                        <input type="datetime-local" class="form-control" name="due_date" value="<?= date("Y-m-d 17:00:00"); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="">Priority <span id="priorityValue"></span></label><br>
                        <input type="range" min="1" max="100" step="1" id="priority" name="priority" data-target="priorityValue" class="slider" value="50" required>

                    </div>

                    <div class="form-group">
                        <label for=""><?= $plg_settings->single_term ?> Type</label>
                        <select name="category_id" id="" class="form-select" required>
                            <?php foreach ($categories as $id => $cat) { ?>
                                <option value="<?= $id ?>"><?= $cat ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <input type="submit" name="create_task" value="Create <?= $plg_settings->single_term ?>" class="btn btn-primary mt-2">
                </form>
            </div>
        </div>


    </div>
    <div class="col-12 col-sm-8">
        <h3><?= $term ?> <?= $plg_settings->plural_term ?>
            <?php if (Input::get("closed") == "true") {  ?>
                <a href="<?= $basePage ?>" class="ms-2 btn btn-outline-primary btn-sm">View Open</a>

            <?php  } else { ?>
                <a href="<?= $basePage ?>&closed=true" class="ms-2 btn btn-outline-primary btn-sm">View Closed</a>
            <?php } ?>
        </h3>
        <table class="table table-striped table-hover paginate">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Due Date</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Sub <?= $plg_settings->plural_term ?> complete</th>
                    <th></th>
                    <th></th>
                    <th></th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($existing as $task) { ?>
                    <tr>
                        <td><?= $task->id ?></td>
                        <td>
                            <span class="hideSpan"><?= $task->due_date ?></span>
                            <?= taskDT($task->due_date); ?>
                        </td>
                        <td>
                            <?= taskCategoryBadge($task); ?>
                        </td>
                        <td><?= $task->title ?></td>
                        <td><?php
                            if ($task->completed_tasks != "") {
                                echo $task->completed_tasks;
                            } else {
                                echo "n/a";
                            }

                            ?>
                        </td>
                        <td>
                            <?php if ($task->marked_complete_by > 0) { ?>
                                <span class="badge" style="background-color:blue;">Marked Complete</span>
                            <?php } ?>
                        </td>
                        <td><a href="<?= $basePage ?>method=manage&id=<?= $task->id ?>" class="btn btn-outline-primary">Manage</a></td>
                        <td>
                            <?php if ($task->closed == 0) { ?>
                                <form action="" method="post">
                                    <?= tokenHere(); ?>
                                    <input type="hidden" name="id" value="<?= $task->id ?>">

                                    <input type="submit" name="close_task" value="Close" class="btn btn-outline-danger">
                                </form>
                            <?php } ?>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>