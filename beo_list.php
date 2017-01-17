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

//
$layout = trim($protect->request('get', "l"));
if ( $layout == '' ) {
	$layout = $oWebuser->getUserSetting($type_of_beo . '_display_format', 'tabular');
}
if ( !in_array($layout, array('tabular', 'tile')) ) {
	$layout = 'tabular';
}
// save setting
$query_update = "INSERT INTO staff_user_settings (`user_id`, `setting`, `value`) VALUES (" . $oWebuser->getId() . ", '" . $type_of_beo . "_display_format', '$layout') ON DUPLICATE KEY UPDATE `value`='$layout' ";
$stmt = $dbConn->getConnection()->prepare($query_update);
$stmt->execute();

$retval = "
<h1>" . $oBeo->getLabel() . "</h1>
<div class='incaseofemergency'>" . Translations::get('in_case_of_emergency_call') . " <span class='incaseofemergencynumber'>" . Settings::get('emergency_number') . "</span></div>
";

//
$checkInOutIds = implode(',', $oWebuser->getFavourites('checkinout'));

//
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

// TODOGCU
$querySelect = "
SELECT " . Settings::get('protime_tables_prefix') . "curric.PERSNR
FROM " . Settings::get('protime_tables_prefix') . "curric
	LEFT JOIN staff_today_checkinout ON protime_curric.PERSNR = staff_today_checkinout.PERSNR AND  staff_today_checkinout.BOOKDATE = '" . date("Ymd") . "'
WHERE ". $dateOutCriterium . "
	AND " . $oBeo->getQuery() . Misc::getNeverShowPersonsCriterium() . "
ORDER BY " . Settings::get('protime_tables_prefix') . "curric.FIRSTNAME, " . Settings::get('protime_tables_prefix') . "curric.NAME ";

//echo $querySelect;

$stmt = $dbConn->getConnection()->prepare($querySelect);
$stmt->execute();
$result = $stmt->fetchAll();
foreach ($result as $row) {
	$verdieping = '';
	$telephone = '';

	$oEmployee = new ProtimeUser($row["PERSNR"]);

	if ( $oBeo->getShowFloor() ) {
		$verdieping = "<td align=\"center\">" . $oEmployee->getOntruimerVerdieping() . "</td>";
	}

	if ( $oWebuser->hasAuthorisationBeoTelephone() ) {
		$telephone = "<td align=\"center\">" . Telephone::getTelephonesHref($oEmployee->getTelephones()) . "</td>";
	}

	$tmp = "
<tr>
	<td><div id=\"divCheckInOut" . $oEmployee->getId() . "\">::CHECKINOUT::</div></td>
	<td>" . createUrl( array( 'url' => 'employee.php?id=' .  $oEmployee->getId(), 'label' => $oEmployee->getNiceFirstLastname() ) ) . "</td>
	<td class=\"presentornot_absence\" style=\"::STATUS_STYLE::\"><A class=\"checkinouttime\" TITLE=\"::STATUS_ALT::\">::STATUS_TEXT::</A></td>
	$telephone
	$verdieping
</tr>
";

	//
	if ( strpos(',' . $checkInOutIds . ',', ',' . $oEmployee->getId() . ',') !== false ) {
		$alttitle = Translations::get('lbl_click_to_not_get_email_notification');
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-red.png" border=0></a>', $tmp);
	} else {
		$alttitle = Translations::get('lbl_click_to_get_email_notification');
		$tmp = str_replace('::CHECKINOUT::', '<a href="#" onClick="return checkInOut(' . $oEmployee->getId() . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-black.png" border=0></a>', $tmp);
	}

	//
	$status = getCurrentDayCheckInoutState($oEmployee->getId());

	if ( $status["aanwezig"] == 1 ) {
		$totaal["aanwezig"]++;
		$ontruimersAanwezigOpVerdieping[$oEmployee->getOntruimerVerdieping()] = 1;
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

if ( $retval != '' ) {
	$verdieping = '';
	if ( $oBeo->getShowFloor() ) {
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

if ( $oBeo->getShowFloor() ) {
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
