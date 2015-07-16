<?php 
/**
 * Class for loading and getting translations from the database
 * @version 0.1 2015-07-12
 */
class class_allowed_visible_absences {
	private static $is_loaded = false;
	private static $settings = null;
	private static $settings_table = 'Staff_allowed_visible_absences';

	/**
	 * Load the settings from the database
	 */
	private static function load() {
		global $databases;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$arr = array();

		$result = mysql_query('SELECT * FROM ' . self::$settings_table, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[] = $row["absence"];
			}
			mysql_free_result($result);

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

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
