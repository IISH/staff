<?php 
// TODOEXPLAIN
function getStatusColor( $persnr, $date ) {
	global $settings;

	$retval = array();

	//
	$status_color = 'background-color:#C62431;color:white;';
	$status_text = '';
	$status_alt = '';

	// TODOXXX
	$oProtime = new class_mssql($settings, 'protime');
	$oProtime->connect();

	// achterhaal 'present' status
	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM BOOKINGS WHERE PERSNR=" . $persnr . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY REC_NR ";
	$result = mssql_query($query, $oProtime->getConnection());
	$status = 0;
	$found = 0;
	$aanwezig = 0;
	while ( $row = mssql_fetch_array($result) ) {
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
	mssql_free_result($result);

	$status_alt = trim($status_alt);

	// als status nog leeg
	// dan betekent dat dat de persoon vandaag nog niet ingeklokt heeft
	// misschien omdat de persoon op vakantie is
	if ( $status_text == '' && $found == 0 ) {
		$oProtime->connect();

		$query = "SELECT ABSENCE.SHORT_1 FROM P_ABSENCE INNER JOIN ABSENCE ON P_ABSENCE.ABSENCE = ABSENCE.ABSENCE WHERE (P_ABSENCE.PERSNR = " . $persnr . ") AND (P_ABSENCE.BOOKDATE = '" . $date . "') ";
		// TODOXXX
		$result = mssql_query($query, $oProtime->getConnection());
		$status_separator = '';
		while ( $row = mssql_fetch_array($result) ) {
			$status_text .= $status_separator . $row["SHORT_1"];
			$status_separator = ', ';
		}
		mssql_free_result($result);
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
function createTelephoneArray($arrTel, $tel, $explode = 1) {
	if ( $explode == 1 ) {
		$tel = cleanUpTelephone($tel);
	} else {
		if ( strlen($tel) >= 3 ) {
			if ( substr($tel, 0, 3) == '06 ' ) {
				$tel = '06-' . substr($tel, -strlen($tel)+3);
			}
		}
	}

	if ( $tel != '' ) {
		$arr = explode(',', $tel);

		foreach ( $arr as $a ) {
			if ( trim($a) != '' ) {
				array_push($arrTel, trim($a));
			}
		}
	}

	return $arrTel;
}

// TODOEXPLAIN
function cleanUpTelephone($telephone) {
	$retval = $telephone;

	// remove some dirty data from telephone
	$retval = str_replace(array('.', ',', '/', "(", ")", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"), ' ', $retval);

	// 
	while ( strpos($retval, '  ') !== false ) {
		$retval = str_replace('  ',' ', $retval);
	}
	$retval = trim($retval);

	// add comma between the telephones
	$retval = str_replace(' ', ', ', $retval);

	return $retval;
}

// TODOEXPLAIN
function addslashes_mssql($text) {
	$text = str_replace('\'', '\'\'', $text);
	return $text;
}

// TODOEXPLAIN
function getAbsences($eid) {
	global $settings;

	$ret = '';

	// TODOXXX
	$oProtime = new class_mssql($settings, 'protime');
	$oProtime->connect();

	$query = "SELECT TOP 2000 P_ABSENCE.REC_NR, P_ABSENCE.PERSNR, P_ABSENCE.BOOKDATE, P_ABSENCE.ABSENCE_VALUE, P_ABSENCE.ABSENCE_STATUS, ABSENCE.SHORT_1, ABSENCE.ABSENCE FROM P_ABSENCE LEFT OUTER JOIN ABSENCE ON P_ABSENCE.ABSENCE = ABSENCE.ABSENCE WHERE P_ABSENCE.PERSNR=" . $eid . " AND P_ABSENCE.BOOKDATE>='" . date("Ymd") . "' AND ( ABSENCE_VALUE>0 OR SHORT_1 <> 'Vakantie' ) AND P_ABSENCE.ABSENCE NOT IN (6) ORDER BY P_ABSENCE.BOOKDATE, P_ABSENCE.REC_NR ";
	// TODOXXX
	$result = mssql_query($query, $oProtime->getConnection());
	// TODOXXX
	$num = mssql_num_rows($result);
	if ( $num ) {
		$ret .= "
<table>
<tr><td>&nbsp;</td></tr>
<tr>
	<td colspan=2><b>Protime/Reception absences</b></td>
</tr>
<tr>
	<td><b>Date</b></td>
	<td><b>Absence</b></td>
	<td><b>Hours</b></td>
</tr>
";

		while ( $row = mssql_fetch_array($result) ) {

			$ret .= "
<tr>
	<td>" . class_datetime::formatDatePresentOrNot($row["BOOKDATE"]) . "&nbsp;</td>
	<td>" . $row["SHORT_1"] . "&nbsp;</td>
	<td>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["ABSENCE_VALUE"]) . "</td>
</tr>
";

		}

		$ret .= "</table>";
	}

	mssql_free_result($result);

	return $ret;
}

// TODOEXPLAIN
function getAbsencesAndHolidays($eid, $year, $month, $min_minutes = 0) {
	global $settings;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$query = "
SELECT P_ABSENCE.REC_NR, P_ABSENCE.PERSNR, P_ABSENCE.BOOKDATE, P_ABSENCE.ABSENCE_VALUE, P_ABSENCE.ABSENCE_STATUS, ABSENCE.SHORT_1, P_ABSENCE.ABSENCE 
FROM P_ABSENCE 
	LEFT OUTER JOIN ABSENCE ON P_ABSENCE.ABSENCE = ABSENCE.ABSENCE 
WHERE P_ABSENCE.PERSNR=" . $eid . " AND P_ABSENCE.BOOKDATE LIKE '" . $yearMonth . "%' AND P_ABSENCE.ABSENCE NOT IN (5, 19) 
AND ( P_ABSENCE.ABSENCE_VALUE>=" . $min_minutes . " OR P_ABSENCE.ABSENCE_VALUE=0 ) 
ORDER BY P_ABSENCE.BOOKDATE, P_ABSENCE.REC_NR 
";

	// TODOXXX
	$oProtime = new class_mssql($settings, 'protime');
	$oProtime->connect();

	// TODOXXX
	$result = mssql_query($query, $oProtime->getConnection());
	$num = mssql_num_rows($result);
	if ( $num ) {
		// TODOXXX
		while ( $row = mssql_fetch_array($result) ) {
			$ret[] = array( 'date' => $row["BOOKDATE"], 'description' => $row["SHORT_1"] );
		}
	}

	mssql_free_result($result);

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
