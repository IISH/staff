<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

//
$s = getAndProtectSearch();
$layout = trim($protect->requestPositiveNumberOrEmpty('get', "l"));
if ( $layout <> "2" ) {
	$layout = 1;
}

$retval = '';

//
$favIds = implode(',', $oWebuser->getFavourites('present'));
$checkInOutIds = implode(',', $oWebuser->getFavourites('checkinout'));

// CRITERIUM
$queryCriterium = '';
$title = '';
if ( $s == '-a-' ) {
	//
	$title = Translations::get('all_employees');
} elseif ( $s == '-r-' ) {
	//
	$title = Translations::get('absent_employes');
} elseif ( $s == '-g-' ) {
	//
	$title = Translations::get('present_employees');
} elseif ( $s == '' ) {
	// no search
	// use favourites
	$queryCriterium = ' AND PERSNR IN (' . $favIds . ') ';
	$title = Translations::get('your_favourites');
} else {
	// search
	$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02", Settings::get('curric_room'), "SHORT_" . getLanguage()), explode(' ', $s));
	$title = 'Search: ' . $s;
}

//

//
$querySelect = "
SELECT *
FROM " . Settings::get('protime_tables_prefix') . "CURRIC
	LEFT JOIN " . Settings::get('protime_tables_prefix') . "DEPART ON " . Settings::get('protime_tables_prefix') . "CURRIC.DEPART = " . Settings::get('protime_tables_prefix') . "DEPART.DEPART
WHERE " . $dateOutCriterium . $queryCriterium . Misc::getNeverShowPersonsCriterium() . " ORDER BY FIRSTNAME, NAME ";

$totaal["aanwezig"] = 0;
$totaal["afwezig"] = 0;

$retvalArray = array();

$stmt = $dbConn->getConnection()->prepare($querySelect);
$stmt->execute();
$result = $stmt->fetchAll();
foreach ($result as $row) {
	$photo = '';

	$oEmployee = new ProtimeUser( $row["PERSNR"] );

	if ( $layout == 1 ) {
		$tmp = "
<tr>
	<td><div id=\"divAddRemove" . $oEmployee->getId() . "\">::ADDREMOVE::</div></td>
	<td><div id=\"divCheckInOut" . $oEmployee->getId() . "\">::CHECKINOUT::</div></td>
	<td>" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname() ) ) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<td align=\"center\">" . Telephone::getTelephonesHref($oEmployee->getTelephones()) . "</td>
	<td align=\"center\">" . static_Room::createRoomUrl( $oEmployee->getRoom() ) . "</td>
</td>
</tr>
";
	} else {

		$tmp = "
<table class=\"photobook\">
<tr class=\"photobook\">
	<td  class=\"photobook\" colspan=4>" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => '::PHOTO::' ) ) . "</td>
</tr>
<tr>
	<td  class=\"photobook\" colspan=4>" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname() ) ) . "</td>
</tr>
<tr>
	<td class=\"photobook\"><div id=\"divAddRemove" . $oEmployee->getId() . "\">::ADDREMOVE::</div></td>
	<td class=\"photobook\"><div id=\"divCheckInOut" . $oEmployee->getId() . "\">::CHECKINOUT::</div></td>
	<td class=\"photobook presentornot_absence\" colspan=2 width=\"100px\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
</tr>
<tr>
	<td class=\"photobook\" colspan=4>" . Translations::get('lbl_telephone_short') . ": " . valueOr(Telephone::getTelephonesHref($oEmployee->getTelephones())) . "<br>" . Translations::get('lbl_room_short') . ": " . valueOr( static_Room::createRoomUrl( $oEmployee->getRoom() ) ) . "</td>
</tr>
</table>
";

		$photo = $oEmployee->getPhoto();
		$photo = checkImageExists( Settings::get('staff_images_directory') . $photo, Settings::get('noimage_file') );
		$photo = "<img src=\"$photo\"  style=\"height:140px;\">";
		$tmp = str_replace('::PHOTO::', $photo, $tmp);
	}

	//
	if ( strpos(',' . $favIds . ',', ',' . $oEmployee->getId() . ',') !== false ) {
		$alttitle = Translations::get('lbl_click_to_remove_from_favourites');
		$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'r\');" title="' . $alttitle . '" class="nolink favourites_on">&#9733;</a>', $tmp);
	} else {
		$alttitle = Translations::get('lbl_click_to_add_to_favourites');
		$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'a\');" title="' . $alttitle . '" class="nolink favourites_off">&#9733;</a>', $tmp);
	}

	// 
	if ( strpos(',' . $checkInOutIds . ',', ',' . $oEmployee->getId() . ',') !== false ) {
		$alttitle = Translations::get('lbl_click_to_not_get_email_notification');
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-red.png" border=0></a>', $tmp);
	} else {
		$alttitle = Translations::get('lbl_click_to_get_email_notification');
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-black.png" border=0></a>', $tmp);
	}

	//
	$status = getCurrentDayCheckInoutState($oEmployee->getId());

	if ( $status["aanwezig"] == 1 ) {
		$totaal["aanwezig"]++;
	} else {
		$totaal["afwezig"]++;
	}

	//
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}
	$tmp = str_replace('::STATUS_STYLE::', $status["status_color"], $tmp);
	$tmp = str_replace('::STATUS_TEXT::', $status["status_text"], $tmp);
	$tmp = str_replace('::STATUS_ALT::', $status["status_alt"], $tmp);

	// moet regel getoond worden?
	if ( $s == '-r-' ) {
		// als rood, dan alleen tonen als persoon niet aanwezig is
		if ( $status["aanwezig"] == 0 ) {
			//
//			$retval .= $tmp;
			$retvalArray[] = $tmp;
		}
	} elseif ( $s == '-g-' ) {
		// als groen, dan alleen tonen als persoon aanwezig is
		if ( $status["aanwezig"] == 1 ) {
			//
//			$retval .= $tmp;
			$retvalArray[] = $tmp;
		}
	} else {
		// als niet rood en ook niet groen dan altijd tonen
//		$retval .= $tmp;
		$retvalArray[] = $tmp;
	}
}

if ( count($retvalArray) > 0 ) {

	if ( $layout == 1 ) {
		$retval = "
<table border=0 cellspacing=1 cellpadding=1>
<TR>
	<TD width=25></TD>
	<TD width=25></TD>
	<TD width=250><font size=-1><b>" . Translations::get('lbl_name') . "</b></font></TD>
	<td width=100 align=\"center\"><font size=-1><b>" . Translations::get('lbl_check_inout') . "</b></font></td>
	<td width=100 align=\"center\"><font size=-1><b>" . Translations::get('lbl_telephone') . "</b></font></td>
	<td width=100 align=\"center\"><font size=-1><b>" . Translations::get('lbl_room') . "</b></font></td>
</TR>";

		foreach ( $retvalArray as $item ) {
			$retval .= $item;
		}

		$retval .= "</table>";

	} else {
		$retval = "<table border=0 cellspacing=1 cellpadding=10>";
		$cols = 5;
		$counter = 0;
		$first_td = true;
		foreach ( $retvalArray as $item ) {
			$counter++;
			if ( $counter == 1 && !$first_td ) {
				$retval .= '</tr>';
			}
			if ( $counter == 1 ) {
				$retval .= '<tr>';
			}
			$retval .= '<td valign=top>' . $item . '</td>';
			if ( $counter == $cols ) {
				$counter = 0;
			}
			$first_td = false;
		}

		$retval .= '</tr>';
		$retval .= "</table>";


	}

	$retval .= "<br><font size=-1><i>" . Translations::get('lbl_present') . ": " . $totaal["aanwezig"] . "<br>
		" . Translations::get('lbl_not_present') . ": " . $totaal["afwezig"] . "<br><br>
		" . Translations::get('lbl_page_refreshes_every') . " " . date("H:i:s") . "</i></font>";
}

$retval = "<h1>$title</h1>" . $retval;

echo $retval;
