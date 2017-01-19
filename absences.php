<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAuthorisationTabAbsences() ) {
	die('Access denied.<br><a href="index.php">Go back</a>');
}

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('menu_absences'));
$oPage->setContent(createPresentContent( ));

// show page
echo $oPage->getPage();

function createPresentContent( ) {
	global $oWebuser;

	$refreshAfterXSeconds = 60;

	//
	$s = getAndProtectSearch();

	$ret = "
<h1>" . Translations::get('menu_absences') . "</h1>

<script type=\"text/javascript\">
<!--
var xmlhttpSearch=false;
var xmlhttpAddRemove=false;

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

function setYearMonth(year, month) {
	document.getElementById('fldMonth').value = month;
	document.getElementById('fldYear').value = year;

	//
	tcRefreshSearch();
}

function setMonth(value) {
	var month = parseInt(document.getElementById('fldMonth').value,10);
	var year = parseInt(document.getElementById('fldYear').value,10);
	month = month + value;
	if ( month < 1 ) {
		month = 12;
		year = year - 1;
	} else if ( month > 12 ) {
		month = 1;
		year = year + 1;
	}

	setYearMonth(year, month);
}

function tcRefreshSearch() {
	var strSearch = document.getElementById('fldSearch').value;
	xmlhttpSearch.open(\"GET\", \"absences_list.php?s=\" + escape(strSearch) + \"&y=\" + escape(document.getElementById('fldYear').value) + \"&m=\" + escape(document.getElementById('fldMonth').value), true);
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
	xmlhttpAddRemove.open(\"GET\", \"addremove_favourite.php?id=\" + pid + \"&dowhat=\" + dowhat + \"&fav=vakantie\", true);
	xmlhttpAddRemove.onreadystatechange=function() {
		if (xmlhttpAddRemove.readyState==4) {
			document.getElementById('divAddRemove'+pid).innerHTML = xmlhttpAddRemove.responseText;
		}
	}
	xmlhttpAddRemove.send(null);
}

function setSearchField(fldValue) {
	document.getElementById('fldSearch').value = fldValue;
	tcRefreshSearch();
	return false;
}
// -->
</script>
<form name=\"frmTc\" method=\"GET\" onsubmit=\"return false;\">
<TABLE width=\"100%\">
<TR>
	<TD>

" . Translations::get('lbl_quick_search') . ": <input type=\"\" name=\"fldSearch\" id=\"fldSearch\" maxlength=\"20\" onkeyup=\"tcRefreshSearch();\" value=\"" . $s . "\">
<em>" . Translations::get('min_x_characters') . "</em> &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('');\">" . Translations::get('lbl_show_favourites') . "</a> &nbsp; <font size=-2></font>
	</TD>
	<TD align=\"right\">
<input type=\"hidden\" name=\"fldYear\" id=\"fldYear\" value=\"" . date("Y") . "\">
<input type=\"hidden\" name=\"fldMonth\" id=\"fldMonth\" value=\"" . date("m") . "\">
" . Translations::get('go_to') . " &nbsp; &nbsp;
<a href=\"#\" onclick=\"javascript:setYearMonth(" . date("Y") . ", " . date("m") . ");\" title=\"" . Translations::get('go_to_current_month') . "\">*</a> &nbsp; &nbsp;
<a href=\"#\" onclick=\"javascript:setMonth(-1);\" title=\"" . Translations::get('go_to_previous_month') . "\">" . Translations::get('prev') . "</a> &nbsp;
<a href=\"#\" onclick=\"javascript:setMonth(+1);\" title=\"" . Translations::get('go_to_next_month') . "\">" . Translations::get('next') . "</a>
	</TD>
</TR>
</table>
</form>
<br>
<div id=\"tcContentSearch\"></div>
<script type=\"text/javascript\">
<!--
tcRefreshSearchStart();
// -->
</script>
";

	// Legenda
	$arrLegenda = array();
	$ret .= "<br>" . Translations::get('lbl_legenda') . ":<br>";

	$legenda = new Legenda();
	// create array of legenda items
	foreach ( $legenda->getAll() as $item ) {
		if ( $oWebuser->isAdmin() || $oWebuser->isHeadOfDepartment() || $oWebuser->hasAuthorisationReasonOfAbsenceAll() || $item->isVisibleForEveryone() ) {
			$arrLegenda[strtolower($item->getDescriptin())] = "<span align=\"center\" style=\"" . $item->getBackgroundColor() . "display:inline-block;margin-bottom:5px;margin-right:5px;\">&nbsp;" . strtolower($item->getDescriptin()) . "&nbsp;</span>";
		}
	}

	// sort the legenda array on 'description'
	ksort($arrLegenda);

	//
	foreach ( $arrLegenda as $item ) {
		$ret .= $item;
	}

	return $ret;
}
