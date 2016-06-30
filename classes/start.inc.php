<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

//
$settings = array();
require_once dirname(__FILE__) . "/../sites/default/staff.settings.php";

if ( !isset($_SESSION["loginname"]) ) {
	$_SESSION["loginname"] = '';
}

//
require_once dirname(__FILE__) . "/_misc_functions.inc.php";
require_once dirname(__FILE__) . "/allowed_visible_absences.inc.php";
require_once dirname(__FILE__) . "/authentication.inc.php";
require_once dirname(__FILE__) . "/color.inc.php";
require_once dirname(__FILE__) . "/colors.inc.php";
require_once dirname(__FILE__) . "/date.inc.php";
require_once dirname(__FILE__) . "/datetime.inc.php";
require_once dirname(__FILE__) . "/department.inc.php";
require_once dirname(__FILE__) . "/feestdag.inc.php";
require_once dirname(__FILE__) . "/holiday.inc.php";
require_once dirname(__FILE__) . "/legenda.inc.php";
require_once dirname(__FILE__) . "/menu.inc.php";
require_once dirname(__FILE__) . "/page.inc.php";
require_once dirname(__FILE__) . "/pdo.inc.php";
require_once dirname(__FILE__) . "/protime_user.inc.php";
require_once dirname(__FILE__) . "/protime_user_schedule.inc.php";
require_once dirname(__FILE__) . "/role_authorisation.inc.php";
require_once dirname(__FILE__) . "/room.inc.php";
require_once dirname(__FILE__) . "/settings.inc.php";
require_once dirname(__FILE__) . "/syncinfo.inc.php";
require_once dirname(__FILE__) . "/syncprotimemysql.inc.php";
require_once dirname(__FILE__) . "/telephone.inc.php";
require_once dirname(__FILE__) . "/tcdatetime.inc.php";
require_once dirname(__FILE__) . "/translations.inc.php";
require_once dirname(__FILE__) . "/website_protection.inc.php";
require_once dirname(__FILE__) . "/Mobile_Detect.php";

//
$protect = new WebsiteProtection();

// connect to database
$dbConn = new class_pdo( $databases['default'] );

//
if ( !defined('ENT_XHTML') ) {
	define('ENT_XHTML', 32);
}

// date out criterium for solving the problem when date_in > date_out
$dateOutCriterium = " ( DATE_OUT='0' OR DATE_OUT>='" . date("Ymd") . "' OR ( DATE_IN > DATE_OUT AND DATE_IN <='" . date("Ymd") . "' ) ) ";

//
$oWebuser = static_protime_user::getProtimeUserByLoginName( $_SESSION["loginname"] );

$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

//
$menu = array();
$menu[] = new MenuItem(Translations::get('menu_presentornot'), 'presentornot.php');
$menu[] = new MenuItem(Translations::get('menu_photobook'), 'photobook.php');
if ( $oWebuser->hasAuthorisationTabAbsences() ) {
	$menu[] = new MenuItem(Translations::get('menu_absences'), 'absences.php');
}
$menu[] = new MenuItem(Translations::get('menu_specialnumbers'), 'special_numbers.php');
$menu[] = new MenuItem(Translations::get('menu_ert'), 'ert.php');
$menu[] = new MenuItem(Translations::get('menu_firstaid'), 'firstaid.php');
if ( $oWebuser->hasAuthorisationTabOntruimer() || $oWebuser->isOntruimer() ) {
	$menu[] = new MenuItem(Translations::get('menu_evacuator'), 'evacuators.php');
}
$menu[] = new MenuItem(Translations::get('menu_nationalholidays'), 'nationalholidays.php');
if ( $oWebuser->isSuperAdmin() ) {
	$menu[] = new MenuItem(Translations::get('menu_switch_user'), 'switch_user.php');
}
if ( $oWebuser->hasAuthorisationTabFire() ) {
	$menu[] = new MenuItem(Translations::get('menu_fire'), 'fire.php', 'fire');
}
//$menu[] = new MenuItem(Translations::get('menu_contact'), 'contact.php');
