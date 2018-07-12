<?php
$doPing = false;
require_once "classes/start.inc.php";

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | Login');
$oPage->setContent(createLoginPage());

// show page
echo $twig->render('design.twig', $oPage->getPageAttributes() );

function createLoginPage() {
	global $protect, $twig;

	$fldLogin = '';
	$error = '';

	//
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
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

			// try to authenticate
			$result_login_check = Authentication::authenticate($fldLogin, $fldPassword);

			if ($result_login_check == 1) {
				// authenticated and authorised
				$alert = '';
				// als login een punt bevat dan is het een iisg account, toon melding op scherm
				if (strpos($fldLogin, '.') !== false) {
					$alert = '?alert=next_time';
				}

				// retain login name
				$_SESSION["loginname"] = Authentication::getLoginPart($fldLogin);

				//
				$burl = getBackUrl();
				if ($burl == '') {
					$burl = 'presentornot.php';
				}
				Header("Location: " . $burl . $alert);
				die(Translations::get('go_to') . " <a href=\"" . $burl . $alert . "\">next</a>");
			} elseif ( $result_login_check == 2 ) {
				// authenticated but not authorised
				// show error
				$error .= "You are not authorized to use this application. Please contact IT department.";
			} else {
				// not authenticated
				// show error
				$error .= "User/Password combination incorrect.";
			}
		} else {
			// show error
			$error .= "Both field are required.<br>";
		}
	}

	return $twig->render('login.twig', array(
		'title' => Translations::get('please_log_in')
		, 'your_login_credentials_are' => Translations::get('your_login_credentials_are')
		, 'error' => $error
		, 'loginname' => $fldLogin
		, 'action' => "?" . $_SERVER["QUERY_STRING"]
		, 'btn_login' => Translations::get('btn_login')
		, 'loginname_placeholder' => Translations::get('loginname_placeholder')
		, 'loginname_help' => Translations::get('loginname_help')
		, 'password_placeholder' => Translations::get('password_placeholder')
		, 'lblPassword' => Translations::get('password')
		, 'lblLoginname' => Translations::get('loginname')
		, 'focusjavascriptcode' => "
<script language=\"javascript\">
<!--
document.frmA.fldLogin.focus();
// -->
</script>
"
	));
}
