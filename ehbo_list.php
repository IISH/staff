<?php
require_once "classes/class_translations.inc.php";
require_once "classes/class_mysql.inc.php";
require_once "sites/default/staff.settings.php";

$type_of_beo = "e";
$label = class_translations::get('menu_firstaid');

require_once "beo_list.php";
