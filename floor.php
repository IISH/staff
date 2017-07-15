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
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createFloorContent( $floor ) {
	global $twig;

	$floorImage = Settings::get('floorplan_level' . $floor);
	if ( $floorImage != '' ) {
		$floor = "<a href=\"#\" onClick=\"window.history.back();\" alt=\"Click to go back\" title=\"Click to go back\"><img src=\"" . $floorImage . "\"></a>";
	} else {
		$floor = 'Unknown floor';
	}

	return $twig->render('floor.html', array(
		'go back' => 'Floors'
		, 'floor' => $floor
	));
}
