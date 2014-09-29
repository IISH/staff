<?php 

require_once("./classes/class_view/fieldtypes/class_field.inc.php");

// TODOEXPLAIN
class class_field_bit extends class_field {
	protected $m_show_different_values;
	protected $m_different_true_value;
	protected $m_different_false_value;

	// TODOEXPLAIN
	function __construct($fieldSettings) {
		parent::__construct($fieldSettings);

		$this->m_show_different_values = false;
		$this->m_different_true_value = '';
		$this->m_different_false_value = '';

		if ( is_array( $fieldSettings ) ) {
			foreach ( $fieldSettings as $field => $setting ) {
				switch ($field) {
					// only bit specific parameters

					case "show_different_values":
						$this->m_show_different_values = $setting;
						break;
					case "different_true_value":
						$this->m_different_true_value = $setting;
						break;
					case "different_false_value":
						$this->m_different_false_value = $setting;
						break;

				}
			}
		}
	}

	// TODOEXPLAIN
	function get_value($row) {
		$retval = parent::get_value($row);

		if ( $this->get_show_different_values() == true || $this->get_show_different_values() == 1 ) {
			if ( $retval == "1" || $retval == true || $retval == "on" ) {
				$retval = $this->get_different_true_value();
			} else {
				$retval = $this->get_different_false_value();
			}
		}

		return $retval;
	}

	// TODOEXPLAIN
	function get_show_different_values() {
		return $this->m_show_different_values;
	}

	// TODOEXPLAIN
	function get_different_true_value() {
		return $this->m_different_true_value;
	}

	// TODOEXPLAIN
	function get_different_false_value() {
		return $this->m_different_false_value;
	}

}
