<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('contact'));
$oPage->setContent(createContactContent( ));

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createContactContent( ) {
	global $oWebuser, $twig;

	$message = Translations::get('questions_bugs_comments');
	if ( $oWebuser->isLoggedIn() ) {
		$message = str_replace('::NAME::', "<a href=\"mailto:" . Settings::get("email_sender_email") . "\">" . Settings::get("functional_maintainer") . "</a>", $message);
	} else {
		$message = str_replace('::NAME::', Settings::get("functional_maintainer"), $message);
	}

	return $twig->render('contact.twig', array(
		'title' => Translations::get('contact')
		, 'message' => $message
	));
}
