<?php
class class_protime_user_schedule {
	private $persnr;
	private $last_year;
	private $arr = array();

	// TODOEXPLAIN
	function class_protime_user_schedule( $persnr, $last_year ) {
		global $databases;
		$this->databases = $databases;

		$this->persnr = $persnr;
		$this->last_year = $last_year;

		$this->initValues();
	}

	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// probleem erhan
		// nieuwe query naar aanleiding van wisselende week roosters
		$query = "
SELECT PROTIME_LNK_CURRIC_PROFILE.DATEFROM, MOD(CAST(PROTIME_CYC_DP.DAYNR AS UNSIGNED),7) AS DAG, PROTIME_DAYPROG.NORM AS HOEVEEL
FROM PROTIME_LNK_CURRIC_PROFILE
	LEFT JOIN PROTIME_CYC_DP ON PROTIME_LNK_CURRIC_PROFILE.PROFILE = PROTIME_CYC_DP.CYCLIQ
	LEFT JOIN PROTIME_DAYPROG ON PROTIME_CYC_DP.DAYPROG = PROTIME_DAYPROG.DAYPROG
WHERE PROFILETYPE = '4'
	AND PROTIME_LNK_CURRIC_PROFILE.PERSNR = '" . $this->persnr . "'
	AND PROTIME_LNK_CURRIC_PROFILE.DATEFROM < '" . ($this->last_year+1)  . "'
ORDER BY PROTIME_LNK_CURRIC_PROFILE.DATEFROM DESC, CAST(PROTIME_CYC_DP.DAYNR AS UNSIGNED) ASC
";

		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {

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

		mysql_free_result($result);
	}

	public function getCurrentSchedule( $show_all_weekdays = 0 ) {
		$lastDate = '';
		$ret = '';
		$separator = '';
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

					if ( $element['dayOfWeek'] <= $nrOfDays ) {
						$backgroundColor = ( ( $currentDayOfWeek == $element['dayOfWeek'] ) ? 'employee_schedule_accentuate' : '' );
						$value = $element['minutes'];
						$value = ( $value == 0 ) ? '&nbsp;' : number_format(($value/60),1);
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
		$dow = '';

		//
		while ( $i > 6 ) {
			$i -= 7;
		}

		switch ( $i ) {
			case 0:
				$dow = "Sunday";
				break;
			case 1:
				$dow = "Monday";
				break;
			case 2:
				$dow = "Tuesday";
				break;
			case 3:
				$dow = "Wednesday";
				break;
			case 4:
				$dow = "Thursday";
				break;
			case 5:
				$dow = "Friday";
				break;
			case 6:
				$dow = "Saturday";
				break;
			case 7:
				$dow = "Sunday";
				break;
		}

		// TODO
		$dow = substr($dow, 0, 1);

		return $dow;
	}
}
