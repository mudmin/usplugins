<?php
require_once "../../../users/init.php";
if(!hasPerm(2)){
    die("no permission");
}


if (Input::get('export_type') != "") {
    $exportType = Input::get('export_type');
    if ($exportType === 'db' && Input::get('db_id')) {
        $db_id = Input::get('db_id');
        exportDatabase($db_id);
    } elseif ($exportType === 'table' && Input::get('table_id')) {
        $table_id = Input::get('table_id');
        exportTable($table_id);
    } else {
        // Invalid export request
        echo "Invalid export request.";
    }
} else {
    // Export type not specified
    echo "Export type not specified.";
}

function exportDatabase($db_id)
{
    global $db;
    $searchQ = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
    if ($searchQ->count() == 0) {
        die("Database not found.");        
    }
    $db_name = $searchQ->first()->db_name;
    $dt = date('Y-m-d_H-i-s');
    $filename = $db_name . '_' . $dt . '.csv';
 
    
    $query = "
        SELECT
            c.id AS 'Column ID',
            t.table_name AS 'Table Name',
            c.column_name AS 'Column Name',
            c.column_type AS 'Column Type',
            c.column_length AS 'Column Length',
            c.column_description AS 'Description',
            t2.table_name AS 'Related Table',
            c2.column_name AS 'Related Column'
        FROM
            plg_db_explainer_columns c
        JOIN
            plg_db_explainer_tables t ON c.table_id = t.id
        LEFT JOIN
            plg_db_explainer_tables t2 ON c.related_to_table = t2.id
        LEFT JOIN
            plg_db_explainer_columns c2 ON c.related_to_column = c2.id
        WHERE
            c.db_id = ?
    ";

    $results = $db->query($query, [$db_id])->results();

    // Generate CSV

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    $header = array('Column ID', 'Table Name', 'Column Name', 'Column Type', 'Column Length', 'Description', 'Related Table', 'Related Column');
    fputcsv($output, $header);

    foreach ($results as $row) {
        fputcsv($output, (array)$row);
    }

    fclose($output);
}

function exportTable($table_id)
{
    global $db;

    

    $tableSearchQ = $db->query("SELECT * FROM plg_db_explainer_tables WHERE id = ?", [$table_id]);
    if ($tableSearchQ->count() == 0) {
        die("Table not found.");        
    }
    $tableSearch = $tableSearchQ->first();
    $db_id = $tableSearch->db_id;
    $table_name = $tableSearch->table_name;
    
    $dbSearchQ = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
    if ($dbSearchQ->count() == 0) {
        die("Database not found.");        
    }
    $db_name = $dbSearchQ->first()->db_name;
    $dt = date('Y-m-d_H-i-s');
    $filename = $db_name . "_" . $table_name . '_' . $dt . '.csv';

    $query = "
        SELECT
            c.id AS 'Column ID',
            t.table_name AS 'Table Name',
            c.column_name AS 'Column Name',
            c.column_type AS 'Column Type',
            c.column_length AS 'Column Length',
            c.column_description AS 'Description',
            t2.table_name AS 'Related Table',
            c2.column_name AS 'Related Column'
        FROM
            plg_db_explainer_columns c
        JOIN
            plg_db_explainer_tables t ON c.table_id = t.id
        LEFT JOIN
            plg_db_explainer_tables t2 ON c.related_to_table = t2.id
        LEFT JOIN
            plg_db_explainer_columns c2 ON c.related_to_column = c2.id
        WHERE
            t.id = ?
    ";

    $results = $db->query($query, [$table_id])->results();

    // Generate CSV
   
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    $header = array('Column ID', 'Table Name', 'Column Name', 'Column Type', 'Column Length', 'Description', 'Related Table', 'Related Column');
    fputcsv($output, $header);

    foreach ($results as $row) {
        fputcsv($output, (array)$row);
    }

    fclose($output);
}
