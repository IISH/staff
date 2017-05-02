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

		$prefix = Settings::get('protime_tables_prefix');

		// SHORT_1 - dutch, SHORT_2 - english
		$query = "
SELECT CODE, ${prefix}p_absence.REC_NR, ${prefix}p_absence.PERSNR, ${prefix}p_absence.BOOKDATE, ${prefix}p_absence.ABSENCE_VALUE, ${prefix}p_absence.ABSENCE_STATUS, ${prefix}absence.SHORT_" . $language . ", ${prefix}p_absence.ABSENCE
FROM ${prefix}p_absence
	LEFT OUTER JOIN ${prefix}absence ON ${prefix}p_absence.ABSENCE = ${prefix}absence.ABSENCE
WHERE ${prefix}p_absence.PERSNR=" . $this->protimePersnr . " AND ${prefix}p_absence.BOOKDATE LIKE '" . $yearMonth . "%' AND ${prefix}p_absence.ABSENCE NOT IN (5, 19)
AND ( ${prefix}p_absence.ABSENCE_VALUE>=" . $this->min_minutes . " OR ${prefix}p_absence.ABSENCE_VALUE=0 )
ORDER BY ${prefix}p_absence.BOOKDATE, ${prefix}p_absence.REC_NR
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

		$prefix = Settings::get('protime_tables_prefix');

		// SHORT_1 - dutch, SHORT_2 - english
		$query = "
SELECT CODE, ${prefix}p_absence.REC_NR, ${prefix}p_absence.PERSNR, ${prefix}p_absence.BOOKDATE, ${prefix}p_absence.ABSENCE_VALUE, ${prefix}p_absence.ABSENCE_STATUS, ${prefix}absence.SHORT_" . $language . ", ${prefix}p_absence.ABSENCE
FROM ${prefix}p_absence
	LEFT OUTER JOIN ${prefix}absence ON ${prefix}p_absence.ABSENCE = ${prefix}absence.ABSENCE
WHERE ${prefix}p_absence.PERSNR=" . $this->protimePersnr . " AND ${prefix}p_absence.BOOKDATE >= '" . $firstDayOfChosenWeek->format('Ymd') . "'  AND ${prefix}p_absence.BOOKDATE < '" . $firstDayOfNextWeek->format('Ymd') . "' AND ${prefix}p_absence.ABSENCE NOT IN (5, 19)
AND ( ${prefix}p_absence.ABSENCE_VALUE>=" . $this->min_minutes . " OR ${prefix}p_absence.ABSENCE_VALUE=0 )
ORDER BY ${prefix}p_absence.BOOKDATE, ${prefix}p_absence.REC_NR
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