<?php
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('menu_fire'));
$oPage->setContent(createBrandContent( ));

// show page
echo $oPage->getPage();

function createBrandContent( ) {
	global $dbConn;

	$total_of_present_employees = 0;
	$title = Translations::get('header_fire');
	$ret = "<h1>$title</h1>
" . Translations::get('printed_on') . ": " . date("d") . ' ' . Translations::get('month' . (date("m")+0)) . ' ' . date("Y H:i") . "<br><br>";


	//
	$oBeoMedewerker = new Beo( 'mnotonotb', Translations::get('employees') );
	$oBeoOntruimer = new Beo( 'o',  Translations::get('menu_evacuator'));
	$oBeoBhv = new Beo( 'b', Translations::get('menu_ert'));
	$loop = array();

	// Remark: the queries return all employees
	// checking if an employee is present or not, is done below
	$loop[] = array(
		'label' => Translations::get('present_employees_long')
		, 'query' => "SELECT * FROM " . Settings::get('protime_tables_prefix') . "curric WHERE " . $oBeoMedewerker->getQuery() . " ORDER BY NAME, FIRSTNAME "
	);
	$loop[] = array(
		'label' => Translations::get('present_evacuators')
		, 'query' => "SELECT * FROM " . Settings::get('protime_tables_prefix') . "curric WHERE " . $oBeoOntruimer->getQuery() . " ORDER BY NAME, FIRSTNAME "
	);
	$loop[] = array(
		'label' => Translations::get('present_ert')
		, 'query' => "SELECT * FROM " . Settings::get('protime_tables_prefix') . "curric WHERE " . $oBeoBhv->getQuery() . " ORDER BY NAME, FIRSTNAME "
	);

	//
	foreach( $loop as $item ) {
		$ret .= "<h1>" . $item['label'] . "</h1>
<table border=0 cellspacing=0 cellpadding=7 style=\"border: 1px solid black;\">
<TR>
	<TD width=\"25\" style=\"border: 1px solid black;\">&nbsp;</TD>
	<TD width=\"25\" style=\"border: 1px solid black;\">&nbsp;</TD>
	<TD width=\"250\" style=\"border: 1px solid black;\"><font size=-1><b>" . Translations::get('lbl_name') . "</b></font></TD>
	<td width=\"170\" align=\"center\" style=\"border: 1px solid black;\"><font size=-1><b>" . Translations::get('lbl_telephone') . "</b></font></td>
	<td width=\"200\" align=\"center\" style=\"border: 1px solid black;\"><font size=-1><b>" . Translations::get('lbl_roles') . "</b></font></td>
</TR>
";

		$totaal["aanwezig"] = 0;

		//
		$stmt = $dbConn->getConnection()->prepare( $item['query'] );
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$oEmployee = new ProtimeUser($row["PERSNR"]);

			//
			$status = getCurrentDayCheckInoutState($oEmployee->getId());

			// check if employee is present, if so, show the employee
			if ( $status["aanwezig"] == 1 ) {
				$total_of_present_employees++;

				$totaal["aanwezig"]++;

				$tmp = "
<tr>
	<td style=\"border: 1px solid black;\">&nbsp;</td>
	<td style=\"border: 1px solid black;\">" . $totaal["aanwezig"] . "</td>
	<td style=\"border: 1px solid black;\">" . createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getFireName() ) ) . "</td>
	<td style=\"border: 1px solid black;\">" . Telephone::getTelephonesHref($oEmployee->getTelephones()) . "&nbsp;</td>
	<td style=\"border: 1px solid black;\">" . $oEmployee->getRolesForFirePage() . "&nbsp;</td>
</a></td>
</tr>
";

				$ret .= $tmp;
			}
		}

		$ret .= "
</table><br>
";
	}

	$ret .= '<br>' . Translations::get('total_number_of_employees') . ': ' . $total_of_present_employees . "<br><br>";

	$ret .= "

<SCRIPT LANGUAGE=\"JavaScript\">
<!--
window.print();
// -->
</script>
";

	return $ret;
}
