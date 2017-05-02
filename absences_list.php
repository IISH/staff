<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAuthorisationTabAbsences() ) {
	die('Access denied.<br><a href="index.php">Go back</a>');
}

//
$s = getAndProtectSearch();

$retval = '';

//
//$favIds = implode(',', $oWebuser->getFavourites('vakantie'));
$favIds = implode(',', $oWebuser->getFavourites('present'));

// CRITERIUM
$queryCriterium = '';
$to_short = 0;
if ( $s == '' ) {
	// no search
	// use favourites
	$queryCriterium = ' AND ' . Settings::get('protime_tables_prefix') . 'curric.PERSNR IN (' . $favIds . ') ';
} else {
	$to_short = strlen(str_replace(' ', '', $s)) < 3;
	if ( $to_short == 1 ) {
		// search
		$queryCriterium = ' AND 1=0 ';
	} else {
		// search
		$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02", Settings::get('curric_room'), "SHORT_" . getLanguage()), explode(' ', $s));
	}
}

$selectedMonth = trim(substr($_GET["m"],0,2));
if ( $selectedMonth == '' ) {
	$selectedMonth = date("m");
}

$selectedYear = trim(substr($_GET["y"],0,4));
if ( $selectedYear == '' ) {
	$selectedYear = date("Y");
}

// allow only previous, current and next year
if ( $selectedYear < date("Y")-1 ) {
	$selectedYear = date("Y")-1;
	$selectedMonth = 1;
} elseif ( $selectedYear > date("Y")+1 ) {
	$selectedYear = date("Y")+1;
	$selectedMonth = 12;
}

$daysInCurrentMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

$cellWidth = 23;

$arrHolidays = getNationalHolidays($selectedYear, $selectedMonth );

if ( $to_short != 1 ) {

	// TODOGCU
	// loop employees
	$querySelect = "
SELECT DISTINCT " . Settings::get('protime_tables_prefix') . "curric.PERSNR, " . Settings::get('protime_tables_prefix') . "curric.FIRSTNAME, " . Settings::get('protime_tables_prefix') . "curric.NAME
FROM " . Settings::get('protime_tables_prefix') . "curric
	LEFT JOIN staff_today_checkinout ON protime_curric.PERSNR = staff_today_checkinout.PERSNR AND  staff_today_checkinout.BOOKDATE = '" . date("Ymd") . "'
	LEFT JOIN " . Settings::get('protime_tables_prefix') . "depart ON " . Settings::get('protime_tables_prefix') . "curric.DEPART = " . Settings::get('protime_tables_prefix') . "depart.DEPART
WHERE " . $dateOutCriterium . $queryCriterium . Misc::getNeverShowPersonsCriterium() . "
ORDER BY " . Settings::get('protime_tables_prefix') . "curric.FIRSTNAME, " . Settings::get('protime_tables_prefix') . "curric.NAME
";

//echo $querySelect;

	$stmt = $dbConn->getConnection()->prepare($querySelect);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {

		$oEmployee = new ProtimeUser($row["PERSNR"]);

		$tmp = "
<TR>
	<TD><div id=\"divAddRemove" . $oEmployee->getId() . "\">::ADDREMOVE::</div></TD>
	<TD>" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname() ) ) . "</TD>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<TD></TD>
	<TD align=\"center\">::VAKANTIE::</TD>
</TR>
";

		//
		$status = getCurrentDayCheckInoutState($oEmployee->getId());

		//
		$tmp = str_replace('::STATUS_STYLE::', $status["status_color"], $tmp);
		$tmp = str_replace('::STATUS_TEXT::', $status["status_text"], $tmp);
		$tmp = str_replace('::STATUS_ALT::', $status["status_alt"], $tmp);

		//
		if ( strpos(',' . $favIds . ',', ',' . $oEmployee->getId() . ',') !== false ) {
			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'r\');" title="' . Translations::get('lbl_click_to_remove_from_favourites') . '" class="nolink favourites_on">&#9733;</a>', $tmp);
		} else {
			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'a\');" title="' . Translations::get('lbl_click_to_add_to_favourites') . '" class="nolink favourites_off">&#9733;</a>', $tmp);
		}

		//
		$oAbsenceCalendar = new AbsenceCalendar($oEmployee->getId());
		$arrVakantie = $oAbsenceCalendar->getAbsencesAndHolidaysMonth($selectedYear, $selectedMonth );

		$vak = AbsenceCalendarFormat::inMonthListFormat($selectedYear, $selectedMonth, $arrVakantie, $arrHolidays );

		$tmp = str_replace('::VAKANTIE::', $vak, $tmp);

		// 
		$retval .= $tmp;
	}

	// HEADERS
	$headerDays = '';
	for ( $i = 1; $i <= $daysInCurrentMonth; $i++ ) {
		$extrastyle = "width:" . $cellWidth ."px;border: thin solid white;";

		$celValue = $i;

		$cellStyle = getStyle($selectedYear, $selectedMonth, $i, array(), $arrHolidays,1 );

		$extrastyle .= $cellStyle["tdStyle"];

		if ( $cellStyle["alt"] != '' ) {
			$celValue = '<a title="' . $cellStyle["alt"] . '" style="' . $cellStyle["hrefStyle"] . '" >' . $celValue . '</a>';
		}

		$headerDays .= "<TD style=\"" . $extrastyle . "\" align=center><font size=-2>" . $celValue . "</font></TD>";
	}

	// 
	if ( $retval != '' ) {
		$vakantieWidth = $daysInCurrentMonth * $cellWidth+($daysInCurrentMonth*2);
		$retval = "
<TABLE border=0 cellspacing=1 cellpadding=0>
<TR>
	<TD width=25></TD>
	<TD width=220><font size=-1><b>" . Translations::get('lbl_name') . "</b></font></TD>
	<TD width=100 align=\"center\"><font size=-1><b>" . Translations::get('lbl_today') . "</b></font></TD>
	<TD width=18 align=\"center\"></TD>
	<TD style=\"width:" . $vakantieWidth . "px;\" align=\"center\"><font size=-1><b>" . Translations::get('lbl_holidayabsences') . " " . Translations::get('month' . ($selectedMonth+0)) . ' ' . $selectedYear . "</b></font></TD>
</TR>
<TR>
	<TD></TD>
	<TD></TD>
	<TD></TD>
	<TD></TD>
	<TD align=center>
		<TABLE border=0 cellspacing=0 cellpadding=0>
		<TR>" . $headerDays . "</TR>
		</TABLE>
	</TD>
</TR>
" . $retval . "
</table><br>";
	} elseif ( $s != '' ) {
		$retval .= '<span class="error">' . Translations::get('nothing_found') . '</span>';
	} else {
		$retval .= Translations::get('start_searching');
	}
}

//
echo $retval;

//function calculateNrOfDaysForMonth($year, $month) {
//	return cal_days_in_month(CAL_GREGORIAN, $month, $year);
//}
