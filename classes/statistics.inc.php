<?php

class Statistics {

	public static function ping( $loginname ) {
		global $dbConn;

		$loginname = trim($loginname);

		if ( $loginname != '' ) {
			$date = date('Y-m-d');

			$query = "INSERT INTO staff_user_statistics (loginname, date, page_count)
						VALUES ('$loginname', '$date', 1)
						ON DUPLICATE KEY UPDATE page_count=page_count+1 ; ";
			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
		}
	}
}