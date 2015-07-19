<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('contact'));
$oPage->setContent(createContactContent( ));

// show page
echo $oPage->getPage();

function createContactContent( ) {
	$ret = "<h2>" . Translations::get('contact') . "</h2><br>";
	$ret .= "Questions, bugs, comments, ideas, ... please contact the functional maintainer of this application <a href=\"mailto:" . Settings::get("email_sender_email") . "\">" . Settings::get("functional_maintainer") . "</a>.";

	return $ret;
}
