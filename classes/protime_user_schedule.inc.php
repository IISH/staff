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

		// probleem erhan
		// nieuwe query naar aanleiding van wisselende week roosters
		$query = "
SELECT protime_lnk_curric_profile.DATEFROM, MOD(CAST(protime_cyc_dp.DAYNR AS UNSIGNED),7) AS DAG, protime_dayprog.NORM AS HOEVEEL
FROM protime_lnk_curric_profile
	LEFT JOIN protime_cyc_dp ON protime_lnk_curric_profile.PROFILE = protime_cyc_dp.CYCLIQ
	LEFT JOIN protime_dayprog ON protime_cyc_dp.dayprog = protime_dayprog.DAYPROG
WHERE PROFILETYPE = '4'
	AND protime_lnk_curric_profile.PERSNR = '" . $this->persnr . "'
    AND protime_lnk_curric_profile.DATEFROM <= '" . ($this->date_from+1)  . "'
ORDER BY protime_lnk_curric_profile.DATEFROM DESC, CAST(protime_cyc_dp.DAYNR AS UNSIGNED) ASC
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


	public function getFreeWorkingdays() {
		$ret = array();

		//
		if ( count( $this->arr ) > 0 ) {
			// alle default werkdagen
			for ( $i = 1; $i <= 5; $i++ ) {
				$ret[$i] = $i;
			}

			foreach ( $this->arr as $element ) {
				if ( $element['dayOfWeek'] <= 5 ) {
					if ( $element['minutes'] > 0 ) {
						// verwijder als niet werkt op deze weekdag
						unset($ret[$element['dayOfWeek']]);
					}
				}
			}
		}

		// return vrije werkdagen
		return $ret;
	}
}
