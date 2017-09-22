<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

//
$s = getAndProtectSearch();
$layout = trim($protect->request('get', "l"));
if ( $layout == '' ) {
	$layout = $oWebuser->getUserSetting('presentornot_display_format', 'tabular');
}
if ( !in_array($layout, array('tabular', 'tile')) ) {
	$layout = 'tabular';
}
// save setting
$query_update = "INSERT INTO staff_user_settings (`user_id`, `setting`, `value`) VALUES (" . $oWebuser->getId() . ", 'presentornot_display_format', '$layout') ON DUPLICATE KEY UPDATE `value`='$layout' ";
$stmt = $dbConn->getConnection()->prepare($query_update);
$stmt->execute();


$retval = '';

//
$favIds = implode(',', $oWebuser->getFavourites('present'));
$checkInOutIds = implode(',', $oWebuser->getFavourites('checkinout'));

// CRITERIUM
$showCalendar = 0;
$queryCriterium = '';
$queryCriterium2 = '';
$title = '';
if ( $s == '-a-' ) {
	//
	$title = Translations::get('all_employees');
	$showCalendar = 0;
} elseif ( $s == '-r-' ) {
	//
	$title = Translations::get('absent_employes');
	$showCalendar = 0;
} elseif ( $s == '-g-' ) {
	//
	$title = Translations::get('present_employees');
	$showCalendar = 0;
} elseif ( $s == '' ) {
	// no search
	// use favourites
	$queryCriterium = ' AND ' . Settings::get('protime_tables_prefix') . 'curric.PERSNR IN (' . $favIds . ') ';
	$queryCriterium2 = $queryCriterium;

	$title = Translations::get('your_favourites');
	$showCalendar = 1;
} else {
	// search
	$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02", Settings::get('curric_room'), "SHORT_" . getLanguage()), explode(' ', $s));
	$queryCriterium2 = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02", Settings::get('curric_room')), explode(' ', $s));
	$title = 'Search: ' . $s;
	$showCalendar = 1;
}

// TODOGCU
$querySelect = "
SELECT DISTINCT " . Settings::get('protime_tables_prefix') . "curric.PERSNR, " . Settings::get('protime_tables_prefix') . "curric.NAME, " . Settings::get('protime_tables_prefix') . "curric.FIRSTNAME
FROM " . Settings::get('protime_tables_prefix') . "curric
	LEFT JOIN staff_today_checkinout ON protime_curric.PERSNR = staff_today_checkinout.PERSNR AND  staff_today_checkinout.BOOKDATE = '" . date("Ymd") . "'
	LEFT JOIN " . Settings::get('protime_tables_prefix') . "depart ON " . Settings::get('protime_tables_prefix') . "curric.DEPART = " . Settings::get('protime_tables_prefix') . "depart.DEPART
WHERE " . $dateOutCriterium . $queryCriterium . Misc::getNeverShowPersonsCriterium() . "

ORDER BY FIRSTNAME, NAME ";

//echo $querySelect;

$totaal["aanwezig"] = 0;
$totaal["afwezig"] = 0;

$retvalArray = array();

$stmt = $dbConn->getConnection()->prepare($querySelect);
$stmt->execute();
$result = $stmt->fetchAll();
foreach ($result as $row) {
	$photo = '';

	$oEmployee = new ProtimeUser( $row["PERSNR"] );

	if ( $layout == 'tabular' ) {
		$tmp = "
<tr>
	<td><div id=\"divAddRemove" . $oEmployee->getId() . "\">::ADDREMOVE::</div></td>
	<td><div id=\"divCheckInOut" . $oEmployee->getId() . "\">::CHECKINOUT::</div></td>
	<td>::UITDIENST::" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname() ) ) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<td align=\"center\">" . Telephone::getTelephonesHref($oEmployee->getTelephones()) . "</td>
	<td align=\"center\">" . static_Room::createRoomUrl( $oEmployee->getRoom() ) . "</td>
</td>
</tr>
";
	} else {
		$absenceCalendarWeek = '';

		if ( $showCalendar ) {
			$arrHolidays = getNationalHolidays(date("Y"), date("m"));

			$oAbsenceCalendar = new AbsenceCalendar( $oEmployee->getId());
			$arrAbsences = $oAbsenceCalendar->getAbsencesAndHolidaysWeek(date("Y"), date("m"), date("d") );

			$firstDayOfChosenWeek = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));
			$firstDayOfChosenWeek->modify('-' . ( date("w") - 1 ) . ' day');

			$absenceCalendarWeek = AbsenceCalendarFormat::InWeekFormat( date("Y"), date("m"), $firstDayOfChosenWeek->format('d'), $arrAbsences, $arrHolidays );
		}

		$tmp = "
<table class=\"photobook\">
<tr class=\"photobook\">
	<td class=\"photobook\" colspan=4>::UITDIENST::" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => '::PHOTO::' ) ) . "</td>
</tr>
<tr>
	<td class=\"photobook\" colspan=4>" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname() ) ) . "</td>
</tr>
<tr>
	<td class=\"photobook\"><div id=\"divAddRemove" . $oEmployee->getId() . "\">::ADDREMOVE::</div></td>
	<td class=\"photobook\"><div id=\"divCheckInOut" . $oEmployee->getId() . "\">::CHECKINOUT::</div></td>
	<td class=\"photobook presentornot_absence\" colspan=2 width=\"100px\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
</tr>
";
		if ( $showCalendar ) {
			$tmp .= "
<tr>
	<td class=\"photobook\" colspan=\"4\">::ABSENCE::</td>
</tr>
";
		}

		$tmp .= "
<tr>
	<td class=\"photobook\" colspan=4>" . Translations::get('lbl_telephone_short') . ": " . valueOr(Telephone::getTelephonesHref($oEmployee->getTelephones())) . "</td>
</tr>
<tr>
	<td class=\"photobook\" colspan=4>" . Translations::get('lbl_room_short') . ": " . valueOr( static_Room::createRoomUrl( $oEmployee->getRoom() ) ) . "</td>
</tr>
</table>
";

		//
		$photo = $oEmployee->getPhoto();
		// TODOGCU
        $alttitle = '';
        if ( checkPhotoExists(Settings::get('staff_images_directory') . $photo) ) {
            $photo = Settings::get('staff_images_directory') . $photo;
        } else {
            if ( $oWebuser->isAdmin() ) {
                $alttitle = 'Missing photo: &quot;' . Settings::get('staff_images_directory') . $photo . '&quot;';
            }
            $photo = Settings::get('noimage_file');
        }
		$photo = "<img src=\"$photo\"  style=\"height:140px;\" title=\"$alttitle\">";
		$tmp = str_replace('::PHOTO::', $photo, $tmp);

		// absence
		$tmp = str_replace('::ABSENCE::', $absenceCalendarWeek, $tmp);
	}

	// show person is 'uit dienst' if date_out is in the past
	// only front desk and administrators
	$dateOutWarning = '';
	if ( ( $oWebuser->isAdmin() || $oWebuser->hasAuthorisationTabFire() ) && $oEmployee->getDateOut() != '0' && $oEmployee->getDateOut() < date("Y-m-d") ) {
		if ( $layout == 'tabular' ) {
			$separator = ' ';
		} else {
			$separator = '<br>';
		}
		$dateOutFormatted = class_datetime::formatDate($oEmployee->getDateOut());
		$dateOutWarning = '<a title="' . str_replace('::DATE::', $dateOutFormatted, Translations::get("warning_uit_dienst_explanation")) . '" class="blink">' . Translations::get("warning_uit_dienst") . '</a>' . $separator;
	}
	$tmp = str_replace('::UITDIENST::', $dateOutWarning, $tmp);

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
			$retvalArray[] = $tmp;
		}
	} elseif ( $s == '-g-' ) {
		// als groen, dan alleen tonen als persoon aanwezig is
		if ( $status["aanwezig"] == 1 ) {
			//
			$retvalArray[] = $tmp;
		}
	} else {
		// als niet rood en ook niet groen dan altijd tonen
		$retvalArray[] = $tmp;
	}
}

if ( count($retvalArray) > 0 ) {

	if ( $layout == 'tabular' ) {
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
} elseif ( $s != '' ) {
	$retval .= '<br><span class="error">' . Translations::get('nothing_found') . '</span>';
} else {
	$retval .= '<br><span class="error">' . Translations::get('start_searching') . '</span>';
}

$retval = "<h1>$title</h1>" . $retval;

echo $retval;
