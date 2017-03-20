<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

if ( !$oWebuser->isAdmin() ) {
	die('Access denied.<br><a href="index.php">Go back</a>');
}

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('header_specialnumbers'));
$oPage->setContent(createSpecialNumbersContent( ));

// show page
echo $oPage->getPage();

function createSpecialNumbersContent( ) {
    global $databases, $settings;

	$ret = "<h1>" . Translations::get('header_specialnumbers') . "</h1>";

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");

	$oForm = new class_form($settings);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM staff_special_numbers WHERE ID=[FLD:ID] AND isdeleted=0 '
		, 'table' => 'staff_special_numbers'
		, 'primarykey' => 'ID'
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'object'
		, 'fieldlabel' => Translations::get('lbl_specnumber_object')
		)));

    $oForm->add_field( new class_field_string ( array(
        'fieldname' => 'number'
        , 'fieldlabel' => Translations::get('lbl_specnumber_number')
        )));

    $oForm->add_field( new class_field_string ( array(
        'fieldname' => 'extra'
        , 'fieldlabel' => Translations::get('lbl_specnumber_extra')
        )));

    $oForm->add_field( new class_field_string ( array(
        'fieldname' => 'location'
        , 'fieldlabel' => Translations::get('lbl_specnumber_location')
        )));

    $oForm->add_field( new class_field_string ( array(
        'fieldname' => 'room'
        , 'fieldlabel' => Translations::get('lbl_specnumber_room')
        )));

    $oForm->add_field( new class_field_string ( array(
        'fieldname' => 'fax'
        , 'fieldlabel' =>  Translations::get('lbl_specnumber_fax')
        )));

    $oForm->add_field( new class_field_string ( array(
        'fieldname' => 'email'
        , 'fieldlabel' =>  Translations::get('lbl_specnumber_email')
        )));

    $oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'isdeleted'
		, 'fieldlabel' => 'isdeleted'
		)));

	// generate form
	$ret .= $oForm->generate_form();

	return $ret;
}
