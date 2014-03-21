<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | Login');
$oPage->setContent(createLoginPage());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createLoginPage() {
	global $protect, $settings;

	$fldLogin = '';
	$fldPassword = '';
	$error = '';

	if ( $protect->request_positive_number_or_empty('post', 'issubmitted') == '1' ) {
		// get values
		$fldLogin = $protect->request('post', 'fldLogin');
		$fldPassword = $protect->request('post', 'fldPassword');
		$burl = trim($protect->request('get', 'burl'));

		// quick protect
		$fldLogin = str_replace(array(';', ':', '!', '<', '>', '(', ')', '%'), ' ', $fldLogin);

		// remove domainnames
		$fldLogin = str_replace(array('@iisg.nl', '@iisg.net', 'iisgnet\\'), ' ', $fldLogin);

		// trim
		$fldLogin = trim($fldLogin);
		$fldPassword = trim($fldPassword);

		// use the left part until the space
		$fldLogin = $protect->get_left_part($fldLogin, ' ');

		// check if both field are entered
		if ( $fldLogin != '' && $fldPassword != '' ) {

			$result_login_check = class_authentication::authenticate($fldLogin, $fldPassword);

			if ( $result_login_check == 1 ) {
				// save id
				$_SESSION["presentornot"]["name"] = $fldLogin;

				// 
				$burl = 'present_or_not.php';
				Header("Location: " . $burl);
				die("Go to <a href=\"" . $burl . "\">next</a>");
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
<h2>Please log in...</h2>
";

	if ( $error != '' ) {
		$ret .= "<span class=\"error\">" . $error . "</span><br>";
	}

	$ret .= "
<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
<form name=\"frmA\" method=\"POST\">
<input type=\"hidden\" name=\"issubmitted\" value=\"1\">
<tr>
	<td>Login name:</td>
	<td><input type=\"text\" name=\"fldLogin\" class=\"login\" maxlength=\"50\" value=\"" . $fldLogin . "\"> <i>(firstname.lastname)</i></td>
</tr>
<tr>
	<td>Password:&nbsp;</td>
	<td><input type=\"password\" name=\"fldPassword\" class=\"password\" maxlength=\"50\" value=\"\"></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align=\"right\"><input class=\"button_login\" type=\"reset\" name=\"btnReset\" value=\"Clear\">&nbsp;</td>
	<td>&nbsp;<input class=\"button_login\" type=\"submit\" name=\"btnSubmit\" value=\"Submit\"></td>
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
?>