<?php 
session_start(); ///////////////

//
$settings = array();
$menu = array();

if ( !isset($_SESSION["loginname"]) ) {
	$_SESSION["loginname"] = '';
}

//
require_once dirname(__FILE__) . "/../sites/default/presentornot.settings.php";
require_once dirname(__FILE__) . "/colors.inc.php";
require_once dirname(__FILE__) . "/_misc_functions.inc.php";

require_once dirname(__FILE__) . "/class_authentication.inc.php";
require_once dirname(__FILE__) . "/class_date.inc.php";
require_once dirname(__FILE__) . "/class_datetime.inc.php";
require_once dirname(__FILE__) . "/class_department.inc.php";
require_once dirname(__FILE__) . "/class_feestdag.inc.php";
require_once dirname(__FILE__) . "/class_holiday.inc.php";
require_once dirname(__FILE__) . "/class_menu.inc.php";
require_once dirname(__FILE__) . "/class_mysql.inc.php";
require_once dirname(__FILE__) . "/class_page.inc.php";
require_once dirname(__FILE__) . "/class_protime_user.inc.php";
require_once dirname(__FILE__) . "/class_protime_user_schedule.inc.php";
require_once dirname(__FILE__) . "/class_role_authorisation.inc.php";
require_once dirname(__FILE__) . "/class_settings.inc.php";
require_once dirname(__FILE__) . "/class_syncinfo.inc.php";
require_once dirname(__FILE__) . "/class_syncprotimemysql.inc.php";
require_once dirname(__FILE__) . "/class_tcdatetime.inc.php";
require_once dirname(__FILE__) . "/class_translations.inc.php";
require_once dirname(__FILE__) . "/class_website_protection.inc.php";

//
$protect = new class_website_protection();

//
$oWebuser = static_protime_user::getProtimeUserByLoginName( $_SESSION["loginname"] );

//
$menu[] = new class_menuitem('protime.presentornot', 'Present or not', 'presentornot.php');
$menu[] = new class_menuitem('protime.photobook', 'Photobook', 'photobook.php');
if ( $oWebuser->isTabAbsences() ) {
	$menu[] = new class_menuitem('protime.vakantie', 'Absences', 'absences.php');
}
$menu[] = new class_menuitem('protime.bhv', 'ERT (BHV)', 'bhv.php');
$menu[] = new class_menuitem('protime.ehbo', 'First Aid (EHBO)', 'ehbo.php');
if ( $oWebuser->isTabOntruimer() ) {
	$menu[] = new class_menuitem('protime.ontruimer', 'Evacuator (Ontruimer)', 'ontruimer.php');
}
$menu[] = new class_menuitem('protime.holidays', 'Nat. holidays', 'nationalholidays.php');
if ( $oWebuser->isTabFire() ) {
	$menu[] = new class_menuitem('protime.brand', 'Fire (Brand)', 'brand.php');
}

//
if ( !defined('ENT_XHTML') ) {
	define('ENT_XHTML', 32);
}
