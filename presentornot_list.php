<?php
$doPing = false;
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

echo createPresentOrNotList();

function createPresentOrNotList() {
	global $twig, $protect, $dbConn, $oWebuser, $dateOutCriterium;

	//
	$error = '';
	$s = getAndProtectSearch();
	$layout = trim($protect->request('get', "l"));
	if ($layout == '') {
		$layout = $oWebuser->getUserSetting('presentornot_display_format', 'tabular');
	}
	if (!in_array($layout, array('tabular', 'tile'))) {
		$layout = 'tabular';
	}

	// save setting
	$query_update = "INSERT INTO staff_user_settings (`user_id`, `setting`, `value`) VALUES (" . $oWebuser->getId() . ", 'presentornot_display_format', '$layout') ON DUPLICATE KEY UPDATE `value`='$layout' ";
	$stmt = $dbConn->getConnection()->prepare($query_update);
	$stmt->execute();

	//
	$favIds = implode(',', $oWebuser->getFavourites('present'));
	$checkInOutIds = implode(',', $oWebuser->getFavourites('checkinout'));

	// CRITERIUM
	$queryCriterium = '';
	if ($s == '-a-') {
		//
		$title = Translations::get('all_employees');
		$showCalendar = 0;
	} elseif ($s == '-r-') {
		//
		$title = Translations::get('absent_employes');
		$showCalendar = 0;
	} elseif ($s == '-g-') {
		//
		$title = Translations::get('present_employees');
		$showCalendar = 0;
	} elseif ($s == '') {
		// no search
		// use favourites
		$queryCriterium = ' AND protime_curric.PERSNR IN (' . $favIds . ') ';

		$title = Translations::get('your_favourites');
		$showCalendar = 1;
	} else {
		// search
		$searchArray = array("NAME", "FIRSTNAME", "EMAIL", "USER02", Settings::get('curric_room'), "SHORT_" . getLanguage());
		if ( $oWebuser->hasAuthorisationTabFire() ) {
			$searchArray[] = 'BADGENR';
		}
		$queryCriterium = Generate_Query($searchArray, explode(' ', $s));
		$title = 'Search: ' . $s;
		$showCalendar = 1;
	}

	//
	$querySelect = "
SELECT DISTINCT protime_curric.PERSNR, protime_curric.NAME, protime_curric.FIRSTNAME
FROM protime_curric
	LEFT JOIN staff_today_checkinout ON protime_curric.PERSNR = staff_today_checkinout.PERSNR AND  staff_today_checkinout.BOOKDATE = '" . date("Ymd") . "'
	LEFT JOIN protime_depart ON protime_curric.DEPART = protime_depart.DEPART
WHERE " . $dateOutCriterium . $queryCriterium . Misc::getNeverShowPersonsCriterium() . "
ORDER BY FIRSTNAME, NAME ";

//preprint( $querySelect );

	$totaal["aanwezig"] = 0;
	$totaal["afwezig"] = 0;

	$stmt = $dbConn->getConnection()->prepare($querySelect);
	$stmt->execute();
	$result = $stmt->fetchAll();

	$items = array();

	foreach ($result as $row) {
		$item = array();
		$oEmployee = new ProtimeUser($row["PERSNR"]);

		$item['id'] = $oEmployee->getId();
		$item['url'] = createUrl(array('url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname()));
		$item['url_photo'] = createUrl(array('url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getPhotoImg()));
		$item['telephone'] = Telephone::getTelephonesHref($oEmployee->getTelephones());
		$item['room'] = static_Room::createRoomUrl($oEmployee->getRoom());
		$item['lbl_telephone_short'] = Translations::get('lbl_telephone_short');
		$item['lbl_room_short'] = Translations::get('lbl_room_short');

		// show person is 'uit dienst' if date_out is in the past
		// only front desk and administrators
		$dateOutWarning = '';
		if (($oWebuser->isAdmin() || $oWebuser->hasAuthorisationTabFire()) && $oEmployee->getDateOut() != '0' && $oEmployee->getDateOut() < date("Y-m-d")) {
			if ($layout == 'tabular') {
				$separator = ' ';
			} else {
				$separator = '<br>';
			}
			$dateOutFormatted = class_datetime::formatDate($oEmployee->getDateOut());
			$dateOutWarning = '<a title="' . str_replace('::DATE::', $dateOutFormatted, Translations::get("warning_uit_dienst_explanation")) . '" class="blink">' . Translations::get("warning_uit_dienst") . '</a>' . $separator;
		}
		$item['uitdienst'] = $dateOutWarning;

		// PHOTO
		$photo = $oEmployee->getPhoto();
		$alttitle = '';
		if (checkPhotoExists(Settings::get('staff_images_directory') . $photo)) {
			$photo = Settings::get('staff_images_directory') . $photo;
		} else {
			if ($oWebuser->isAdmin()) {
				$alttitle = 'Missing photo: &quot;' . Settings::get('staff_images_directory') . $photo . '&quot;';
			}
			$photo = Settings::get('noimage_file');
		}
		$photo = "<img src=\"$photo\"  style=\"height:140px;\" title=\"$alttitle\">";
		$item['photo'] = $photo;

		//
		if (strpos(',' . $favIds . ',', ',' . $oEmployee->getId() . ',') !== false) {
			$alttitle = Translations::get('lbl_click_to_remove_from_favourites');
			$item['addremove'] = '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'r\');" title="' . $alttitle . '" class="nolink favourites_on">&#9733;</a>';
		} else {
			$alttitle = Translations::get('lbl_click_to_add_to_favourites');
			$item['addremove'] = '<a href="#" onClick="return addRemove(' . $oEmployee->getId() . ', \'a\');" title="' . $alttitle . '" class="nolink favourites_off">&#9733;</a>';
		}

		//
		if (strpos(',' . $checkInOutIds . ',', ',' . $oEmployee->getId() . ',') !== false) {
			$alttitle = Translations::get('lbl_click_to_not_get_email_notification');
			$item['checkinout'] = '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-red.png" border=0></a>';
		} else {
			$alttitle = Translations::get('lbl_click_to_get_email_notification');
			$item['checkinout'] = '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-black.png" border=0></a>';
		}

		//
		$arrHolidays = getNationalHolidays(date("Y"), date("m"));

		$oAbsenceCalendar = new AbsenceCalendar($oEmployee->getId());
		$arrAbsences = $oAbsenceCalendar->getAbsencesAndHolidaysWeek(date("Y"), date("m"), date("d"));

		$firstDayOfChosenWeek = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));
		$firstDayOfChosenWeek->modify('-' . (date("w") - 1) . ' day');
		$absenceCalendarWeek = AbsenceCalendarFormat::InWeekFormat($firstDayOfChosenWeek->format("Y"), $firstDayOfChosenWeek->format("m"), $firstDayOfChosenWeek->format('d'), $arrAbsences, $arrHolidays);
		$item['absenceCalendarWeek'] = $absenceCalendarWeek;

		//
		$item['showCalendar'] = ( $showCalendar ? 1 : 0 );

		// STATUS
		$status = getCurrentDayCheckInoutState($oEmployee->getId());

		if ($status["aanwezig"] == 1) {
			$totaal["aanwezig"]++;
		} else {
			$totaal["afwezig"]++;
		}


		if ($status["status_text"] == '') {
			$status["status_text"] = '&nbsp;';
		}

		$item['status_style'] = $status["status_color"];
		$item['status_text'] = $status["status_text"];
		$item['status_alt'] = $status["status_alt"];

		// moet regel getoond worden?
		if ($s == '-r-') {
			// als rood, dan alleen tonen als persoon niet aanwezig is
			if ($status["aanwezig"] == 0) {
				//
				$items[] = $item;
			}
		} elseif ($s == '-g-') {
			// als groen, dan alleen tonen als persoon aanwezig is
			if ($status["aanwezig"] == 1) {
				//
				$items[] = $item;
			}
		} else {
			// als niet rood en ook niet groen dan altijd tonen
			$items[] = $item;
		}
	}

	// error
	if (count($items) == 0) {
		if ($s != '') {
			$error = Translations::get('nothing_found');
		} else {
			$error = Translations::get('start_searching');
		}
	}

	//
	return $twig->render('presentornot_list_' . $layout . '.twig', array(
		'title' => $title
		, 'error' => $error
		, 'lbl_name' => Translations::get('lbl_name')
		, 'lbl_check_inout' => Translations::get('lbl_check_inout')
		, 'lbl_telephone' => Translations::get('lbl_telephone')
		, 'lbl_room' => Translations::get('lbl_room')
		, 'lbl_present' => Translations::get('lbl_present')
		, 'lbl_not_present' => Translations::get('lbl_not_present')
		, 'lbl_page_refreshes_every' => Translations::get('lbl_page_refreshes_every')
		, 'date' => date("H:i:s")
		, 'totaal_aanwezig' => $totaal["aanwezig"]
		, 'totaal_afwezig' => $totaal["afwezig"]
		, 'items' => $items
	));
}
