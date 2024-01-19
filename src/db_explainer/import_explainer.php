<?php
require_once "../../../users/init.php";
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';

if (!hasPerm(2)) {
    die("no permission");
}

$db_id = Input::get('db_id');
$checkQ = $db->query("SELECT * FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
if ($checkQ->count() == 0) {
    die("Invalid database ID");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['json_file'])) {
    // Handle the uploaded file
    if ($_FILES['json_file']['error'] == UPLOAD_ERR_OK && $_FILES['json_file']['type'] == 'application/json') {
        $jsonData = file_get_contents($_FILES['json_file']['tmp_name']);
        $targetDbId = 1; // Set this to your target DB ID

        importDatabase($db, $jsonData, $db_id);
        usSuccess("Import successful!");
    
    } else {
        usError("Invalid file type or upload error!");

    }
    Redirect::to($us_url_root . 'users/admin?view=plugins_config&plugin=db_explainer&db_id='.$db_id);
}

function importDatabase($db, $jsonData, $targetDbId) {
    $importData = json_decode($jsonData, true);


    try {
  
        // Process 'tables'
        $tableIdMap = [];
        foreach ($importData['tables'] as $table) {
            // Check if the table exists
            $existingTable = $db->query("SELECT id FROM plg_db_explainer_tables WHERE table_name = ? AND db_id = ?", [$table['table_name'], $targetDbId])->results();
            if ($existingTable) {
                // Update existing table
                $db->update('plg_db_explainer_tables', $existingTable[0]->id, [
                    'table_description' => $table['table_description'],
                    
                ]);
             
                $tableIdMap[$table['id']] = $existingTable[0]->id;
            } else {
                // Insert new table
                $db->insert('plg_db_explainer_tables', [
                    'db_id' => $targetDbId,
                    'table_name' => $table['table_name'],
                    'table_description' => $table['table_description'],
                    // ... other fields as necessary
                ]);
                $newTableId = $db->lastId();
                $tableIdMap[$table['id']] = $newTableId;
            }
        }

        // Process 'columns'
     
        foreach ($importData['columns'] as $column) {
            // Resolve the correct table ID using $tableIdMap
            $tableId = $tableIdMap[$column['table_id']] ?? null;
            if (!$tableId) {
                continue; // Skip if no corresponding table ID found
            }
           
            // Check if the column exists
            $existingColumn = $db->query("SELECT id FROM plg_db_explainer_columns WHERE column_name = ? AND table_id = ?  AND db_id = ?", [$column['column_name'], $tableId, $targetDbId])->results();
        
            if ($existingColumn) {
                
                // Update existing column
                $db->update('plg_db_explainer_columns', $existingColumn[0]->id, [
                    'column_type' => $column['column_type'],
                    'column_length' => $column['column_length'],
                    'column_description' => $column['column_description'],
               
                ]);
              
            } else {
                // Insert new column
                $db->insert('plg_db_explainer_columns', [
                    'db_id' => $targetDbId,
                    'table_id' => $tableId,
                    'column_name' => $column['column_name'],
                    'column_type' => $column['column_type'],
                    'column_length' => $column['column_length'],
                    'column_description' => $column['column_description'],
     
                ]);

            }
        }
        // Process 'related_to_table' and 'related_to_column'
        foreach ($importData['columns'] as $importedColumn) {
            // Resolve the correct table ID using $tableIdMap
            $tableId = $tableIdMap[$importedColumn['table_id']] ?? null;
           
            if (!$tableId) {
                continue; // Skip if no corresponding table ID found
            }

            // Find the current column ID in the database
            $currentColumn = $db->query("SELECT id FROM plg_db_explainer_columns WHERE column_name = ? AND table_id = ? AND db_id = ?", [$importedColumn['column_name'], $tableId, $targetDbId])->results();
          
            if (!$currentColumn) {
                continue; // Skip if no corresponding column ID found
            }
            $currentColumnId = $currentColumn[0]->id;
          
            // Find the related table ID in the database
            $relatedTableId = null;
          
            if (!empty($importedColumn['related_to_table'])) {               
                $relatedTable = $db->query("SELECT id FROM plg_db_explainer_tables WHERE table_name = ? AND db_id = ?", [$importedColumn['related_to_table_name'], $targetDbId])->results();
                if ($relatedTable) {
                    $relatedTableId = $relatedTable[0]->id;
                }
            }
         

            // Find the related column ID in the database
            $relatedColumnId = null;
            if (!empty($importedColumn['related_to_column']) && $relatedTableId) {
                $relatedColumn = $db->query("SELECT id FROM plg_db_explainer_columns WHERE column_name = ? AND table_id = ? AND db_id = ?", [$importedColumn['related_to_column_name'], $relatedTableId, $targetDbId])->results();
                if ($relatedColumn) {
                    $relatedColumnId = $relatedColumn[0]->id;
                }
            }

            // Update the current column with the related IDs
            $db->update('plg_db_explainer_columns', $currentColumnId, [
                'related_to_table' => $relatedTableId,
                'related_to_column' => $relatedColumnId
            ]);
        }

    } catch (Exception $e) {
        die($e->getMessage());
    }
}


?>
    <h3>Import Database Explainer
    <a target="" class="btn btn-sm btn-primary" href="<?= $us_url_root ?>users/admin.php?view=plugins_config&plugin=db_explainer&db_id=<?=$db_id?>" class="btn btn-sm btn-primary ms-3">Back</a>
    </h3>
    <p>This page is for importing a database explainer into your existing database explainer. A perfect use case for this is to import the core UserSpice table and column definitions into your existing database scan so you don't have to keep document them. This WILL overwrite any columns that match your existing columns. It will leave everything else alone.</p>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="json_file" accept=".json" class="btn btn-outline-primary">
        <br><br>
        <input type="submit" value="Upload JSON" class="btn btn-primary">
    </form>