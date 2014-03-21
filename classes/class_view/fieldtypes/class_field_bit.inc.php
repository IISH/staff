<?php 
// version: 2014-01-20

require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_bit extends class_field {
	var $m_show_different_values;
	var $m_different_true_value;
	var $m_different_false_value;

	// TODOEXPLAIN
	function class_field_bit($settings) {
		parent::class_field($settings);

		$this->m_show_different_values = false;
		$this->m_different_true_value = '';
		$this->m_different_false_value = '';

		if ( is_array( $settings ) ) {
			foreach ( $settings as $field => $value ) {
				switch ($field) {
					// only bit specific parameters

					case "show_different_values":
						$this->m_show_different_values = $settings["show_different_values"];
						break;
					case "different_true_value":
						$this->m_different_true_value = $settings["different_true_value"];
						break;
					case "different_false_value":
						$this->m_different_false_value = $settings["different_false_value"];
						break;

				}
			}
		}
	}

	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {
		$retval = parent::view_field($row, $criteriumResult);

		if ( $criteriumResult["fieldname"] == "-novalue-" ) {
			$retval = '';
		} else {
			if ( $this->get_show_different_values() == true || $this->get_show_different_values() == 1 ) {
				if ( $retval == "1" || $retval == true || $retval == "on" ) {
					$retval = $this->get_different_true_value();
				} else {
					$retval = $this->get_different_false_value();
				}
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
?>