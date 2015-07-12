<?php 

// TODOEXPLAIN
class class_mail_checkin {
	protected $databases;

	// TODOEXPLAIN
	function __construct() {
		global $databases;
		$this->databases = $databases;
	}

	// TODOEXPLAIN
	function getListOfNotifications() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$arr = array();
		$arr[] = 0;

		$query = "SELECT ProtimeID FROM Staff_favourites WHERE type='checkinout' GROUP BY ProtimeID ";

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

		$oProtime = new class_mysql($this->databases['default']);
		$oProtime->connect();

		//
		$query = "SELECT PERSNR, MAX(BOOKTIME) AS CHECKTIME, COUNT(*) AS AANTAL FROM Staff_today_checkinout WHERE PERSNR IN ( " . $ids . " ) AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 GROUP BY PERSNR HAVING COUNT(*) % 2 = 1 ";
		$result = mysql_query($query, $oProtime->getConnection());
		while ( $row = mysql_fetch_assoc($result) ) {
			$user = array();
			$user["user"] = new class_protime_user( $row["PERSNR"] );
			$user["time"] = $row["CHECKTIME"];
			$user["date"] = $date;

			$arr[] = $user;
		}

		return $arr;
	}

	// TODOEXPLAIN
	function getListOfTimecardUsersForProtimeUserNotification( $protime_id ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$arr = array();

		$query = "SELECT * FROM Staff_favourites WHERE ProtimeID=" . $protime_id . " AND type='checkinout' ";
		$result = mysql_query($query, $oConn->getConnection());

		while ( $row = mysql_fetch_assoc($result) ) {
			$arr[] = static_protime_user::getProtimeUserByLoginName($row["user"]);
		}

		return $arr;
	}

	// TODOEXPLAIN
	function deleteNotification( $user, $protime_id ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = 'DELETE FROM Staff_favourites WHERE user=\'' . $user . '\' AND ProtimeID=' . $protime_id . ' AND type=\'checkinout\' ';
		mysql_query($query, $oConn->getConnection());
	}
}
