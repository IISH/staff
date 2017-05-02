<?php

class AbsenceCalendarFormat {
	public static function InWeekFormat($year, $month, $firstDay, $arrVakantie, $arrHolidays ) {
		global $oWebuser;

		$vak = '';

		for ($i = $firstDay; $i < $firstDay + 7; $i++  ) {
			$cellStyleAlt = '';
			$cellStyleHrefStyle = '';

			$style = "border: thin solid white;";

			$oDate = DateTime::createFromFormat('Y-m-d', $year . '-' . $month . '-' . $i);

			$cellStyle = getStyle($oDate->format('Y'), $oDate->format('m'), $i, $arrVakantie, $arrHolidays, 0);

			$style .= $cellStyle["tdStyle"];
			if ( $cellStyle["alt"] != '' ) {
				$cellStyleAlt = $cellStyle["alt"];
				$cellStyleHrefStyle = $cellStyle["hrefStyle"];

				//
				if ( !$oWebuser->isAdmin() && !$oWebuser->isHeadOfDepartment() && !$oWebuser->hasAuthorisationReasonOfAbsenceAll() && !$oWebuser->hasInOutTimeAuthorisation() ) {
					$cellStyleAlt = '';
					$cellStyleHrefStyle = 'color:white;';
				}
			}

			$celValue = '<a title="' . $cellStyleAlt . '" style="' . $cellStyleHrefStyle . '">' . substr($oDate->format('l'), 0, 2) . '</a>';

			$vak .= "<TD style=\"" . $style . "\" align=\"center\" width=\"14%\"><font size=-3>" . $celValue . "</font></TD>";
		}
		$vak = str_replace('::VAK::', $vak, "
<TABLE border=0 cellspacing=0 cellpadding=0 width=\"98%\" align=\"center\">
	<TR>::VAK::</TR>
</table>");

		$ret = $vak;

		return $ret;
	}

	public static function inMonthListFormat($year, $month, $arrVakantie, $arrHolidays ) {
		global $oWebuser;
		$cellWidth = 23;
		$daysInCurrentMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		$vak = '';

		for ( $i = 1; $i <= $daysInCurrentMonth; $i++ ) {
			$style = "border: thin solid white;";
			//
			$celValue = '&nbsp;&nbsp;';

			$cellStyle = getStyle($year, $month, $i, $arrVakantie, $arrHolidays, 1);

			$style .= $cellStyle["tdStyle"];
			if ( $cellStyle["alt"] != '' ) {
				$cellStyleAlt = $cellStyle["alt"];
				$cellStyleHrefStyle = $cellStyle["hrefStyle"];

				//
				if ( !$oWebuser->isAdmin() && !$oWebuser->isHeadOfDepartment() && !$oWebuser->hasAuthorisationReasonOfAbsenceAll() && !$oWebuser->hasInOutTimeAuthorisation() ) {
					$cellStyleAlt = '';
					$cellStyleHrefStyle = 'color:white;';
				}

				//
				$celValue = '<a title="' . $cellStyleAlt . '" style="' . $cellStyleHrefStyle . '">' . $i . '</a>';
			}

			// width
			$style .= "width: " . $cellWidth . "px;";

			$vak .= "<TD style=\"" . $style . "\" align=\"center\" ><font size=-3>" . $celValue . "</font></TD>";
		}

		$vak = str_replace('::VAK::', $vak, "<TABLE border=0 cellspacing=0 cellpadding=0><TR>::VAK::</TR></table>");

		return $vak;
	}
}
