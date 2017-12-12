<?php
class ProtimeUserSchedule {
	private $persnr;
	private $date_from;
	private $arr = array();

	function __construct($persnr, $date_from ) {
		global $databases;
		$this->databases = $databases;

		$this->persnr = $persnr;
		$this->date_from = $date_from;

		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		$prefix = Settings::get('protime_tables_prefix');

		// probleem erhan
		// nieuwe query naar aanleiding van wisselende week roosters
		$query = "
SELECT ${prefix}lnk_curric_profile.DATEFROM, MOD(CAST(${prefix}cyc_dp.DAYNR AS UNSIGNED),7) AS DAG, ${prefix}dayprog.NORM AS HOEVEEL
FROM ${prefix}lnk_curric_profile
	LEFT JOIN ${prefix}cyc_dp ON ${prefix}lnk_curric_profile.PROFILE = ${prefix}cyc_dp.CYCLIQ
	LEFT JOIN ${prefix}dayprog ON ${prefix}cyc_dp.dayprog = ${prefix}dayprog.DAYPROG
WHERE PROFILETYPE = '4'
	AND ${prefix}lnk_curric_profile.PERSNR = '" . $this->persnr . "'
    AND ${prefix}lnk_curric_profile.DATEFROM <= '" . ($this->date_from+1)  . "'
ORDER BY ${prefix}lnk_curric_profile.DATEFROM DESC, CAST(${prefix}cyc_dp.DAYNR AS UNSIGNED) ASC
";

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {

			// convert date format
			$oTCDate = new TCDateTime();
			$oTCDate->setFromString($row['DATEFROM'], "Ymd");
			$date = $oTCDate->getToString("Y-m-d");

			//
			$dayOfWeek = $row['DAG'];
			if ( $dayOfWeek == 0 ) {
				$dayOfWeek = 7;
			}
			$minutes = $row['HOEVEEL'];

			$this->arr[] = array( 'date' => $date, 'dayOfWeek' => $dayOfWeek, 'minutes' => $minutes );
		}
	}

	public function getCurrentSchedule() {
		$lastDate = '';
		$ret = '';
		$lastWeekday = 0;

		if ( count( $this->arr ) > 0 ) {

			// calculate how many days in a week should we show
			$nrOfDays = 5;
			foreach ( $this->arr as $element ) {
				if ( $element['dayOfWeek'] >= 6 && $element['minutes'] > 0 ) {
					$nrOfDays = 7;
					break;
				}
			}

			//
			$currentDayOfWeek = date('N');

			//
			$ret .= "
<table class=\"employee_schedule\">
<tr class=\"employee_schedule\">
";
			$ret .= '';

			for ( $i = 1; $i <= $nrOfDays; $i++ ) {
				$backgroundColor = ( ( $currentDayOfWeek == $i ) ? 'employee_schedule_accentuate' : '' );
				$ret .= "   <th class=\"employee_schedule $backgroundColor\">" . $this->dow($i) . "</th>\n";
			}
			$ret .= "</tr>
<tr>
";
			foreach ( $this->arr as $element ) {
				if ( $lastDate == '' || $lastDate == $element['date'] ) {

					if ( $element['dayOfWeek'] < $lastWeekday ) {
						$ret .= "</tr>
";
					}

					// if by accident reception did not enter a value for a specific day, we then miss one or more values
					// by checking the previous 'lastWeekday' with the current weekday we can calculate if we have to add extra table cells
					if ( $element['dayOfWeek']-$lastWeekday > 1 ) {
						$ret .= str_repeat("	<td class=\"employee_schedule\"></td>\n", $element['dayOfWeek']-$lastWeekday-1);
					}

					if ( $element['dayOfWeek'] <= $nrOfDays ) {
						$backgroundColor = ( ( $currentDayOfWeek == $element['dayOfWeek'] ) ? 'employee_schedule_accentuate' : '' );
						$value = $element['minutes'];

						$value = ( $value == 0 ) ? '&nbsp;' : date('G:i', mktime(0,$value));
						$ret .=  "  <td class=\"employee_schedule $backgroundColor\">" . $value . "</td>\n";
					}

					$lastDate = $element['date'];
					$lastWeekday = $element['dayOfWeek'];
				}
			}
			$ret .= "</tr>
</table>
";
		}

		return $ret;
	}

	private function dow( $i ) {
		while ( $i > 6 ) {
			$i -= 7;
		}

		$dow = Translations::get('day' . $i);

		$dow = strtoupper(substr($dow, 0, 1));

		return $dow;
	}
}
