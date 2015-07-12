<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | Staff');
$oPage->setContent(createStaffContent( ));

// show page
echo $oPage->getPage();

// TODOEXPLAIN
function createStaffContent( ) {
	global $protect, $databases;

	$id = substr(trim($protect->request_positive_number_or_empty('get', "id")), 0, 4);

	$oProtime = new class_mysql($databases['default']);
	$oProtime->connect();

	$staff = new class_protime_user($id);
	$goback = getReferer();
	if ( $goback == '' ) {
		$goback = 'presentornot.php';
	} else {
		$goback = stripDomainnameFromUrl( $goback );
	}

	$goback = createUrl( array( 'url' => $goback, 'label' => 'Go back' ) );

	$ret = '<h2>' . $staff->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin($staff->getLastname()) . '</h2>';
	$ret .= $goback . '<br><br>';

	$ret .= "<table border=0 cellpadding=2 cellspacing=0>";

	//
	$status = getStatusColor($staff->getId(), date("Ymd"));

	//
//	$tmp = str_replace('::STATUS_STYLE::', $status["status_color"], $tmp);
//	$tmp = str_replace('::STATUS_TEXT::', $status["status_text"], $tmp);
//	$tmp = str_replace('::STATUS_ALT::', $status["status_alt"], $tmp);
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}

	$ret .= "
<tr>
	<td>Name:</td>
	<td>" . $staff->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin($staff->getLastname()) . "</td>
	<td width=\"20px\"></td>
	<td rowspan=10 valign=top>::PHOTO::</td>
</tr>
";

	$ret .= "
<tr>
	<td>Check in/out:</td>
	<td>

<table border=0 cellpadding=0 cellspacing=0>
<tr>
	<td width=\"120px\" class=\"presentornot_absence\" style=\"" . $status["status_color"] . "\"><A class=\"checkinouttime\" TITLE=\"" . $status["status_alt"] . "\">" . $status["status_text"] . "</A></td>
</tr>
</table>

	</td>
</tr>
";

	$ret .= "
<tr>
	<td>Room:</td>
	<td>" . $staff->getRoom() . "</td>
</tr>
";

	$ret .= "
<tr>
	<td>Telephone:</td>
	<td>" . $staff->getTelephone() . "</td>
</tr>
";

	$ret .= "
<tr>
	<td>E-mail:</td>
	<td><a href=\"mailto:" . $staff->getEmail() . "\">" . $staff->getEmail() . "</a></td>
</tr>
";

	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'bhv.php', 'label' => 'BHV' ) ) . ":</td>
	<td>" . ( $staff->isBhv() ? 'yes' : 'no' ) . "</td>
</tr>
";

	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'ehbo.php', 'label' => 'EHBO' ) ) . ":</td>
	<td>" . ( $staff->isEhbo() ? 'yes' : 'no' ) . "</td>
</tr>
";

	$ret .= "
<tr>
	<td>Ontruimer:</td>
	<td>" . ( $staff->isOntruimer() ? 'yes' : 'no' ) . "</td>
</tr>
";

	//
	$currentSchedule = new class_protime_user_schedule($staff->getId(), 2015);
	$show_all_weekdays = false;
	// TODO: move persmueum to settings table
	if (strpos($staff->getEmail(), "@persmuseum.nl") !== false ) {
		$show_all_weekdays = true;
	}
	$ret .= "
<tr>
	<td valign=top>Schedule:</td>
	<td>" . $currentSchedule->getCurrentSchedule( $show_all_weekdays ) . "</td>
</tr>
";

	$ret .= "</table>";

	return $ret;
}
