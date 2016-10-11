<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('header_user'));
$oPage->setContent(createUserContent( ));

// show page
echo $oPage->getPage();

function createUserContent( ) {
    global $oWebuser;

	$ret = "<h1>" . Translations::get('header_user') . "</h1>";

	// get check in/out status
	$status = getCurrentDayCheckInoutState($oWebuser->getId());
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}

	$ret .= "
<div class=\"personalpage\">
<table border=0 cellpadding=2 cellspacing=0>
";

	$photo = $oWebuser->getPhoto();
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
	<td>" . $oWebuser->getNiceFirstLastname() . "</td>
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
	<td>" . static_Room::createRoomUrl($oWebuser->getRoom()) . "</td>
</tr>
";

	// TELEPHONE
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_telephone') . ":</td>
	<td>" . Telephone::getTelephonesHref($oWebuser->getTelephones()) . "</td>
</tr>
";

	// EMAIL
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_email') . ":</td>
	<td><a href=\"mailto:" . $oWebuser->getEmail() . "\">" . $oWebuser->getEmail() . "</a></td>
</tr>
";

	// DEPARTMENT
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_department') . ":</td>
	<td>" . ( is_object($oWebuser->getDepartment()) ? $oWebuser->getDepartment()->getShort() : '' ) . "</td>
</tr>
";

	// BHV
	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'ert.php', 'label' => Translations::get('lbl_ert') ) ) . ":</td>
	<td>" . ( $oWebuser->isBhv() ? Translations::get('yes') : Translations::get('no') ) . "</td>
</tr>
";

	// EHBO
	$ret .= "
<tr>
	<td>" . createUrl( array( 'url' => 'firstaid.php', 'label' => Translations::get('lbl_firstaid') ) ) . ":</td>
	<td>" . ( $oWebuser->isEhbo() ? Translations::get('yes') : Translations::get('no') ) . "</td>
</tr>
";

	// ONTRUIMER
	$ret .= "
<tr>
	<td>" . Translations::get('lbl_evacuator') . ":</td>
	<td>" . ( $oWebuser->isOntruimer() ? Translations::get('yes') : Translations::get('no') ) . "</td>
</tr>
";

	// SCHEDULE
	$currentSchedule = new ProtimeUserSchedule($oWebuser->getId(), 2015);
	$ret .= "
<tr>
	<td valign=top>" . Translations::get('lbl_schedule') . ":</td>
	<td>" . $currentSchedule->getCurrentSchedule() . "</td>
</tr>
";

	$ret .= "
</table>
</div>
";

	$ret .= "
<div class=\"personalpage\">$photo</div>
";

	$smallerFontInstitutePrefix = $oWebuser->getUserSetting('smaller_font_institute_prefix', 0);
//	$callViaComputer = $oWebuser->getUserSetting('call_via_computer', 0);
//	$callViaTablet = $oWebuser->getUserSetting('call_via_tablet', 0);
//	$callViaSmarthphone = $oWebuser->getUserSetting('call_via_mobile', 0);
	$choices = array('No', 'Yes');

	$ret .= "
<div class=\"personalpage\">
<b>Preferences</b><br>
<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
	<td>Smaller font institute prefix:&nbsp;</td>
	<td valign=top>" . $choices[$smallerFontInstitutePrefix] . " <a href=\"switch.php?s=smallerfont&v=$smallerFontInstitutePrefix\">Switch</a></td>
</tr>
";

//	$ret .= "
//<tr>
//	<td>Enable calls via computer:&nbsp;</td>
//	<td valign=top>" . $choices[$callViaComputer] . " <a href=\"switch.php?s=callviacomputer&v=$callViaComputer\">Switch</a></td>
//</tr>
//<tr>
//	<td>Enable calls via tablet:&nbsp;</td>
//	<td valign=top>" . $choices[$callViaTablet] . " <a href=\"switch.php?s=callviatablet&v=$callViaTablet\">Switch</a></td>
//</tr>
//<tr>
//	<td>Enable calls via mobile phone:&nbsp;</td>
//	<td valign=top>" . $choices[$callViaSmarthphone] . " <a href=\"switch.php?s=callviamobile&v=$callViaSmarthphone\">Switch</a></td>
//</tr>
//";

	$ret .= "
</table>
</div>";

	$ret .= "<br class=\"clearBoth\">";

	return $ret;
}
