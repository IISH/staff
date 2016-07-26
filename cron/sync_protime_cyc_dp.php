<?php
require_once "../classes/start.inc.php";

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
}
if ( trim( $cron_key ) != Settings::get('cron_key') ) {
	die('Error: Incorrect cron key...');
}

// connect to timecard database
$dbTimecard = new class_pdo( $databases['timecard'] );

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";

// sync
$sync = new SyncProtime2Pdo();
$sync->setSourceTable("CYC_DP");
$sync->setTargetTable(Settings::get('protime_tables_prefix') . "cyc_dp");
$sync->setPrimaryKey("CYC_DP");
$sync->addFields( array("CYC_DP", "CYCLIQ", "DAYNR", "DAYPROG") );
SyncInfo::save($sync->getTargetTable(), 'start', date("Y-m-d H:i:s"), array($dbConn, $dbTimecard));
$sync->doSync();

//
echo "<br>Rows inserted/updated: " . $sync->getCounter() . "<br>";

// save sync last run
SyncInfo::save($sync->getTargetTable(), 'end', date("Y-m-d H:i:s"), array($dbConn, $dbTimecard));
SyncInfo::save($sync->getTargetTable(), 'last_insert_id', $sync->getLastInsertId(), array($dbConn, $dbTimecard));

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
