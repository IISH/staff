<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Staff | National holidays');
$oPage->setContent(createNationalHolidaysContent( ));

// show page
echo $oPage->getPage();

// TODOEXPLAIN
function createNationalHolidaysContent( ) {
    global $databases;

	$ret = "<h2>National holidays</h2><br>";

	require_once("./classes/class_mysql.inc.php");
	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($oDb);

	$oView->set_view( array(
		'query' => 'SELECT * FROM Staff_feestdagen WHERE 1=1 AND isdeleted=0 AND datum >= \'' . date('Y-m-d') . '\' ORDER BY datum ASC '
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
		, 'fieldlabel' => ''
		, 'show_different_values' => 1
		, 'different_true_value' => 'brugdag'
		, 'different_false_value' => ''
		)));

	// calculate and show view
	$ret .= $oView->generate_view();

	// add source
	$ret .= "<br>Source: <a href=\"https://intranet.iisg.nl/nl/manual/feest-en-sluitingsdagen\" target=\"_blank\">https://intranet.iisg.nl/nl/manual/feest-en-sluitingsdagen</a>";

	return $ret;
}
