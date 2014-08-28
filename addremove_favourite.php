<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

//
$id = substr(trim($protect->request_positive_number_or_empty('get', "id")), 0, 4);
$dowhat = substr(trim($_GET["dowhat"]), 0, 1);
if ( !in_array($dowhat, array('a', 'r') ) ) {
	die('Error 541642716');
}

$fav = substr(trim($protect->request_only_characters_or_numbers_or_empty('get', "fav")), 0, 10);
if ( !in_array($fav, array('', 'vakantie', 'present', 'checkinout', 'absences') ) ) {
	die('Error 421794532 Unknown favourite: ' . $fav);
}

// 
$query = '';
if ( $dowhat == 'a' ) {
	// add to database
	$query = 'INSERT INTO Favourites(user, ProtimeID, type) VALUES(\'' . $oWebuser->getUser() . '\', ' . $id . ', \'' . $fav . '\') ';
	// show remove button in window
	if ( $fav == 'checkinout' ) {
		$alttitle = "Click to remove the 'check in' email notification";
		$div = '<a href="#" onClick="checkInOut(' . $id . ', \'r\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink"><img src="images/clock-red.png" border=0></a>';
	} else {
		$alttitle = "Click to remove the person from your favourites";
		$div = '<a href="#" onClick="addRemove(' . $id . ', \'r\');" alt="' . $alttitle . '" title="' . $alttitle . '"><img src="images/favourites-on.png" border=0></a>';
	}
} elseif ( $dowhat == 'r' ) {
	// remove from database
	$query = 'DELETE FROM Favourites WHERE user=\'' . $oWebuser->getUser() . '\' AND ProtimeID=' . $id . ' AND type=\'' . $fav . '\' ';
	// show add button in window
	if ( $fav == 'checkinout' ) {
		$alttitle = "Click to get a 'check in' email notification";
		$div = '<a href="#" onClick="checkInOut(' . $id . ', \'a\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink"><img src="images/clock-black.png" border=0></a>';
	} else {
		$alttitle = "Click to add the person from your favourites";
		$div = '<a href="#" onClick="addRemove(' . $id . ', \'a\');" alt="' . $alttitle . '" title="' . $alttitle . '"><img src="images/favourites-off.png" border=0></a>';
	}
}

$oConn = new class_mysql($settings, 'presentornot');
$oConn->connect();

if ( $query != '' ) {
	$resultSelect = mysql_query($query, $oConn->getConnection());

	echo $div;
} else {
	echo 'Error';
}
