<?php 
// TODOEXPLAIN
function getStatusColor( $persnr, $date ) {
	global $settings;

	$retval = array();

	//
	$status_color = 'background-color:#C62431;color:white;';
	$status_text = '';
	$status_alt = '';

	$oProtime = new class_mysql($settings, 'presentornot');
	$oProtime->connect();

	// achterhaal 'present' status
	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM SPEC_PROTIME_BOOKINGS_CURRENTDAY WHERE PERSNR=" . $persnr . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY REC_NR ";
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
			$status_alt .= 'In: ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
			$aanwezig = 1;
		} else {
			// red cell
			$status_color = 'background-color:#C62431;color:white;';
			$status_alt .= ' - Out: ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]) . "\n";
			$aanwezig = 0;
		}
		$status_text = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);

		$status = $status % 2;
	}
	mysql_free_result($result);

	$status_alt = trim($status_alt);

	// als status nog leeg
	// dan betekent dat dat de persoon vandaag nog niet ingeklokt heeft
	// misschien omdat de persoon op vakantie is
	if ( $status_text == '' && $found == 0 ) {
		$oProtime->connect();

		$query = "
SELECT PROTIME_ABSENCE.SHORT_1
FROM PROTIME_P_ABSENCE
	INNER JOIN PROTIME_ABSENCE ON PROTIME_P_ABSENCE.ABSENCE = PROTIME_ABSENCE.ABSENCE
WHERE PROTIME_P_ABSENCE.PERSNR = " . $persnr . " AND PROTIME_P_ABSENCE.BOOKDATE = '" . $date . "'
";
		$result = mysql_query($query, $oProtime->getConnection());
		$status_separator = '';
		while ( $row = mysql_fetch_assoc($result) ) {
			$status_text .= $status_separator . $row["SHORT_1"];
			$status_separator = ', ';
		}
		mysql_free_result($result);
	}

	$retval["aanwezig"] = $aanwezig;
	$retval["status_text"] = $status_text;
	$retval["status_color"] = $status_color;
	$retval["status_alt"] = $status_alt;

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
	global $settings;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$query = "
SELECT PROTIME_P_ABSENCE.REC_NR, PROTIME_P_ABSENCE.PERSNR, PROTIME_P_ABSENCE.BOOKDATE, PROTIME_P_ABSENCE.ABSENCE_VALUE, PROTIME_P_ABSENCE.ABSENCE_STATUS, PROTIME_ABSENCE.SHORT_1, PROTIME_P_ABSENCE.ABSENCE
FROM PROTIME_P_ABSENCE
	LEFT OUTER JOIN PROTIME_ABSENCE ON PROTIME_P_ABSENCE.ABSENCE = PROTIME_ABSENCE.ABSENCE
WHERE PROTIME_P_ABSENCE.PERSNR=" . $eid . " AND PROTIME_P_ABSENCE.BOOKDATE LIKE '" . $yearMonth . "%' AND PROTIME_P_ABSENCE.ABSENCE NOT IN (5, 19)
AND ( PROTIME_P_ABSENCE.ABSENCE_VALUE>=" . $min_minutes . " OR PROTIME_P_ABSENCE.ABSENCE_VALUE=0 )
ORDER BY PROTIME_P_ABSENCE.BOOKDATE, PROTIME_P_ABSENCE.REC_NR
";

	$oProtime = new class_mysql($settings, 'presentornot');
	$oProtime->connect();

	$result = mysql_query($query, $oProtime->getConnection());
	$num = mysql_num_rows($result);
	if ( $num ) {
		while ( $row = mysql_fetch_assoc($result) ) {
			$ret[] = array( 'date' => $row["BOOKDATE"], 'description' => $row["SHORT_1"] );
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
			$retval .= $separatorBetweenFields . $field . " LIKE '%" . $value . "%' ";
			$separatorBetweenFields = " OR ";
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
	echo "<font color=red>";
	if ( is_array($text) ) {
		echo "<pre>" . date("H:i:s ") . $extra;
		print_r($text);
		echo " +</pre><br>";
	} else {
		echo date("H:i:s ") . $extra . $text . " +<br>";
	}
	echo "</font>";
}

// TODOEXPLAIN
function cleanUpTelephone($telephone) {
	$retval = $telephone;

	// remove some dirty data from telephone
	$retval = str_replace(array("(", ")", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"), ' ', $retval);

	//
	while ( strpos($retval, '  ') !== false ) {
		$retval = str_replace('  ',' ', $retval);
	}

	//
	$retval = trim($retval);

	return $retval;
}

// TODOEXPLAIN
function fixBrokenChars($text) {
	return htmlentities($text, ENT_COMPAT | ENT_XHTML, 'ISO-8859-1', true);
}
