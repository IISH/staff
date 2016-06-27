<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff | ' . Translations::get('header_specialnumbers'));
$oPage->setContent(createSpecialNumbersContent( ));

// show page
echo $oPage->getPage();

// disconnect database connection
$dbConn->close();

function createSpecialNumbersContent( ) {
    global $dbConn, $oWebuser;

	$ret = "
<h1>" . Translations::get('header_specialnumbers') . "</h1>";

	if ( $oWebuser->isAdmin() ) {
	$ret .= "
<input type=\"button\" class=\"button\" name=\"addNewButton\" value=\"Add new\" onClick=\"open_page('special_numbers_edit.php?ID=0');\"><br>
";
	}

	$ret .= "<br>
<table class=\"special_numbers\">
<tr>
	<th>" . Translations::get('lbl_object') . "</th>
	<th>" . Translations::get('lbl_number') . "</th>
</tr>
";

	//
	$query = 'SELECT ID, object, number FROM staff_special_numbers WHERE isdeleted=0 ORDER BY object ASC ';
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		if ( $oWebuser->isAdmin() ) {
			$object = "<a href=\"special_numbers_edit.php?ID=" . $row['ID'] . "\">" . $row['object'] . "</a>";
		} else {
			$object = $row['object'];
		}

		$ret .= "
<tr>
	<td>" . $object . "</td>
	<td>" . Telephone::getTelephonesHref($row['number']) . "</td>
</tr>
";
	}

	//
$ret .= "
</table>
";

	return $ret;
}
