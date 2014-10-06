<?php 
//
require_once "classes/start.inc.php";
require_once "classes/class_beo.inc.php";

//
$oBeo = new class_beo( isset($type_of_beo) ? $type_of_beo : '' );

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$retval = "<h2>" . $oBeo->getLabel() . "</h2>";

$oEmployee = new class_employee($oWebuser->getUser(), $settings);
$checkInOutIds = implode(',', $oEmployee->getFavourites('checkinout'));

$oProtime = new class_mysql($databases['default']);
$oProtime->connect();

//
$never_show_persnr = '0,' . preg_replace('/[^0-9]/', ',', trim(class_settings::getSetting("never_show_persnr")));
$never_show_persnr = preg_replace('/,{2,}/', ',', $never_show_persnr);

$querySelect = "SELECT * FROM PROTIME_CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND " . $oBeo->getQuery() . " AND PERSNR NOT IN ($never_show_persnr) ORDER BY FIRSTNAME, NAME ";
$resultSelect = mysql_query($querySelect, $oProtime->getConnection());

$totaal["aanwezig"] = 0;
$totaal["afwezig"] = 0;

$ontruimersAanwezigOpVerdieping = array();

while ( $rowSelect = mysql_fetch_assoc($resultSelect) ) {
	$verdieping = '';

	if ( $oBeo->showLevel() ) {
		$verdieping = "<td align=\"center\">" . cleanUpVerdieping($rowSelect["USER03"]) . "</td>";
	}

	$tmp = "
<tr>
	<td><div id=\"divCheckInOut" . $rowSelect["PERSNR"] . "\">::CHECKINOUT::</div></td>
	<td>" . fixBrokenChars(trim($rowSelect["FIRSTNAME"]) . " " . verplaatsTussenvoegselNaarBegin(trim($rowSelect["NAME"]))) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	<td align=\"center\">" . cleanUpTelephone($rowSelect["USER02"]) . "</td>
	$verdieping
</tr>
";

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
		$ontruimersAanwezigOpVerdieping[cleanUpVerdieping($rowSelect["USER03"])] = 1;
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

	// als niet rood en ook niet groen dan altijd tonen
	$retval .= $tmp;
}
mysql_free_result($resultSelect);

if ( $retval != '' ) {
	$verdieping = '';
	if ( $oBeo->showLevel() ) {
		$verdieping = "<td width=100 align=\"center\"><font size=-1><b>Verdieping</b></font></td>";
	}

	$retval = "
<table border=0 cellspacing=1 cellpadding=1>
<TR>
	<TD width=25></TD>
	<TD width=250><font size=-1><b>Name</b></font></TD>
	<td width=100 align=\"center\"><font size=-1><b>Check in/out</b></font></td>
	<td width=100 align=\"center\"><font size=-1><b>Telephone</b></font></td>
	$verdieping
</TR>
" . $retval . "
</table>";
}

if ( $oBeo->showLevel() ) {
	//
	$retval .= "<br>
	<table>
	<tr>
		<td><font size=-1><b>Verdieping: </b></font></td>
	";

		for( $i =0 ; $i <= 5; $i++ ) {
			if ( $ontruimersAanwezigOpVerdieping[$i] == 1 ) {
				$style = " background-color:green;color:white; ";
			} else {
				$style = " background-color:#C62431;color:white; ";
			}
			$retval .= "<td align=\"center\" style=\"" .  $style . "width:22px;\">" . $i. "</td>";
		}

		$retval .= "
		</tr>
		</table>
	";
}

//
$retval .= "<br><font size=-1><i>Present: " . $totaal["aanwezig"] . "<br>Not present: " . $totaal["afwezig"] . "<br><br>Page refreshes every minute, last refresh at: " . date("H:i:s") . "</i></font>";

echo $retval;
