<?php 
require_once "classes/start.inc.php";

//
if ( !isset($settings) ) {
	$settings = array();
}

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page('design/page.php', $settings);
$oPage->setTitle('Staff - ' . Translations::get('header_specialnumbers'));
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
	<th align=\"left\" style=\"padding-right:10px;\">" . Translations::get('lbl_specnumber_object') . "</th>
	<th align=\"left\" style=\"padding-right:10px;\">" . Translations::get('lbl_specnumber_number') . "</th>
	<th align=\"left\" style=\"padding-right:10px;\">" . Translations::get('lbl_specnumber_extra') . "</th>
	<th align=\"left\" style=\"padding-right:10px;\">" . Translations::get('lbl_specnumber_location') . "</th>
	<th align=\"left\" style=\"padding-right:10px;\">" . Translations::get('lbl_specnumber_room') . "</th>
	<th align=\"left\" style=\"padding-right:10px;\">" . Translations::get('lbl_specnumber_fax') . "</th>
	<th align=\"left\">" . Translations::get('lbl_specnumber_email') . "</th>
</tr>
";

	//
	$query = 'SELECT ID, object, extra, email, number, fax, room, location FROM staff_special_numbers WHERE isdeleted=0 ORDER BY object ASC ';
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		if ( $oWebuser->isAdmin() ) {
			$object = "<a href=\"special_numbers_edit.php?ID=" . $row['ID'] . "\">" . $row['object'] . "</a>";
		} else {
			$object = $row['object'];
		}

        $formattedEmail = trim($row['email']);
        if ( $formattedEmail != '' ) {
            $formattedEmail = "<a href=\"mailto:" . $formattedEmail . "\">" . $formattedEmail . "</a>";
        }

		$ret .= "
<tr>
	<td style=\"padding-right:10px;\">" . $object . "</td>
	<td style=\"padding-right:10px;\">" . Telephone::getTelephonesHref($row['number']) . "</td>
	<td style=\"padding-right:10px;\">" . $row['extra'] . "</td>
	<td style=\"padding-right:10px;\">" . $row['location'] . "</td>
	<td style=\"padding-right:10px;\">" . $row['room'] . "</td>
	<td style=\"padding-right:10px;\">" . $row['fax'] . "</td>
	<td>" . $formattedEmail . "</td>
</tr>
";
	}

	//
$ret .= "
</table>
";

	return $ret;
}
