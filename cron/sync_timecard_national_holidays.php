<?php
require_once "../classes/start.inc.php";

$path_parts['filename'] = 'staff_feestdagen';

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

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";
SyncInfo::save($path_parts['filename'], 'start', date("Y-m-d H:i:s"), $dbConn);

// download holidays from website source
$url = Settings::get("download_url_national_holidays");
$source = file_get_contents( $url );

// decode data
$holidays = json_decode( $source );

$counter = 0;

// loop through all holidays
foreach ( $holidays as $holiday ) {
	$counter++;
	echo $holiday->description . " (" . $holiday->date . ")<br>";
	$f = new Feestdag( $holiday->id );
	$f->setDate( $holiday->date );
	$f->setDescription( $holiday->description );
	$f->setIsdeleted( $holiday->isdeleted );
	$f->setVooreigenrekening( $holiday->vooreigenrekening );
	$f->setLastrefresh( date("Y-m-d H:i:s") );
	$f->save();
}

// save sync last run
SyncInfo::save($path_parts['filename'], 'counter', $counter, $dbConn);
SyncInfo::save($path_parts['filename'], 'end', date("Y-m-d H:i:s"), $dbConn);

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
