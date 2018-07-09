<?php
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

// check if key correct
if ( $_SESSION["FIRE_KEY_CORRECT"] != '1' ) {
	// key not correct, check if user logged in
	$oWebuser->checkLoggedIn();
}

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('menu_fire'));
$oPage->setFavicon("images/misc/fire.ico");
$oPage->setContent(createBrandContent( ));

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createBrandContent( ) {
	global $dbConn, $oWebuser, $twig;

	$groupCounter = 0;
	$total_of_present_employees = 0;

	$ret = "";

	//
	$oBeoOntruimer = new Beo( 'o',  Translations::get('menu_evacuator'));
	$oBeoBhv = new Beo( 'b', Translations::get('menu_ert'));
	$loop = array();

	// Remark: the queries return all employees
	// checking if an employee is present or not, is done below
	// Remark: in fire page, sorting always on (last)name, firstname

	$aTotEnMet = 'L';
	$vanafTotEnMetZ = chr(ord($aTotEnMet)+1);

	$loop[] = array(
		'label' => Translations::get('present_employees_long') . " (A - $aTotEnMet)"
		, 'query' => "SELECT * FROM protime_curric WHERE NAME < '$vanafTotEnMetZ' ORDER BY NAME, FIRSTNAME "
		, 'count_total' => true
		, 'reset_group_counter' => true
	);
	$loop[] = array(
		'label' => Translations::get('present_employees_long') . " ($vanafTotEnMetZ - Z)"
		, 'query' => "SELECT * FROM protime_curric WHERE NAME >= '$vanafTotEnMetZ' ORDER BY NAME, FIRSTNAME "
		, 'count_total' => true
		, 'reset_group_counter' => false
	);
	$loop[] = array(
		'label' => Translations::get('present_evacuators')
		, 'query' => "SELECT * FROM protime_curric WHERE " . $oBeoOntruimer->getQuery() . " ORDER BY NAME, FIRSTNAME "
		, 'count_total' => false
		, 'reset_group_counter' => true
	);
	$loop[] = array(
		'label' => Translations::get('present_ert')
		, 'query' => "SELECT * FROM protime_curric WHERE " . $oBeoBhv->getQuery() . " ORDER BY NAME, FIRSTNAME "
		, 'count_total' => false
		, 'reset_group_counter' => true
	);

	//
	$checkedInEmployees = getListOfIdsOfCheckedInEmployees();

	$groups = array();

	//
	foreach( $loop as $item ) {
		$group = array();
		$group['label'] = $item['label'];
		$group['lbl_name'] = Translations::get('lbl_name');
		$group['lbl_telephone'] = Translations::get('lbl_telephone');
		$group['lbl_roles'] = Translations::get('lbl_roles');
		$group['items'] = array();


		if ( $item['reset_group_counter'] ) {
			$groupCounter = 0;
		}

		//
		$stmt = $dbConn->getConnection()->prepare( $item['query'] );
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$oEmployee = new ProtimeUser($row["PERSNR"]);

			// check if employee can be found in the the array of checked employees
			if ( in_array( $oEmployee->getId(), $checkedInEmployees) ) {
				$groupItem = array();

				if ( $item['count_total'] ) {
					$total_of_present_employees++;
				}

				$groupCounter++;

				if ( $oWebuser->isLoggedIn() ) {
					$empNameLink = createUrl( array( 'url' => 'employee.php?id=' . $oEmployee->getId(), 'label' => $oEmployee->getNameForFirePage() ) );
				} else {
					$empNameLink = $oEmployee->getNameForFirePage();
				}

				$groupItem['groupCounter'] = $groupCounter;
				$groupItem['empNameLink'] = $empNameLink;
				$groupItem['telephone'] = Telephone::getTelephonesHref($oEmployee->getTelephones(), false);
				$groupItem['remarks'] = $oEmployee->getRolesForFirePage();

				$group['items'][] = $groupItem;
			}
		}

		//
		$groups[] = $group;
	}

	return $twig->render('fire.twig', array(
		'title' => Translations::get('header_fire')
		, 'print_date' => Translations::get('printed_on') . ": " . date("d") . ' ' . Translations::get('month' . (date("m")+0)) . ' ' . date("Y H:i")
		, 'total_number_of_employees' => Translations::get('total_number_of_employees')
		, 'total_of_present_employees' => $total_of_present_employees
		, 'groups' => $groups
	));
}
