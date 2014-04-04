<?php 
// version: 2014-04-04

$settings["timecard_connection_successful"] = 1;

// connection to the database
$dbhandleTimecard = mysql_connect($settings["timecard_server"], $settings["timecard_user"], $settings["timecard_password"]);

if ( !$dbhandleTimecard ) {
    $settings["timecard_connection_successful"] = 0;

    //echo mysql_error();
} else {
    // select a database to work with
    $selectedTimecard = mysql_select_db($settings["timecard_database"], $dbhandleTimecard) or die("Couldn't open database " . $settings["timecard_database"]);
}
