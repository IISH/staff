<?php
function uploadReplacementPhoto($oStaff ) {
	$target_dir = Settings::get('uploadDir');

	$newName = $oStaff->getId() . '-' . str_replace('_.', '.', basename($_FILES["fileToUpload"]["name"]));
	$target_file = $target_dir . $newName;

	$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

	if ( $_FILES["fileToUpload"]["tmp_name"] == '' ) {
		return '';
	}

	// Check file size
	if ($_FILES["fileToUpload"]["size"] > 2*1024*1024) {
		die("Sorry, your file is too large.");
	}

	// Allow certain file formats
	if ( !in_array($imageFileType, array('jpg', 'png', 'jpeg', 'gif')) ) {
		die( "Sorry, only JPG, JPEG, PNG & GIF files are allowed." );
	}

	//
	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	if ( $check === false ) {
		die("File is not an image.");
	}

	// Check if $uploadOk is set to 0 by an error
	if ( !move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file) ) {
		die("Sorry, there was an error uploading your file.");
	}

	return $newName;
}

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
	if ( !file_exists ( $photo ) ) {
		error_log("Error: Image does not exist: " . $photo);
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
	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM staff_today_checkinout WHERE PERSNR=" . $persnr . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY BOOKTIME, REC_NR ";
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
		$query = "
SELECT DISTINCT CODE, protime_absence.SHORT_" . getLanguage() . "
FROM protime_p_absence
	INNER JOIN protime_absence ON protime_p_absence.ABSENCE = protime_absence.ABSENCE
WHERE protime_p_absence.PERSNR = " . $persnr . " AND protime_p_absence.BOOKDATE = '" . $date . "'
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
