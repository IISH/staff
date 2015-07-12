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
	$query = 'INSERT INTO Staff_favourites(user, ProtimeID, type) VALUES(\'' . $oWebuser->getLoginname() . '\', ' . $id . ', \'' . $fav . '\') ';
	// show remove button in window
	if ( $fav == 'checkinout' ) {
		$alttitle = "Click to remove the 'checked in' email notification";
		$div = '<a href="#" onClick="checkInOut(' . $id . ', \'r\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-red.png" border=0></a>';
	} else {
		$alttitle = "Click to remove the person from your favourites";
		$div = '<a href="#" onClick="addRemove(' . $id . ', \'r\');" title="' . $alttitle . '" class="nolink favourites_on">&#9733;</a>';
	}
} elseif ( $dowhat == 'r' ) {
	// remove from database
	$query = 'DELETE FROM Staff_favourites WHERE user=\'' . $oWebuser->getLoginname() . '\' AND ProtimeID=' . $id . ' AND type=\'' . $fav . '\' ';
	// show add button in window
	if ( $fav == 'checkinout' ) {
		$alttitle = "Click to get a 'checked in' email notification when user checks in.";
		$div = '<a href="#" onClick="checkInOut(' . $id . ', \'a\');" title="' . $alttitle . '" class="nolink"><img src="images/misc/clock-black.png" border=0></a>';
	} else {
		$alttitle = "Click to add the person to your favourites";
		$div = '<a href="#" onClick="addRemove(' . $id . ', \'a\');" title="' . $alttitle . '" class="nolink favourites_off">&#9733;</a>';
	}
}

$oConn = new class_mysql($databases['default']);
$oConn->connect();

if ( $query != '' ) {
	$resultSelect = mysql_query($query, $oConn->getConnection());

	echo $div;
} else {
	echo 'Error';
}
