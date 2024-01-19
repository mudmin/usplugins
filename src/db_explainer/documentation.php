<?php
require_once "../../../users/init.php";
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';

if(!hasPerm(2)){
    die("no permission");
}

?>
<h3>DB Explainer Plugin
    <a href="<?=$us_url_root?>users/admin?view=plugins_config&plugin=db_explainer" class="btn btn-outline-primary btn-sm">Return to Plugin</a>
</h3>
<p>
  If appreciate this work and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
</p>

<h4>Why it's important</h4>
<p>
This tool is a fundamental component of the UserSpice.ai toolkit. It's an indispensable tool designed to analyze and document the structure of your database. It provides users with a visual representation of the database schema and the intricate relationships between its tables, rendering complex data structures more accessible and comprehensible.
</p>
<p>
This data can then be shared with other developers to facilitate collaboration, your client for documentation purposes, or even exported into a format which is understandable to Large Language Models (LLMs) such as ChatGPT or even your own private GPTs such as UserSpice.ai.  When it comes to AI, it's garbage in, garbage out.  This tool helps you get important data into the model so the model can get relevant code and queries back to you.  There are some tips below to help you engineer your prompts for better results.  
</p>

<h4>How to use it</h4>
<h5>Importing Databases</h5>
<p>From the plugin home, you can enter any database which your current DB user has access to.  Generally this is your UserSpice database, but on something like XAMPP, it's all of your databases.  If there is a need for remote SQL, we will consider adding it.  Simply enter the name of the DB you want to parse and it will be automatically parsed. </p>

<p>
    Note that you can re-parse at any time by simply entering the name again. You will not overwrite and of the data you entered and you will get any fields and columns which have been added.  At this time, we are not purging missing columns or tables.  This may be added in the future.
</p>

<h5>Documenting Your Database</h5>
<p>You can use the export tools to export the data and try to get ChatGPT to do the documentation for you, or you can simply go through and do it yourself.  The clearer the descriptions, the better. Remember garbage in, garbage out.</p>

<p>You should fill out descriptions of every single column.  You can also get creative and do this in mysql with something like: <br><strong>UPDATE plg_db_explainer_columns SET column_description = 'Primary key' WHERE column_name = 'id';</strong><br>
If any columns are related to other columns, you can fill out the related table and related column fields.  This will help you and others understand the relationships between the tables and columns. </p>

<p>Finally, you should set your table names and database description lower on the page.</p>

<h5>Visualizing the Database</h5>
<p>The visualizer allows you to see all the columns and definitions of your database. If there is a foreign key, you can hover over the link to see the relationship or click the eye to be scrolled to it. If you would like to make a custom visualizer, you can copy diagram.php and edit your copy.</p>

<h5>Security Settings</h5>
<p>By default, only admins can access the database visualization.  You can change this at the bottom of the editor page. You can make your visualizer public, or only allow users with a certain tag (if the tags plugin is installed) or permission level.  Note that this is only for viewing the visualization. Only admins can edit the database information at this time.</p>

<h5>Importing and Exporting in General Terms</h5>
<p>It should be noted that there are several types of exports, each with its own purpose.</p>

<h5>Export Column and Table Definitions</h5>
<p>These buttons allow you to download summarized versions of your data in CSV format which can be uploaded to ChatGPT or your own private GPTs. The data is structured in a way which these tools understand.  They are not designed to be imported back into the system.</p>

<h5>Export and Import Explainer</h5>
<p>You can use the export explainer to download your explainer data and use it as a backup or to import into other sites.  In the future you will be able to export subsets of your data, but for now, you are downlaoding the full explainer.</p>

<p>The most important part of the import tool is that you can use it to import not only your own data but official datasets from UserSpice. There's no reason for you to document core UserSpice tables and columns when you can import them. In the future you will be able to pull in documentation from plugins and other sources as well. Currently, the best place to get the official database explainer in the #official-userspice-resources channel <a href="https://discord.gg/6XZ7mEWnzZ" style="color:blue;">on our Discord</a>. You can discuss it in the #userspice-ai channel.</p>

<h4>Prompt Engineering for Code Generation and Requesting SQL Queries</h4>
<h6>Use this example text to help you explain to the GPT what you are giving it and what you would like it to do.</h6>
<p>
    I have uploaded two CSV files. One file contains the table names in my database and their descriptions. The second one contains information related to the columns in these tables as well as some information about how these columns are related.  It includes 'Table Name', 'Column Name', 'Column Type', 'Column Length', 'Description', 'Related Table', 'Related Column'.
</p>

<p>
    The columns Related Table and Related Column are used to show you which table and column the current column belongs to as a foreign key. For example, if the row has a related table of 'users' and a related column of 'id', then the column is a foreign key to the users table's id column.  If the related table is blank, then the column is not a foreign key.
</p>

<p>
    I am using the UserSpice PHP framework so please format your queries like this:<br>
        $query = $db->query("SELECT * FROM users WHERE id = ?", [$id]);<br>
        $count = $query->count();<br>
        if($count > 0){<br>
            $user = $query->first();<br>
        }<br>
        To select all, you can simply do <br>
        $users = $db->query("SELECT * FROM users")->results();<br>
        Please note that first() returns an object and results() returns an array of objects.
</p>
<h6>Then make your request...</h6>
<p>
    I would like you to use this information to generate a SQL query that will return the following information:
</p>
<h6>--or--</h6>
<p>
    I would like you to use this information to better create code to help me accomplish my goals. 
</p>


<h4>Prompt Engineering for asking ChatGPT to do as much of your documentation for you.</h4>
<h6>The following prompt is a good starting point to using a tool such as GPT-4 to produce. Results will vary widely. </h6>

<p>
I have uploaded a CSV file containing a full export from the UserSpice DB Explainer plugin which contains my database schema. Please update the schema in the following way:
</p>

<p>
Add human-readable descriptions to the 6th column ('Description'). These descriptions should explain the purpose of each column, considering the column's name, type, and length. For example, if a column is named 'user_id' and is of type 'int', the description should be 'The associated user's id'. For boolean (bool, int(1), tinyint) columns, provide a meaningful explanation of what the boolean value indicates.
</p>

<p>
Identify and annotate foreign key associations. If a column seems to be a foreign key, update the 7th ('Related Table') and 8th ('Related Column') columns with the associated table and column names, respectively.
</p>

<p>
Please avoid generic descriptions and strive for specificity and clarity. Also, leave fields blank if there's no relevant information to fill in (e.g., for columns that don't have foreign key associations).
</p>

<p>
Some examples:<br>
logs.id - description: primary key<br>
logs.user_id = The user the log is referring to, related table: users (7th column), related column: id(8th column)<br>
logs.logdate = The date of the log entry<br>
logs.logtype = The category of the log entry<br>
logs.lognote = The actual log data<br>
logs.metadata = Free form data associated with the log<br>
</p>

<p>
Please consider that if a column is called "viewed" and is a bool, then the definition would be "Bool showing whether audit has been viewed"
</p>

<p>
I do not need to see data on the screen. Please just update the csv after following the instructions so I can download it.
</p>

<p>
Use the column types to help you better determine what the columns are. If it's an int called id, it's a primary key.  If it's an int 1, it's most likely a bool.  For user_id, instead of saying Foreign key reference, put something more human readable like The user's id. Use the database table name to give you context.  If the table is called logs, then the column logdate is most likely the date of the log entry.
</p>

<p>
Don't use terms like "textual data relating to".  Just give short, concise descriptions.  If you can't think of a description, leave it blank.
</p>









