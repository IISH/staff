<?php 

class MailCheckin {
	protected $databases;

	function __construct() {
		global $databases;
		$this->databases = $databases;
	}

	public function getListOfNotifications() {
		global $dbConn;

		$arr = array();
		$arr[] = 0;

		$query = "SELECT ProtimeID FROM Staff_favourites WHERE type='checkinout' GROUP BY ProtimeID ";

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = $row["ProtimeID"];
		}

		return $arr;
	}

	public function getListOfCheckedProtimeUserNotifications( $date = '' ) {
		global $dbConn;

		if ( $date == '' ) {
			$date = date("Ymd");
		}

		$arr = array();

		$ids = implode(',', $this->getListOfNotifications());

		//
		$query = "SELECT PERSNR, MAX(BOOKTIME) AS CHECKTIME, COUNT(*) AS AANTAL FROM Staff_today_checkinout WHERE PERSNR IN ( " . $ids . " ) AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 GROUP BY PERSNR HAVING COUNT(*) % 2 = 1 ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$user = array();
			$user["user"] = new ProtimeUser( $row["PERSNR"] );
			$user["time"] = $row["CHECKTIME"];
			$user["date"] = $date;

			$arr[] = $user;
		}

		return $arr;
	}

	public function getListOfTimecardUsersForProtimeUserNotification( $protime_id ) {
		global $dbConn;

		$arr = array();

		$query = "SELECT * FROM Staff_favourites WHERE ProtimeID=" . $protime_id . " AND type='checkinout' ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = static_protime_user::getProtimeUserByLoginName($row["user"]);
		}

		return $arr;
	}

	public function deleteNotification( $protime_id ) {
		global $dbConn;

		$query = 'DELETE FROM Staff_favourites WHERE ProtimeID=' . $protime_id . ' AND type=\'checkinout\' ';
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
	}
}
