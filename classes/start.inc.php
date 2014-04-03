<?php 
session_start(); ///////////////

//
$settings = array();
$menu = array();

//
require_once dirname(__DIR__) . "/sites/default/settings.inc.php";
require_once "classes/colors.inc.php";
require_once "classes/class_authentication.inc.php";
require_once "classes/class_date.inc.php";
require_once "classes/class_datetime.inc.php";
require_once "classes/class_employee.inc.php";
require_once "classes/class_holiday.inc.php";
require_once "classes/class_page.inc.php";
require_once "classes/class_website_protection.inc.php";
require_once "classes/class_protime_user.inc.php";
require_once "classes/class_menu.inc.php";
require_once "classes/class_settings.inc.php";
require_once "classes/_misc_functions.inc.php";
require_once "classes/_db_connect_timecard.inc.php";
require_once "classes/_db_connect_presentornot.inc.php";

//
$protect = new class_website_protection();
$protect->class_website_protection($settings);

//
$oWebuser = new class_employee($_SESSION["presentornot"]["name"], $settings);

//
$menu[] = new class_menuitem('protime.presentornot', 'Present or not', 'present_or_not.php');
$menu[] = new class_menuitem('protime.vakantie', 'Absences', 'absences.php');
$menu[] = new class_menuitem('protime.holidays', 'Holidays', 'holidays.php');

// settings from database
$settings_from_database = class_settings::getSettings( $dbhandlePresentornot );
