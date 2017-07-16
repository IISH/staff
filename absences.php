<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAuthorisationTabAbsences() ) {
	die('Access denied.<br><a href="index.php">Go back</a>');
}

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('menu_absences'));
$oPage->setContent(createPresentContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createPresentContent() {
	global $oWebuser, $twig;

	//
	$s = getAndProtectSearch();

	// Legenda
	$arrLegenda = array();
	$legenda = new Legenda();
	// create array of legenda items
	foreach ( $legenda->getAll() as $item ) {
		if ( $oWebuser->isAdmin() || $oWebuser->isHeadOfDepartment() || $oWebuser->hasAuthorisationReasonOfAbsenceAll() || $item->isVisibleForEveryone() ) {
			$arrLegenda[strtolower($item->getDescriptin())] = "<span align=\"center\" style=\"" . $item->getBackgroundColor() . "display:inline-block;margin-bottom:5px;margin-right:5px;\">&nbsp;" . strtolower($item->getDescriptin()) . "&nbsp;</span>";
		}
	}

	// sort the legenda array on 'description'
	ksort($arrLegenda);

	//
	$legendas = array();
	foreach ( $arrLegenda as $item ) {
		$legendas[] = $item;
	}

	//
	return $twig->render('absences.html', array(
		'title' => Translations::get('menu_absences')
		, 's' => $s
		, 'refreshAfterXSeconds' => 60
		, 'currentYear' => date("Y")
		, 'currentMonth' => date("m")
		, 'lbl_quick_search' => Translations::get('lbl_quick_search')
		, 'min_x_characters' => Translations::get('min_x_characters')
		, 'lbl_show_favourites' => Translations::get('lbl_show_favourites')
		, 'go_to' => Translations::get('go_to')
		, 'go_to_current_month' => Translations::get('go_to_current_month')
		, 'go_to_previous_month' => Translations::get('go_to_previous_month')
		, 'go_to_next_month' => Translations::get('go_to_next_month')
		, 'prev' => Translations::get('prev')
		, 'next' => Translations::get('next')
		, 'lblLegenda' => Translations::get('lbl_legenda')
		, 'legendas' => $legendas
	));
}
