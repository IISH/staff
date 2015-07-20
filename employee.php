<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

$id = substr(trim($protect->requestPositiveNumberOrEmpty('get', "id")), 0, 4);

$oProtime = new class_mysql($databases['default']);
$oProtime->connect();

$staff = new ProtimeUser($id);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle(Translations::get('iisg_employee') . ' | ' . $staff->getNiceFirstLastname());
$oPage->setContent(createStaffContent( $staff ));

// show page
echo $oPage->getPage();

function createStaffContent( $staff ) {
	// header
	$ret = '<h2>' . $staff->getNiceFirstLastname() . '</h2>';

	// go back
	$goback = ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
	if ( $goback == '' ) {
		$goback = 'presentornot.php';
	} else {
		$goback = stripDomainnameFromUrl( $goback );
	}
	$goback = createUrl( array( 'url' => $goback, 'label' => Translations::get('go_back') ) );
	$ret .= $goback . '<br><br>';

	// get check in/out status
	$status = getCurrentDayCheckInoutState($staff->getId());
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}

	$ret .= "
<table border=0 cellpadding=2 cellspacing=0>
";

	$photo = $staff->getPhoto();
	$photo = checkImageExists( Settings::get('staff_images_directory') . $photo, Settings::get('noimage_file') );
	$photo = "<img src=\"$photo\">";

	// NAAM
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_name') . ":</td>
	<td>" . $staff->getNiceFirstLastname() . "</td>
	<td width=\"20px\"></td>
	<td rowspan=9 valign=top>$photo</td>
</tr>
";

	// CHECK IN/OUT
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_check_inout') . ":</td>
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
	<td>" . Translations::get('lbl_room') . ":</td>
	<td>" . static_Room::createRoomUrl($staff->getRoom()) . "</td>
</tr>
";

	// TELEPHONE
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_telephone') . ":</td>
	<td>" . $staff->getTelephone() . "</td>
</tr>
";

	// EMAIL
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_email') . ":</td>
	<td><a href=\"mailto:" . $staff->getEmail() . "\">" . $staff->getEmail() . "</a></td>
</tr>
";

	// DEPARTMENT
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_department') . ":</td>
	<td>" . $staff->getDepartment()->getShort() . "</td>
</tr>
";

	// BHV
	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'ert.php', 'label' => Translations::get('lbl_ert') ) ) . ":</td>
	<td>" . ( $staff->isBhv() ? Translations::get('yes') : Translations::get('no') ) . "</td>
</tr>
";

	// EHBO
	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'firstaid.php', 'label' => Translations::get('lbl_firstaid') ) ) . ":</td>
	<td>" . ( $staff->isEhbo() ? Translations::get('yes') : Translations::get('no') ) . "</td>
</tr>
";

	// ONTRUIMER
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_evacuator') . ":</td>
	<td>" . ( $staff->isOntruimer() ? Translations::get('yes') : Translations::get('no') ) . "</td>
</tr>
";

	// SCHEDULE
	$currentSchedule = new ProtimeUserSchedule($staff->getId(), 2015);
	$ret .= "
<tr>
	<td valign=top>" . Translations::get('lbl_schedule') . ":</td>
	<td colspan=3>" . $currentSchedule->getCurrentSchedule() . "</td>
</tr>
";

	$ret .= "
</table>
";

	return $ret;
}
