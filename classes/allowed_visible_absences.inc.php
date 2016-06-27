<?php 
/**
 * Class for loading and getting absence colors from the database
 */
class ForEveryoneVisibleAbsences {
	private static $is_loaded = false;
	private static $settings = null;

	/**
	 * Load the settings from the database
	 */
	private static function load() {
		global $databases, $dbConn;

		$arr = array();

		//
		$query = 'SELECT * FROM staff_colors WHERE everyone=1 ';
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = $row["absence_code"];
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
	public static function in_array($setting_name) {
		if ( !self::$is_loaded ) {
			self::load();
		}

		return in_array($setting_name, self::$settings);
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
