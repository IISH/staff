<?php
require_once "classes/start.inc.php";
require_once "classes/class_beo.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | Brand');
$oPage->setContent(createBrandContent( ));

// show page
echo $oPage->getPage();

// TODOEXPLAIN
function createBrandContent( ) {
	global $databases;

	$title = 'BRAND / Calamiteitenlijst';
	$ret = "<h2>$title</h2>
Overzicht van " . date("d-m-Y") . " om " . date("H:i:s") . "<br>
";

	//
	$oBeoMedewerker = new class_beo( 'mnotonotb' );
	$oBeoOntruimer = new class_beo( 'o' );
	$oBeoBhv = new class_beo( 'b' );
	$loop = array();
	$loop[] = array(
			'label' => 'Medewerkers'
			, 'query' => "SELECT * FROM PROTIME_CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND " . $oBeoMedewerker->getQuery() . " ORDER BY NAME, FIRSTNAME "
		);
	$loop[] = array(
			'label' => 'Ontruimers'
			, 'query' => "SELECT * FROM PROTIME_CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND " . $oBeoOntruimer->getQuery() . " ORDER BY NAME, FIRSTNAME "
		);
	$loop[] = array(
			'label' => 'BHV'
			, 'query' => "SELECT * FROM PROTIME_CURRIC WHERE ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' ) AND " . $oBeoBhv->getQuery() . " ORDER BY NAME, FIRSTNAME "
		);

	//
	foreach( $loop as $item ) {
		$ret .= "<h2>" . $item['label'] . "</h2>
<table border=0 cellspacing=0 cellpadding=7 style=\"border: 1px solid black;\">
<TR>
	<TD width=25 style=\"border: 1px solid black;\">&nbsp;</TD>
	<TD width=25 style=\"border: 1px solid black;\">&nbsp;</TD>
	<TD width=270 style=\"border: 1px solid black;\"><font size=-1><b>Naam</b></font></TD>
	<td width=130 align=\"center\" style=\"border: 1px solid black;\"><font size=-1><b>Interne tel.</b></font></td>
	<td width=130 align=\"center\" style=\"border: 1px solid black;\"><font size=-1><b>EHBO/BRAND/ONTR</b></font></td>
</TR>
";

		$oProtime = new class_mysql($databases['default']);
		$oProtime->connect();

		//
		$querySelect = $item['query'];
		$resultSelect = mysql_query($querySelect, $oProtime->getConnection());

		$totaal["aanwezig"] = 0;

		while ( $rowSelect = mysql_fetch_assoc($resultSelect) ) {
			//
			$status = getStatusColor($rowSelect["PERSNR"], date("Ymd"));

			if ( $status["aanwezig"] == 1 ) {
				$totaal["aanwezig"]++;

				$tmp = "
<tr>
	<td style=\"border: 1px solid black;\">&nbsp;</td>
	<td style=\"border: 1px solid black;\">" . $totaal["aanwezig"] . "</td>
	<td style=\"border: 1px solid black;\">" . fixBrokenChars(trim($rowSelect["NAME"]) . ', ' . trim($rowSelect["FIRSTNAME"])) . "</td>
	<td style=\"border: 1px solid black;\">" . cleanUpTelephone($rowSelect["USER02"]) . "&nbsp;</td>
	<td style=\"border: 1px solid black;\">" . $rowSelect["USER03"] . "&nbsp;</td>
</a></td>
</tr>
";

				$ret .= $tmp;
			}
		}
		mysql_free_result($resultSelect);

		$ret .= "
</table><br>Aanwezig: " . $totaal["aanwezig"] . "<br>
<br>
";
	}

	$ret .= "
<SCRIPT LANGUAGE=\"JavaScript\">
<!--
window.print();
// -->
</script>
";

	return $ret;
}
