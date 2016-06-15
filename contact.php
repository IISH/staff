<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('contact'));
$oPage->setContent(createContactContent( ));

// show page
echo $oPage->getPage();

function createContactContent( ) {
	global $oWebuser;

	$ret = "<h1>" . Translations::get('contact') . "</h1>";

	$message = Translations::get('questions_bugs_comments');
	if ( $oWebuser->isLoggedIn() ) {
		$message = str_replace('::NAME::', "<a href=\"mailto:" . Settings::get("email_sender_email") . "\">" . Settings::get("functional_maintainer") . "</a>", $message);
	} else {
		$message = str_replace('::NAME::', Settings::get("functional_maintainer"), $message);
	}

	$ret .= $message;

	return $ret;
}
