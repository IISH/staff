<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

$id = substr(trim($protect->requestPositiveNumberOrEmpty('get', "id")), 0, 4);

$staff = new ProtimeUser($id);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle($staff->getNiceFirstLastname() . ' - ' . Translations::get('iisg_employee'));
$oPage->setContent(createStaffContent( $staff ));

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createStaffContent( $staff ) {
	global $oWebuser, $twig;

	// go back
	$goback = ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
	if ( $goback == '' ) {
		$goback = 'presentornot.php';
	} else {
		$goback = stripDomainnameFromUrl( $goback );
	}
	$goback = createUrl( array( 'url' => $goback, 'label' => Translations::get('go_back') ) );

	// get check in/out status
	$status = getCurrentDayCheckInoutState($staff->getId());
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}

	//
	$photo = $staff->getPhoto();
	// TODOGCU
	$alttitle = '';
	if ( checkPhotoExists(Settings::get('staff_images_directory') . $photo) ) {
		$photo = Settings::get('staff_images_directory') . $photo;
	} else {
		if ( $oWebuser->isAdmin() ) {
			$alttitle = 'Missing photo: &quot;' . Settings::get('staff_images_directory') . $photo . '&quot;';
		}
		$photo = Settings::get('noimage_file');
	}
	$photo = "<img src=\"$photo\" style=\"height:140px;\" title=\"$alttitle\">";

	//
	if ( $oWebuser->hasAuthorisationTabOntruimer() || $oWebuser->isOntruimer() ) {
		$ontruimers_link = createUrl( array( 'url' => 'evacuators.php', 'label' => Translations::get('lbl_evacuator') ) );
	} else {
		$ontruimers_link = Translations::get('lbl_evacuator');
	}

	// UITDIENST
	$dateOut = $staff->getDateOut();
	if ( $dateOut == '0' || $dateOut > date("Ymd") ) {
		$dateOut = '';
	} else {
		$dateOut = class_datetime::formatDate($dateOut);
	}

	// SCHEDULE
	$currentSchedule = new ProtimeUserSchedule($staff->getId(), date("Ymd"));

	//
	return $twig->render('employee.twig', array(
		'title' => $staff->getNiceFirstLastname()
	, 'photo' => $photo
	, 'lbl_name' => Translations::get('lbl_name')
	, 'name' => $staff->getNiceFirstLastname()
	, 'go_back' => $goback
	, 'lbl_check_inout' => Translations::get('lbl_check_inout')
	, 'status_color' => $status["status_color"]
	, 'status_alt' => $status["status_alt"]
	, 'status_text' => $status["status_text"]
	, 'lbl_room' => Translations::get('lbl_room')
	, 'room' => static_Room::createRoomUrl($staff->getRoom())
	, 'lbl_telephone' => Translations::get('lbl_telephone')
	, 'telephone' => Telephone::getTelephonesHref($staff->getTelephones())
	, 'lbl_email' => Translations::get('lbl_email')
	, 'email' => $staff->getEmail()
	, 'lbl_department' => Translations::get('lbl_department')
	, 'department' => $staff->getDepartment()->getShort()
	, 'lbl_ert' => createUrl( array( 'url' => 'ert.php', 'label' => Translations::get('lbl_ert') ) )
	, 'ert' => ( $staff->isBhv() ? Translations::get('yes') : Translations::get('no') )
	, 'lbl_firstaid' => createUrl( array( 'url' => 'firstaid.php', 'label' => Translations::get('lbl_firstaid') ) )
	, 'firstaid' => ( $staff->isEhbo() ? Translations::get('yes') : Translations::get('no') )
	, 'lbl_evacuator' => $ontruimers_link
	, 'evacuator' => ( $staff->isOntruimer() ? Translations::get('yes') : Translations::get('no') )
	, 'lbl_date_out' => Translations::get('lbl_date_out')
	, 'date_out' => $dateOut
	, 'lbl_schedule' => Translations::get('lbl_schedule')
	, 'schedule' => $currentSchedule->getCurrentSchedule()
	, 'isAdmin' => ( $oWebuser->isAdmin() ? 1 : 0 )
	, 'lbl_authorisation' => Translations::get('lbl_authorisation')
//		, 'authorisation' => implode('<br>', $staff->getAuthorisations())
	, 'lblBadgenr' => Translations::get('lbl_badgenr')
	, 'badgenr' => $staff->getBadgenr()
	, 'hasBadgeAuthorisation' => $staff->hasAuthorisationTabFire() || $staff->getId() == $oWebuser->getId()
	, 'lbl_afwezig' => Translations::get('lbl_afwezig')
	, 'afwezig' => convertAfwezigDatesToNiceString($staff->getAfwezigDates())
	));
}

function convertAfwezigDatesToNiceString( $arr ) {
	$ret = '';
	if ( count($arr) == 0 ) {
		return $ret;
	}

	$lastDate = '';
	$range = array();
	$datesRange = array();
	foreach ( $arr as $stringDate ) {
		$date = strtotime( $stringDate );

		if ( count($range) == 0 ) {
			// als range nog leeg, dan nog geen begin datum
			$range[] = $date;
		} else {
			// er is al een begindatum

			// calculate verschil tussen dagen
			if ( ($date - $lastDate)/86400 == 1 ) {
				// 1 dag verschil
				$range[1] = $date;

			} else {
				// meerdere dagen verschil

				// bewaar eerst de oude
				$datesRange[] = $range;

				// begin nieuwe range
				unset($range);
				$range = array();
				$range[] = $date;
			}
		}

		$lastDate = $date;
	}

	if ( count($range) > 0 ) {
		$datesRange[] = $range;
	}

	foreach ($datesRange as $dayOrRange ) {
		$ret .= substr(Translations::get("day" . date("w", ($dayOrRange[0]))), 0,2);
		$ret .= ' ' . date("j", ($dayOrRange[0]));
		$ret .= ' ' . substr(Translations::get("month" . date("m", ($dayOrRange[0]))),0,3);

		if ( isset( $dayOrRange[1] ) ) {
			$ret .= ' - ';
			$ret .= substr(Translations::get("day" . date("w", ($dayOrRange[1]))), 0,2);
			$ret .= ' ' . date("j", ($dayOrRange[1]));
			$ret .= ' ' . substr(Translations::get("month" . date("m", ($dayOrRange[1]))),0,3);
		}
		$ret .= '<br>';
	}

	return $ret;
}
