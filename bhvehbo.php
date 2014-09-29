<?php
require_once "classes/start.inc.php";

//
if ( !isset($bhv_or_ehbo)  ) {
	$bhv_or_ehbo = "bhv";
}
$bhv_or_ehbo = strtolower($bhv_or_ehbo);

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | ' . strtoupper($bhv_or_ehbo));
$oPage->setContent(createBhvEhboContent( $bhv_or_ehbo ));

// show page
echo $oPage->getPage();

// TODOEXPLAIN
function createBhvEhboContent( $bhv_or_ehbo ) {
	$refreshAfterXSeconds = 60;

	$ret = "
<h2>" . strtoupper($bhv_or_ehbo) . "</h2>

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

// TODOEXPLAIN
function tcRefreshSearch() {
	xmlhttpSearch.open(\"GET\", \"" . strtolower($bhv_or_ehbo) . "_list.php\", true);
	xmlhttpSearch.onreadystatechange=function() {
		if (xmlhttpSearch.readyState==4) {
			document.getElementById('tcContentSearch').innerHTML = xmlhttpSearch.responseText;
		}
	}
	xmlhttpSearch.send(null);
}

// TODOEXPLAIN
function tcRefreshSearchStart() {
	tcRefreshSearch();

	// refresh automatically after X seconds
	var t = setTimeout(\"tcRefreshSearchStart()\", " . $refreshAfterXSeconds . " * 1000);
}

// TODOEXPLAIN
function checkInOut(pid, dowhat) {
	document.getElementById('divCheckInOut'+pid).innerHTML = '';
	xmlhttpCheckInOut.open(\"GET\", \"addremove_favourite.php?id=\" + pid + \"&dowhat=\" + dowhat + \"&fav=checkinout\", true);
	xmlhttpCheckInOut.onreadystatechange=function() {
		if (xmlhttpCheckInOut.readyState==4) {
			document.getElementById('divCheckInOut'+pid).innerHTML = xmlhttpCheckInOut.responseText;
		}
	}
	xmlhttpCheckInOut.send(null);
}

// -->
</script>
<br>
<div id=\"tcContentSearch\">...</div>
<script type=\"text/javascript\">
<!--
tcRefreshSearchStart();
// -->
</script>
";

	return $ret;
}
