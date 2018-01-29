<?php
require_once "classes/start.inc.php";

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Floor plans');
$oPage->setContent(createFloorsContent());

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createFloorsContent() {
	global $twig;

	$floorImages = array();

	$numberOfLevels = Settings::get('number_of_levels');
	if ( $numberOfLevels != '' && $numberOfLevels >= 0 ) {
		for ( $i = 0; $i <= $numberOfLevels; $i++ ) {
			$floorImages[] = Settings::get('floorplan_level' . $i);
		}
	}

	return $twig->render('floors.twig', array(
		'title' => 'Floors'
		, 'floorImages' => $floorImages
	));
}
