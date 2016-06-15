<?php 
/**
 * Class for settings syncinfo
 */
class SyncInfo {
	private static $settings_table = 'Staff_syncinfo';

	public static function save( $setting_name, $type, $value ) {
		global $databases, $dbConn;

		$settingsTable = self::$settings_table;

		if ( $setting_name != '' ) {
			$query = "SELECT * FROM $settingsTable WHERE property='" . $setting_name . "' ";
			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
			if ( $row = $stmt->fetch() ) {
				$query = "UPDATE $settingsTable SET $type='" . addslashes($value) . "' WHERE property='" . $setting_name . "' ";
				$stmt = $dbConn->getConnection()->prepare($query);
				$stmt->execute();
			}
			else {
				$query = "INSERT INTO $settingsTable (property, $type) VALUES ( '" . $setting_name . "', '" . addslashes($value) . "' ) ";
				$stmt = $dbConn->getConnection()->prepare($query);
				$stmt->execute();
			}
		}
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
