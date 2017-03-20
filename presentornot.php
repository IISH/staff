<?php
require_once "classes/start.inc.php";

$pageSettings = array(
	'title' => Translations::get('menu_presentornot')
);

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . $pageSettings['title']);
$oPage->setContent(createPresentContent( $pageSettings ));

// show page
echo $oPage->getPage();

function createPresentContent( $pageSettings ) {
	$refreshAfterXSeconds = 60;

	//
	$s = getAndProtectSearch();

	$ret = "
<h1>" . $pageSettings['title'] . "</h1>

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

function tcRefreshSearch() {
	var strSearch = document.getElementById('fldSearch').value;
	var strDesign = document.getElementById('fldDesign').value;
	xmlhttpSearch.open(\"GET\", \"presentornot_list.php?l=\" + escape(strDesign) + \"&s=\" + escape(strSearch), true);
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

function addRemove(pid, dowhat) {
	document.getElementById('divAddRemove'+pid).innerHTML = '';
	xmlhttpAddRemove.open(\"GET\", \"addremove_favourite.php?id=\" + pid + \"&dowhat=\" + dowhat + \"&fav=present\", true);
	xmlhttpAddRemove.onreadystatechange=function() {
		if (xmlhttpAddRemove.readyState==4) {
			document.getElementById('divAddRemove'+pid).innerHTML = xmlhttpAddRemove.responseText;
		}
	}
	xmlhttpAddRemove.send(null);

	return false;
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

function setSearchField(fldValue) {
	document.getElementById('fldSearch').value = fldValue;
	tcRefreshSearch();
	return false;
}

function setDesign(fldValue) {
	document.getElementById('fldDesign').value = fldValue;
	tcRefreshSearch();
	return false;
}
// -->
</script>
<form name=\"frmTc\" method=\"GET\" onsubmit=\"return false;\">
<div style=\"float:left;\">
" . Translations::get('lbl_quick_search') . ": 
<input type=\"text\" name=\"fldSearch\" id=\"fldSearch\" maxlength=\"20\" onkeyup=\"tcRefreshSearch();\" value=\"" . $s . "\">
<input type=\"hidden\" name=\"fldDesign\" id=\"fldDesign\" value=\"\">
 &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('');\">" . Translations::get('lbl_show_favourites') . "</a>
 &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('-g-');\">" . Translations::get('lbl_show_present') . "</a>
 &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('-r-');\">" . Translations::get('lbl_show_absent') . "</a>
 &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('-a-');\">" . Translations::get('lbl_show_all') . "</a>
</div>
<div style=\"float:right;\">
	<a href=\"#\" onclick=\"javascript:setDesign('tabular');\"><img src=\"images/misc/tabular.png\" border=\"0\"></a> <a href=\"#\" onclick=\"javascript:setDesign('tile');\"><img src=\"images/misc/tile.png\" border=\"0\"></a>
</div>
</form>
<br>
<div id=\"tcContentSearch\" style=\"clear:both;\"></div>
<script type=\"text/javascript\">
<!--
tcRefreshSearchStart();
// -->
</script>
";

	return $ret;
}
