<?php
class Legenda {
	private static $is_loaded = false;
	private static $settings = null;

	/**
	 * Load the settings from the database
	 */
	public function __construct() {
		global $dbConn;
		$language = getLanguage();

		$arr = array();

		$min_minutes = 120;

		$query = "
SELECT DISTINCT ID, protime_absence.ABSENCE, SHORT_" . $language . ", CODE, background_color, font_color, everyone, show_always, substitute_" . $language . "
FROM protime_absence
	RIGHT JOIN staff_colors ON protime_absence.CODE = staff_colors.absence_code
	LEFT JOIN protime_p_absence ON protime_absence.ABSENCE = protime_p_absence.ABSENCE
WHERE ( BOOKDATE LIKE '" . date("Y") . "%' AND protime_p_absence.ABSENCE NOT IN (5, 19) AND ( protime_p_absence.ABSENCE_VALUE>=" . $min_minutes . " OR protime_p_absence.ABSENCE_VALUE=0 ) ) OR show_always = 1
";

		//
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$desc = trim($row["substitute_" . getLanguage()]);
			if ( $desc == '' ) {
				$desc = trim($row["SHORT_" . getLanguage()]);
			}

			$arr[ $row["ID"] ] = new class_color( $row["ABSENCE"], $desc, $row["CODE"], $row["background_color"], $row["font_color"], $row["everyone"] );
		}

		self::$settings = $arr;
		self::$is_loaded = true;
	}

	public function getAll() {
		if ( !self::$is_loaded ) {
			self::load();
		}

		return self::$settings;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
