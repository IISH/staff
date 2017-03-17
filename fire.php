<?php
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

// check if key correct
if ( $_SESSION["FIRE_KEY_CORRECT"] != '1' ) {
	// key not correct, check if user logged in
	$oWebuser->checkLoggedIn();
}

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('menu_fire'));
$oPage->setContent(createBrandContent( ));

// show page
echo $oPage->getPage();

function createBrandContent( ) {
	global $dbConn, $oWebuser;

	$total_of_present_employees = 0;
	$title = Translations::get('header_fire');
	$ret = "<h1>$title</h1>
" . Translations::get('printed_on') . ": " . date("d") . ' ' . Translations::get('month' . (date("m")+0)) . ' ' . date("Y H:i") . "<br>";

	//
	$oBeoOntruimer = new Beo( 'o',  Translations::get('menu_evacuator'));
	$oBeoBhv = new Beo( 'b', Translations::get('menu_ert'));
	$loop = array();

	// Remark: the queries return all employees
	// checking if an employee is present or not, is done below
	// Remark: in fire page, sorting always on (last)name, firstname
	$loop[] = array(
		'label' => Translations::get('present_employees_long')
		, 'query' => "SELECT * FROM " . Settings::get('protime_tables_prefix') . "curric ORDER BY NAME, FIRSTNAME "
		, 'count_total' => true
	);
	$loop[] = array(
		'label' => Translations::get('present_evacuators')
		, 'query' => "SELECT * FROM " . Settings::get('protime_tables_prefix') . "curric WHERE " . $oBeoOntruimer->getQuery() . " ORDER BY NAME, FIRSTNAME "
		, 'count_total' => false
	);
	$loop[] = array(
		'label' => Translations::get('present_ert')
		, 'query' => "SELECT * FROM " . Settings::get('protime_tables_prefix') . "curric WHERE " . $oBeoBhv->getQuery() . " ORDER BY NAME, FIRSTNAME "
		, 'count_total' => false
	);

	$ret .= "
<table class=\"fire\">
";

	//
	$checkedInEmployees = getListOfIdsOfCheckedInEmployees();

	//
	foreach( $loop as $item ) {
		$ret .= "
<TR>
	<TH colspan=5 class=\"fire_header\"><br>" . $item['label'] . "</TH>
</TR>
<TR>
	<TH width=\"30\">&nbsp;</TH>
	<TH width=\"30\">&nbsp;</TH>
	<TH>" . Translations::get('lbl_name') . "</TH>
	<TH align=\"center\">" . Translations::get('lbl_telephone') . "</TH>
	<TH align=\"center\">" . Translations::get('lbl_roles') . "</TH>
</TR>
";

		$groupCounter = 0;

		//
		$stmt = $dbConn->getConnection()->prepare( $item['query'] );
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$oEmployee = new ProtimeUser($row["PERSNR"]);

			// check if employee can be found in the the array of checked employees
			if ( in_array( $oEmployee->getId(), $checkedInEmployees) ) {
				if ( $item['count_total'] ) {
					$total_of_present_employees++;
				}

				$groupCounter++;

				if ( $oWebuser->isLoggedIn() ) {
					$empNameLink = createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNameForFirePage() ) );
				} else {
					$empNameLink = $oEmployee->getNameForFirePage();
				}

				$tmp = "
<tr>
	<td>&nbsp;</td>
	<td align=\"right\">" . $groupCounter . "</td>
	<td>" . $empNameLink . "</td>
	<td>" . Telephone::getTelephonesHref($oEmployee->getTelephones(), false) . "&nbsp;</td>
	<td>" . $oEmployee->getRolesForFirePage() . "&nbsp;</td>
</a></td>
</tr>
";

				$ret .= $tmp;
			}
		}
	}
	$ret .= "
</table><br>
";

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
