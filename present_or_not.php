<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Present or not | Present or not');
$oPage->setContent(createPresentContent( ));

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createPresentContent( ) {
	$refreshAfterXSeconds = 60;

	//
	$s = getAndProtectSearch();

	$ret = "
<h2>Present or not</h2>

<script type=\"text/javascript\">
<!--
var xmlhttpSearch=false;
var xmlhttpAddRemove=false;
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

if (!xmlhttpAddRemove && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttpAddRemove = new XMLHttpRequest();
	} catch (e) {
		xmlhttpAddRemove=false;
	}
}
if (!xmlhttpAddRemove && window.createRequest) {
	try {
		xmlhttpAddRemove = window.createRequest();
	} catch (e) {
		xmlhttpAddRemove=false;
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
	var strZoek = document.getElementById('fldZoek').value;
	xmlhttpSearch.open(\"GET\", \"present_or_not_list.php?s=\" + escape(document.getElementById('fldZoek').value), true);
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
function addRemove(pid, dowhat) {
	document.getElementById('divAddRemove'+pid).innerHTML = '';
	xmlhttpAddRemove.open(\"GET\", \"addremove_favourite.php?id=\" + pid + \"&dowhat=\" + dowhat + \"&fav=present\", true);
	xmlhttpAddRemove.onreadystatechange=function() {
		if (xmlhttpAddRemove.readyState==4) {
			document.getElementById('divAddRemove'+pid).innerHTML = xmlhttpAddRemove.responseText;
		}
	}
	xmlhttpAddRemove.send(null);
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

// TODOEXPLAIN
function setSearchField(fldValue) {
	document.getElementById('fldZoek').value = fldValue;
	tcRefreshSearch();
	return false;
}
// -->
</script>
<form name=\"frmTc\" method=\"GET\" onsubmit=\"return false;\">
Quick search: <input type=\"\" name=\"fldZoek\" id=\"fldZoek\" maxlength=\"20\" onkeyup=\"tcRefreshSearch();\" value=\"" . $s . "\">
 &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('');\">Clear</a> &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('-a-');\">All</a> &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('-r-');\">Red</a> &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('-g-');\">Green</a>
</form>
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
?>