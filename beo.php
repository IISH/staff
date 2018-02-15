<?php
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

//
$oBeo = new Beo( (isset($type_of_beo) ? $type_of_beo : ''), $label );

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . strip_tags($oBeo->getLabel()));
$oPage->setContent(createBhvEhboContent( $oBeo ));

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createBhvEhboContent( $oBeo ) {
	global $twig, $type_of_beo, $oWebuser;

	$refreshAfterXSeconds = 60;

	if ( !isset($_GET["m"]) ) {
		$selectedMonth = date("m");
	} else {
		$selectedMonth = trim(substr($_GET["m"],0,2));
	}
	if ( $selectedMonth == '' ) {
		$selectedMonth = date("m");
	}

	if ( !isset($_GET["y"]) ) {
		$selectedYear = date("Y");
	} else {
		$selectedYear = trim(substr($_GET["y"], 0, 4));
	}
	if ( $selectedYear == '' ) {
		$selectedYear = date("Y");
	}

	// allow only previous, current and next year
	if ( $selectedYear < date("Y")-1 ) {
		$selectedYear = date("Y")-1;
		$selectedMonth = 1;
	} elseif ( $selectedYear > date("Y")+1 ) {
		$selectedYear = date("Y")+1;
		$selectedMonth = 12;
	}

	return $twig->render('beo.twig', array(
		'refreshAfterXSeconds' => $refreshAfterXSeconds
		, 'scriptName' => $oBeo->getScriptName()
		, 'currentMonth' => $selectedMonth
		, 'currentYear' => $selectedYear
		, 'go_to' => Translations::get('go_to')
		, 'go_to_current_month' => Translations::get('go_to_current_month')
		, 'go_to_previous_month' => Translations::get('go_to_previous_month')
		, 'go_to_next_month' => Translations::get('go_to_next_month')
		, 'prev' => Translations::get('prev')
		, 'next' => Translations::get('next')
		, 'isBhv' => ( $type_of_beo == 'b' && $oWebuser->isBhv() ) ? 1 : 0
	));
}
