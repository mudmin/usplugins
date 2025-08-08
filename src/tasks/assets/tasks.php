<?php
if (in_array($method, $admin_methods)) {
    $admin_page = true;
} else {
    $admin_page = false;
}
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
a.*,
t.*,
c.*
FROM plg_tasks_assignments a
LEFT OUTER JOIN plg_tasks t ON a.task_id = t.id 
LEFT OUTER JOIN plg_tasks_categories c ON c.id = t.category_id
WHERE a.user_id = ? AND a.closed = {$closedVar}
ORDER BY t.due_date ASC, t.priority DESC
LIMIT 500", [$user->data()->id])->results();

?>
<div class="row">
    <div class="col-12">
        <h3>Your <?= $plg_settings->plural_term ?></h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover paginate">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Category</th>
                        <th>Completed</th>
                        <th>Closed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($existing as $task) { ?>
                        <tr>
                            <td><?= $task->title ?></td>
                            <td>
                                <textarea class="form-control" rows="1" readonly><?= $task->description ?></textarea>
                            </td>
                            <td>
                                <span class="hideSpan"><?= $task->due_date ?></span>
                                <?= taskDT($task->due_date) ?>
                            </td>
                            <td><?= $task->priority ?></td>
                            <td><?= taskCategoryBadge($task); ?></td>


                            <td><?= bin($task->completed) ?></td>
                            <td><?= bin($task->closed) ?></td>
                            <td>
                                <a href="<?= $basePage ?>method=view_task&id=<?= $task->task_id ?>" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    <?php } ?>
            </table>
        </div>
    </div>
</div>