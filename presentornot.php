<?php
require_once "classes/start.inc.php";

$pageSettings = array(
	'title' => Translations::get('menu_presentornot')
);

if ( !isset($chosenFilter) ) {
	$chosenFilter = '';
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . $pageSettings['title']);
$oPage->setContent(createPresentContent( $pageSettings ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createPresentContent( $pageSettings ) {
	global $chosenFilter, $twig;

	//
	$refreshAfterXSeconds = 60;

	//
	$s = getAndProtectSearch();

	return $twig->render('presentornot.html', array(
		'title' => $pageSettings['title']
		, 'refreshAfterXSeconds' => $refreshAfterXSeconds
		, 's' => $s
		, 'min_x_characters' => Translations::get('min_x_characters')
		, 'lbl_quick_search' => Translations::get('lbl_quick_search')
		, 'lbl_show_favourites' => Translations::get('lbl_show_favourites')
		, 'lbl_show_present' => Translations::get('lbl_show_present')
		, 'lbl_show_absent' => Translations::get('lbl_show_absent')
		, 'lbl_show_all' => Translations::get('lbl_show_all')
		, 'chosenFilter' => $chosenFilter
	));
}
