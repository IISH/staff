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
$favIds = implode(',', $oWebuser->getFavourites('vakantie'));

// CRITERIUM
$queryCriterium = '';
$to_short = 0;
if ( $s == '' ) {
	// no search
	// use favourites
	$queryCriterium = 'AND PERSNR IN (' . $favIds . ') ';
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

//$cellWidth = 18;
$cellWidth = 23;

$arrHolidays = getNationalHolidays($selectedYear, $selectedMonth );

if ( $to_short != 1 ) {

	$oProtime = new class_mysql($databases['default']);
	$oProtime->connect();

	// loop employees
	$querySelect = "
SELECT *
FROM " . Settings::get('protime_tables_prefix') . "CURRIC
	LEFT JOIN " . Settings::get('protime_tables_prefix') . "DEPART ON " . Settings::get('protime_tables_prefix') . "CURRIC.DEPART = " . Settings::get('protime_tables_prefix') . "DEPART.DEPART
WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) " . $queryCriterium . " ORDER BY FIRSTNAME, NAME
";
	$resultSelect = mysql_query($querySelect, $oProtime->getConnection());

	while ( $row = mysql_fetch_assoc($resultSelect) ) {

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
		$arrVakantie = getAbsencesAndHolidays($oEmployee->getId(), $selectedYear, $selectedMonth );

		$vak = '';

		for ( $i = 1; $i <= $daysInCurrentMonth; $i++ ) {
			$style = "border: 1px solid white;";
			//
			$celValue = '&nbsp;&nbsp;';

			$cellStyle = getStyle($selectedYear, $selectedMonth, $i, $arrVakantie, $arrHolidays);

			$style .= $cellStyle["tdStyle"];
			if ( $cellStyle["alt"] != '' ) {
				$cellStyleAlt = $cellStyle["alt"];
				$cellStyleHrefStyle = $cellStyle["hrefStyle"];

				//
				if ( !$oWebuser->isAdmin() && !$oWebuser->isHeadOfDepartment() && !$oWebuser->hasAuthorisationReasonAbsence() && !$oWebuser->hasInOutTimeAuthorisation() ) {
					$cellStyleAlt = '';
					$cellStyleHrefStyle = 'color:white;';
				}

				//
				$celValue = '<a title="' . $cellStyleAlt . '" style="' . $cellStyleHrefStyle . '">' . $i . '</a>';
			}

			// width
			$style .= "width: " . $cellWidth . "px;";

			$vak .= "<TD style=\"" . $style . "\" align=\"center\" ><font size=-3>" . $celValue . "</font></TD>";
		}

		$vak = str_replace('::VAK::', $vak, "<TABLE border=0 cellspacing=0 cellpadding=0><TR>::VAK::</TR></table>");

		$tmp = str_replace('::VAKANTIE::', $vak, $tmp);

		// 
		$retval .= $tmp;
	}
	mysql_free_result($resultSelect);

	// HEADERS
	$headerDays = '';
	for ( $i = 1; $i <= $daysInCurrentMonth; $i++ ) {
		$extrastyle = "width:" . $cellWidth ."px;border: 1px solid white;";

		$celValue = $i;

		$cellStyle = getStyle($selectedYear, $selectedMonth, $i, array(), $arrHolidays);

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
	<TD width=190><font size=-1><b>" . Translations::get('lbl_name') . "</b></font></TD>
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
	}
}

//
echo $retval;

function calculateNrOfDaysForMonth($year, $month) {
	return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

function getStyle($selectedYear, $selectedMonth, $day, $absences = array(), $holidays = array()) {
	global $oWebuser;

	$tdStyle = '';
	$hrefStyle = '';
	$alt = '';

	$datum = createDateAsString($selectedYear, $selectedMonth, $day);
	$dayOfWeek = date("w", mktime(0,0,0,$selectedMonth, $day, $selectedYear));

	// if
	if ( $tdStyle == '' && $dayOfWeek != 0 && $dayOfWeek != 6 ) {
		for ($i = 0; $i < count($holidays); $i++) {
			if ( $datum == str_replace('-', '', $holidays[$i]->getDate()) ) {
				if ( strtolower($holidays[$i]->getDescription()) == 'bridgeday' ) {
					$tdStyle = class_colors::get('bridgeday')->getBackgroundColor();
					$hrefStyle = class_colors::get('bridgeday')->getFontColor();
				} else {
					$tdStyle = class_colors::get('fst')->getBackgroundColor();
					$hrefStyle = class_colors::get('fst')->getFontColor();
				}
				$alt = $holidays[$i]->getDescription();
			}
		}
	}

	// absences
	if ( $tdStyle == '' && $dayOfWeek != 0 && $dayOfWeek != 6 ) {
		for ($i = 0; $i < count($absences); $i++) {
			if ( $datum == $absences[$i]["date"] ) {
				//
				if ( !$oWebuser->hasInOutTimeAuthorisation() && !$oWebuser->isAdmin() && !$oWebuser->hasAuthorisationTabAbsences() && !$oWebuser->isHeadOfDepartment() && !$oWebuser->hasInOutTimeAuthorisation() ) {
					$tdStyle = 'background-color: #C62431;';
					$hrefStyle = 'color:white';
					$alt = 'Leave';
				} else {
					$tdStyle = class_colors::get(strtolower($absences[$i]["code"]))->getBackgroundColor();
					$hrefStyle = class_colors::get(strtolower($absences[$i]["code"]))->getFontColor();
					$alt = $absences[$i]["description"];
				}
			}
		}
	}

	if ( $tdStyle == '' ) {
		if ( $day == date("d") && $selectedMonth == date("m") && $selectedYear == date("Y") ) {
			// current day
			$tdStyle = class_colors::get(strtolower('today'))->getBackgroundColor();
			$hrefStyle = class_colors::get(strtolower('today'))->getFontColor();
		} elseif ( $dayOfWeek == 0 || $dayOfWeek == 6 ) {
			// weekend
			$tdStyle = class_colors::get(strtolower('weekend'))->getBackgroundColor();
			$hrefStyle = class_colors::get(strtolower('weekend'))->getFontColor();
		}
	}

	$style["tdStyle"] = $tdStyle;
	$style["hrefStyle"] = $hrefStyle;
	$style["alt"] = $alt;

	return $style;
}

function isHoliday($datum, $holidays) {
	for ($i = 0; $i < count($holidays); $i++) {
		if ( $datum == $holidays[$i]->getDate() ) {
			return true;
		}
	}

	return false;
}

function getNationalHolidays($year, $month) {
	global $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$arr = array();

    $query = "SELECT * FROM Staff_feestdagen WHERE datum LIKE '" . $year . '-' . substr("0" . $month,-2) . "-%' AND isdeleted=0 ";
   	$result = mysql_query($query, $oConn->getConnection());
    while ($row = mysql_fetch_assoc($result)) {
   		$arr[] = new Holiday($row["ID"]);
    }
   	mysql_free_result($result);

	return $arr;
}
