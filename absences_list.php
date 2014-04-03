<?php 
//
require_once "classes/start.inc.php";
require_once "classes/_db_connect_protime.inc.php";

$oWebuser->checkLoggedIn();

//
$s = getAndProtectSearch();

$retval = '';

$oEmployee = new class_employee($oWebuser->getUser(), $settings);
$favIds = implode(',', $oEmployee->getFavourites('vakantie'));

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
		$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02"), explode(' ', $s));
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

// TODOTODO: in absences.php moet url gedisabled worden als min/max bereikt is
if ( $selectedYear < date("Y") ) {
	$selectedYear = date("Y");
	$selectedMonth = 1;
} elseif ( $selectedYear > date("Y")+1 ) {
	$selectedYear = date("Y")+1;
	$selectedMonth = 12;
}

$daysInCurrentMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

$cellWidth = 18;

$arrHolidays = getNationalHolidays($selectedYear, $selectedMonth );

if ( $to_short != 1 ) {

	// loop employees
	$querySelect = "SELECT * FROM CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) " . $queryCriterium . " ORDER BY NAME, FIRSTNAME ";
	$resultSelect = mssql_query($querySelect, $dbhandleProtime);

	while ( $rowSelect = mssql_fetch_array($resultSelect) ) {
		$tmp = "
<TR>
	<TD><div id=\"divAddRemove" . $rowSelect["PERSNR"] . "\">::ADDREMOVE::</div></TD>
	<TD>" . trim($rowSelect["NAME"]) . ", " . trim($rowSelect["FIRSTNAME"]) . "</TD>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<TD></TD>
	<TD align=\"center\">::VAKANTIE::</TD>
</TR>
";

		//
		$status = getStatusColor($rowSelect["PERSNR"], date("Ymd"));

		//
		$tmp = str_replace('::STATUS_STYLE::', $status["status_color"], $tmp);

        if ( $oWebuser->hasInOutTimeAuthorisation() || $oWebuser->getProtimeId() == $rowSelect["PERSNR"] ) {
            $tmp = str_replace('::STATUS_TEXT::', $status["status_text"], $tmp);
            $tmp = str_replace('::STATUS_ALT::', $status["status_alt"], $tmp);
        } else {
            $tmp = str_replace('::STATUS_TEXT::', '', $tmp);
            $tmp = str_replace('::STATUS_ALT::', '', $tmp);
        }

		//
		if ( strpos(',' . $favIds . ',', ',' . $rowSelect["PERSNR"] . ',') !== false ) {
			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $rowSelect["PERSNR"] . ', \'r\');" alt="Stop following this person" title="Stop following this person" class="nolink"><img src="images/minus-sign.png" border=0></a>', $tmp);
		} else {
			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $rowSelect["PERSNR"] . ', \'a\');" alt="Start following this person" title="Start following this person" class="nolink"><img src="images/plus-sign.png" border=0></a>', $tmp);
		}

		$arrVakantie = getAbsencesAndHolidays($rowSelect["PERSNR"], $selectedYear, $selectedMonth, 120 );

		$vak = '';

		for ( $i = 1; $i <= $daysInCurrentMonth; $i++ ) {
			$style = "border: 1px solid white;";
			//
			$celValue = '&nbsp;&nbsp;';

			$cellStyle = getColors($selectedYear, $selectedMonth, $i, $arrVakantie, $arrHolidays);
			$style .= $cellStyle["tdStyle"];

			if ( $cellStyle["alt"] != '' ) {
				//
				$celValue = '<a title="' . $cellStyle["alt"] . '" style="' . $cellStyle["hrefStyle"] . '">' . $i . '</a>';
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
	mssql_free_result($resultSelect);

	// HEADERS
	$headerDays = '';
	for ( $i = 1; $i <= $daysInCurrentMonth; $i++ ) {
		$extrastyle = "width:" . $cellWidth ."px;border: 1px solid white;";

		$celValue = $i;

		$cellStyle = getColors($selectedYear, $selectedMonth, $i, array(), $arrHolidays);

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
	<TD width=180><font size=-1><b>Name</b></font></TD>
	<TD width=90 align=\"center\"><font size=-1><b>Today</b></font></TD>
	<TD width=18 align=\"center\"></TD>
	<TD style=\"width:" . $vakantieWidth . "px;\" align=\"center\"><font size=-1><b>Vakanties/afwezigheden " . date("F Y", mktime(0,0,0,$selectedMonth, 1, $selectedYear)) . "</b></font></TD>
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
echo $retval;

require_once "classes/_db_disconnect.inc.php";

function calculateNrOfDaysForMonth($year, $month) {
	return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

function getColors($selectedYear, $selectedMonth, $day, $absences = array(), $holidays = array()) {
	$tdStyle = '';
	$hrefStyle = '';
	$alt = '';

	$datum = createDateAsString($selectedYear, $selectedMonth, $day);
	$dayOfWeek = date("w", mktime(0,0,0,$selectedMonth, $day, $selectedYear));

	//
	if ( $tdStyle == '' && $dayOfWeek != 0 && $dayOfWeek != 6 ) {
		for ($i = 0; $i < count($holidays); $i++) {
			if ( $datum == $holidays[$i]->getDate() ) {
				if ( strtolower($holidays[$i]->getDescription()) == 'brugdag' ) {
					$tdStyle = getColor("td", "brugdag");
					$hrefStyle = getColor("href", "brugdag");
				} else {
					$tdStyle = getColor("td", "holiday");
					$hrefStyle = getColor("href", "holiday");
				}
				$alt = $holidays[$i]->getDescription();
			}
		}
	}

	// absences
	if ( $tdStyle == '' && $dayOfWeek != 0 && $dayOfWeek != 6 ) {
		for ($i = 0; $i < count($absences); $i++) {
			if ( $datum == $absences[$i]["date"] ) {
				$tdStyle = getColor("td", strtolower($absences[$i]["description"]));
				$hrefStyle = getColor("href", strtolower($absences[$i]["description"]));
				$alt = $absences[$i]["description"];
			}
		}
	}

	if ( $tdStyle == '' ) {
		if ( $day == date("d") && $selectedMonth == date("m") && $selectedYear == date("Y") ) {
			// current day
			$tdStyle = getColor("td", "vandaag");
			$hrefStyle = getColor("href", "vandaag");
		} elseif ( $dayOfWeek == 0 || $dayOfWeek == 6 ) {
			// weekend
			$tdStyle = getColor("td", "weekend");
			$hrefStyle = getColor("href", "weekend");
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
	global $dbhandleTimecard, $settings;

	$arr = array();

	$query = "SELECT * FROM Feestdagen WHERE datum LIKE '" . $year . substr("0" . $month,-2) . "%' AND isdeleted=0 ";
	$result = mssql_query($query, $dbhandleTimecard);
	while ($row = mssql_fetch_assoc($result)) {
		$arr[] = new class_holiday($row["ID"], $settings);
	}
	mssql_free_result($result);

	return $arr;
}
?>