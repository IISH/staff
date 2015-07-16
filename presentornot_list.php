<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

//
$s = getAndProtectSearch();
$layout = trim($protect->request_positive_number_or_empty('get', "l"));
if ( $layout <> "2" ) {
	$layout = 1;
}

$retval = '';

//
$favIds = implode(',', $oWebuser->getFavourites('present'));
$checkInOutIds = implode(',', $oWebuser->getFavourites('checkinout'));

// CRITERIUM
$queryCriterium = '';
$title = '';
if ( $s == '-a-' ) {
	//
    $title = 'All employees';
} elseif ( $s == '-r-' ) {
	//
    $title = 'Absent employees';
} elseif ( $s == '-g-' ) {
	//
    $title = 'Present employees';
} elseif ( $s == '' ) {
	// no search
	// use favourites
	$queryCriterium = 'AND PERSNR IN (' . $favIds . ') ';
    $title = 'Your favourites';
} else {
	// search
	$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02", class_settings::get('curric_room')), explode(' ', $s));
    $title = 'Search: ' . $s;
}

$oProtime = new class_mysql($databases['default']);
$oProtime->connect();

//
$querySelect = "SELECT * FROM " . class_settings::get('protime_tables_prefix') . "CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) " . $queryCriterium . " ORDER BY FIRSTNAME, NAME ";
$resultSelect = mysql_query($querySelect, $oProtime->getConnection());

$totaal["aanwezig"] = 0;
$totaal["afwezig"] = 0;

$retvalArray = array();

while ( $rowSelect = mysql_fetch_assoc($resultSelect) ) {
	$photo = '';

	$empName = $rowSelect["FIRSTNAME"] . " " . verplaatsTussenvoegselNaarBegin(trim($rowSelect["NAME"]));
	$empName = removeJobFunctionFromName($empName);
	$empName = trim($empName);

	if ( $layout == 1 ) {
		$tmp = "
<tr>
	<td><div id=\"divAddRemove" . $rowSelect["PERSNR"] . "\">::ADDREMOVE::</div></td>
	<td><div id=\"divCheckInOut" . $rowSelect["PERSNR"] . "\">::CHECKINOUT::</div></td>
	<td>" . createUrl( array( 'url' => 'employee.php?id=' . $rowSelect["PERSNR"], 'label' => fixBrokenChars( $empName ) ) ) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<td align=\"center\">" . cleanUpTelephone($rowSelect["USER02"]) . "</td>
	<td align=\"center\">" . $rowSelect[ class_settings::get('curric_room') ]. "</td>
</td>
</tr>
";
	} else {

		$tmp = "
<table class=\"photobook\">
<tr class=\"photobook\">
	<td  class=\"photobook\" colspan=4>::PHOTO::</td>
</tr>
<tr>
	<td  class=\"photobook\" colspan=4>" . createUrl( array( 'url' => 'employee.php?id=' . $rowSelect["PERSNR"], 'label' => fixBrokenChars( $empName ) ) ) . "</td>
</tr>
<tr>
	<td class=\"photobook\"><div id=\"divAddRemove" . $rowSelect["PERSNR"] . "\">::ADDREMOVE::</div></td>
	<td class=\"photobook\"><div id=\"divCheckInOut" . $rowSelect["PERSNR"] . "\">::CHECKINOUT::</div></td>
	<td class=\"photobook presentornot_absence\" colspan=2 width=\"100px\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
</tr>
<tr>
	<td class=\"photobook\" colspan=4>tel.: " . valueOr(cleanUpTelephone($rowSelect["USER02"])) . " room: " . valueOr($rowSelect[class_settings::get('curric_room')]). "</td>
</tr>
</table>
";

		$photo = trim(trim($rowSelect["FIRSTNAME"]) . ' ' . trim(verplaatsTussenvoegselNaarBegin($rowSelect["NAME"])));
		$photo = removeJobFunctionFromName($photo);
		$photo = fixPhotoCharacters($photo);
		$photo = replaceDoubleTripleSpaces($photo);
		$photo = str_replace(' ', '.', $photo);
		$photo = strtolower( $photo . '.jpg' );
		$photo = checkImageExists( class_settings::get('staff_images_directory') . $photo, class_settings::get('noimage_file') );
		$photo = "<img src=\"$photo\"  style=\"height:140px;\">";

		$tmp = str_replace('::PHOTO::', $photo, $tmp);
	}

	//
	if ( strpos(',' . $favIds . ',', ',' . $rowSelect["PERSNR"] . ',') !== false ) {
		$alttitle = "Click to remove the person from your favourites";
		$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $rowSelect["PERSNR"] . ', \'r\');" title="' . $alttitle . '" class="nolink favourites_on">&#9733;</a>', $tmp);
	} else {
		$alttitle = "Click to add the person to your favourites";
		$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $rowSelect["PERSNR"] . ', \'a\');" title="' . $alttitle . '" class="nolink favourites_off">&#9733;</a>', $tmp);
	}

	// 
	if ( strpos(',' . $checkInOutIds . ',', ',' . $rowSelect["PERSNR"] . ',') !== false ) {
		$alttitle = "Click to remove the 'checked in' email notification";
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="checkInOut(' . $rowSelect["PERSNR"] . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-red.png" border=0></a>', $tmp);
	} else {
		$alttitle = "Click to get a 'checked in' email notification when user checks in.";
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="checkInOut(' . $rowSelect["PERSNR"] . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-black.png" border=0></a>', $tmp);
	}

	//
	$status = getStatusColor($rowSelect["PERSNR"], date("Ymd"));

	if ( $status["aanwezig"] == 1 ) {
		$totaal["aanwezig"]++;
	} else {
		$totaal["afwezig"]++;
	}

	//
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}
	$tmp = str_replace('::STATUS_STYLE::', $status["status_color"], $tmp);
	$tmp = str_replace('::STATUS_TEXT::', $status["status_text"], $tmp);
	$tmp = str_replace('::STATUS_ALT::', $status["status_alt"], $tmp);

	// moet regel getoond worden?
	if ( $s == '-r-' ) {
		// als rood, dan alleen tonen als persoon niet aanwezig is
		if ( $status["aanwezig"] == 0 ) {
			//
//			$retval .= $tmp;
			$retvalArray[] = $tmp;
		}
	} elseif ( $s == '-g-' ) {
		// als groen, dan alleen tonen als persoon aanwezig is
		if ( $status["aanwezig"] == 1 ) {
			//
//			$retval .= $tmp;
			$retvalArray[] = $tmp;
		}
	} else {
		// als niet rood en ook niet groen dan altijd tonen
//		$retval .= $tmp;
		$retvalArray[] = $tmp;
	}
}
mysql_free_result($resultSelect);

if ( count($retvalArray) > 0 ) {

	if ( $layout == 1 ) {
		$retval = "
<table border=0 cellspacing=1 cellpadding=1>
<TR>
	<TD width=25></TD>
	<TD width=25></TD>
	<TD width=250><font size=-1><b>Name</b></font></TD>
	<td width=100 align=\"center\"><font size=-1><b>Check in/out</b></font></td>
	<td width=80 align=\"center\"><font size=-1><b>Telephone</b></font></td>
	<td width=60 align=\"center\"><font size=-1><b>Room</b></font></td>
</TR>";

		foreach ( $retvalArray as $item ) {
			$retval .= $item;
		}

		$retval .= "</table>";

	} else {
		$retval = "<table border=0 cellspacing=1 cellpadding=10>";
		$counter = 0;
		$first_td = true;
		foreach ( $retvalArray as $item ) {
			$counter++;
			if ( $counter == 1 && !$first_td ) {
				$retval .= '</tr>';
			}
			if ( $counter == 1 ) {
				$retval .= '<tr>';
			}
			$retval .= '<td valign=top>' . $item . '</td>';
			if ( $counter == 4 ) {
				$counter = 0;
			}
			$first_td = false;
		}

		$retval .= '</tr>';
		$retval .= "</table>";


	}

	$retval .= "<br><font size=-1><i>Present: " . $totaal["aanwezig"] . "<br>Not present: " . $totaal["afwezig"] . "<br><br>Page refreshes every minute, last refresh at: " . date("H:i:s") . "</i></font>";
}

$retval = "<h2>$title</h2>" . $retval;

echo $retval;
