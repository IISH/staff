<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle(Translations::get('iisg_employee') . ' | Floor plans');
$oPage->setContent(createFloorsContent());

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createFloorsContent() {
	global $twig;

	$floorImages = array();

	$numberOfLevels = Settings::get('number_of_levels');
	if ( $numberOfLevels != '' && $numberOfLevels >= 0 ) {
		for ( $i = 0; $i <= $numberOfLevels; $i++ ) {
			$floorImages[] = Settings::get('floorplan_level' . $i);
		}
	}

	return $twig->render('floors.html', array(
		'title' => 'Floors'
		, 'floorImages' => $floorImages
	));
}
