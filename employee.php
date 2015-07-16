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
$oPage->setTitle('Staff | Employee');
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

	// header
	$ret = '<h2>' . $staff->getNiceFirstLastname() . '</h2>';

	// go back
	$goback = getReferer();
	if ( $goback == '' ) {
		$goback = 'presentornot.php';
	} else {
		$goback = stripDomainnameFromUrl( $goback );
	}
	$goback = createUrl( array( 'url' => $goback, 'label' => 'Go back' ) );
	$ret .= $goback . '<br><br>';

	// get check in/out status
	$status = getStatusColor($staff->getId(), date("Ymd"));
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}

	$ret .= "
<table border=0 cellpadding=2 cellspacing=0>
";

	$photo = $staff->getPhoto();
	$photo = checkImageExists( class_settings::get('staff_images_directory') . $photo, class_settings::get('noimage_file') );
	$photo = "<img src=\"$photo\">";

	// NAAM
	$ret .= "
<tr>
	<td>Name:</td>
	<td>" . $staff->getNiceFirstLastname() . "</td>
	<td width=\"20px\"></td>
	<td rowspan=10 valign=top>$photo</td>
</tr>
";

	// CHECK IN/OUT
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

	// ROOM
	$ret .= "
<tr>
	<td>Room:</td>
	<td>" . $staff->getRoom() . "</td>
</tr>
";

	// TELEPHONE
	$ret .= "
<tr>
	<td>Telephone:</td>
	<td>" . $staff->getTelephone() . "</td>
</tr>
";

	// EMAIL
	$ret .= "
<tr>
	<td>E-mail:</td>
	<td><a href=\"mailto:" . $staff->getEmail() . "\">" . $staff->getEmail() . "</a></td>
</tr>
";

	// DEPARTMENT
	$ret .= "
<tr>
	<td>Department:</td>
	<td>" . $staff->getDepartment()->getShort1() . "</td>
</tr>
";

	// BHV
	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'bhv.php', 'label' => 'BHV' ) ) . ":</td>
	<td>" . ( $staff->isBhv() ? 'yes' : 'no' ) . "</td>
</tr>
";

	// EHBO
	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'ehbo.php', 'label' => 'EHBO' ) ) . ":</td>
	<td>" . ( $staff->isEhbo() ? 'yes' : 'no' ) . "</td>
</tr>
";

	// ONTRUIMER
	$ret .= "
<tr>
	<td>Ontruimer:</td>
	<td>" . ( $staff->isOntruimer() ? 'yes' : 'no' ) . "</td>
</tr>
";

	// SCHEDULE
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

	$ret .= "
</table>
";

	return $ret;
}
