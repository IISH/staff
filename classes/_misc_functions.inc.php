<?php
function getLanguage() {
	$language = 1;

	if ( isset( $_SESSION['language'] ) && $_SESSION['language'] == 2 ) {
		$language = 2;
	}

	$_SESSION['language'] = $language;

	return $language;
}

function replaceDoubleTripleSpaces( $string ) {
	return preg_replace('!\s+!', ' ', $string);
}

function valueOr( $value, $or = '?' ) {
	return ( ( trim($value) != '' ) ? $value : $or );
}

function checkImageExists( $photo, $imageIfNotExists = '' ) {
	if ( !file_exists ( $_SERVER['DOCUMENT_ROOT'] . '/' . $photo ) ) {
		$photo = $imageIfNotExists;
	}

	return $photo;
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

function getCurrentDayCheckInoutState( $persnr ) {
	global $databases, $oWebuser;

	$date = date("Ymd");

	$retval = array();

	//
	$status_color = 'background-color:#C62431;color:white;';
	$status_text = '';
	$status_alt = '';

	$oProtime = new class_mysql($databases['default']);
	$oProtime->connect();

	// achterhaal 'present' status
	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM Staff_today_checkinout WHERE PERSNR=" . $persnr . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY REC_NR ";
	$result = mysql_query($query, $oProtime->getConnection());
	$status = 0;
	$found = 0;
	$aanwezig = 0;
	while ( $row = mysql_fetch_assoc($result) ) {
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
	mysql_free_result($result);

	$status_alt = trim($status_alt);

	// als status nog leeg
	// dan betekent dat dat de persoon vandaag nog niet ingeklokt heeft
	// misschien omdat de persoon op vakantie is
	if ( $status_text == '' && $found == 0 ) {
		$oProtime->connect();

		$prefix = Settings::get('protime_tables_prefix');

		$query = "
SELECT DISTINCT CODE, ${prefix}ABSENCE.SHORT_" . getLanguage() . "
FROM ${prefix}P_ABSENCE
	INNER JOIN ${prefix}ABSENCE ON ${prefix}P_ABSENCE.ABSENCE = ${prefix}ABSENCE.ABSENCE
WHERE ${prefix}P_ABSENCE.PERSNR = " . $persnr . " AND ${prefix}P_ABSENCE.BOOKDATE = '" . $date . "'
";

		$result = mysql_query($query, $oProtime->getConnection());
		$status_separator = '';
		while ( $row = mysql_fetch_assoc($result) ) {
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
		mysql_free_result($result);
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
	global $databases;
	$language = getLanguage();
	$min_minutes = 120;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$prefix = Settings::get('protime_tables_prefix');

	// SHORT_1 - dutch, SHORT_2 - english
	$query = "
SELECT CODE, ${prefix}P_ABSENCE.REC_NR, ${prefix}P_ABSENCE.PERSNR, ${prefix}P_ABSENCE.BOOKDATE, ${prefix}P_ABSENCE.ABSENCE_VALUE, ${prefix}P_ABSENCE.ABSENCE_STATUS, ${prefix}ABSENCE.SHORT_" . $language . ", ${prefix}P_ABSENCE.ABSENCE
FROM ${prefix}P_ABSENCE
	LEFT OUTER JOIN ${prefix}ABSENCE ON ${prefix}P_ABSENCE.ABSENCE = ${prefix}ABSENCE.ABSENCE
WHERE ${prefix}P_ABSENCE.PERSNR=" . $eid . " AND ${prefix}P_ABSENCE.BOOKDATE LIKE '" . $yearMonth . "%' AND ${prefix}P_ABSENCE.ABSENCE NOT IN (5, 19)
AND ( ${prefix}P_ABSENCE.ABSENCE_VALUE>=" . $min_minutes . " OR ${prefix}P_ABSENCE.ABSENCE_VALUE=0 )
ORDER BY ${prefix}P_ABSENCE.BOOKDATE, ${prefix}P_ABSENCE.REC_NR
";

	$oProtime = new class_mysql($databases['default']);
	$oProtime->connect();

	$result = mysql_query($query, $oProtime->getConnection());
	$num = mysql_num_rows($result);
	if ( $num ) {
		while ( $row = mysql_fetch_assoc($result) ) {
			// SHORT_1 - dutch, SHORT_2 - english
			$ret[] = array( 'code' => $row["CODE"], 'date' => $row["BOOKDATE"], 'description' => strtolower($row["SHORT_" . $language]) );
		}
	}

	mysql_free_result($result);

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
		if ( isset( $_GET["parentbackurl"] ) ) {
			$ret = $_GET["parentbackurl"];
		}
	}

	if ( $ret == '' ) {
		if ( isset( $_GET["backurl"] ) ) {
			$ret = $_GET["backurl"];
		}
	}

	if ( $ret == '' ) {
		$scriptNameStrippedEdit = str_replace('_edit', '', $_SERVER['SCRIPT_NAME']);
		if ( $_SERVER['SCRIPT_NAME'] != $scriptNameStrippedEdit ) {
			$ret = $scriptNameStrippedEdit;
		}
	}

	$ret = str_replace('<', ' ', $ret);
	$ret = str_replace('>', ' ', $ret);

	$ret = trim($ret);

	$ret = $protect->get_left_part($ret);

	return $ret;
}
