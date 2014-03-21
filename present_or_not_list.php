<?php 
//
require_once "classes/start.inc.php";
require_once "classes/_db_connect_protime.inc.php";

$oWebuser->checkLoggedIn();

//
$s = getAndProtectSearch();

$retval = '';

//echo $oWebuser->getUser() . ' --<br>';
//echo $oWebuser->getEmail() . ' --<br>';
//echo $oWebuser->getProtimeId() . ' --<br>';

$oEmployee = new class_employee($oWebuser->getUser(), $settings);
$favIds = implode(',', $oEmployee->getFavourites('present'));
$checkInOutIds = implode(',', $oEmployee->getFavourites('checkinout'));

//echo '<br>+';
//print_r( $favIds );
//echo '+<br>';

// CRITERIUM
$queryCriterium = '';
if ( $s == '-a-' ) {
	//
} elseif ( $s == '-r-' ) {
	//
} elseif ( $s == '-g-' ) {
	//
} elseif ( $s == '' ) {
	// no search
	// use favourites
	$queryCriterium = 'AND PERSNR IN (' . $favIds . ') ';
} else {
	// search
	$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME", "EMAIL", "USER02"), explode(' ', $s));
}

// 
$querySelect = "SELECT * FROM CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) " . $queryCriterium . " ORDER BY NAME, FIRSTNAME ";
$resultSelect = mssql_query($querySelect, $dbhandleProtime);

$totaal["aanwezig"] = 0;
$totaal["afwezig"] = 0;

while ( $rowSelect = mssql_fetch_array($resultSelect) ) {
	$tmp = "
<tr>
	<td><div id=\"divAddRemove" . $rowSelect["PERSNR"] . "\">::ADDREMOVE::</div></td>
	<td><div id=\"divCheckInOut" . $rowSelect["PERSNR"] . "\">::CHECKINOUT::</div></td>
	<td>" . trim($rowSelect["NAME"]) . ", " . trim($rowSelect["FIRSTNAME"]) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<td align=\"center\">::TELEPHONE::</td>
</a></td>
</tr>
";
	$arrTel = array();

	$arrTel = createTelephoneArray($arrTel, $rowSelect["USER02"]);

	// make unique
	$arrTel = array_unique($arrTel);

	if ( count( $arrTel ) > 0 ) {
		$arrTel = implode(', ', $arrTel);
	} else {
		$arrTel = '';
	}
	$tmp = str_replace('::TELEPHONE::', $arrTel, $tmp);

	// 
	if ( strpos(',' . $favIds . ',', ',' . $rowSelect["PERSNR"] . ',') !== false ) {
		$alttitle = "Click to remove the person from your favourites";
		$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $rowSelect["PERSNR"] . ', \'r\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink"><img src="images/minus-sign.png" border=0></a>', $tmp);
	} else {
		$alttitle = "Click to add the person from your favourites";
		$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $rowSelect["PERSNR"] . ', \'a\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink"><img src="images/plus-sign.png" border=0></a>', $tmp);
	}

	// 
	if ( strpos(',' . $checkInOutIds . ',', ',' . $rowSelect["PERSNR"] . ',') !== false ) {
		$alttitle = "Click to remove the 'check in' email notification";
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="checkInOut(' . $rowSelect["PERSNR"] . ', \'r\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink"><img src="images/clock-red.png" border=0></a>', $tmp);
	} else {
		$alttitle = "Click to get a 'check in' email notification";
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="checkInOut(' . $rowSelect["PERSNR"] . ', \'a\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink"><img src="images/clock-black.png" border=0></a>', $tmp);
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

	if ( $oWebuser->hasInOutTimeAuthorisation() || $oWebuser->getProtimeId() == $rowSelect["PERSNR"] ) {
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
mssql_free_result($resultSelect);

if ( $retval != '' ) {
	$retval = "
<table border=0 cellspacing=1 cellpadding=1>
<TR>
	<TD width=25></TD>
	<TD width=25></TD>
	<TD width=250><font size=-1><b>Name</b></font></TD>
	<td width=100 align=\"center\"><font size=-1><b>Check in/out</b></font></td>
	<td width=100 align=\"center\"><font size=-1><b>Telephone</b></font></td>
</TR>
" . $retval . "
</table><br><font size=-1><i>Present: " . $totaal["aanwezig"] . "<br>Not present: " . $totaal["afwezig"] . "<br><br>Refreshed at:" . date("H:i:s") . "</i></font>";
}

echo $retval;

require_once "classes/_db_disconnect.inc.php";
?>