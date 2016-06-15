<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$f = substr(trim($protect->requestPositiveNumberOrEmpty('get', "f")), 0, 4);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle(Translations::get('iisg_employee') . ' | Floor plan level ' . $f);
$oPage->setContent(createFloorContent( $f ));

// show page
echo $oPage->getPage();

function createFloorContent( $floor ) {
	$ret = "<a href=\"#\" onClick=\"window.history.back();\">Go back</a><br>";

	$floorImage = Settings::get('floorplan_level' . $floor);
	if ( $floorImage != '' ) {
		$ret .= "<a href=\"#\" onClick=\"window.history.back();\" alt=\"Click to go back\" title=\"Click to go back\"><img src=\"" . $floorImage . "\"></a>";
	} else {
		$ret .= 'Unknown floor';
	}

	return $ret;
}

