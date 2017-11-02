<?php
$includeTwig = false;
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
$sync->setSourceTable("BOOKINGS");
$sync->setSourceCriterium(" BOOKDATE >= '" . date("Ymd") . "' ");
$sync->setTargetTable("staff_today_checkinout");
$sync->setPrimaryKey("REC_NR");
$sync->addFields( array("REC_NR", "PERSNR", "BOOKDATE", "BOOK_ORIG", "BOOKTIME", "BOOKTYPE", "CCABS", "TERMINAL", "USER_ID", "COMMENTS", "REQUEST", "CALCBOOKTIME") );
SyncInfo::save($sync->getTargetTable(), 'start', date("Y-m-d H:i:s"), array($dbConn, $dbTimecard));
$sync->doSync();

//
echo "<br>Rows inserted/updated: " . $sync->getCounter() . "<br>";

// remove old records
$query = "DELETE FROM " . $sync->getTargetTable() . " WHERE BOOKDATE<'" . date("Ymd") . "' ";
$stmt = $dbConn->getConnection()->prepare($query);
$stmt->execute();

// save sync last run
SyncInfo::save($sync->getTargetTable(), 'end', date("Y-m-d H:i:s"), array($dbConn, $dbTimecard));
SyncInfo::save($sync->getTargetTable(), 'last_insert_id', $sync->getLastInsertId(), array($dbConn, $dbTimecard));

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
