<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('header_nationalholidays'));
$oPage->setContent(createNationalHolidaysContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createNationalHolidaysContent( ) {
	global $dbConn, $twig;

	$days = array();
	$query = 'SELECT * FROM staff_feestdagen WHERE isdeleted=0 AND datum >= \'' . date('Y-m-d') . '\' AND datum <= \'' . (date('Y')+1) . '-01-02\' ORDER BY datum ASC ';
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$days[] = array(
			'date' => date('D j F Y', strtotime($row['datum']))
			, 'description' => $row['omschrijving']
		);
	}

	return $twig->render('nationalholidays.html', array(
		'title' => Translations::get('header_nationalholidays')
		, 'lblDate' => Translations::get('lbl_date')
		, 'lblDescription' => Translations::get('lbl_description')
		, 'days' => $days
	));
}
