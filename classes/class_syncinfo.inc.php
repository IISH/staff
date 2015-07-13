<?php 
/**
 * Class for settings syncinfo
 * @version 0.1 2015-07-02
 */
class class_syncinfo {
	private static $settings_table = 'Staff_syncinfo';

	public static function save( $setting_name, $type, $value ) {
		global $databases;

		$settingsTable = self::$settings_table;

		if ( $setting_name != '' ) {
			$oConn = new class_mysql($databases['default']);
			$oConn->connect();

			$result = mysql_query("SELECT * FROM $settingsTable WHERE property='" . $setting_name . "' ");
			$num_rows = mysql_num_rows($result);

			if ($num_rows > 0) {
				$result = mysql_query("UPDATE $settingsTable SET $type='" . addslashes($value) . "' WHERE property='" . $setting_name . "' ", $oConn->getConnection());
			}
			else {
				$result = mysql_query("INSERT INTO $settingsTable (property, $type) VALUES ( '" . $setting_name . "', '" . addslashes($value) . "' ) ", $oConn->getConnection());
			}
		}
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
