<?php
class ProtimeUserSchedule {
	private $persnr;
	private $last_year;
	private $arr = array();

	function __construct( $persnr, $last_year ) {
		global $databases;
		$this->databases = $databases;

		$this->persnr = $persnr;
		$this->last_year = $last_year;

		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		$prefix = Settings::get('protime_tables_prefix');

		// probleem erhan
		// nieuwe query naar aanleiding van wisselende week roosters
		$query = "
SELECT ${prefix}LNK_CURRIC_PROFILE.DATEFROM, MOD(CAST(${prefix}CYC_DP.DAYNR AS UNSIGNED),7) AS DAG, ${prefix}DAYPROG.NORM AS HOEVEEL
FROM ${prefix}LNK_CURRIC_PROFILE
	LEFT JOIN ${prefix}CYC_DP ON ${prefix}LNK_CURRIC_PROFILE.PROFILE = ${prefix}CYC_DP.CYCLIQ
	LEFT JOIN ${prefix}DAYPROG ON ${prefix}CYC_DP.DAYPROG = ${prefix}DAYPROG.DAYPROG
WHERE PROFILETYPE = '4'
	AND ${prefix}LNK_CURRIC_PROFILE.PERSNR = '" . $this->persnr . "'
	AND ${prefix}LNK_CURRIC_PROFILE.DATEFROM < '" . ($this->last_year+1)  . "'
ORDER BY ${prefix}LNK_CURRIC_PROFILE.DATEFROM DESC, CAST(${prefix}CYC_DP.DAYNR AS UNSIGNED) ASC
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
