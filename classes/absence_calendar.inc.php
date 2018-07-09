<?php

class AbsenceCalendar {
	private $dbConn;
	private $min_minutes;
	private $protimePersnr;

	function __construct( $protimePersnr ) {
		global $dbConn;
		$this->dbConn = $dbConn;

		$this->protimePersnr = $protimePersnr;

		$this->min_minutes = 120;
	}

	function getAbsencesAndHolidaysMonth($year, $month ) {
		$language = getLanguage();

		$ret = array();

		$yearMonth = createDateAsString($year, $month);

		// SHORT_1 - dutch, SHORT_2 - english
		$query = "
SELECT CODE, protime_p_absence.REC_NR, protime_p_absence.PERSNR, protime_p_absence.BOOKDATE, protime_p_absence.ABSENCE_VALUE, protime_p_absence.ABSENCE_STATUS, protime_absence.SHORT_" . $language . ", protime_p_absence.ABSENCE
FROM protime_p_absence
	LEFT OUTER JOIN protime_absence ON protime_p_absence.ABSENCE = protime_absence.ABSENCE
WHERE protime_p_absence.PERSNR=" . $this->protimePersnr . " AND protime_p_absence.BOOKDATE LIKE '" . $yearMonth . "%' AND protime_p_absence.ABSENCE NOT IN (5, 19)
AND ( protime_p_absence.ABSENCE_VALUE>=" . $this->min_minutes . " OR protime_p_absence.ABSENCE_VALUE=0 )
ORDER BY protime_p_absence.BOOKDATE, protime_p_absence.REC_NR
";

		$stmt = $this->dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			// SHORT_1 - dutch, SHORT_2 - english
			$ret[] = array( 'code' => $row["CODE"], 'date' => $row["BOOKDATE"], 'description' => strtolower($row["SHORT_" . $language]) );
		}

		return $ret;
	}

	function getAbsencesAndHolidaysWeek( $year, $month, $day ) {
		$language = getLanguage();

		$ret = array();

		$chosenDay = mktime(0,0,0, $month, $day, $year);

		$firstDayOfChosenWeek = DateTime::createFromFormat('Y-m-d', date("Y-m-d", $chosenDay));
		$firstDayOfChosenWeek->modify('-' . ( date("w", $chosenDay) - 1 ) . ' day');

		$firstDayOfNextWeek = DateTime::createFromFormat('Y-m-d', date("Y-m-d", $chosenDay));
		$firstDayOfNextWeek->modify('+' . ( 7 - date("w", $chosenDay) + 1) . ' day');

		// SHORT_1 - dutch, SHORT_2 - english
		$query = "
SELECT CODE, protime_p_absence.REC_NR, protime_p_absence.PERSNR, protime_p_absence.BOOKDATE, protime_p_absence.ABSENCE_VALUE, protime_p_absence.ABSENCE_STATUS, protime_absence.SHORT_" . $language . ", protime_p_absence.ABSENCE
FROM protime_p_absence
	LEFT OUTER JOIN protime_absence ON protime_p_absence.ABSENCE = protime_absence.ABSENCE
WHERE protime_p_absence.PERSNR=" . $this->protimePersnr . " AND protime_p_absence.BOOKDATE >= '" . $firstDayOfChosenWeek->format('Ymd') . "'  AND protime_p_absence.BOOKDATE < '" . $firstDayOfNextWeek->format('Ymd') . "' AND protime_p_absence.ABSENCE NOT IN (5, 19)
AND ( protime_p_absence.ABSENCE_VALUE>=" . $this->min_minutes . " OR protime_p_absence.ABSENCE_VALUE=0 )
ORDER BY protime_p_absence.BOOKDATE, protime_p_absence.REC_NR
";

//preprint( $query );

		$stmt = $this->dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			// SHORT_1 - dutch, SHORT_2 - english

			$oDate = DateTime::createFromFormat('Ymd', $row["BOOKDATE"] );
			$dow = $oDate->format('w');

			$ret[] = array(
				'code' => $row["CODE"]
				, 'date' => $row["BOOKDATE"]
				, 'description' => strtolower($row["SHORT_" . $language])
				, 'dayOfWeek' => $dow
				);
		}

		return $ret;
	}
}
