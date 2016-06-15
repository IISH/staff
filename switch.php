<?php
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$switch = $protect->request('get', "s");
$value = $protect->requestPositiveNumberOrEmpty('get', "v");

switch ( $switch ) {
	case "smallerfont":
		$field = 'smaller_font_institute_prefix';
		$howManyChoices = 2;
		break;
	case "callviacomputer":
		$field = 'call_via_computer';
		$howManyChoices = 2;
		break;
	case "callviatablet":
		$field = 'call_via_tablet';
		$howManyChoices = 2;
		break;
	case "callviamobile":
		$field = 'call_via_mobile';
		$howManyChoices = 2;
		break;
	default:
		die('Error 742564: Unknown switch: ' . $switch);
}

$newValue = ($value+1)%$howManyChoices;

$query_update = "INSERT INTO Staff_user_settings (`user_id`, `setting`, `value`) VALUES (" . $oWebuser->getId() . ", '$field', '$newValue') ON DUPLICATE KEY UPDATE `value`='$newValue' ";
$stmt = $dbConn->getConnection()->prepare($query_update);
$stmt->execute();

$backurl = "user.php";
Header("location: " . $backurl);
die('go <a href="' . $backurl . '">back</a>');
