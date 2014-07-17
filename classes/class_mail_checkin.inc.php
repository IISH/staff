<?php 

// TODOEXPLAIN
class class_mail_checkin {
	protected $project_settings;

	// TODOEXPLAIN
	function class_mail_checkin( $project_settings ) {
		$this->project_settings = $project_settings;
	}

	// TODOEXPLAIN
	function getListOfNotifications() {
		$oConn = new class_mysql($this->project_settings, 'presentornot');
		$oConn->connect();

		$arr = array();
		$arr[] = 0;

		$query = "SELECT ProtimeID FROM Favourites WHERE type='checkinout' GROUP BY ProtimeID ";

		$result = mysql_query($query, $oConn->getConnection());

		while ($row = mysql_fetch_assoc($result)) {
			$arr[] = $row["ProtimeID"];
		}
		mysql_free_result($result);

		return $arr;
	}

	// TODOEXPLAIN
	function getListOfCheckedProtimeUserNotifications( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Ymd");
		}

		$arr = array();

		$ids = implode(',', $this->getListOfNotifications());

		// TODOXXX
		$oProtime = new class_mssql($this->project_settings, 'protime');
		$oProtime->connect();

		//
		$query = "SELECT PERSNR, MAX(BOOKTIME) AS CHECKTIME, COUNT(*) AS AANTAL FROM BOOKINGS WHERE PERSNR IN ( " . $ids . " ) AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 GROUP BY PERSNR HAVING COUNT(*) % 2 = 1 ";
		// TODOXXX
		$result = mssql_query($query, $oProtime->getConnection());
		// TODOXXX
		while ( $row = mssql_fetch_array($result) ) {
			$user = array();
			$user["user"] = new class_protime_user( $row["PERSNR"], $this->project_settings );
			$user["time"] = $row["CHECKTIME"];
			$user["date"] = $date;

			$arr[] = $user;
		}

		return $arr;
	}

	// TODOEXPLAIN
	function getListOfTimecardUsersForProtimeUserNotification( $protime_id ) {
		$oConn = new class_mysql($this->project_settings, 'presentornot');
		$oConn->connect();

		$arr = array();

		$query = "SELECT * FROM Favourites WHERE ProtimeID=" . $protime_id . " AND type='checkinout' ";
		$result = mysql_query($query, $oConn->getConnection());

		while ( $row = mysql_fetch_array($result) ) {
            $arr[] = new class_employee( $row["user"], $this->project_settings );
		}

		return $arr;
	}

	// TODOEXPLAIN
	function deleteNotification( $user, $protime_id ) {
		$oConn = new class_mysql($this->project_settings, 'presentornot');
		$oConn->connect();

		$query = 'DELETE FROM Favourites WHERE user=\'' . $user . '\' AND ProtimeID=' . $protime_id . ' AND type=\'checkinout\' ';
		mysql_query($query, $oConn->getConnection());
	}
}
