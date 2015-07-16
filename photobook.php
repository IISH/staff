<?php
require_once "classes/class_translations.inc.php";
require_once "classes/class_mysql.inc.php";
require_once "sites/default/staff.settings.php";

$pageSettings = array(
	'title' => class_translations::get('menu_photobook')
	, 'layout' => '2'
);
require "presentornot_main.php";
