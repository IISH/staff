<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('header_user'));
$oPage->setContent(createUserContent( ));

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createUserContent( ) {
    global $oWebuser, $twig;

    // photo
	$photo = $oWebuser->getPhoto();
	$alttitle = '';
	if ( !checkPhotoExists($photo) ) {
		if ( $oWebuser->isAdmin() ) {
			$alttitle = 'Missing photo: &quot;' . $oWebuser->getDefaultPhoto() . '&quot;';
		}
		$photo = Settings::get('noimage_file');
	}
	$photo = "<img src=\"$photo\" title=\"$alttitle\">";

	// get check in/out status
	$status = getCurrentDayCheckInoutState($oWebuser->getId());
	if ( $status["status_text"] == '' ) {
		$status["status_text"] = '&nbsp;';
	}

	// SCHEDULE
	$currentSchedule = new ProtimeUserSchedule($oWebuser->getId(), date("Ymd"));

	// links to bhv/ehbo/ontruimers
	$bhv_link = createUrl( array( 'url' => 'ert.php', 'label' => Translations::get('lbl_ert') ) );
	$ehbo_link = createUrl( array( 'url' => 'firstaid.php', 'label' => Translations::get('lbl_firstaid') ) );
	if ( $oWebuser->hasAuthorisationTabOntruimer() || $oWebuser->isOntruimer() ) {
		$ontruimers_link = createUrl( array( 'url' => 'evacuators.php', 'label' => Translations::get('lbl_evacuator') ) );
	} else {
		$ontruimers_link = Translations::get('lbl_evacuator');
	}

	//
	return $twig->render('user.twig', array(
		'title' => Translations::get('header_user')
		, 'photo' => $photo
		, 'lblName' => Translations::get('lbl_name')
		, 'name' => $oWebuser->getNiceFirstLastname()
		, 'lblCheckInOut' => Translations::get('lbl_check_inout')
		, 'status_color' => $status["status_color"]
		, 'status_text' => $status["status_text"]
		, 'status_alt' => $status["status_alt"]
		, 'lblRoom' => Translations::get('lbl_room')
		, 'room' => static_Room::createRoomUrl($oWebuser->getRoom())
		, 'lblTelephone' => Translations::get('lbl_telephone')
		, 'telephone' => Telephone::getTelephonesHref($oWebuser->getTelephones())
		, 'lblEmail' => Translations::get('lbl_email')
		, 'email' => $oWebuser->getEmail()
		, 'lblDepartment' => Translations::get('lbl_department')
		, 'department' => ( is_object($oWebuser->getDepartment()) ? $oWebuser->getDepartment()->getShort() : '' )
		, 'lblBhv' => $bhv_link
		, 'bhv' => ( $oWebuser->isBhv() ? Translations::get('yes') : Translations::get('no') )
		, 'lblEhbo' => $ehbo_link
		, 'ehbo' => ( $oWebuser->isEhbo() ? Translations::get('yes') : Translations::get('no') )
		, 'lblOntruimer' => $ontruimers_link
		, 'ontruimer' => ( $oWebuser->isOntruimer() ? Translations::get('yes') : Translations::get('no') )
		, 'lblSchedule' => Translations::get('lbl_schedule')
		, 'schedule' => $currentSchedule->getCurrentSchedule()
		, 'lblBadgenr' => Translations::get('lbl_badgenr')
		, 'badgenr' => $oWebuser->getBadgenr()
	));
}
