<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oProtime = new class_mysql($databases['default']);
$oProtime->connect();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle(Translations::get('iisg_employee') . ' | Floor plans');
$oPage->setContent(createFloorsContent());

// show page
echo $oPage->getPage();

function createFloorsContent() {
	$ret = "";

	$numberOfLevels = Settings::get('number_of_levels');
	if ( $numberOfLevels != '' && $numberOfLevels >= 0 ) {
		for ( $i = 0; $i <= $numberOfLevels; $i++ ) {
			$ret .= "<img src=\"" . Settings::get('floorplan_level' . $i) . "\"><br><br>";
		}
	}

	return $ret;
}
