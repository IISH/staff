<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('header_nationalholidays'));
$oPage->setContent(createNationalHolidaysContent( ));

// show page
echo $oPage->getPage();

function createNationalHolidaysContent( ) {
    global $databases;

	$ret = "<h2>" . Translations::get('header_nationalholidays') . "</h2><br>";

	require_once("./classes/mysql.inc.php");
	require_once("./classes/class_view/view.inc.php");
	require_once("./classes/class_view/fieldtypes/field_date.inc.php");
	require_once("./classes/class_view/fieldtypes/field.inc.php");
	require_once("./classes/class_view/fieldtypes/field_bit.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new View($oDb);

	$oView->set_view( array(
		'query' => 'SELECT * FROM Staff_feestdagen WHERE 1=1 AND isdeleted=0 AND datum >= \'' . date('Y-m-d') . '\' ORDER BY datum ASC '
		, 'count_source_type' => 'query'
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new FieldDate ( array(
		'fieldname' => 'datum'
		, 'fieldlabel' => Translations::get('lbl_date')
		, 'format' => 'D j F Y'
		)));

	$oView->add_field( new Field ( array(
		'fieldname' => 'omschrijving'
		, 'fieldlabel' => Translations::get('lbl_description')
		)));

	$oView->add_field( new FieldBit ( array(
		'fieldname' => 'vooreigenrekening'
		, 'fieldlabel' => Translations::get('lbl_bridgeday')
		, 'show_different_values' => 1
		, 'different_true_value' => Translations::get('lbl_bridgeday_yes')
		, 'different_false_value' => Translations::get('lbl_bridgeday_no')
		)));

	// calculate and show view
	$ret .= $oView->generate_view();

	// add source
	$ret .= "<br>" . Translations::get('lbl_source') . ": <a href=\"https://intranet.iisg.nl/nl/manual/feest-en-sluitingsdagen\" target=\"_blank\">https://intranet.iisg.nl/nl/manual/feest-en-sluitingsdagen</a>";

	return $ret;
}
