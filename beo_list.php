<?php 
//
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

//
$oBeo = new Beo( (isset($type_of_beo) ? $type_of_beo : ''), $label );

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$retval = "
<h2>" . $oBeo->getLabel() . "</h2>
<div class='incaseofemergency'>" . Translations::get('in_case_of_emergency_call') . " <span class='incaseofemergencynumber'>" . Settings::get('emergency_number') . "</span></div>
";

//
$checkInOutIds = implode(',', $oWebuser->getFavourites('checkinout'));

$oProtime = new class_mysql($databases['default']);
$oProtime->connect();

//
$never_show_persnr = '0,' . preg_replace('/[^0-9]/', ',', trim(Settings::get("never_show_persnr")));
$never_show_persnr = preg_replace('/,{2,}/', ',', $never_show_persnr);

$querySelect = "SELECT * FROM " . Settings::get('protime_tables_prefix') . "CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND " . $oBeo->getQuery() . " AND PERSNR NOT IN ($never_show_persnr) ORDER BY FIRSTNAME, NAME ";
$resultSelect = mysql_query($querySelect, $oProtime->getConnection());

$totaal["aanwezig"] = 0;
$totaal["afwezig"] = 0;

$ontruimersAanwezigOpVerdieping = array();
$nrOfLevels = Settings::get("number_of_levels");
if ( $nrOfLevels == '' ) {
	$nrOfLevels = 6;
}
for( $i=0 ; $i <= $nrOfLevels; $i++ ) {
    $ontruimersAanwezigOpVerdieping[$i] = 0;
}

while ( $rowSelect = mysql_fetch_assoc($resultSelect) ) {
	$verdieping = '';
	$telephone = '';

	$oEmployee = new ProtimeUser($rowSelect["PERSNR"]);

	if ( $oBeo->getShowLevel() ) {
		$verdieping = "<td align=\"center\">" . cleanUpVerdieping($rowSelect["USER03"]) . "</td>";
	}

	if ( $oWebuser->hasAuthorisationBeoTelephone() ) {
		$telephone = "<td align=\"center\">" . cleanUpTelephone($rowSelect["USER02"]) . "</td>";
	}

	$tmp = "
<tr>
	<td><div id=\"divCheckInOut" . $rowSelect["PERSNR"] . "\">::CHECKINOUT::</div></td>
	<td>" . createUrl( array( 'url' => 'employee.php?id=' .  $oEmployee->getId(), 'label' => fixBrokenChars( $oEmployee->getNiceFirstLastname() ) ) ) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	$telephone
	$verdieping
</tr>
";

	//
	if ( strpos(',' . $checkInOutIds . ',', ',' . $rowSelect["PERSNR"] . ',') !== false ) {
		$alttitle = Translations::get('lbl_click_to_not_get_email_notification');
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="return checkInOut(' . $rowSelect["PERSNR"] . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-red.png" border=0></a>', $tmp);
	} else {
		$alttitle = Translations::get('lbl_click_to_get_email_notification');
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="return checkInOut(' . $rowSelect["PERSNR"] . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-black.png" border=0></a>', $tmp);
	}

	//
	$status = getCurrentDayCheckInoutState($rowSelect["PERSNR"]);

	if ( $status["aanwezig"] == 1 ) {
		$totaal["aanwezig"]++;
		$ontruimersAanwezigOpVerdieping[cleanUpVerdieping($rowSelect["USER03"])] = 1;
	} else {
		$totaal["afwezig"]++;
	}

	//
	$tmp = str_replace('::STATUS_STYLE::', $status["status_color"], $tmp);
	$tmp = str_replace('::STATUS_TEXT::', $status["status_text"], $tmp);
	$tmp = str_replace('::STATUS_ALT::', $status["status_alt"], $tmp);

	// als niet rood en ook niet groen dan altijd tonen
	$retval .= $tmp;
}
mysql_free_result($resultSelect);

if ( $retval != '' ) {
	$verdieping = '';
	if ( $oBeo->getShowLevel() ) {
		$verdieping = "<td width=100 align=\"center\"><font size=-1><b>" . Translations::get('lbl_level') . "</b></font></td>";
	}
	if ( $oWebuser->hasAuthorisationBeoTelephone() ) {
		$telephone = "<td width=100 align=\"center\"><font size=-1><b>" . Translations::get('lbl_telephone') . "</b></font></td>";
	}

	$retval = "
<table border=0 cellspacing=1 cellpadding=1>
<TR>
	<TD width=25></TD>
	<TD width=250><font size=-1><b>" . Translations::get('lbl_name') . "</b></font></TD>
	<td width=100 align=\"center\"><font size=-1><b>" . Translations::get('lbl_check_inout') . "</b></font></td>
	$telephone
	$verdieping
</TR>
" . $retval . "
</table>";
}

if ( $oBeo->getShowLevel() ) {
	//
	$retval .= "<br>
	<table>
	<tr>
		<td><font size=-1><b>" . Translations::get('lbl_level') . ": </b></font></td>
	";

		for( $i=0 ; $i <= $nrOfLevels; $i++ ) {
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
$retval .= "<br><font size=-1><i>" . Translations::get('lbl_present') . ": " . $totaal["aanwezig"] . "
	<br>" . Translations::get('lbl_not_present') . ": " . $totaal["afwezig"] . "<br><br>
	" . Translations::get('lbl_page_refreshes_every') . " " . date("H:i:s") . "</i></font>";

echo $retval;
