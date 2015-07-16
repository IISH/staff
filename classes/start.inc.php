<?php 
session_start(); ///////////////

//
$settings = array();
$menu = array();

if ( !isset($_SESSION["loginname"]) ) {
	$_SESSION["loginname"] = '';
}

//
require_once dirname(__FILE__) . "/../sites/default/staff.settings.php";
require_once dirname(__FILE__) . "/colors.inc.php";
require_once dirname(__FILE__) . "/_misc_functions.inc.php";

require_once dirname(__FILE__) . "/class_allowed_visible_absences.inc.php";
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
$menu[] = new class_menuitem('protime.presentornot', class_translations::get('menu_presentornot'), 'presentornot.php');
$menu[] = new class_menuitem('protime.photobook', class_translations::get('menu_photobook'), 'photobook.php');
if ( $oWebuser->hasAuthorisationTabAbsences() ) {
	$menu[] = new class_menuitem('protime.vakantie', class_translations::get('menu_absences'), 'absences.php');
}
$menu[] = new class_menuitem('protime.bhv', class_translations::get('menu_ert'), 'bhv.php');
$menu[] = new class_menuitem('protime.ehbo', class_translations::get('menu_firstaid'), 'ehbo.php');
if ( $oWebuser->hasAuthorisationTabOntruimer() ) {
	$menu[] = new class_menuitem('protime.ontruimer', class_translations::get('menu_evacuator'), 'ontruimer.php');
}
$menu[] = new class_menuitem('protime.holidays', class_translations::get('menu_nationalholidays'), 'nationalholidays.php');
if ( $oWebuser->hasAuthorisationTabFire() ) {
	$menu[] = new class_menuitem('protime.brand', class_translations::get('menu_fire'), 'brand.php');
}

//
if ( !defined('ENT_XHTML') ) {
	define('ENT_XHTML', 32);
}
