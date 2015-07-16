<?php
function getLanguage() {
	$language = 1;

	if ( isset( $_SESSION['language'] ) && $_SESSION['language'] == 2 ) {
		$language = 2;
	}

	$_SESSION['language'] = $language;

	return $language;
}

function fixPhotoCharacters( $photo ) {
	$photo =  iconv('Windows-1252', 'ASCII//TRANSLIT//IGNORE', $photo);
	$photo = str_replace('`', '', $photo);
	$photo = str_replace('"', '', $photo);
	return $photo;
}

function removeJobFunctionFromName( $string ) {
	$string = str_ireplace('(vrijwilliger)', '', $string);
	$string = str_ireplace('(vrijwillig)', '', $string);
	$string = str_ireplace('(stz)', '', $string);

	return $string;
}

function replaceDoubleTripleSpaces( $string ) {
	return preg_replace('!\s+!', ' ', $string);
}

function valueOr( $value, $or = '?' ) {
	return ( ( trim($value) != '' ) ? $value : $or );
}

function checkImageExists( $photo, $imageIfNotExists = '' ) {
	error_log("\nDEBUG: " . "1. server document root: " . $_SERVER['DOCUMENT_ROOT']);
	error_log("\nDEBUG: " . "2. path_public_html: " . class_settings::get('path_public_html'));
	error_log("\nDEBUG: " . "3. photo: " . $photo);
	error_log("\nDEBUG: " . "4. noimage: " . $imageIfNotExists);
	error_log("\nDEBUG: " . "5. : " . $_SERVER['DOCUMENT_ROOT'] . '/' . $photo);
	error_log("\nDEBUG: " . "6. : " . class_settings::get('path_public_html') . $photo);

	if ( !file_exists ( class_settings::get('path_public_html') . $photo ) ) {
//	if ( !file_exists ( $_SERVER['DOCUMENT_ROOT'] . '/' . $photo ) ) {
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

function getReferer() {
	$url = '';

	if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
		$url = $_SERVER['HTTP_REFERER'];
	}

	return $url;
}

function goBack() {
	$url = 'presentornot.php';

	$referer = getReferer();
	if ( $referer != '' ) {
		$url = $referer;
		$url = stripDomainnameFromUrl( $url );
	}

	Header("Location: " . $url);
}

// TODOEXPLAIN
function getStatusColor( $persnr, $date ) {
	global $databases, $oWebuser;

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
			$status_alt .= class_translations::get('in') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
			$aanwezig = 1;
			// check if user has inout_tme rights
			// TODO: check if headOfDepartment and if so, check if current user, one of his subemployees is
			//
			if ( $oWebuser->hasInOutTimeAuthorisation() || $oWebuser->isAdmin() || $oWebuser->isHeadOfDepartment() || $oWebuser->getId() == $persnr ) {
				$status_text = class_translations::get('in') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
			} else {
				$status_text = class_translations::get('in');
			}
		} else {
			// red cell
			$status_color = 'background-color:#C62431;color:white;';
			$status_alt .= ' - ' . class_translations::get('out') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]) . "\n";
			$aanwezig = 0;
			// check if user has inout_tme rights
			// TODO: check if headOfDepartment and if so, check if current user, one of his subemployees is
			if ( $oWebuser->hasInOutTimeAuthorisation() || $oWebuser->isAdmin() || $oWebuser->isHeadOfDepartment() || $oWebuser->getId() == $persnr ) {
				$status_text = class_translations::get('out') . ': ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
			} else {
				$status_text = class_translations::get('out');
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

		$prefix = class_settings::get('protime_tables_prefix');

		// SHORT_1 - dutch, SHORT_2 - english
		$query = "
SELECT DISTINCT ${prefix}ABSENCE.SHORT_" . getLanguage() . "
FROM ${prefix}P_ABSENCE
	INNER JOIN ${prefix}ABSENCE ON ${prefix}P_ABSENCE.ABSENCE = ${prefix}ABSENCE.ABSENCE
WHERE ${prefix}P_ABSENCE.PERSNR = " . $persnr . " AND ${prefix}P_ABSENCE.BOOKDATE = '" . $date . "'
";
		$result = mysql_query($query, $oProtime->getConnection());
		$status_separator = '';
		while ( $row = mysql_fetch_assoc($result) ) {
			// SHORT_1 - dutch, SHORT_2 - english
// TODO: controleer of persoon wel de status mag zien
			$reasonAbsence = $row["SHORT_" . getLanguage()];
			if ( !$oWebuser->hasAuthorisationReasonAbsence() ) {
				if ( !class_allowed_visible_absences::in_array($reasonAbsence) ) {
					$reasonAbsence = class_translations::get('default_absent_value');
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

// TODOEXPLAIN
function getColor( $value, $value2 ) {
	global $colors;

	$value = trim(strtolower($value));
	$value2 = trim(strtolower($value2));

	if ( !isset( $colors[$value][$value2] ) ) {
		$ret = $colors[$value]["no color defined"];
	} else {
		$ret = $colors[$value][$value2];
	}

	return $ret;
}

// TODOEXPLAIN
function fillTemplate($template, $data) {
	foreach ( $data as $a => $b ) {
		$template = str_replace('{' . $a . '}', $b, $template);
	}

	return $template;
}

// TODOEXPLAIN
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

// TODOEXPLAIN
function getAbsencesAndHolidays($eid, $year, $month, $min_minutes = 0) {
	global $databases;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$prefix = class_settings::get('protime_tables_prefix');

	// SHORT_1 - dutch, SHORT_2 - english
	$query = "
SELECT ${prefix}P_ABSENCE.REC_NR, ${prefix}P_ABSENCE.PERSNR, ${prefix}P_ABSENCE.BOOKDATE, ${prefix}P_ABSENCE.ABSENCE_VALUE, ${prefix}P_ABSENCE.ABSENCE_STATUS, ${prefix}ABSENCE.SHORT_2, ${prefix}P_ABSENCE.ABSENCE
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
			$ret[] = array( 'date' => $row["BOOKDATE"], 'description' => $row["SHORT_2"] );
		}
	}

	mysql_free_result($result);

	return $ret;
}

//TODOEXPLAIN
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

// TODOEXPLAIN
function createDateAsString($year, $month, $day = '') {
	$ret = $year;

	$ret .= substr('0' . $month, -2);

	if ( $day != '' ) {
		$ret .= substr('0' . $day, -2);
	}

	return $ret;
}

// TODOEXPLAIN
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

// TODOEXPLAIN
function debug($text = "", $extra = '') {
	echo "<span style=\"color:red;\">";
	if ( is_array($text) ) {
		echo "<pre>" . date("H:i:s ") . $extra;
		print_r($text);
		echo " +</pre><br>";
	} else {
		echo date("H:i:s ") . $extra . $text . " +<br>";
	}
	echo "</span>";
}

// TODOEXPLAIN
function cleanUpTelephone($telephone) {
	$retval = $telephone;

	// remove some dirty data from telephone
	$retval = str_replace(array("/", "(", ")", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"), ' ', $retval);

	//
	while ( strpos($retval, '  ') !== false ) {
		$retval = str_replace('  ',' ', $retval);
	}

	//
	$retval = trim($retval);

	return $retval;
}

// TODOEXPLAIN
function cleanUpVerdieping($verdieping) {
	$retval = strtolower($verdieping);

	// remove some dirty data from telephone
	$retval = str_replace(array('bhv', 'ehbo', '+', 'e', 'o'), '', $retval);

	//
	$retval = trim($retval);

	return $retval;
}

// TODOEXPLAIN
function fixBrokenChars($text) {
	return htmlentities($text, ENT_COMPAT | ENT_XHTML, 'ISO-8859-1', true);
}

// TODOEXPLAIN
function verplaatsTussenvoegselNaarBegin( $text ) {
	$array = array( ' van den', ' van der', ' van', ' de', ' el' );

	foreach ( $array as $t ) {
		if ( strtolower(substr($text, -strlen($t))) == strtolower($t) ) {
			$text = trim($t . ' ' . substr($text, 0, strlen($text)-strlen($t)));
		}
	}

	return $text;
}
