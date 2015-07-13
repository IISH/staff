<?php
require_once "../classes/start.inc.php";

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
}
if ( trim( $cron_key ) != class_settings::getSetting('cron_key') ) {
	die('Error: Incorrect cron key...');
}

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";

// sync
$sync = new class_syncProtimeMysql();
$sync->setSourceTable("DAYPROG");
$sync->setTargetTable(class_settings::get('protime_tables_prefix') . "DAYPROG");
$sync->setPrimaryKey("DAYPROG");
$sync->addFields( array("DAYPROG", "SHORT_1", "SHORT_2", "ITEM_LEVEL", "CODE", "COLOR", "NORM", "F_CORE", "T_CORE", "F_FICTIF", "T_FICTIF", "B_CLOSURE", "E_CLOSURE", "DP_ROUND", "DP_OVERTIME", "DP_SLIDE", "SHIFT", "ABSENCEDAY", "ACCESSDAYTYPE", "CUSTOMER", "DISPLAY_1", "DISPLAY_2", "VALIDFORDAYS", "F_PLANNABLE", "T_PLANNABLE", "USEPLANNEDCORE") );
class_syncinfo::save($sync->getTargetTable(), 'start', date("Y-m-d H:i:s"));
$sync->doSync();

//
echo "<br>Rows inserted/updated: " . $sync->getCounter() . "<br>";

// save sync last run
class_syncinfo::save($sync->getTargetTable(), 'end', date("Y-m-d H:i:s"));
class_syncinfo::save($sync->getTargetTable(), 'last_insert_id', $sync->getLastInsertId());

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";