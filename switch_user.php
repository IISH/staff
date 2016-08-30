<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

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
echo $oPage->getPage();

function createChangeUserContent() {
	global $protect, $settings;

	$fldUserName = '';

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

	$ret = "
<h1>" . Translations::get('menu_switch_user') . "</h1>

<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
<form name=\"frmA\" method=\"POST\">
<tr>
	<td>" . Translations::get('loginname') . ":</td>
	<td><input type=\"text\" name=\"fldUserName\" class=\"login\" maxlength=\"50\" value=\"" . $fldUserName . "\"> <i>" . Translations::get('loginname_help') . "</i></td>
</tr>
<tr>
	<td align=\"right\"><!-- <input class=\"button_login\" type=\"reset\" name=\"btnReset\" value=\"" . Translations::get('btn_clear') . "\"> // -->&nbsp;</td>
	<td>&nbsp;<input class=\"button_login\" type=\"submit\" name=\"btnSubmit\" value=\"" . Translations::get('btn_login') . "\"></td>
</tr>
</form>
</table>

<br>
<script language=\"javascript\">
<!--
document.frmA.fldUserName.focus();
// -->
</script>
";

	return $ret;
}