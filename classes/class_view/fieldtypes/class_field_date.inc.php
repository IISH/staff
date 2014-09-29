<?php 

require_once("./classes/class_view/fieldtypes/class_field.inc.php");

// TODOEXPLAIN
class class_field_date extends class_field {
	protected $m_format;

	// TODOEXPLAIN
	function __construct($fieldSettings) {
		parent::__construct($fieldSettings);

		$this->m_format = '';

		if ( is_array( $fieldSettings ) ) {
			foreach ( $fieldSettings as $field => $setting ) {
				switch ($field) {
					// only string specific parameters

					case "format":
						$this->m_format = $setting;
						break;

				}
			}
		}

	}

	// TODOEXPLAIN
	function get_value($row) {
		$retval = parent::get_value($row);

		if ( $retval != '' ) {
			// verwijder tijd uit datum
			$retval = trim(str_replace('12:00:00:000AM', '', $retval));
			$retval = trim(str_replace('12:00AM', '', $retval));

			if ( $this->m_format != '' ) {
				$retval = date($this->m_format, strtotime($retval));
			}
		}

		return $retval;
	}

}
