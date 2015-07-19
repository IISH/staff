<?php 

require_once("./classes/misc.inc.php");

class Field {
	protected $oClassMisc;
	protected $m_fieldname;
	protected $m_fieldlabel;

	function __construct($fieldSettings) {
		$this->oClassMisc = new Misc();
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

	public function getFieldname() {
		return $this->m_fieldname;
	}

	public function getFieldlabel() {
		return $this->m_fieldlabel;
	}

	public function getValue($row) {
		return stripslashes($row[$this->getFieldname()]);
	}
}
