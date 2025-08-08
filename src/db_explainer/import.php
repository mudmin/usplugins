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
$t = $db->query("SELECT * FROM plg_db_explainer_tables WHERE db_id = ?", [$db_id])->results();
$tables = [];
foreach($t as $table){
    $tables[$table->id] = $table;
}
$skipped = 0;
$updated = 0;
$inserted = 0;

// Check if a file was uploaded
if (isset($_FILES["csv_file"]) && $_FILES["csv_file"]["error"] == 0) {
    $file_name = $_FILES["csv_file"]["name"];
    $file_tmp = $_FILES["csv_file"]["tmp_name"];

    // Check if the uploaded file is a CSV
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    if ($file_extension === "csv") {
        // Read the CSV file
        $csv_data = array_map('str_getcsv', file($file_tmp));
        $associations = Input::get('associations');
        $insert = Input::get('insert');
        if($associations == 1){
            $associations = true;
        }else{
            $associations = false;
        }

        if($insert == 1){
            $insert = true;
        }else{
            $insert = false;
        }


        // Loop through each row in the CSV
        foreach ($csv_data as $row) {
            if(!is_numeric($row[0])){
                continue;
            }
            $column_id = intval($row[0]);
            $table_name = Input::sanitize($row[1]);
            $column_name = Input::sanitize($row[2]);
            $description = Input::sanitize($row[5]);
            $related_table = Input::sanitize($row[6]);
            $related_column = Input::sanitize($row[7]);
            //search through the tables array to find the table id
            $table_id = 0;
            foreach($tables as $table){
                if($table->table_name == $table_name){
                    $table_id = $table->id;
                }
            }
            
            //let's make sure these columns match
            $checkQ = $db->query("SELECT id FROM plg_db_explainer_columns WHERE id = ? AND db_id = ? AND table_id = ? AND column_name = ?", [$column_id, $db_id, $table_id, $column_name]);
            $checkC = $checkQ->count();
            if($checkC < 1 && !$insert){
                echo "Column ID $column_id does not exist in this database.  Please check your CSV file and try again.";
                $skipped++;
                continue;
            }elseif($checkC < 1 && $insert){
                $fields = [
                    "db_id" => $db_id,
                    "table_id" => $table_id,
                    "column_name" => $column_name,
                    "column_type" => Input::sanitize($row[3]), //column type
                    "column_length" => Input::sanitize($row[4]), //column length
                    "column_description" => $description,
     
                ];
                if($associations){
                    $tableSearchQ = $db->query("SELECT id FROM plg_db_explainer_tables WHERE db_id = ? AND table_name = ?", [$db_id, $related_table]);
                    if($tableSearchQ->count() > 0){
                       $tableSearch = $tableSearchQ->first();
                       $columnSearchQ = $db->query("SELECT id FROM plg_db_explainer_columns WHERE db_id = ? AND table_id = ? AND column_name = ?", [$db_id, $tableSearch->id, $related_column]);
                          if($columnSearchQ->count() > 0){
                            $columnSearch = $columnSearchQ->first();
                            $fields["related_to_table"] = $tableSearch->id;
                            $fields["related_to_column"] = $columnSearch->id;
                          }
                    }
                }
                $db->insert("plg_db_explainer_columns", $fields);
                $inserted++;

            }else{
                //update only
                $update_data = ["column_description" => $description];
                if($associations){
                    $tableSearchQ = $db->query("SELECT id FROM plg_db_explainer_tables WHERE db_id = ? AND table_name = ?", [$db_id, $related_table]);
                    if($tableSearchQ->count() > 0){
                       $tableSearch = $tableSearchQ->first();
                       $columnSearchQ = $db->query("SELECT id FROM plg_db_explainer_columns WHERE db_id = ? AND table_id = ? AND column_name = ?", [$db_id, $tableSearch->id, $related_column]);
                          if($columnSearchQ->count() > 0){
                            $columnSearch = $columnSearchQ->first();
                            $update_data["related_to_table"] = $tableSearch->id;
                            $update_data["related_to_column"] = $columnSearch->id;
                          }
                    }
                }
                $db->update("plg_db_explainer_columns", $column_id, $update_data);
                $updated++;
            }
        }

        echo "CSV data updated successfully!";
        echo "<br>";
        echo "Skipped: $skipped";
        echo "<br>";
        echo "Updated: $updated";
        echo "<br>";
        echo "Inserted: $inserted";
        
    } else {
        echo "Invalid file format. Please upload a CSV file.";
    }
} else {
    echo "Error uploading the file.";
}

?>
<h4>Import a CSV File</h4>
You can use this form to upload a CSV file containing descriptions for each column in the database or update your associations.  This is great if you use tools such as ChatGPT or your local AI to generate descriptions and assocations for your columns.
<div class="row">
    <div class="col-12 col-md-6 col-lg-4">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" class="btn btn-outline-primary">
            <br>
            <input type="checkbox" name="descriptions" value="1" readonly checked>Import Descriptions
            <br>
            <input type="checkbox" name="associations" value="1">Import Relationships
            <br>
            <input type="checkbox" name="insert" value="1">Insert New Columns
            <br>
            <input type="submit" value="Upload CSV" class="btn btn-primary">
        </form>
    </div>
</div>