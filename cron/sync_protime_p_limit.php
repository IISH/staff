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
	die('Error: Incorrect cron key');
}

// connect to timecard database
$dbTimecard = new class_pdo( $databases['timecard'] );

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";

// sync
$sync = new SyncProtime2Pdo();
$sync->setSourceTable("P_LIMIT");
$sync->setSourceCriterium(" EXEC_ORDER=2 AND BOOKDATE>='" . date("Ymd", mktime(0, 0, 0, date("m")-3, 1, date("Y"))) . "' ");
$sync->setTargetTable(Settings::get('protime_tables_prefix') . "p_limit");
$sync->setPrimaryKey("REC_NR");
$sync->addFields( array("REC_NR", "PERSNR", "BOOKDATE", "LIMIT_LINE", "LIM_PERIODE", "ITEM_TYPE", "YEARCOUNTER", "BEGIN_VAL", "END_VAL", "EXEC_ORDER") );
SyncInfo::save($sync->getTargetTable(), 'start', date("Y-m-d H:i:s"), $dbConn);
$sync->doSync();

//
echo "<br>Rows inserted/updated: " . $sync->getCounter() . "<br>";

// save sync last run
SyncInfo::save($sync->getTargetTable(), 'end', date("Y-m-d H:i:s"), $dbConn);
SyncInfo::save($sync->getTargetTable(), 'last_insert_id', $sync->getLastInsertId(), $dbConn);

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
