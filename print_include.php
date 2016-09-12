<?php
require_once "classes/start.inc.php";
header('Content-Type: text/html; charset=iso-8859-1');

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

echo createSpecialNumbersContent( );

// disconnect database connection
$dbConn->close();

function createSpecialNumbersContent( ) {
	global $dbConn, $sortOn;

	if ( !isset($sortOn) ) {
		$sortOn = 'lastname';
	} else {
		if ( !in_array($sortOn, array('firstname', 'lastname') ) ) {
			$sortOn = 'lastname';
		}
	}

	$ret = "
<style>
@page {
    size: auto; /* auto is the initial value */ 
    /* this affects the margin in the printer settings */ 
    margin: 0mm 0mm 0mm 0mm;  
}

body {
	font-family: Verdana;
	font-size: 83%;
	margin: 5px;
}

.multicols {
	-moz-column-count: 3;
	-webkit-column-count: 3;
	column-count: 3;
}
</style>
<body>
<div class=\"multicols\">
";

	$ret .= "Print: " . date("j F Y") . "<br><br>";

	// medewerkers
	$lastChar = '';
	$lastRow = '';
	$ret .= "<b>Medewerkers</b>";
	if ( $sortOn == 'firstname' ) {
		$orderBy = 'TRIM(CONCAT(FIRSTNAME, NAME))';
	} else {
		$orderBy = 'TRIM(CONCAT(NAME, FIRSTNAME))';
	}

	$query = "SELECT NAME, FIRSTNAME, USER02
FROM protime_curric
WHERE ( TRIM(DATE_OUT) = '' OR DATE_OUT = '0' OR DATE_OUT > '" . date("Ymd") . "' ) AND ( TRIM(USER02) <> '' )
ORDER BY $orderBy ";

	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {

		if ( $sortOn == 'firstname' ) {
			$name = trim(Misc::removeJobFunctionFromName($row["FIRSTNAME"]) . ' ' . Misc::removeJobFunctionFromName(Misc::verplaatsTussenvoegselNaarBegin($row["NAME"])));
		} else {
			$name = trim(Misc::removeJobFunctionFromName($row["NAME"]));
			if ( trim($row["FIRSTNAME"]) != '' ) {
				$name .= ', ' . Misc::removeJobFunctionFromName($row["FIRSTNAME"]);
			}
		}
		if ( $lastRow != $name.$row['USER02'] ) {
			if ($lastChar != substr($name, 0, 1)) {
				$ret .= '<br>';
			}
			$ret .= $name . " " . $row['USER02'] . "<br>";

			if ( $sortOn == 'firstname' ) {
				$lastChar = substr($row["FIRSTNAME"], 0, 1);
			} else {
				$lastChar = substr($row["NAME"], 0, 1);
			}

			$lastRow = $name.$row['USER02'];
		}
	}

	$ret .="<br>";

	// Speciale nummers
	$ret .= "<b>Speciale nummers</b><br>";
	$query = 'SELECT ID, object, number FROM staff_special_numbers WHERE isdeleted=0 ORDER BY object ASC ';
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$ret .= $row['object'] . "\t" . $row['number'] . "<br>";
	}

	$ret .= "
</div>
</body>
";

	$ret .= "
<SCRIPT LANGUAGE=\"JavaScript\">
<!--
window.print();
// -->
</script>
";

	return $ret;
}
