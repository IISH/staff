<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | National holidays');
$oPage->setContent(createNationalHolidaysContent( ));

// show page
echo $oPage->getPage();

// TODOEXPLAIN
function createNationalHolidaysContent( ) {
    global $settings;

	$ret = "<h2>National holidays</h2><br>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oDbSettings = new class_db($settings, 'presentornot');
	$oView = new class_view($settings, $oDbSettings);

	$oView->set_view( array(
		'query' => 'SELECT * FROM Feestdagen WHERE 1=1 AND isdeleted=0 AND datum >= \'' . date('Y') . '\' ORDER BY datum ASC '
		, 'count_source_type' => 'query'
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_date ( array(
		'fieldname' => 'datum'
		, 'fieldlabel' => 'Date'
		, 'format' => 'D j F Y'
		)));

	$oView->add_field( new class_field ( array(
		'fieldname' => 'omschrijving'
		, 'fieldlabel' => 'Description'
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'vooreigenrekening'
		, 'fieldlabel' => 'For own account'
		, 'show_different_values' => 1
		, 'different_true_value' => 'yes'
		, 'different_false_value' => 'no'
		)));

	// calculate and show view
	$ret .= $oView->generate_view();

	// add source
	$ret .= "<br>Source: <a href=\"https://intranet.iisg.nl/nl/manual/feest-en-sluitingsdagen\" target=\"_blank\">https://intranet.iisg.nl/nl/manual/feest-en-sluitingsdagen</a>";

	return $ret;
}
