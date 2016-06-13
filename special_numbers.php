<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('header_specialnumbers'));
$oPage->setContent(createSpecialNumbersContent( ));

// show page
echo $oPage->getPage();

function createSpecialNumbersContent( ) {
    global $databases;

	$ret = "<h2>" . Translations::get('header_specialnumbers') . "</h2><br>";

	require_once("./classes/mysql.inc.php");
	require_once("./classes/class_view/view.inc.php");
	require_once("./classes/class_view/fieldtypes/field.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new View($oDb);

	$oView->set_view( array(
		'query' => 'SELECT * FROM Staff_special_numbers WHERE isdeleted=0 ORDER BY object ASC '
		, 'count_source_type' => 'query'
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new Field ( array(
		'fieldname' => 'object'
		, 'fieldlabel' => Translations::get('lbl_object')
		)));

	$oView->add_field( new Field ( array(
		'fieldname' => 'number'
		, 'fieldlabel' => Translations::get('lbl_number')
		)));

	// calculate and show view
	$ret .= $oView->generate_view();

	return $ret;
}
