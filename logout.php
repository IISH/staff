<?php 
require_once "classes/start.inc.php";

$_SESSION["presentornot"]["name"] = '';

Header("Location: login.php");
die();
?>