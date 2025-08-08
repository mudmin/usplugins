<?php
require_once "../../../users/init.php";
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';


$db_id = Input::get('db_id');
if (is_numeric($db_id)) {
    $databaseQ = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
    $databaseC = $databaseQ->count();
    if ($databaseC == 0) {
        die("Database not found");
    }
    $database = $databaseQ->first();
    if ($database->diagram_is_public == 0) {
        if (!isUserLoggedIn()) {
            die("You must be logged in to view this diagram");
        }
        if ($database->required_perms != "") {
            $perms = explode(",", $database->required_perms);
        } else {
            $perms = [];
        }

        if ($database->required_tags != "") {
            $tags = explode(",", $database->required_tags);
        } else {
            $tags = [];
        }

        if (pluginActive("usertags", true) && $tags != []) {
            $checkTags = true;
        } else {
            $checkTags = false;
        }

        if ($perms != []) {
            $checkPerms = true;
        } else {
            $checkPerms = false;
        }


        if ($checkTags == true && $checkPerms == true) {

            if (!hasPerm($perms) && !hasOneTag($tags)) {
                die("You do not have one of the required permissions or tags to view this diagram");
            }
        } elseif ($checkTags == true) {

            if (!hasOneTag($tags)) {
                die("You do not have one of the required tags to view this diagram");
            }
        } elseif ($checkPerms == true) {

            if (!hasPerm($perms)) {
                die("You do not have one of the required permissions to view this diagram");
            }
        } else {

            die("This diagram is private and there are no permissions or tags which are allowed to view it");
        }
    }

    $tables = $db->query("SELECT * FROM plg_db_explainer_tables WHERE db_id = ? ORDER BY table_name", [$db_id])->results();
}
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><?= $database->db_description ?></h1>
            
        </div>
    </div>

    <div class="row">
        <?php
        // Loop through $cols variable to generate cards
        foreach ($tables as $t) {
        ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">


                <div class="card mt-3">
                    <div class="card-header">
                        <h5 style="margin-bottom:0px;"><?= $t->table_name ?></h5>
                        <?php if ($t->table_description == "") {
                            $t->table_description = "<br>";
                        } ?>
                        <span style="font-size:.75rem;"><?= $t->table_description ?></span>
                    </div>
                    <ul>
                        <?php
                        $where = "WHERE c.db_id = ? AND c.table_id = ?";
                        $binds = [$db_id, $t->id];
                        $cols = $db->query("SELECT 
            t.table_name,
            t.table_description as table_description,
            t2.table_name as related_table_name,
            c2.column_name as related_column_name,
            c.*          
            FROM plg_db_explainer_columns c 
            LEFT OUTER JOIN plg_db_explainer_tables t ON c.table_id = t.id
            LEFT OUTER JOIN plg_db_explainer_tables t2 ON c.related_to_table = t2.id
            LEFT OUTER JOIN plg_db_explainer_columns c2 ON c.related_to_column = c2.id
            $where

            ", $binds)->results();

                        foreach ($cols as $col) {
                            // Display columns as one column per line
                        ?>
                            <li id="<?= $t->table_name ?>___<?= $col->column_name ?>">
                                <span><?= $col->column_name ?> (<?= $col->column_type ?>)</span>
                                <?php if ($col->column_description != "") : ?>
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $col->column_description ?>"><i class="fa fa-question-circle text-primary" style="font-size:.7rem"></i></span>
                                <?php endif; ?>
                                <?php if ($col->related_to_table != "") : ?>
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="References <?= $col->related_table_name ?>.<?= $col->related_column_name ?>"><i class="fa fa-link text-success" style="font-size:.7rem"></i></span>
                                    <i class="fa fa-eye text-secondary viewRef" style="font-size:.7rem" data-link-to="<?= $col->related_table_name ?>___<?= $col->related_column_name ?>"></i>
                                <?php endif; ?>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        <?php
        }


        ?>
    </div>
</div>
<style>
    .red-line {
        position: absolute;
        background-color: red;
        z-index: 9999;
        transition: height 0.5s, width 0.5s;
        border-radius: 5px;
    }

    .highlight-cell {
        background-color: lightpink;
    }
</style>
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Handle click event on .viewRef icons
        $('.viewRef').on('click', function() {
            // Get the target element ID from data-link-to attribute
            var targetId = $(this).data('link-to');

            // Check if the target element exists
            var targetElement = $('#' + targetId);
            if (targetElement.length === 0) {
                console.error('Target element not found: ' + targetId);
                return;
            }

            // Get the position of the target element
            var targetPosition = targetElement.offset().top;

            // Scroll to the target element
            $('html, body').animate({
                scrollTop: targetPosition
            }, 1000, function() {
                // Highlight the target element in green for 10 seconds
                targetElement.addClass('highlight-cell');
                setTimeout(function() {
                    targetElement.removeClass('highlight-cell');
                }, 10000); // Remove the highlight after 10 seconds
            });
        });
    });
</script>