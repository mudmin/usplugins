<?php
require_once "../../../users/init.php";
if (!hasPerm(2)) {
    die("no permission");
}

if (Input::get('db_id')) {
    $db_id = Input::get('db_id');
    exportDatabase($db, $db_id);
} else {
    // Database ID not specified
    echo "Database ID not specified.";
}
function exportDatabase($db, $db_id) {
    $searchQ = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
    if ($searchQ->count() == 0) {
        die("Database not found.");        
    }
    $db_name = $searchQ->first()->db_name;
    $dt = date('Y-m-d_H-i-s');
    $filename = "FULL DATA Export " . $db_name . '_' . $dt . '.json';
    $exportData = [];

    // Export 'databases' data
    $exportData['databases'] = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id])->results();

    // Export 'tables' data
    $exportData['tables'] = $db->query("SELECT * FROM plg_db_explainer_tables WHERE db_id = ?", [$db_id])->results();

    // Export 'columns' data
    $exportData['columns'] = $db->query("SELECT 
            c.*,
            t2.table_name as related_to_table_name,
            c2.column_name as related_to_column_name
            FROM plg_db_explainer_columns c 
            LEFT OUTER JOIN plg_db_explainer_tables t2 ON c.related_to_table = t2.id
            LEFT OUTER JOIN plg_db_explainer_columns c2 ON c.related_to_column = c2.id
            WHERE c.db_id = ?", [$db_id])->results();

    // Convert to JSON
    $jsonContent = json_encode($exportData);

    // Setting headers to force download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($jsonContent));

    // Output the JSON content
    echo $jsonContent;
    exit;
}
