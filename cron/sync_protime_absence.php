<?php
require_once "../classes/start.inc.php";

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
}
if ( trim( $cron_key ) != class_settings::getSetting('cron_key') ) {
	die('Error: Incorrect cron key');
}

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";

// sync
$sync = new class_syncProtimeMysql();
$sync->setSourceTable("ABSENCE");
$sync->setTargetTable("PROTIME_ABSENCE");
$sync->setPrimaryKey("ABSENCE");
$sync->addFields( array("ABSENCE", "SHORT_1", "SHORT_2", "CODE") );
class_settings::saveSetting('cron_' . $sync->getTargetTable() . '_start', date("Y-m-d H:i:s"), $sync->getTargetTable() . "_syncinfo");
$sync->doSync();

//
echo "<br>Rows inserted/updated: " . $sync->getCounter() . "<br>";

// save sync last run
class_settings::saveSetting('cron_' . $sync->getTargetTable() . '_end', date("Y-m-d H:i:s"), $sync->getTargetTable() . "_syncinfo");
class_settings::saveSetting('cron_last_insert_id_' . $sync->getTargetTable(), $sync->getLastInsertId(), $sync->getTargetTable() . "_syncinfo");

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
