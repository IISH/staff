<?php
$doPing = false;
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAuthorisationTabAbsences() ) {
	die('Access denied.<br><a href="index.php">Go back</a>');
}

// show page
echo createAbsencesList();

function createAbsencesList() {
	global $dbConn, $oWebuser, $twig, $dateOutCriterium;

	//
	$headerDays = '';
	$users = array();

	//
	$s = getAndProtectSearch();

	//
	if ( !isset($_GET['m'])) {
		$_GET['m'] = '';
	}
	if ( !isset($_GET['y'])) {
		$_GET['y'] = '';
	}

	//
	$favIds = implode(',', $oWebuser->getFavourites('present'));

	// CRITERIUM
	$to_short = 0;
	if ( $s == '' ) {
		// no search
		// use favourites
		$queryCriterium = ' AND ' . Settings::get('protime_tables_prefix') . 'curric.PERSNR IN (' . $favIds . ') ';
	} else {
		$to_short = strlen(str_replace(' ', '', $s)) < 2;
		if ( $to_short == 1 ) {
			// search nothing
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

	$arrHolidays = getNationalHolidays($selectedYear, $selectedMonth );

	$daysInCurrentMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
	$cellWidth = 23;
	$vakantieWidth = $daysInCurrentMonth * $cellWidth+($daysInCurrentMonth*2);
	$error = '';

	if ( $to_short != 1 ) {

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

			//
			$status = getCurrentDayCheckInoutState($oEmployee->getId());

			$tmp = array();
			$tmp['employeeId'] = $oEmployee->getId();
			$tmp['url'] = createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname() ) );
			$tmp['status_style'] = $status["status_color"];
			$tmp['status_alt'] = $status["status_alt"];
			$tmp['status_text'] = $status["status_text"];

			//
			if ( strpos(',' . $favIds . ',', ',' . $oEmployee->getId() . ',') !== false ) {
				$tmp['addremove'] = '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'r\');" title="' . Translations::get('lbl_click_to_remove_from_favourites') . '" class="nolink favourites_on">&#9733;</a>';
			} else {
				$tmp['addremove'] = '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'a\');" title="' . Translations::get('lbl_click_to_add_to_favourites') . '" class="nolink favourites_off">&#9733;</a>';
			}

			//
			$oAbsenceCalendar = new AbsenceCalendar($oEmployee->getId());
			$arrVakantie = $oAbsenceCalendar->getAbsencesAndHolidaysMonth($selectedYear, $selectedMonth );

			$vak = AbsenceCalendarFormat::inMonthListFormat($selectedYear, $selectedMonth, $arrVakantie, $arrHolidays );

			$tmp['vakantie'] = $vak;

			//
			$users[] = $tmp;
		}

		// HEADERS
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


		if ( count($users) == 0 ) {
			if ( $s != '' ) {
				$error = '<span class="error">' . Translations::get('nothing_found') . '</span>';
			} else {
				$error = Translations::get('start_searching');
			}
		}
	}

	return $twig->render('absences_list.twig', array(
		'title' => Translations::get('contact')
		, 'users' => $users
		, 'error' => $error
		, 'vakantieWidth' => $vakantieWidth
		, 'lbl_name' => Translations::get('lbl_name')
		, 'lbl_today' => Translations::get('lbl_today')
		, 'lbl_holidayabsences' => Translations::get('lbl_holidayabsences')
		, 'selectedMonth' => Translations::get('month' . ($selectedMonth+0))
		, 'selectedYear' => $selectedYear
		, 'headerDays' => $headerDays
	));
}
