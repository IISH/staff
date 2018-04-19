<?php
$doPing = false;
require_once "classes/start.inc.php";

//
if ( !isset( $_GET["cron_key"] ) || Settings::get("cron_key") == '' || $_GET["cron_key"] != Settings::get("cron_key") ) {
	die('Blocked due to incorrect security code');
}

require_once "classes/mail_checkin.inc.php";

$oMail = new MailCheckin( $settings );

$protimeUsers = $oMail->getListOfCheckedProtimeUserNotifications();
foreach ( $protimeUsers as $protimeUser ) {

	echo $protimeUser["user"]->getFirstname() . " has checked in.<br>";

	$headers = "From: " . Settings::get("email_sender_email") . "\r\nReply-To: " . Settings::get("email_sender_email");
	$subject = trim( $protimeUser["user"]->getFirstname() . ' ' . $protimeUser["user"]->getLastname() ) . ' has checked in.';

	$timecardUsers = $oMail->getListOfTimecardUsersForProtimeUserNotification( $protimeUser["user"]->getId() );

	foreach ( $timecardUsers as $timecardUser ) {
		// controleer of user inout tijd mag zien
		if ( $timecardUser->isAdmin() || $timecardUser->isHeadOfDepartment() || $timecardUser->hasInOutTimeAuthorisation() ) {
			$body = trim( $protimeUser["user"]->getFirstname() . ' ' . $protimeUser["user"]->getLastname() ) . " has checked in at " . class_datetime::formatDate( $protimeUser["date"] ) . " " . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $protimeUser["time"] ) . " \r\n";
		} else {
			$body = trim( $protimeUser["user"]->getFirstname() . ' ' . $protimeUser["user"]->getLastname() ) . " has checked in. \r\n";
		}
		$body .= "E-mail sent at " . date("d-m-Y H:i");

        if ( $timecardUser->getEmail() != '' ) {
	        Mail::sendEmail($timecardUser->getEmail(), $subject, $body);
        }
	}
	$oMail->deleteNotification( $protimeUser["user"]->getId() );
}
?>Done <em>(<?php echo date("Y-m-d H:i:s"); ?>)</em>