<?php 
// version: 2014-01-20

// connection to the database
$dbhandleTimecard = mssql_connect($settings["timecard_server"], $settings["timecard_user"], $settings["timecard_password"]) or die("Couldn't connect to SQL Server on " . $settings["timecard_server"]);

// select a database to work with
$selectedTimecard = mssql_select_db($settings["timecard_database"], $dbhandleTimecard) or die("Couldn't open database " . $settings["timecard_database"]);
