<?php
if (!hasPerm(2)) {
    Redirect::to($basePage);
}
?>
<div class="row">
    <div class="col-12">
        <h3>Documentation</h3>
        <h4>Basic Concepts</h4>
        <p>
            This plugin is designed to be flexible and grow with your needs. Although the tasks plugin is called "tasks", you can set your own singular and plural terms (ie "workorder" and "workorders") and the plugin will adjust accordingly.
        </p>
        <p>
            This plugin uses a new "alternate location" feature that is built into some of the newer plugins. This allows you to put management pages outside of the UserSpice dashboard and they will still work as intended. For instance, if you set the alternate location to "workorders", you can access the management pages at yoursite.com/workorders. This is a great way to keep your dashboard clean and organized. To do this, simply create a folder called "workorders" off your root and drop in the index.php file that is located in: <span style="background-color:black;  color:lightgreen; padding-left:10px;padding-right:10px;">usersc/plugins/tasks/assets/index.php</span>

        </p>

        <h4>Task Types</h4>
        <p>At this time, the various task types are simply for your own convenience. The long-term plan is to offer more functionaliy per task types. Each task type technically has the ability to use its own child table to store the relevant sub-task information</p>

        <h4>Sub Tasks</h4>
        <p>
            Any task can simply be the "Description" field of the task or it can have sub tasks. If you add sub tasks, you can mark them as required or not. As a user completes the sub tasks, the parent task will update to show the progress. If all sub tasks are marked as required, the parent task will not be able to be marked as complete until all sub tasks are complete. If you don't want to use sub tasks, simply leave the sub task fields blank.
        </p>

        <h4>Assigning Tasks</h4>
        <p>
            Tasks can be assigned to individuals or to groups if the User Tags plugin is installed. The settings page also lets you decide who can create/assign tasks by UserSpice permission level.
        </p>

        <h4>Notifications</h4>
        <p>
            This plugin has the ability to send notifications to users when a new task is created or when a comment is added to a task. You can set the type of notification in the settings. If you don't want to send notifications, simply set to No Notifications. Currently the plugin supports standard UserSpice email, Brevo (SendinBlue) and the UserSpice Messages plugin (which offers 3 types of notifications);
        </p>
        <h4>Donate</h4>
        <p>
            If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
        </p>

    </div>

</div>