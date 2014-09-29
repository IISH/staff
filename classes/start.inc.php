<?php 
session_start(); ///////////////

//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

//
$settings = array();
$menu = array();

if ( !isset($_SESSION["presentornot"]["name"]) ) {
	$_SESSION["presentornot"]["name"] = '';
}

//
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/colors.inc.php";
require_once dirname(__FILE__) . "/class_authentication.inc.php";
require_once dirname(__FILE__) . "/class_date.inc.php";
require_once dirname(__FILE__) . "/class_datetime.inc.php";
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/class_feestdag.inc.php";
require_once dirname(__FILE__) . "/class_holiday.inc.php";
require_once dirname(__FILE__) . "/class_page.inc.php";
require_once dirname(__FILE__) . "/class_website_protection.inc.php";
require_once dirname(__FILE__) . "/class_protime_user.inc.php";
require_once dirname(__FILE__) . "/class_menu.inc.php";
require_once dirname(__FILE__) . "/class_settings.inc.php";
require_once dirname(__FILE__) . "/_misc_functions.inc.php";
require_once dirname(__FILE__) . "/class_mysql.inc.php";
require_once dirname(__FILE__) . "/class_syncprotimemysql.inc.php";

//
$protect = new class_website_protection();

//
$oWebuser = new class_employee($_SESSION["presentornot"]["name"], $settings);

//
$menu[] = new class_menuitem('protime.presentornot', 'Present or not', 'presentornot.php');
$menu[] = new class_menuitem('protime.vakantie', 'Absences', 'absences.php');
$menu[] = new class_menuitem('protime.bhv', 'BHV', 'bhv.php');
$menu[] = new class_menuitem('protime.ehbo', 'EHBO', 'ehbo.php');
$menu[] = new class_menuitem('protime.holidays', 'National holidays', 'nationalholidays.php');

//
define('ENT_XHTML', 32);
