<?php 
require_once "classes/start.inc.php";
require_once "classes/_db_connect_protime.inc.php";
require_once "classes/class_mail_checkin.inc.php";

if ( !isset( $_GET["cron_key"] ) || $_GET["cron_key"] != '4512af347df45d854768hfg652145214' ) {
	die('Blocked due to incorrect security code');
}

$oMail = new class_mail_checkin( $settings );

$protimeUsers = $oMail->getListOfCheckedProtimeUserNotifications();
foreach ( $protimeUsers as $protimeUser ) {

	echo $protimeUser["user"]->getFirstname() . " has checked in.<br>";

	$headers = "From: noreply@iisg.nl\r\nReply-To: noreply@iisg.nl";
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