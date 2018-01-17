<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once "classes/start.inc.php";

// check if export_key is valid
if ( !isExportKeyValid() ) {
	// export_key not correct, check if user logged in
	$oWebuser->checkLoggedIn();

	if ( !$oWebuser->isSuperAdmin() ) {
		echo "You are not authorized to access this page.<br>";
		die('Go to <a href="index.php">staff home</a>');
	}
}

createExport();

function createExport() {
	global $dbConn, $twig;

	$export_type = '';
	if ( isset($_GET['export_type']) ) {
		$export_type = strtolower(trim($_GET['export_type']));
	}
	if ( !in_array($export_type, array('csv', 'xml', 'json') ) ) {
		$export_type = 'csv';
	}

	$users = array();

	//
	$query = "SELECT `NAME` AS LASTNAME, FIRSTNAME, EMAIL, SHORT_1 AS DEPARTMENT FROM protime_curric
	LEFT JOIN protime_depart ON protime_curric.DEPART = `protime_depart`.DEPART
WHERE DATE_OUT = 0 OR DATE_OUT >= '" . date("Ymd") . "'
ORDER BY NAME, FIRSTNAME, SHORT_1
";

	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$user = array();
		$user['LASTNAME'] = makeSafe(''.$row['LASTNAME'], $export_type);
		$user['FIRSTNAME'] = makeSafe(''.$row['FIRSTNAME'], $export_type);
		$user['EMAIL'] = makeSafe(''.$row['EMAIL'], $export_type);
		$user['DEPARTMENT'] = makeSafe(''.$row['DEPARTMENT'], $export_type);

		$users[] = $user;
	}

	if ( $export_type == 'csv' ) {
//		$data = $twig->render("export01_csv.html", array(
//			'users' => $users
//		));

		//
		$data = "LASTNAME\tFIRSTNAME\tEMAIL\tDEPARTMENT\r\n";
		foreach ( $users as $user ) {
			$data .= $user['LASTNAME'] . "\t" . $user['FIRSTNAME'] . "\t" . $user['EMAIL'] . "\t" . $user['DEPARTMENT'] . "\r\n";
		}

		$response = new Response( $data );
		$response->headers->set('Content-Type', 'text/csv', 'charset=iso-8859-1');
		$response->headers->set('Content-Disposition', 'attachment; filename="export01.csv"');
	} elseif ( $export_type == 'xml' ) {
		$xml =  $twig->render('export01_xml.html', array(
			'users' => $users
		));

		$response = new Response( $xml );
		$response->headers->set('Content-Type', 'application/xml', 'charset=iso-8859-1');
	} elseif ( $export_type == 'json' ) {
		$response = new JsonResponse($users);
		$response->headers->set('Content-Type', 'application/json', 'charset=iso-8859-1');
	} else {
		die('Incorrect response 54128745');
	}

	$response->send();
}

function makeSafe( $value, $export_type = 'csv', $charset = 'iso-8859-1' ) {
	switch ( $export_type ) {
		case "csv":
			$value = htmlspecialchars($value, ENT_XHTML, $charset);
			break;
		case "json":
			$value = htmlentities($value);
			break;
		case "xml":
//			$value = htmlspecialchars($value, ENT_XML1, $charset); // werkt niet onder PHP 5.3
			$value = htmlspecialchars($value, ENT_XHTML, $charset);
			break;
		default:
			$value = htmlentities($value);
	}

	return $value;
}
