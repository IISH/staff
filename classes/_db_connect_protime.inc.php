<?php 
// version: 2014-03-04

// connection to the database
$dbhandleProtime = mssql_connect($settings["protime_server"], $settings["protime_user"], $settings["protime_password"]) or die("Couldn't connect to Protime SQL Server: " . $settings["protime_server"]);

// select a database to work with
$selectedProtime = mssql_select_db($settings["protime_database"], $dbhandleProtime) or die("Couldn't open database " . $settings["protime_database"]);
