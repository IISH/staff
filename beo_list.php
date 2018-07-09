<?php 
//
$doPing = false;
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

//
$oBeo = new Beo((isset($type_of_beo) ? $type_of_beo : ''), $label);

$oWebuser->checkLoggedIn();

// show page
echo createBeoListContent();

function createBeoListContent() {
	global $twig, $oWebuser, $type_of_beo, $protect, $dbConn, $dateOutCriterium, $oBeo;

//	if ( $type_of_beo == 'b' && $oWebuser->isBhv() ) {
		if ( !isset($_GET["m"]) ) {
			$selectedMonth = date("m");
		} else {
			$selectedMonth = trim(substr($_GET["m"],0,2));
		}
		if ( $selectedMonth == '' ) {
			$selectedMonth = date("m");
		}

		if ( !isset($_GET["y"]) ) {
			$selectedYear = date("Y");
		} else {
			$selectedYear = trim(substr($_GET["y"], 0, 4));
		}
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
//	}

	//
	$layout = trim($protect->request('get', "l"));
	if ($layout == '') {
		$layout = $oWebuser->getUserSetting($type_of_beo . '_display_format', 'tabular');
	}
	if (!in_array($layout, array('tabular', 'tile'))) {
		$layout = 'tabular';
	}
	// save setting
	$query_update = "INSERT INTO staff_user_settings (`user_id`, `setting`, `value`) VALUES (" . $oWebuser->getId() . ", '" . $type_of_beo . "_display_format', '$layout') ON DUPLICATE KEY UPDATE `value`='$layout' ";
	$stmt = $dbConn->getConnection()->prepare($query_update);
	$stmt->execute();

	//
	$checkInOutIds = implode(',', $oWebuser->getFavourites('checkinout'));

	//
	$totaal["aanwezig"] = 0;
	$totaal["afwezig"] = 0;

	$ontruimersAanwezigOpVerdieping = array();
	$nrOfLevels = Settings::get("number_of_levels");
	if ($nrOfLevels == '') {
		$nrOfLevels = 6;
	}
	for ($i = 0; $i <= $nrOfLevels; $i++) {
		$ontruimersAanwezigOpVerdieping[$i] = 0;
	}

	//
	$querySelect = "
	SELECT DISTINCT protime_curric.PERSNR, protime_curric.FIRSTNAME, protime_curric.NAME
	FROM protime_curric
		LEFT JOIN staff_today_checkinout ON protime_curric.PERSNR = staff_today_checkinout.PERSNR AND  staff_today_checkinout.BOOKDATE = '" . date("Ymd") . "'
	WHERE " . $dateOutCriterium . "
		AND " . $oBeo->getQuery() . Misc::getNeverShowPersonsCriterium() . "
	ORDER BY " . $oBeo->getExtraOrderBy() . "protime_curric.FIRSTNAME, protime_curric.NAME ";

	//echo $querySelect;

	$showFloors = 0;
	if ($oBeo->getShowFloor()) {
		$showFloors = 1;
	}
	$showTelephone = 0;
	if ($oWebuser->hasAuthorisationBeoTelephone()) {
		$showTelephone = 1;
	}

	//
	$stmt = $dbConn->getConnection()->prepare($querySelect);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$item = array();

		$oEmployee = new ProtimeUser($row["PERSNR"]);

		$item['id'] = $oEmployee->getId();
		$item['url'] = createUrl(array('url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname()));
		$item['verdieping'] = $oEmployee->getOntruimerVerdieping();
		$item['telephone'] = Telephone::getTelephonesHref($oEmployee->getTelephones());

		//
		if (strpos(',' . $checkInOutIds . ',', ',' . $oEmployee->getId() . ',') !== false) {
			$alttitle = Translations::get('lbl_click_to_not_get_email_notification');
			$item['checkinout'] = '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-red.png" border=0></a>';
		} else {
			$alttitle = Translations::get('lbl_click_to_get_email_notification');
			$item['checkinout'] = '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-black.png" border=0></a>';
		}

		//
		$status = getCurrentDayCheckInoutState($oEmployee->getId());

		if ($status["aanwezig"] == 1) {
			$totaal["aanwezig"]++;
			$ontruimersAanwezigOpVerdieping[$oEmployee->getOntruimerVerdieping()] = 1;
		} else {
			$totaal["afwezig"]++;
		}

		//
		$item['status_style'] = $status["status_color"];
		$item['status_text'] = $status["status_text"];
		$item['status_alt'] = $status["status_alt"];

		//
		if ( $type_of_beo == 'b' && $oWebuser->isBhv() ) {
			$oAbsenceCalendar = new AbsenceCalendar($oEmployee->getId());
			$arrVakantie = $oAbsenceCalendar->getAbsencesAndHolidaysMonth($selectedYear, $selectedMonth);

			$vak = AbsenceCalendarFormat::inMonthListFormat($selectedYear, $selectedMonth, $arrVakantie, $arrHolidays);

			$item['vakantie'] = $vak;
		}

		$items[] = $item;

		// als niet rood en ook niet groen dan altijd tonen
	}

	// HEADERS
	$headerDays = '';
	if ( $type_of_beo == 'b' && $oWebuser->isBhv() ) {
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
	}

	//

	// FLOORS
	$floors = '';
	if ($oBeo->getShowFloor()) {
		$showFloors = 1;

		for ($i = 0; $i <= $nrOfLevels; $i++) {
			if ($ontruimersAanwezigOpVerdieping[$i] == 1) {
				$style = " background-color:green;color:white; ";
			} else {
				$style = " background-color:#C62431;color:white; ";
			}
			$floors .= "<td align=\"center\" style=\"" . $style . "width:22px;\">" . $i . "</td>";
		}
	}

	if ( $type_of_beo == 'b' ) {
		$showBhvComment = 1;
	} else {
		$showBhvComment = 0;
	}

	//
	if ( $type_of_beo == 'b' && $oWebuser->isBhv() ) {
		$twigTemplate = 'beo_list_vakantie.twig';
	} else {
		$twigTemplate = 'beo_list.twig';
	}
	return $twig->render($twigTemplate, array(
		'title' => $oBeo->getLabel()
		, 'in_case_of_emergency_call' => Translations::get('in_case_of_emergency_call')
		, 'emergency_number' => Settings::get('emergency_number')
		, 'lbl_present' => Translations::get('lbl_present')
		, 'lbl_not_present' => Translations::get('lbl_not_present')
		, 'totaal_aanwezig' => $totaal["aanwezig"]
		, 'totaal_afwezig' => $totaal["afwezig"]
		, 'lbl_page_refreshes_every' => Translations::get('lbl_page_refreshes_every')
		, 'date' => date("H:i:s")
		, 'showBhvComment' => $showBhvComment
		, 'showFloors' => $showFloors
		, 'showTelephone' => $showTelephone
		, 'floors' => $floors
		, 'lbl_level' => Translations::get('lbl_level')
		, 'lbl_name' => Translations::get('lbl_name')
		, 'lbl_check_inout' => Translations::get('lbl_check_inout')
		, 'lbl_telephone' => Translations::get('lbl_telephone')
		, 'items' => $items
		, 'vakantieWidth' => $vakantieWidth
		, 'lbl_today' => Translations::get('lbl_today')
		, 'lbl_holidayabsences' => Translations::get('lbl_holidayabsences')
		, 'selectedMonth' => Translations::get('month' . ($selectedMonth+0))
		, 'selectedYear' => $selectedYear
		, 'headerDays' => $headerDays
		, 'isBhv' => ( $type_of_beo == 'b' && $oWebuser->isBhv() ) ? 1 : 0
	));
}
