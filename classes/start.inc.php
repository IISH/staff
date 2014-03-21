<?php 
ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

session_start(); ///////////////

require_once "classes/settings.inc.php";
require_once "classes/colors.inc.php";
require_once "classes/class_authentication.inc.php";
require_once "classes/class_date.inc.php";
require_once "classes/class_datetime.inc.php";
require_once "classes/class_employee.inc.php";
require_once "classes/class_feestdag.inc.php";
require_once "classes/class_page.inc.php";
require_once "classes/class_website_protection.inc.php";
require_once "classes/class_protime_user.inc.php";
require_once "classes/_misc_functions.inc.php";
require_once "classes/_db_connect_timecard.inc.php";
require_once "classes/_db_connect_presentornot.inc.php";

//
$protect = new class_website_protection();
$protect->class_website_protection($settings);

//
$oWebuser = new class_employee($_SESSION["presentornot"]["name"], $settings);

//
require_once "classes/class_menu.inc.php";

// make sublist depending on authentication
$menuList = $menu->getMenuSubset();
