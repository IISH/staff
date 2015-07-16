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
$oPage = new class_page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . class_translations::get('menu_absences'));
$oPage->setContent(createPresentContent( ));

// show page
echo $oPage->getPage();

// TODOEXPLAIN
function createPresentContent( ) {
	global $colors, $oWebuser;

	$refreshAfterXSeconds = 60;

	//
	$s = getAndProtectSearch();

	$ret = "
<h2>" . class_translations::get('menu_absences') . "</h2>

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

// TODOEXPLAIN
function setYearMonth(year, month) {
	document.getElementById('fldMonth').value = month;
	document.getElementById('fldYear').value = year;

	//
	tcRefreshSearch();
}

// TODOEXPLAIN
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
	//document.getElementById('fldMonth').value = month;
	//document.getElementById('fldYear').value = year;
}

// TODOEXPLAIN
function tcRefreshSearch() {
	var strZoek = document.getElementById('fldZoek').value;
	xmlhttpSearch.open(\"GET\", \"absences_list.php?s=\" + escape(document.getElementById('fldZoek').value) + \"&y=\" + escape(document.getElementById('fldYear').value) + \"&m=\" + escape(document.getElementById('fldMonth').value), true);
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
	xmlhttpAddRemove.open(\"GET\", \"addremove_favourite.php?id=\" + pid + \"&dowhat=\" + dowhat + \"&fav=vakantie\", true);
	xmlhttpAddRemove.onreadystatechange=function() {
		if (xmlhttpAddRemove.readyState==4) {
			document.getElementById('divAddRemove'+pid).innerHTML = xmlhttpAddRemove.responseText;
		}
	}
	xmlhttpAddRemove.send(null);
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
<TABLE width=\"100%\">
<TR>
	<TD>

Quick search: <input type=\"\" name=\"fldZoek\" id=\"fldZoek\" maxlength=\"20\" onkeyup=\"tcRefreshSearch();\" value=\"" . $s . "\">
<em>(min. 3 characters)</em> &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('');\">Show favourites</a> &nbsp; <font size=-2></font>
	</TD>
	<TD align=\"right\">
<input type=\"hidden\" name=\"fldYear\" id=\"fldYear\" value=\"" . date("Y") . "\">
<input type=\"hidden\" name=\"fldMonth\" id=\"fldMonth\" value=\"" . date("m") . "\">
Go to &nbsp; &nbsp;
<a href=\"#\" onclick=\"javascript:setYearMonth(" . date("Y") . ", " . date("m") . ");\" title=\"Go to current month\">*</a> &nbsp; &nbsp; 
<a href=\"#\" onclick=\"javascript:setMonth(-1);\" title=\"Go to previous month\">Prev</a> &nbsp; 
<a href=\"#\" onclick=\"javascript:setMonth(+1);\" title=\"Go to next month\">Next</a>
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

		$ret .= "<br>Legenda:<br>";
		foreach ( $colors["td"] as $a => $b ) {
			// TODO: hier moet gecontroleerd worden of persoon inout rechten heeft
			if ( $oWebuser->hasInOutTimeAuthorisation() || $oWebuser->isAdmin() || $oWebuser->hasAuthorisationTabAbsences() || $oWebuser->isHeadOfDepartment() || in_array($a, array('vandaag', 'brugdag', 'holiday', 'vakantie', 'weekend')) ) {
				if ( $a == 'vakantie' ) {
					$a = 'afwezig';
				}
				$ret .= "<span align=\"center\" style=\"" . $b . "display:inline-block;margin-bottom:5px;margin-right:5px;\">&nbsp;" . $a . "&nbsp;</span>";
			}
		}

	return $ret;
}
