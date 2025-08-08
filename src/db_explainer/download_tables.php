<?php
require_once "../../../users/init.php";
if (!hasPerm(2)) {
    die("no permission");
}

if (Input::get('db_id')) {
    $db_id = Input::get('db_id');
    exportTablesForDatabase($db_id);
} else {
    // Database ID not specified
    echo "Database ID not specified.";
}

function exportTablesForDatabase($db_id)
{
    global $db;
    $searchQ = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
    if ($searchQ->count() == 0) {
        die("Database not found.");
    }
    $db_name = $searchQ->first()->db_name;
    $dt = date('Y-m-d_H-i-s');
    $filename = $db_name . '_Tables_' . $dt . '.csv';

    // Generate CSV for Tables
    $tablesQuery = "SELECT id AS 'Table ID', table_name AS 'Table Name', table_description AS 'Table Description' FROM plg_db_explainer_tables WHERE db_id = ?";
    $tablesResults = $db->query($tablesQuery, [$db_id])->results();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    $header = array('Table ID', 'Table Name', 'Table Description');
    fputcsv($output, $header);

    foreach ($tablesResults as $tableRow) {
        fputcsv($output, (array)$tableRow);
    }

    fclose($output);
}
?>
