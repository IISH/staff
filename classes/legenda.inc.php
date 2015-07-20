<?php
class Legenda {
	private static $settings = null;

	/**
	 * Load the settings from the database
	 */
	public function __construct() {
		global $databases, $oWebuser;

		$language = getLanguage();

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$arr = array();

		$prefix = Settings::get('protime_tables_prefix');
		$min_minutes = 120;

/*
		$query = "
SELECT ID, ABSENCE, SHORT_" . $language . ", CODE, background_color, font_color, everyone, show_always, substitute_" . $language . "
FROM ${prefix}ABSENCE
	RIGHT JOIN Staff_colors ON ${prefix}ABSENCE.CODE = Staff_colors.absence_code
WHERE ABSENCE IN (
	SELECT ABSENCE FROM ${prefix}P_ABSENCE
	WHERE BOOKDATE LIKE '" . date("Y") . "%'
		AND ${prefix}P_ABSENCE.ABSENCE NOT IN (5, 19)
		AND ( ${prefix}P_ABSENCE.ABSENCE_VALUE>=" . $min_minutes . " OR ${prefix}P_ABSENCE.ABSENCE_VALUE=0 )
	GROUP BY ABSENCE
)
OR show_always = 1
*/
		$query = "
SELECT DISTINCT ID, ${prefix}ABSENCE.ABSENCE, SHORT_" . $language . ", CODE, background_color, font_color, everyone, show_always, substitute_" . $language . "
FROM ${prefix}ABSENCE
	RIGHT JOIN Staff_colors ON ${prefix}ABSENCE.CODE = Staff_colors.absence_code
	LEFT JOIN ${prefix}P_ABSENCE ON ${prefix}ABSENCE.ABSENCE = ${prefix}P_ABSENCE.ABSENCE
WHERE ( BOOKDATE LIKE '" . date("Y") . "%' AND ${prefix}P_ABSENCE.ABSENCE NOT IN (5, 19) AND ( ${prefix}P_ABSENCE.ABSENCE_VALUE>=" . $min_minutes . " OR ${prefix}P_ABSENCE.ABSENCE_VALUE=0 ) ) OR show_always = 1
";

		//
		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$desc = trim($row["substitute_" . getLanguage()]);
				if ( $desc == '' ) {
					$desc = trim($row["SHORT_" . getLanguage()]);
				}

				$arr[ $row["ID"] ] = new class_color( $row["ABSENCE"], $desc, $row["CODE"], $row["background_color"], $row["font_color"], $row["everyone"] );
			}
			mysql_free_result($result);

		}

		$this->settings = $arr;
	}

	public function getAll() {
		return $this->settings;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
