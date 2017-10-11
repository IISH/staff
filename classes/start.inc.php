<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

//
$settings = array();
require_once __DIR__ . "/../sites/default/staff.settings.php";

//
if ( !isset($_SESSION["loginname"]) ) {
	$_SESSION["loginname"] = '';
}

//
require_once __DIR__ . "/_misc_functions.inc.php";
require_once __DIR__ . "/absence_calendar.inc.php";
require_once __DIR__ . "/absence_calendar_format.inc.php";
require_once __DIR__ . "/allowed_visible_absences.inc.php";
require_once __DIR__ . "/authentication.inc.php";
require_once __DIR__ . "/color.inc.php";
require_once __DIR__ . "/colors.inc.php";
require_once __DIR__ . "/date.inc.php";
require_once __DIR__ . "/datetime.inc.php";
require_once __DIR__ . "/department.inc.php";
require_once __DIR__ . "/feestdag.inc.php";
require_once __DIR__ . "/holiday.inc.php";
require_once __DIR__ . "/legenda.inc.php";
require_once __DIR__ . "/menu.inc.php";
require_once __DIR__ . "/page.inc.php";
require_once __DIR__ . "/pdo.inc.php";
require_once __DIR__ . "/protime_user.inc.php";
require_once __DIR__ . "/protime_user_schedule.inc.php";
require_once __DIR__ . "/role_authorisation.inc.php";
require_once __DIR__ . "/room.inc.php";
require_once __DIR__ . "/settings.inc.php";
require_once __DIR__ . "/syncinfo.inc.php";
require_once __DIR__ . "/syncprotimemysql.inc.php";
require_once __DIR__ . "/telephone.inc.php";
require_once __DIR__ . "/tcdatetime.inc.php";
require_once __DIR__ . "/translations.inc.php";
require_once __DIR__ . "/website_protection.inc.php";
require_once __DIR__ . "/Mobile_Detect.php";

//
$protect = new WebsiteProtection();

// connect to database
$dbConn = new class_pdo( $databases['default'] );

//
if ( !defined('ENT_XHTML') ) {
	define('ENT_XHTML', 32);
}

// date out criterium for solving the problem when date_in > date_out
$dateOutCriterium = " ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' OR ( DATE_IN > DATE_OUT AND DATE_IN <='" . date("Ymd") . "' ) OR staff_today_checkinout.BOOKDATE IS NOT NULL ) ";

//
$oWebuser = static_protime_user::getProtimeUserByLoginName( $_SESSION["loginname"] );

$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

// check fire key
if ( !isset( $_SESSION["FIRE_KEY_CORRECT"] ) ) {
	$_SESSION["FIRE_KEY_CORRECT"] = '';
}
if ( $_SESSION["FIRE_KEY_CORRECT"] != '1' ) {
	$fire_key = '';
	if (isset($_GET["fire_key"])) {
		$fire_key = $_GET["fire_key"];
	}
	if (trim($fire_key) == Settings::get('fire_key')) {
		$_SESSION["FIRE_KEY_CORRECT"] = '1';
	}
}

//
$menu = array();
$menu[] = new MenuItem(Translations::get('menu_presentornot'), 'presentornot.php');
//$menu[] = new MenuItem(Translations::get('menu_photobook'), 'photobook.php');
if ( $oWebuser->hasAuthorisationTabAbsences() ) {
	$menu[] = new MenuItem(Translations::get('menu_absences'), 'absences.php');
}
$menu[] = new MenuItem(Translations::get('menu_specialnumbers'), 'special_numbers.php');
$menu[] = new MenuItem(Translations::get('menu_ert'), 'ert.php');
$menu[] = new MenuItem(Translations::get('menu_firstaid'), 'firstaid.php');
if ( $oWebuser->hasAuthorisationTabOntruimer() || $oWebuser->isOntruimer() ) {
	$menu[] = new MenuItem(Translations::get('menu_evacuator'), 'evacuators.php');
}
$menu[] = new MenuItem(Translations::get('menu_print'), 'print.php');
$menu[] = new MenuItem(Translations::get('menu_nationalholidays'), 'nationalholidays.php');
if ( $oWebuser->isSuperAdmin() ) {
	$menu[] = new MenuItem(Translations::get('menu_switch_user'), 'switch_user.php');
}
if ( $_SESSION["FIRE_KEY_CORRECT"] == '1' || $oWebuser->hasAuthorisationTabFire() ) {
	$menu[] = new MenuItem(Translations::get('menu_fire'), 'fire.php', 'fire');
}
//$menu[] = new MenuItem(Translations::get('menu_contact'), 'contact.php');
