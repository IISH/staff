<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | Login');
$oPage->setContent(createLoginPage());

// show page
echo $oPage->getPage();

function createLoginPage() {
	global $protect, $settings;

	$fldLogin = '';
	$error = '';

	if ( $protect->requestPositiveNumberOrEmpty('post', 'issubmitted') == '1' ) {
		// get values
		$fldLogin = $protect->request('post', 'fldLogin');
		$fldPassword = $protect->request('post', 'fldPassword');

		// quick protect
		$fldLogin = str_replace(array(';', ':', '!', '<', '>', '(', ')', '%'), ' ', $fldLogin);

		// trim
		$fldLogin = trim($fldLogin);
		$fldPassword = trim($fldPassword);

		// use the left part until the space
		$fldLogin = $protect->get_left_part($fldLogin, ' ');

		// check if both field are entered
		if ( $fldLogin != '' && $fldPassword != '' ) {

			$result_login_check = Authentication::authenticate($fldLogin, $fldPassword);

			if ( $result_login_check == 1 ) {
				// retain login name
				$_SESSION["loginname"] = $fldLogin;

				//
				$burl = 'presentornot.php';
				Header("Location: " . $burl);
				die(Translations::get('go_to') . " <a href=\"" . $burl . "\">next</a>");
			} else {
				// show error
				$error .= "User/Password combination incorrect.";
			}
		} else {
			// show error
			$error .= "Both field are required.<br>";
		}
	}

	$ret = "
<h2>" . Translations::get('please_log_in') . "</h2>
";

	if ( $error != '' ) {
		$ret .= "<span class=\"error\">" . $error . "</span><br>";
	}

	$ret .= "
<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
<form name=\"frmA\" method=\"POST\">
<input type=\"hidden\" name=\"issubmitted\" value=\"1\">
<tr>
	<td>" . Translations::get('loginname') . ":</td>
	<td><input type=\"text\" name=\"fldLogin\" class=\"login\" maxlength=\"50\" value=\"" . $fldLogin . "\"> <i>" . Translations::get('loginname_help') . "</i></td>
</tr>
<tr>
	<td>" . Translations::get('password') . ":&nbsp;</td>
	<td><input type=\"password\" name=\"fldPassword\" class=\"password\" maxlength=\"50\" value=\"\"></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align=\"right\"><input class=\"button_login\" type=\"reset\" name=\"btnReset\" value=\"" . Translations::get('btn_clear') . "\">&nbsp;</td>
	<td>&nbsp;<input class=\"button_login\" type=\"submit\" name=\"btnSubmit\" value=\"" . Translations::get('btn_login') . "\"></td>
</tr>
</form>
</table>

<script language=\"javascript\">
<!--
document.frmA.fldLogin.focus();
// -->
</script>
";

	return $ret;
}
