<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

$id = substr(trim($protect->requestPositiveNumberOrEmpty('get', "id")), 0, 4);

$staff = new ProtimeUser($id);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle(Translations::get('iisg_employee') . ' - ' . $staff->getNiceFirstLastname());
$oPage->setContent(createStaffContent( $staff ));

// show page
echo $oPage->getPage();

function createStaffContent( $staff ) {
    global $oWebuser;

	// header
	$ret = '<h1>' . $staff->getNiceFirstLastname() . '</h1>';

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
<div class=\"personalpage\">
<table border=0 cellpadding=2 cellspacing=0>
";

	$photo = $staff->getPhoto();
    $alttitle = '';
	if ( checkPhotoExists(Settings::get('staff_images_directory') . $photo) ) {
	    $photo = Settings::get('staff_images_directory') . $photo;
	} else {
        if ( $oWebuser->isAdmin() ) {
            $alttitle = 'Missing photo: &quot;' . Settings::get('staff_images_directory') . $photo . '&quot;';
        }
        $photo = Settings::get('noimage_file');
	}
	$photo = "<img src=\"$photo\" title=\"$alttitle\">";

	// NAAM
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_name') . ":</td>
	<td>" . $staff->getNiceFirstLastname() . "</td>
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
	<td>" . Telephone::getTelephonesHref($staff->getTelephones()) . "</td>
</tr>
";

	// EMAIL
	if ( $staff->getEmail() != '' ) {
		$ret .= "
<tr>
	<td>" . Translations::get('lbl_email') . ":</td>
	<td><a href=\"mailto:" . $staff->getEmail() . "\">" . $staff->getEmail() . "</a></td>
</tr>
";
	}

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

	// UITDIENST
	$dateOut = $staff->getDateOut();
	if ( $dateOut != '' && $dateOut != '0' && $dateOut < date("Ymd") ) {
		$ret .= "
<tr>
	<td>" . Translations::get('lbl_date_out') . ":</td>
	<td>" . class_datetime::formatDate($dateOut) . "</td>
</tr>
";
	}

	// SCHEDULE
	$currentSchedule = new ProtimeUserSchedule($staff->getId(), date("Ymd"));
	$ret .= "
<tr>
	<td valign=top>" . Translations::get('lbl_schedule') . ":</td>
	<td>" . $currentSchedule->getCurrentSchedule() . "</td>
</tr>
";


	if ( $oWebuser->isAdmin() ) {
		if ( count($staff->getAuthorisations()) > 0 ) {

			$ret .= "
<tr>
	<td valign=top>" . Translations::get('lbl_authorisation') . ":</td>
	<td>" . implode('<br>', $staff->getAuthorisations()) . "</td>
</tr>
";

		}
	}

	$ret .= "
</table>
</div>
";

	$ret .= "
<div class=\"personalpage\">$photo</div>
	";

	$ret .= "<br class=\"clearBoth\">";

	return $ret;
}
