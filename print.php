<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('header_print'));
$oPage->setContent(createSpecialNumbersContent( ));

// show page
echo $oPage->getPage();

// disconnect database connection
$dbConn->close();

function createSpecialNumbersContent( ) {
	global $oWebuser;

	$ret = "
<h1>" . Translations::get('header_print') . "</h1>
<i><font size=-1>(" . Translations::get('sorted_on') . ":)</font></i><br>
<ol>
	<li><a href=\"print_firstname.php\" target=\"_blank\">" . Translations::get('lbl_firstname') . " <img src=\"images/misc/popup.png\"></a></li>
	<li><a href=\"print_lastname.php\" target=\"_blank\">" . Translations::get('lbl_lastname') . " <img src=\"images/misc/popup.png\"></a>";

	// show fire link only when allowed to see the fire tab
	if ( $_SESSION["FIRE_KEY_CORRECT"] == '1' || $oWebuser->hasAuthorisationTabFire() ) {
		$ret .= "<br><br>" . Translations::get('or_go_to') . "<br><br></li>
			<li><a href=\"fire.php\">" . strtoupper(Translations::get('menu_fire')) . "</a><br><br>";
	}

	$ret .= "</li>
</ol>
";

	return $ret;
}
