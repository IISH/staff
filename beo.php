<?php
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

//
$oBeo = new Beo( (isset($type_of_beo) ? $type_of_beo : ''), $label );

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . strip_tags($oBeo->getLabel()));
$oPage->setContent(createBhvEhboContent( $oBeo ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createBhvEhboContent( $oBeo ) {
	global $twig;

	$refreshAfterXSeconds = 60;

	return $twig->render('beo.html', array(
		'refreshAfterXSeconds' => $refreshAfterXSeconds
		, 'scriptName' => $oBeo->getScriptName()
	));
}
