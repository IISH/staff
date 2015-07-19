<?php
require_once "classes/start.inc.php";
require_once "classes/beo.inc.php";

//
$oBeo = new Beo( (isset($type_of_beo) ? $type_of_beo : ''), $label );

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . strip_tags($oBeo->getLabel()));
$oPage->setContent(createBhvEhboContent( $oBeo ));

// show page
echo $oPage->getPage();

function createBhvEhboContent( $oBeo ) {
	$refreshAfterXSeconds = 60;

	$ret = "
<script type=\"text/javascript\">
<!--
var xmlhttpSearch=false;
var xmlhttpCheckInOut=false;

if (!xmlhttpSearch && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttpSearch = new XMLHttpRequest();
	} catch (e) {
		xmlhttpSearch=false;
	}
}
if (!xmlhttpSearch && window.createRequest) {
	try {
		xmlhttpSearch = window.createRequest();
	} catch (e) {
		xmlhttpSearch=false;
	}
}

if (!xmlhttpCheckInOut && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttpCheckInOut = new XMLHttpRequest();
	} catch (e) {
		xmlhttpCheckInOut=false;
	}
}
if (!xmlhttpCheckInOut && window.createRequest) {
	try {
		xmlhttpCheckInOut = window.createRequest();
	} catch (e) {
		xmlhttpCheckInOut=false;
	}
}

function tcRefreshSearch() {
	xmlhttpSearch.open(\"GET\", \"" . $oBeo->getScriptName() . "\", true);
	xmlhttpSearch.onreadystatechange=function() {
		if (xmlhttpSearch.readyState==4) {
			document.getElementById('tcContentSearch').innerHTML = xmlhttpSearch.responseText;
		}
	}
	xmlhttpSearch.send(null);
}

function tcRefreshSearchStart() {
	tcRefreshSearch();

	// refresh automatically after X seconds
	var t = setTimeout(\"tcRefreshSearchStart()\", " . $refreshAfterXSeconds . " * 1000);
}

function checkInOut(pid, dowhat) {
	document.getElementById('divCheckInOut'+pid).innerHTML = '';
	xmlhttpCheckInOut.open(\"GET\", \"addremove_favourite.php?id=\" + pid + \"&dowhat=\" + dowhat + \"&fav=checkinout\", true);
	xmlhttpCheckInOut.onreadystatechange=function() {
		if (xmlhttpCheckInOut.readyState==4) {
			document.getElementById('divCheckInOut'+pid).innerHTML = xmlhttpCheckInOut.responseText;
		}
	}
	xmlhttpCheckInOut.send(null);

	return false;
}

// -->
</script>
<div id=\"tcContentSearch\"></div>
<script type=\"text/javascript\">
<!--
tcRefreshSearchStart();
// -->
</script>
";

	return $ret;
}
