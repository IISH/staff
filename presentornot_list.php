<?php 
//
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

//
$s = getAndProtectSearch();

$retval = '';

$oEmployee = new class_employee($oWebuser->getUser());
$favIds = implode(',', $oEmployee->getFavourites('present'));
$checkInOutIds = implode(',', $oEmployee->getFavourites('checkinout'));

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
	$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02"), explode(' ', $s));
    $title = 'Search: ' . $s;
}

$oProtime = new class_mysql($databases['default']);
$oProtime->connect();

//
$querySelect = "SELECT * FROM PROTIME_CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) " . $queryCriterium . " ORDER BY FIRSTNAME, NAME ";
$resultSelect = mysql_query($querySelect, $oProtime->getConnection());

$totaal["aanwezig"] = 0;
$totaal["afwezig"] = 0;

while ( $rowSelect = mysql_fetch_assoc($resultSelect) ) {
	$tmp = "
<tr>
	<td><div id=\"divAddRemove" . $rowSelect["PERSNR"] . "\">::ADDREMOVE::</div></td>
	<td><div id=\"divCheckInOut" . $rowSelect["PERSNR"] . "\">::CHECKINOUT::</div></td>
	<td>" . fixBrokenChars(trim($rowSelect["FIRSTNAME"]) . " " . verplaatsTussenvoegselNaarBegin(trim($rowSelect["NAME"]))) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<td align=\"center\">" . cleanUpTelephone($rowSelect["USER02"]) . "</td>
</a></td>
</tr>
";

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
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="checkInOut(' . $rowSelect["PERSNR"] . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/clock-red.png" border=0></a>', $tmp);
	} else {
		$alttitle = "Click to get a 'checked in' email notification when user checks in.";
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="checkInOut(' . $rowSelect["PERSNR"] . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/clock-black.png" border=0></a>', $tmp);
	}

	//
	$status = getStatusColor($rowSelect["PERSNR"], date("Ymd"));

	if ( $status["aanwezig"] == 1 ) {
		$totaal["aanwezig"]++;
	} else {
		$totaal["afwezig"]++;
	}

	// als nix gevonden
	$tmp = str_replace('::STATUS_STYLE::', $status["status_color"], $tmp);

	if ( $oWebuser->hasInOutTimeAuthorisation() || $oWebuser->isAdmin() || $oWebuser->isReception() || $oWebuser->isHead() || $oWebuser->getProtimeId() == $rowSelect["PERSNR"] ) {
		$tmp = str_replace('::STATUS_TEXT::', $status["status_text"], $tmp);
		$tmp = str_replace('::STATUS_ALT::', $status["status_alt"], $tmp);
	} else {
		$tmp = str_replace('::STATUS_TEXT::', '', $tmp);
		$tmp = str_replace('::STATUS_ALT::', '', $tmp);
	}

	// moet regel getoond worden?
	if ( $s == '-r-' ) {
		// als rood, dan alleen tonen als persoon niet aanwezig is
		if ( $status["aanwezig"] == 0 ) {
			//
			$retval .= $tmp;
		}
	} elseif ( $s == '-g-' ) {
		// als groen, dan alleen tonen als persoon aanwezig is
		if ( $status["aanwezig"] == 1 ) {
			//
			$retval .= $tmp;
		}
	} else {
		// als niet rood en ook niet groen dan altijd tonen
		$retval .= $tmp;
	}
}
mysql_free_result($resultSelect);

if ( $retval != '' ) {
	$retval = "<h2>$title</h2>
<table border=0 cellspacing=1 cellpadding=1>
<TR>
	<TD width=25></TD>
	<TD width=25></TD>
	<TD width=250><font size=-1><b>Name</b></font></TD>
	<td width=100 align=\"center\"><font size=-1><b>Check in/out</b></font></td>
	<td width=100 align=\"center\"><font size=-1><b>Telephone</b></font></td>
</TR>
" . $retval . "
</table><br><font size=-1><i>Present: " . $totaal["aanwezig"] . "<br>Not present: " . $totaal["afwezig"] . "<br><br>Page refreshes every minute, last refresh at: " . date("H:i:s") . "</i></font>";
} else {
    $retval = "<h2>$title</h2>
";
}

echo $retval;
