<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | Contact & Questions');
$oPage->setContent(createContactQuestionsContent( ));

// show page
echo $oPage->getPage();

// TODOEXPLAIN
function createContactQuestionsContent( ) {
	$ret = "<h2>Contact & Questions</h2><br>";
	$ret .= "Questions, bugs, comments, ideas, ... please contact the functional maintainer of this application <a href=\"mailto:" . class_settings::get("email_sender_email") . "\">" . class_settings::get("functional_maintainer") . "</a>.";

	return $ret;
}
