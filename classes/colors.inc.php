<?php
/**
 * Class for loading and getting colors from the database
 */
class class_colors {
	private static $is_loaded = false;
	private static $settings = null;

	/**
	 * Load the settings from the database
	 */
	private static function load() {
		global $dbConn;

		$language = getLanguage();

		$arr = array();

		$prefix = Settings::get('protime_tables_prefix');

		$query = "
SELECT ID, absence_code, ABSENCE, SHORT_" . $language . ", CODE, background_color, font_color, everyone, show_always, substitute_" . $language . "
FROM ${prefix}ABSENCE
	RIGHT JOIN Staff_colors ON ${prefix}ABSENCE.CODE = Staff_colors.absence_code
";

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {

				$desc = trim($row["substitute_" . getLanguage()]);
				if ( $desc == '' ) {
					$desc = trim($row["SHORT_" . getLanguage()]);
				}

				$arr[ $row["absence_code"] ] = new class_color( $row["ABSENCE"], $desc, $row["absence_code"], $row["background_color"], $row["font_color"], $row["everyone"] );
			}

		self::$settings = $arr;
		self::$is_loaded = true;
	}

	/**
	 * Return the value of the setting
	 *
	 * @param string $setting_name The name of the setting
	 * @return string The value of the setting
	 */
	public static function get($setting_name) {
		if ( !self::$is_loaded ) {
			self::load();
		}

		$value = self::$settings[$setting_name];

		return $value;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
