<?php 
/**
 * Class for loading and getting settings from the database
 */
class Settings {
	private static $is_loaded = false;
	private static $settings = null;
	private static $settings_table = 'Staff_settings';

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
				$arr[ $row["property"] ] = $row["value"];
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
	public static function get($setting_name) {
		if ( !self::$is_loaded ) {
			self::load();
		}

		$value = self::$settings[$setting_name];

		return $value;
	}

	public static function save( $setting_name, $value, $settingsTable = '' ) {
		global $databases;

		$setting_name = trim($setting_name);

		$settingsTable = trim($settingsTable);

		if ( $settingsTable == '' ) {
			$settingsTable = self::$settings_table;
		}

		if ( $setting_name != '' ) {
			$oConn = new class_mysql($databases['default']);
			$oConn->connect();

			$result = mysql_query("SELECT * FROM $settingsTable WHERE property='" . $setting_name . "' ");
			$num_rows = mysql_num_rows($result);

			if ($num_rows > 0) {
				$result = mysql_query("UPDATE $settingsTable SET value='" . addslashes($value) . "' WHERE property='" . $setting_name . "' ", $oConn->getConnection());
			}
			else {
				$result = mysql_query("INSERT INTO $settingsTable (value, property) VALUES ( '" . addslashes($value) . "', '" . $setting_name . "' ) ", $oConn->getConnection());
			}
		}
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}