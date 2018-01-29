<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->isSuperAdmin() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">staff home</a>');
}

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Timecard | Admin pages');
$oPage->setContent(createChangeUserContent());

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createChangeUserContent() {
	global $protect, $twig;

	return $twig->render('admin.twig', array(
		'title' => 'Admin pages'
		, 'switch_user' => Translations::get('menu_switch_user')
	));
}