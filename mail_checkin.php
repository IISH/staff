<?php 
require_once "classes/start.inc.php";

if ( !isset( $_GET["cron_key"] ) || $settings_from_database["cron_key"] == '' || $_GET["cron_key"] != $settings_from_database["cron_key"] ) {
	die('Blocked due to incorrect security code');
}

require_once "classes/_db_connect_protime.inc.php";
require_once "classes/class_mail_checkin.inc.php";

$oMail = new class_mail_checkin( $settings );

$protimeUsers = $oMail->getListOfCheckedProtimeUserNotifications();
foreach ( $protimeUsers as $protimeUser ) {

	echo $protimeUser["user"]->getFirstname() . " has checked in.<br>";

	$headers = "From: " . $settings_from_database["from_email"] . "\r\nReply-To: " . $settings_from_database["from_email"];
	$subject = trim( $protimeUser["user"]->getFirstname() . ' ' . $protimeUser["user"]->getLastname() ) . ' has checked in.';

	$timecardUsers = $oMail->getListOfTimecardUsersForProtimeUserNotification( $protimeUser["user"]->getId() );
	foreach ( $timecardUsers as $timecardUser ) {

		if ( $timecardUser->hasInOutTimeAuthorisation() ) {
			$body = trim( $protimeUser["user"]->getFirstname() . ' ' . $protimeUser["user"]->getLastname() ) . " has checked in at " . class_datetime::formatDate( $protimeUser["date"] ) . " " . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $protimeUser["time"] ) . " \r\n";
		} else {
			$body = trim( $protimeUser["user"]->getFirstname() . ' ' . $protimeUser["user"]->getLastname() ) . " has checked in. \r\n";
		}
		$body .= "E-mail sent at " . date("d-m-Y H:i");

        if ( $timecardUser->getEmail() != '' ) {
            mail( $timecardUser->getEmail(), $subject, $body, $headers );
		    $oMail->deleteNotification( $timecardUser->getUser(), $protimeUser["user"]->getId() );
        }
	}
}
?>Done <em>(<?php echo date("Y-m-d H:i:s"); ?>)</em>...