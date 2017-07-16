<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('header_specialnumbers'));
$oPage->setContent(createSpecialNumbersContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

// disconnect database connection
$dbConn->close();

function createSpecialNumbersContent( ) {
    global $dbConn, $oWebuser, $twig;

	$objects = array();

	//
	$isAdmin = 0;
	if ( $oWebuser->isAdmin() ) {
		$isAdmin = 1;
	}

	//
	$query = 'SELECT ID, object, extra, email, number, fax, room, location FROM staff_special_numbers WHERE isdeleted=0 ORDER BY object ASC ';
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$item = array();

		if ( $oWebuser->isAdmin() ) {
			$object = "<a href=\"special_numbers_edit.php?ID=" . $row['ID'] . "\">" . $row['object'] . "</a>";
		} else {
			$object = $row['object'];
		}

        $formattedEmail = trim($row['email']);
        if ( $formattedEmail != '' ) {
            $formattedEmail = "<a href=\"mailto:" . $formattedEmail . "\">" . $formattedEmail . "</a>";
        }

        $item['object'] = $object;
        $item['telephone'] = Telephone::getTelephonesHref($row['number']);
        $item['functie'] = $row['extra'];
		$item['location'] = $row['location'];
		$item['room'] = $row['room'];
		$item['fax'] = $row['fax'];
		$item['email'] = $formattedEmail;

        //
        $objects[] = $item;
	}

	//
	return $twig->render('special_numbers.html', array(
		'title' => Translations::get('header_specialnumbers')
		, 'isAdmin' => $isAdmin
		, 'lbl_specnumber_object' => Translations::get('lbl_specnumber_object')
		, 'lbl_specnumber_number' => Translations::get('lbl_specnumber_number')
		, 'lbl_specnumber_extra' => Translations::get('lbl_specnumber_extra')
		, 'lbl_specnumber_location' => Translations::get('lbl_specnumber_location')
		, 'lbl_specnumber_room' => Translations::get('lbl_specnumber_room')
		, 'lbl_specnumber_fax' => Translations::get('lbl_specnumber_fax')
		, 'lbl_specnumber_email' => Translations::get('lbl_specnumber_email')
		, 'objects' => $objects
	));
}
