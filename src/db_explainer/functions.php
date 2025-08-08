<?php
function explain_database($dbconn, $dbname, $csvFile = 'database_schema.csv') {
    // Define the file path for the CSV output
    $csvFile = 'database_schema.csv';

    // Open the CSV file for writing
    $file = fopen($csvFile, 'w');

    // Write headers to the CSV file
    fputcsv($file, ['Table Name', 'Column Name', 'Column Type', 'Column Length', 'Description', 'Key of Table', 'Key of Column']);

    // Get the list of tables in your database
    $tables = $dbconn->query("SHOW TABLES")->results();

    // Loop through each table
    foreach ($tables as $table) {
        $string = "Tables_in_".$dbname;
        $tableName = $table->$string;

        // Get the list of columns for the current table
        $columns = $dbconn->query("DESCRIBE $tableName")->results();

        // Loop through each column
        foreach ($columns as $column) {
            $columnName = $column->Field;
            $columnType = $column->Type;

            // Separate the type and length if available
            $columnTypeParts = explode('(', $columnType, 2);
            $columnType = $columnTypeParts[0];
            $columnLength = isset($columnTypeParts[1]) ? preg_replace('/[^0-9,]/', '', $columnTypeParts[1]) : '';

            // Check if the length column contains "unsigned" (case-insensitive)
            $unsigned = (stripos($columnLength, 'unsigned') !== false) ? '1' : '0';

            // Remove 'unsigned' from columnLength
            $columnLength = str_ireplace('unsigned', '', $columnLength);
            $columnLength = trim($columnLength);

            // Add your description here.
            $description = '';

            // Leave these for your manual input.
            $keyOfTable = '';
            $keyOfColumn = '';

            // Write the data to the CSV file
            fputcsv($file, [$tableName, $columnName, $columnType, $columnLength, $description, $keyOfTable, $keyOfColumn]);
        }
    }

    // Close the CSV file
    fclose($file);

    usSuccess("CSV file 'database_schema.csv' has been generated.");
    return true;
}

function import_raw_database_to_explainer($dbconn, $dbname) {
    global $db;

    // Check if the database already exists in the 'plg_db_explainer_databases' table
    $checkDbQuery = $db->query("SELECT * FROM plg_db_explainer_databases WHERE db_name = ?", [$dbname]);
    $checkDbCount = $checkDbQuery->count();

    // Get the current timestamp
    $importedOn = date('Y-m-d H:i:s');

    // Insert or update the database record
    if ($checkDbCount > 0) {
        $checkDbRecord = $checkDbQuery->first();
        $db->update("plg_db_explainer_databases", $checkDbRecord->id, [
            'db_name' => $dbname,
            'imported_on' => $importedOn,
     
        ]);
    } else {
        $db->insert("plg_db_explainer_databases", [
            'db_name' => $dbname,
            'db_description' => '',
            'imported_on' => $importedOn,
            'last_updated' => $importedOn,
        ]);
    }

    // Get the database ID
    $dbId = $checkDbCount > 0 ? $checkDbRecord->id : $db->lastId();

    // Get the list of tables in your database
    $tables = $dbconn->query("SHOW TABLES")->results();

    // Loop through each table
    foreach ($tables as $table) {
        $string = "Tables_in_".$dbname;
        $tableName = $table->$string;

        // Get the list of columns for the current table
        $columns = $dbconn->query("DESCRIBE $tableName")->results();

        // Loop through each column
        foreach ($columns as $column) {
            $columnName = $column->Field;
            $columnType = $column->Type;

            // Separate the type and length if available
            $columnTypeParts = explode('(', $columnType, 2);
            $columnType = $columnTypeParts[0];
            $columnLength = isset($columnTypeParts[1]) ? preg_replace('/[^0-9,]/', '', $columnTypeParts[1]) : '';

            // Check if the length column contains "unsigned" (case-insensitive)
            $unsigned = (stripos($columnLength, 'unsigned') !== false) ? '1' : '0';

            // Remove 'unsigned' from columnLength
            $columnLength = str_ireplace('unsigned', '', $columnLength);
            $columnLength = trim($columnLength);

            // Add your description here.
            $description = '';

            // Check if the table already exists in the 'plg_db_explainer_tables' table
            $checkTableQuery = $db->query("SELECT * FROM plg_db_explainer_tables WHERE db_id = ? AND table_name = ?", [$dbId, $tableName]);
            $checkTableCount = $checkTableQuery->count();

            // Insert or update the table record
            if ($checkTableCount > 0) {
                $checkTableRecord = $checkTableQuery->first();
                $db->update("plg_db_explainer_tables", $checkTableRecord->id, [
                    'table_name' => $tableName,
                    'imported_on' => $importedOn,
 
                ]);
            } else {
                $db->insert("plg_db_explainer_tables", [
                    'db_id' => $dbId,
                    'table_name' => $tableName,
                    'table_description' => '', 
                    'imported_on' => $importedOn,
                 
                ]);
                // dump($db->errorString());
            }

            // Get the table ID
            $tableId = $checkTableCount > 0 ? $checkTableRecord->id : $db->lastId();

            // Insert or update the column record
            $checkColumnQuery = $db->query("SELECT * FROM plg_db_explainer_columns WHERE db_id = ? AND table_id = ? AND column_name = ?", [$dbId,$tableId, $columnName]);
            $checkColumnCount = $checkColumnQuery->count();

            if ($checkColumnCount > 0) {
                $checkColumnRecord = $checkColumnQuery->first();
                $db->update("plg_db_explainer_columns", $checkColumnRecord->id, [
                    'db_id' => $dbId, 
                    'table_id' => $tableId,
                    'column_name' => $columnName,
                    'column_type' => $columnType,
                    'column_length' => $columnLength,

                ]);
            } else {
                $db->insert("plg_db_explainer_columns", [
                    'db_id' => $dbId, 
                    'table_id' => $tableId,
                    'column_name' => $columnName,
                    'column_type' => $columnType,
                    'column_length' => $columnLength,
                    'column_description' => $description,
                    'related_to_table' => '',
                    'related_to_column' => '',
                    'imported_on' => $importedOn,
                    'last_updated' => $importedOn,
                ]);
            
            }
        }
    }

    return true;
}

function explain_db_export($db_id) {
    global $db;

    // Define the file path for the CSV output
    $csvFile = $db_id .'_database_export.csv';

    // Open the CSV file for writing
    $file = fopen($csvFile, 'w');

    // Write headers to the CSV file
    fputcsv($file, ['Column ID','Table ID',  'Table Name', 'Column Name', 'Column Type', 'Column Length', 'Description', 'Key of Table', 'Key of Column']);

    // Get the database name based on the provided $db_id
    $dbQuery = $db->query("SELECT db_name FROM plg_db_explainer_databases WHERE id = ?", [$db_id]);
    $dbData = $dbQuery->first();
    $dbName = $dbData->db_name;


    // Get the list of tables for the specified database
    $tablesQuery = $db->query("SELECT id,table_name FROM plg_db_explainer_tables WHERE db_id = ?", [$db_id]);
    $tables = $tablesQuery->results();

    // Loop through each table
    foreach ($tables as $table) {
        $tableName = $table->table_name;
        $tableId = $table->id;
        
        // Get the list of columns for the current table
        $columnsQuery = $db->query("SELECT * FROM plg_db_explainer_columns WHERE db_id = ? AND table_id = ?", [$db_id, $tableId ]);
        $columns = $columnsQuery->results();
    
        // Loop through each column
        foreach ($columns as $column) {
            $columnId = $column->id;
            $tableId = $column->table_id;
            $columnName = $column->column_name;
            $columnType = $column->column_type;
            $columnLength = $column->column_length;
            $description = $column->column_description;
            $keyOfTable = $column->related_to_table;
            $keyOfColumn = $column->related_to_column;

            // Write the data to the CSV file
            fputcsv($file, [$columnId, $tableId, $tableName, $columnName, $columnType, $columnLength, $description, $keyOfTable, $keyOfColumn]);
        }
    }

    // Close the CSV file
    fclose($file);

    usSuccesS("CSV file '".$db_id."_database_export.csv' has been generated.");
    return true;
}