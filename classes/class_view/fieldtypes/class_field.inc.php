<?php 

require_once("./classes/class_misc.inc.php");

// TODOEXPLAIN
class class_field {
	protected $oClassMisc;
	protected $m_fieldname;
	protected $m_fieldlabel;

	// TODOEXPLAIN
	function __construct($fieldSettings) {
		$this->oClassMisc = new class_misc();
		$this->m_fieldname = '';
		$this->m_fieldlabel = '';

		if ( is_array( $fieldSettings ) ) {
			foreach ( $fieldSettings as $field => $setting ) {
				switch ($field) {
					case "fieldname":
						$this->m_fieldname = $setting;
						break;
					case "fieldlabel":
						$this->m_fieldlabel = $setting;
						break;
				}
			}
		}
	}

	// TODOEXPLAIN
	function get_fieldname() {
		return $this->m_fieldname;
	}

	// TODOEXPLAIN
	function get_fieldlabel() {
		return $this->m_fieldlabel;
	}

	// TODOEXPLAIN
	function get_value($row) {
		return stripslashes($row[$this->get_fieldname()]);
	}
}
