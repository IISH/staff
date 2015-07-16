<?php
require_once "classes/start.inc.php";

$pageSettings = array(
	'title' => class_translations::get('menu_presentornot')
	, 'layout' => '1'
);
require "presentornot_main.php";
