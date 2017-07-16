<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('header_print'));
$oPage->setContent(createSpecialNumbersContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

// disconnect database connection
$dbConn->close();

function createSpecialNumbersContent( ) {
	global $oWebuser, $twig;

	$show_fire = 0;
	if ( $_SESSION["FIRE_KEY_CORRECT"] == '1' || $oWebuser->hasAuthorisationTabFire() ) {
		$show_fire = 1;
	}

	return $twig->render('print.html', array(
		'title' => Translations::get('header_print')
		, 'show_fire' => $show_fire
		, 'sorted_on' => Translations::get('sorted_on')
		, 'lbl_firstname' => Translations::get('lbl_firstname')
		, 'lbl_lastname' => Translations::get('lbl_lastname')
		, 'or_go_to' => Translations::get('or_go_to')
		, 'menu_fire' => strtoupper(Translations::get('menu_fire'))
	));
}
