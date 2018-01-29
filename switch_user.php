<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->isSuperAdmin() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">staff home</a>');
}

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Timecard | Switch user');
$oPage->setContent(createChangeUserContent());

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createChangeUserContent() {
	global $protect, $twig;

	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		// get values
		$fldUserName = $protect->request('post', 'fldUserName');

		// quick protect
		$fldUserName = str_replace(array(';', ':', '!', '<', '>', '(', ')', '%'), ' ', $fldUserName);

		// remove domainnames
		$fldUserName = str_replace(array('@iisg.nl', '@iisg.net', 'iisgnet\\'), ' ', $fldUserName);

		// trim
		$fldUserName = trim($fldUserName);

		// use the left part until the space
		$fldUserName = $protect->get_left_part($fldUserName, ' ');

		// check if field is not empty
		if ( $fldUserName != '' ) {

			$_SESSION["loginname"] = $fldUserName;
			// redirect to ...
			$burl = 'index.php';
			Header("Location: " . $burl);
			die(Translations::get('go_to') . " <a href=\"" . $burl . "\">next</a>");
		}
	}

	return $twig->render('switch_user.twig', array(
		'title' => Translations::get('menu_switch_user')
		, 'btn_login' => Translations::get('btn_login')
		, 'lblLoginname' => Translations::get('loginname')
		, 'loginname_placeholder' => Translations::get('loginname_placeholder')
		, 'focusjavascriptcode' => "
<script language=\"javascript\">
<!--
	document.frmA.fldUserName.focus();
// -->
</script>
"
	));
}