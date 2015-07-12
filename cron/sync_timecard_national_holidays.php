<?php
require_once "../classes/start.inc.php";

$path_parts['filename'] = 'timecard_national_holidays';

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
}
if ( trim( $cron_key ) != class_settings::get('cron_key') ) {
	die('Error: Incorrect cron key');
}

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";
class_syncinfo::save($path_parts['filename'], 'start', date("Y-m-d H:i:s"));

// download holidays from website source
$url = class_settings::get("download_url_national_holidays");
$source = file_get_contents( $url );

// decode data
$holidays = json_decode( $source );

// loop through all holidays
foreach ( $holidays as $holiday ) {
	echo $holiday->description . " (" . $holiday->date . ")<br>";
	$f = new class_feestdag( $holiday->id );
	$f->setDate( $holiday->date );
	$f->setDescription( $holiday->description );
	$f->setIsdeleted( $holiday->isdeleted );
	$f->setVooreigenrekening( $holiday->vooreigenrekening );
	$f->setLastrefresh( date("Y-m-d H:i:s") );
	$f->save();
}

// save sync last run
class_syncinfo::save($path_parts['filename'], 'end', date("Y-m-d H:i:s"));

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
