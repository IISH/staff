<?php
// version: 2014-01-20

// connection to the database
$dbhandlePresentornot = mysql_connect($settings["presentornot_server"], $settings["presentornot_user"], $settings["presentornot_password"]) or die("Couldn't connect to MySql Server on " . $settings["presentornot_server"]);

// select a database to work with
$selectedPresentornot = mysql_select_db($settings["presentornot_database"], $dbhandlePresentornot) or die("Couldn't open database " . $settings["presentornot_database"]);
