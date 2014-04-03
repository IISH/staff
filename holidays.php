<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | Holidays');
$oPage->setContent(createHolidaysContent( ));

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createHolidaysContent( ) {
    global $settings;

    if ( $settings["timecard_connection_successful"] == 0 ) {
        return "Cannot load National holidays.";
    }

	global $settings;

	$ret = "<h2>Holidays</h2>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oDbSettings = new class_db($settings, 'timecard');
	$oView = new class_view($settings, $oDbSettings);

	$oView->set_view( array(
		'query' => 'SELECT * FROM Feestdagen WHERE 1=1 AND isdeleted=0 AND datum >= \'' . date('Y') . '\''
		, 'count_source_type' => 'query'
		, 'order_by' => 'datum ASC '
		, 'anchor_field' => 'ID'
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_date ( array(
		'fieldname' => 'datum'
		, 'fieldlabel' => 'Date'
		, 'if_no_value_value' => '-no value-'
		, 'format' => 'D j F Y'
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'omschrijving'
		, 'fieldlabel' => 'Description'
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'vooreigenrekening'
		, 'fieldlabel' => 'National holiday'
		, 'show_different_values' => 1
		, 'different_true_value' => 'no'
		, 'different_false_value' => 'yes'
		)));

	// calculate and show view
	$ret .= $oView->generate_view();

	return $ret;
}
?>