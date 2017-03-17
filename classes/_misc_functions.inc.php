<?php
function getLanguage() {
	global $oWebuser;

	// TODO: move default language to settings table
	// 1 - dutch
	// 2 - english
	$language = $oWebuser->getUserSetting('language', 1);

	return $language;
}

function replaceDoubleTripleSpaces( $string ) {
	return preg_replace('!\s+!', ' ', $string);
}

function valueOr( $value, $or = '?' ) {
	return ( ( trim($value) != '' ) ? $value : $or );
}

function checkPhotoExists( $photo ) {
	if ( !file_exists ( $_SERVER['DOCUMENT_ROOT'] . '/' . $photo ) ) {
		error_log("Error: Image does not exist: " . $_SERVER['DOCUMENT_ROOT'] . '/' . $photo);
        return false;
	}

	return true;
}

function createUrl( $parts ) {
	$ret = "<a href=\"". $parts['url'] . "\">" . $parts["label"] . "</a>";

	return $ret;
}

function splitStringIntoArray( $text, $pattern = "/[^a-zA-Z0-9]/" ) {
	return preg_split($pattern, $text);
}

function stripDomainnameFromUrl( $url ) {
	$arr = parse_url( $url );

	$ret = $arr['path'];
	if ( isset( $arr['query'] ) && $arr['query'] != '' ) {
		$ret .= '?' . $arr['query'];
	}

	return $ret;
}

function goBack() {
	$url = 'presentornot.php';

	$referer = ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
	if ( $referer != '' ) {
		$url = $referer;
		$url = stripDomainnameFromUrl( $url );
	}

	Header("Location: " . $url);
}

function getListOfIdsOfCheckedInEmployees() {
	global $dbConn;

	$ret = array();

	$date = date("Ymd");

	$query = "SELECT PERSNR, COUNT(*) FROM staff_today_checkinout WHERE BOOKDATE='$date' AND BOOKTIME<>9999 GROUP BY PERSNR HAVING COUNT(*) % 2  > 0";
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$ret[] = $row['PERSNR'];
	}

	return $ret;
}

function getCurrentDayCheckInoutState( $persnr ) {
	global $oWebuser, $dbConn;

	$date = date("Ymd");

	$retval = array();

	//
	$status_color = 'background-color:#C62431;color:white;';
	$status_text = '';
	$status_alt = '';

	$status = 0;
	$found = 0;
	$aanwezig = 0;

	// achterhaal 'present' status
	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM staff_today_checkinout WHERE PERSNR=" . $persnr . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY REC_NR ";
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$found = 1;
		$status++;

		if ( $status == 1 ) {
			// green cell
			$status_color = 'background-color:green;color:white;';
			$aanwezig = 1;
			// check if user has inout_time rights
			if ( $oWebuser->isAdmin() || $oWebuser->hasInOutTimeAuthorisation() || ( $oWebuser->isHeadOfDepartment() && $oWebuser->isHeadOfEmployee($persnr) ) || $oWebuser->getId() == $persnr ) {
				$status_text = Translations::get('in') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
				$status_alt .= Translations::get('in') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
			} else {
				$status_text = Translations::get('in');
			}
		} else {
			// red cell
			$status_color = 'background-color:#C62431;color:white;';
			$aanwezig = 0;
			// check if user has inout_time rights
			if ( $oWebuser->isAdmin() || $oWebuser->hasInOutTimeAuthorisation() || ( $oWebuser->isHeadOfDepartment() && $oWebuser->isHeadOfEmployee($persnr) ) || $oWebuser->getId() == $persnr ) {
				$status_text = Translations::get('out') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
				$status_alt .= ' - ' . Translations::get('out') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]) . "\n";
			} else {
				$status_text = Translations::get('out');
			}
		}

		$status = $status % 2;
	}

	$status_alt = trim($status_alt);

	// als status nog leeg
	// dan betekent dat dat de persoon vandaag nog niet ingeklokt heeft
	// misschien omdat de persoon op vakantie is
	if ( $status_text == '' && $found == 0 ) {
		$prefix = Settings::get('protime_tables_prefix');

		$query = "
SELECT DISTINCT CODE, ${prefix}absence.SHORT_" . getLanguage() . "
FROM ${prefix}p_absence
	INNER JOIN ${prefix}absence ON ${prefix}p_absence.ABSENCE = ${prefix}absence.ABSENCE
WHERE ${prefix}p_absence.PERSNR = " . $persnr . " AND ${prefix}p_absence.BOOKDATE = '" . $date . "'
";

		$status_separator = '';
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$reasonAbsence = $row["SHORT_" . getLanguage()];
			$codeAbsence = strtolower($row["CODE"]);

			// check if user has the right to see the reason of absence
			if ( !$oWebuser->hasAuthorisationReasonOfAbsenceAll() ) {
				// no, user has no rights

				// is the absence allowed to be seen
				if ( !ForEveryoneVisibleAbsences::in_array($codeAbsence) ) {
					// no, use default absence
					$reasonAbsence = Translations::get('default_absent_value');
				}
			}
			$status_text .= $status_separator . $reasonAbsence;
			$status_separator = ', ';
		}
	}

	$retval["aanwezig"] = $aanwezig;
	$retval["status_text"] = strtolower($status_text);
	$retval["status_color"] = $status_color;
	$retval["status_alt"] = strtolower($status_alt);

	return $retval;
}

function fillTemplate($template, $data) {
	foreach ( $data as $a => $b ) {
		$template = str_replace('{' . $a . '}', $b, $template);
	}

	return $template;
}

function getAndProtectSearch($field = 's') {
	$s = '';

	if ( isset($_GET[$field]) ) {
		$s = $_GET[$field];
		$s = str_replace(array('?', "~", "`", "#", "$", "%", "^", "'", "\"", "(", ")", "<", ">", ":", ";", "*", "\n"), ' ', $s);
		while ( strpos($s, '  ') !== false ) {
			$s = str_replace('  ',' ', $s);
		}

		$s = trim($s);
		$s = substr($s, 0, 20);
	}

	return $s;
}

function getAbsencesAndHolidays($eid, $year, $month ) {
	global $databases, $dbConn;
	$language = getLanguage();
	$min_minutes = 120;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$prefix = Settings::get('protime_tables_prefix');

	// SHORT_1 - dutch, SHORT_2 - english
	$query = "
SELECT CODE, ${prefix}p_absence.REC_NR, ${prefix}p_absence.PERSNR, ${prefix}p_absence.BOOKDATE, ${prefix}p_absence.ABSENCE_VALUE, ${prefix}p_absence.ABSENCE_STATUS, ${prefix}absence.SHORT_" . $language . ", ${prefix}p_absence.ABSENCE
FROM ${prefix}p_absence
	LEFT OUTER JOIN ${prefix}absence ON ${prefix}p_absence.ABSENCE = ${prefix}absence.ABSENCE
WHERE ${prefix}p_absence.PERSNR=" . $eid . " AND ${prefix}p_absence.BOOKDATE LIKE '" . $yearMonth . "%' AND ${prefix}p_absence.ABSENCE NOT IN (5, 19)
AND ( ${prefix}p_absence.ABSENCE_VALUE>=" . $min_minutes . " OR ${prefix}p_absence.ABSENCE_VALUE=0 )
ORDER BY ${prefix}p_absence.BOOKDATE, ${prefix}p_absence.REC_NR
";

	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		// SHORT_1 - dutch, SHORT_2 - english
		$ret[] = array( 'code' => $row["CODE"], 'date' => $row["BOOKDATE"], 'description' => strtolower($row["SHORT_" . $language]) );
	}

	return $ret;
}

function Generate_Query($arrField, $arrSearch) {
	$retval = '';
	$separatorBetweenValues = '';

	foreach ( $arrSearch as $value ) {
		$separatorBetweenFields = '';
		$retval .= $separatorBetweenValues . " ( ";
		foreach ( $arrField as $field) {
			if ( trim($field) != '' ) {
				$retval .= $separatorBetweenFields . $field . " LIKE '%" . $value . "%' ";
				$separatorBetweenFields = " OR ";
			}
		}
		$retval .= " ) ";
		$separatorBetweenValues = " AND ";
	}

	if ( $retval != '' ) {
		$retval = " AND " . $retval;
	}

	return $retval;
}

function createDateAsString($year, $month, $day = '') {
	$ret = $year;

	$ret .= substr('0' . $month, -2);

	if ( $day != '' ) {
		$ret .= substr('0' . $day, -2);
	}

	return $ret;
}

function getBackUrl() {
	global $protect;

	$ret = '';

	if ( $ret == '' ) {
		if ( isset( $_GET["backurl"] ) ) {
			$ret = $_GET["backurl"];
		}
	}

    if ( $ret == '' ) {
        if ( isset( $_GET["burl"] ) ) {
            $ret = $_GET["burl"];
        }
    }

    if ( $ret == '' ) {
		$scriptNameStrippedEdit = str_replace('_edit', '', $_SERVER['SCRIPT_NAME']);
		if ( $_SERVER['SCRIPT_NAME'] != $scriptNameStrippedEdit ) {
			$ret = $scriptNameStrippedEdit;
		}
	}

    // simple javascript protection
	$ret = str_replace('<', ' ', $ret);
	$ret = str_replace('>', ' ', $ret);

	$ret = trim($ret);

	$ret = $protect->get_left_part($ret);

	return $ret;
}
